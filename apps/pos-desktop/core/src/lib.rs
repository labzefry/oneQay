// Author by Lab | zefry
use rusqlite::{params, Connection, OptionalExtension};
use serde::{Deserialize, Serialize};
use serde_json::{json, Value};
use std::path::Path;
use uuid::Uuid;

pub const MAX_MONEY: i64 = 9_007_199_254_740_991;
#[derive(Debug, thiserror::Error)]
pub enum Error {
    #[error("POS_DATABASE: {0}")]
    Database(#[from] rusqlite::Error),
    #[error("POS_DATA: {0}")]
    Data(#[from] serde_json::Error),
    #[error("{0}")]
    Rule(&'static str),
}
pub type Result<T> = std::result::Result<T, Error>;
#[derive(Clone, Debug, Serialize, Deserialize, PartialEq, Eq)]
pub struct Scope {
    pub tenant_id: String,
    pub identity_id: String,
    pub organization_id: String,
    pub outlet_id: String,
    pub device_id: String,
}
#[derive(Clone, Debug, Serialize, Deserialize)]
pub struct Product {
    pub id: String,
    pub name: String,
    pub price: i64,
    pub currency: String,
    pub scale: u8,
    pub stock: i64,
    #[serde(default)]
    pub barcode: String,
    #[serde(default)]
    pub category: String,
}
#[derive(Clone, Debug, Serialize, Deserialize)]
pub struct Snapshot {
    pub lease_id: String,
    pub scope: Scope,
    pub shift_id: String,
    pub issued_at: i64,
    pub expires_at: i64,
    pub server_time: i64,
    pub catalog_revision: String,
    #[serde(default)]
    pub business_name: String,
    #[serde(default)]
    pub currency_symbol: String,
    #[serde(default)]
    pub can_count: bool,
    pub catalog: Vec<Product>,
}
#[derive(Clone, Debug, Serialize, Deserialize)]
pub struct Line {
    pub product_id: String,
    pub quantity: i64,
}
#[derive(Clone, Debug, Serialize, Deserialize)]
pub struct Operation {
    pub created_at: i64,
    pub kind: String,
    pub lease_id: String,
    pub lines: Vec<Line>,
    pub operation_id: String,
    pub tendered: i64,
}
#[derive(Clone, Debug, Serialize, Deserialize)]
pub struct OperationView {
    pub operation: Operation,
    pub state: String,
    pub total: i64,
    pub result: Option<Value>,
    pub receipt: Value,
}
#[derive(Clone, Debug, Serialize, Deserialize)]
pub struct Workspace {
    pub snapshot: Option<Snapshot>,
    pub products: Vec<Product>,
    pub operations: Vec<OperationView>,
    pub settings: Value,
    pub last_server_at: i64,
    pub pending: i64,
    pub review: i64,
    pub authorized: bool,
    pub lease_remaining: i64,
}
#[derive(Clone, Debug, Serialize, Deserialize)]
pub struct PrinterSettings {
    pub transport: String,
    pub queue: String,
    pub host: String,
    pub port: u16,
    pub paper_mm: u16,
    pub drawer_enabled: bool,
    pub auto_print: bool,
}
impl Default for PrinterSettings {
    fn default() -> Self {
        Self {
            transport: "windows".into(),
            queue: String::new(),
            host: String::new(),
            port: 9100,
            paper_mm: 80,
            drawer_enabled: false,
            auto_print: false,
        }
    }
}
pub struct Store {
    db: Connection,
}
impl Store {
    pub fn open(path: impl AsRef<Path>) -> Result<Self> {
        let db = Connection::open(path)?;
        db.busy_timeout(std::time::Duration::from_secs(5))?;
        db.execute_batch("PRAGMA journal_mode=WAL;PRAGMA synchronous=FULL;PRAGMA foreign_keys=ON;CREATE TABLE IF NOT EXISTS metadata(key TEXT PRIMARY KEY,value TEXT NOT NULL);CREATE TABLE IF NOT EXISTS catalog(id TEXT PRIMARY KEY,payload TEXT NOT NULL,available INTEGER NOT NULL CHECK(available>=0));CREATE TABLE IF NOT EXISTS operations(seq INTEGER PRIMARY KEY AUTOINCREMENT,id TEXT NOT NULL UNIQUE,payload TEXT NOT NULL,state TEXT NOT NULL CHECK(state IN ('pending','accepted','conflict','rejected','review')),total INTEGER NOT NULL CHECK(total>=0),receipt TEXT NOT NULL,result TEXT);CREATE TABLE IF NOT EXISTS print_jobs(id TEXT PRIMARY KEY,operation_id TEXT NOT NULL,receipt TEXT NOT NULL,state TEXT NOT NULL CHECK(state IN ('queued','printing','done','uncertain')),attempts INTEGER NOT NULL DEFAULT 0,last_code TEXT NOT NULL DEFAULT '',FOREIGN KEY(operation_id) REFERENCES operations(id));PRAGMA user_version=1;")?;
        // A crash after the spooler accepted bytes may already have printed. Never duplicate automatically.
        db.execute("UPDATE print_jobs SET state='uncertain',last_code='PRINT_INTERRUPTED' WHERE state='printing'",[])?;
        Ok(Self { db })
    }
    pub fn get(&self, key: &str) -> Result<Option<String>> {
        Ok(self
            .db
            .query_row("SELECT value FROM metadata WHERE key=?", [key], |r| {
                r.get(0)
            })
            .optional()?)
    }
    pub fn set(&self, key: &str, value: &str) -> Result<()> {
        self.db.execute("INSERT INTO metadata(key,value) VALUES(?,?) ON CONFLICT(key) DO UPDATE SET value=excluded.value",params![key,value])?;
        Ok(())
    }
    pub fn snapshot(&self) -> Result<Option<Snapshot>> {
        self.get("snapshot")?
            .map(|v| serde_json::from_str(&v).map_err(Error::from))
            .transpose()
    }
    pub fn apply_snapshot(&mut self, snapshot: &Snapshot, local_now: i64) -> Result<()> {
        if snapshot.catalog.is_empty()
            || snapshot.catalog.len() > 20000
            || snapshot.expires_at <= snapshot.issued_at
            || snapshot.expires_at - snapshot.issued_at > 28800
            || snapshot.server_time < snapshot.issued_at
            || snapshot.server_time > snapshot.expires_at
            || snapshot.lease_id.len() != 32
            || snapshot.shift_id.is_empty()
        {
            return Err(Error::Rule("POS_SNAPSHOT_INVALID"));
        }
        if let Some(old) = self.snapshot()? {
            if old.scope != snapshot.scope {
                return Err(Error::Rule(
                    "POS_ACCOUNT_CHANGE_REQUIRES_NEW_WINDOWS_PROFILE",
                ));
            }
        }
        let mut ids = std::collections::HashSet::new();
        let first = &snapshot.catalog[0];
        for p in &snapshot.catalog {
            if p.price < 0
                || p.price > MAX_MONEY
                || p.stock < 0
                || p.stock > 1_000_000_000
                || p.scale > 6
                || p.currency.len() != 3
                || !p.currency.bytes().all(|c| c.is_ascii_uppercase())
                || p.currency != first.currency
                || p.scale != first.scale
                || p.id.is_empty()
                || !ids.insert(&p.id)
            {
                return Err(Error::Rule("POS_CATALOG_INVALID"));
            }
        }
        let unresolved = self.unresolved_sales()?;
        let tx = self.db.transaction()?;
        tx.execute("DELETE FROM catalog", [])?;
        for p in &snapshot.catalog {
            let reserved: i64 = unresolved
                .iter()
                .flat_map(|o| &o.lines)
                .filter(|l| l.product_id == p.id)
                .map(|l| l.quantity)
                .sum();
            tx.execute(
                "INSERT INTO catalog(id,payload,available) VALUES(?,?,?)",
                params![p.id, serde_json::to_string(p)?, (p.stock - reserved).max(0)],
            )?;
        }
        for (k, v) in [
            ("authority_state", "active".to_string()),
            ("snapshot", serde_json::to_string(snapshot)?),
            ("last_server_at", snapshot.server_time.to_string()),
            ("clock_anchor", local_now.to_string()),
            ("clock_high_water", local_now.to_string()),
        ] {
            tx.execute("INSERT INTO metadata(key,value) VALUES(?,?) ON CONFLICT(key) DO UPDATE SET value=excluded.value",params![k,v])?;
        }
        tx.commit()?;
        Ok(())
    }
    pub fn unresolved_sales(&self) -> Result<Vec<Operation>> {
        let mut q=self.db.prepare("SELECT payload FROM operations WHERE state!='accepted' AND json_extract(payload,'$.kind')='sale'")?;
        let rows = q.query_map([], |r| r.get::<_, String>(0))?;
        let mut out = Vec::new();
        for row in rows {
            out.push(serde_json::from_str(&row?)?)
        }
        Ok(out)
    }
    pub fn products(&self) -> Result<Vec<Product>> {
        let mut q = self
            .db
            .prepare("SELECT payload,available FROM catalog ORDER BY id")?;
        let rows = q.query_map([], |r| Ok((r.get::<_, String>(0)?, r.get::<_, i64>(1)?)))?;
        let mut result = Vec::new();
        for row in rows {
            let (raw, available) = row?;
            let mut p: Product = serde_json::from_str(&raw)?;
            p.stock = available;
            result.push(p);
        }
        Ok(result)
    }
    pub fn operations(&self) -> Result<Vec<OperationView>> {
        let mut q=self.db.prepare("SELECT payload,state,total,result,receipt FROM operations ORDER BY seq DESC LIMIT 1000")?;
        let rows = q.query_map([], |r| {
            Ok((
                r.get::<_, String>(0)?,
                r.get::<_, String>(1)?,
                r.get::<_, i64>(2)?,
                r.get::<_, Option<String>>(3)?,
                r.get::<_, String>(4)?,
            ))
        })?;
        let mut out = Vec::new();
        for row in rows {
            let (p, state, total, result, receipt) = row?;
            out.push(OperationView {
                operation: serde_json::from_str(&p)?,
                state,
                total,
                result: result.map(|s| serde_json::from_str(&s)).transpose()?,
                receipt: serde_json::from_str(&receipt)?,
            });
        }
        Ok(out)
    }
    /// ID is generated before the durable mutation by the UI and reused after ambiguous command failures.
    pub fn commit(
        &mut self,
        id: &str,
        kind: &str,
        lines: Vec<Line>,
        tendered: i64,
        local_now: i64,
    ) -> Result<OperationView> {
        if !id.starts_with("desk-")
            || Uuid::parse_str(&id[5..]).is_err()
            || !matches!(kind, "sale" | "count")
            || lines.is_empty()
            || lines.len() > 100
            || tendered < 0
            || tendered > MAX_MONEY
        {
            return Err(Error::Rule("POS_INPUT_INVALID"));
        }
        let mut seen = std::collections::HashSet::new();
        for l in &lines {
            if l.quantity < (if kind == "sale" { 1 } else { 0 })
                || l.quantity > 1_000_000
                || !seen.insert(&l.product_id)
            {
                return Err(Error::Rule("POS_QUANTITY_INVALID"));
            }
        }
        let existing = self
            .db
            .query_row("SELECT payload FROM operations WHERE id=?", [id], |r| {
                r.get::<_, String>(0)
            })
            .optional()?;
        if let Some(raw) = existing {
            let op: Operation = serde_json::from_str(&raw)?;
            if op.kind != kind
                || serde_json::to_string(&op.lines)? != serde_json::to_string(&lines)?
                || op.tendered != tendered
            {
                return Err(Error::Rule("POS_OPERATION_CHANGED"));
            }
            return self.operation(id);
        }
        if self.get("authority_state")?.as_deref() == Some("blocked") {
            return Err(Error::Rule("POS_SIGN_IN_AGAIN"));
        }
        let s = self.snapshot()?.ok_or(Error::Rule("POS_CONNECT_FIRST"))?;
        if kind == "count" && !s.can_count {
            return Err(Error::Rule("POS_INVENTORY_PERMISSION_REQUIRED"));
        }
        let anchor: i64 = self
            .get("clock_anchor")?
            .and_then(|x| x.parse().ok())
            .ok_or(Error::Rule("POS_CLOCK_INVALID"))?;
        let high: i64 = self
            .get("clock_high_water")?
            .and_then(|x| x.parse().ok())
            .unwrap_or(anchor);
        if local_now < high - 60 {
            return Err(Error::Rule("POS_CLOCK_MOVED_BACKWARD"));
        }
        let elapsed = local_now
            .checked_sub(anchor)
            .ok_or(Error::Rule("POS_CLOCK_INVALID"))?;
        let created_at = s
            .server_time
            .checked_add(elapsed)
            .ok_or(Error::Rule("POS_CLOCK_INVALID"))?;
        if elapsed < 0 || created_at > s.expires_at {
            return Err(Error::Rule("POS_OFFLINE_LEASE_EXPIRED"));
        }
        let products = self.products()?;
        let mut total = 0i64;
        let mut receipt_lines = Vec::new();
        for l in &lines {
            let p = products
                .iter()
                .find(|p| p.id == l.product_id)
                .ok_or(Error::Rule("POS_PRODUCT_UNAVAILABLE"))?;
            if kind == "sale" && p.stock < l.quantity {
                return Err(Error::Rule("POS_LOCAL_STOCK_INSUFFICIENT"));
            }
            let amount = p
                .price
                .checked_mul(l.quantity)
                .and_then(|a| total.checked_add(a))
                .filter(|v| *v <= MAX_MONEY)
                .ok_or(Error::Rule("POS_AMOUNT_TOO_LARGE"))?;
            total = amount;
            receipt_lines.push(json!({"name":p.name,"price":p.price,"quantity":l.quantity}));
        }
        if kind == "sale" && tendered < total {
            return Err(Error::Rule("POS_CASH_INSUFFICIENT"));
        }
        if kind == "count" {
            total = 0;
        }
        let op = Operation {
            created_at,
            kind: kind.into(),
            lease_id: s.lease_id.clone(),
            lines,
            operation_id: id.into(),
            tendered,
        };
        let receipt = json!({"reference":id,"provisional":true,"kind":kind,"created_at":created_at,"outlet":s.scope.outlet_id,"device":s.scope.device_id,"currency":s.catalog[0].currency,"scale":s.catalog[0].scale,"lines":receipt_lines,"total":total,"tendered":tendered,"change":if kind=="sale"{tendered-total}else{0}});
        let tx = self.db.transaction()?;
        tx.execute(
            "INSERT INTO operations(id,payload,state,total,receipt) VALUES(?,?,'pending',?,?)",
            params![
                id,
                serde_json::to_string(&op)?,
                total,
                serde_json::to_string(&receipt)?
            ],
        )?;
        if kind == "sale" {
            for l in &op.lines {
                if tx.execute(
                    "UPDATE catalog SET available=available-? WHERE id=? AND available>=?",
                    params![l.quantity, l.product_id, l.quantity],
                )? != 1
                {
                    return Err(Error::Rule("POS_LOCAL_STOCK_INSUFFICIENT"));
                }
            }
            tx.execute(
                "INSERT INTO print_jobs(id,operation_id,receipt,state) VALUES(?,?,?,'queued')",
                params![
                    Uuid::new_v4().to_string(),
                    id,
                    serde_json::to_string(&receipt)?
                ],
            )?;
        }
        tx.execute("INSERT INTO metadata(key,value) VALUES('clock_high_water',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value",[local_now.to_string()])?;
        tx.commit()?;
        self.operation(id)
    }
    pub fn operation(&self, id: &str) -> Result<OperationView> {
        let row = self
            .db
            .query_row(
                "SELECT payload,state,total,result,receipt FROM operations WHERE id=?",
                [id],
                |r| {
                    Ok((
                        r.get::<_, String>(0)?,
                        r.get::<_, String>(1)?,
                        r.get::<_, i64>(2)?,
                        r.get::<_, Option<String>>(3)?,
                        r.get::<_, String>(4)?,
                    ))
                },
            )
            .optional()?
            .ok_or(Error::Rule("POS_RECEIPT_NOT_FOUND"))?;
        Ok(OperationView {
            operation: serde_json::from_str(&row.0)?,
            state: row.1,
            total: row.2,
            result: row.3.map(|s| serde_json::from_str(&s)).transpose()?,
            receipt: serde_json::from_str(&row.4)?,
        })
    }
    pub fn pending(&self) -> Result<Vec<Operation>> {
        let mut q = self.db.prepare(
            "SELECT payload FROM operations WHERE state='pending' ORDER BY seq LIMIT 50",
        )?;
        let rows = q.query_map([], |r| r.get::<_, String>(0))?;
        let mut out = Vec::new();
        for row in rows {
            out.push(serde_json::from_str(&row?)?)
        }
        Ok(out)
    }
    pub fn acknowledge(&mut self, results: &[Value]) -> Result<()> {
        let tx = self.db.transaction()?;
        for r in results {
            let id = r["operation_id"]
                .as_str()
                .ok_or(Error::Rule("POS_SYNC_RESPONSE_INVALID"))?;
            let state = r["state"]
                .as_str()
                .ok_or(Error::Rule("POS_SYNC_RESPONSE_INVALID"))?;
            if !matches!(state, "accepted" | "conflict" | "rejected" | "review")
                || r["code"].as_str().is_none()
                || (state == "accepted" && r["sale_id"].as_str().is_none())
            {
                return Err(Error::Rule("POS_SYNC_RESPONSE_INVALID"));
            }
            tx.execute(
                "UPDATE operations SET state=?,result=? WHERE id=? AND state='pending'",
                params![state, serde_json::to_string(r)?, id],
            )?;
        }
        tx.commit()?;
        Ok(())
    }
    pub fn workspace(&self) -> Result<Workspace> {
        let operations = self.operations()?;
        let pending = self.db.query_row(
            "SELECT COUNT(*) FROM operations WHERE state='pending'",
            [],
            |r| r.get::<_, i64>(0),
        )?;
        let review = self.db.query_row(
            "SELECT COUNT(*) FROM operations WHERE state IN ('conflict','rejected','review')",
            [],
            |r| r.get::<_, i64>(0),
        )?;
        Ok(Workspace {
            snapshot: self.snapshot()?,
            products: self.products()?,
            operations,
            settings: self
                .get("printer")?
                .map(|s| serde_json::from_str(&s))
                .transpose()?
                .unwrap_or(serde_json::to_value(PrinterSettings::default())?),
            authorized: self.get("authority_state")?.as_deref() != Some("blocked"),
            lease_remaining: self.lease_remaining()?,
            last_server_at: self
                .get("last_server_at")?
                .and_then(|v| v.parse().ok())
                .unwrap_or(0),
            pending,
            review,
        })
    }
    pub fn lease_remaining(&self) -> Result<i64> {
        let s = match self.snapshot()? {
            Some(s) => s,
            None => return Ok(0),
        };
        let anchor: i64 = self
            .get("clock_anchor")?
            .and_then(|s| s.parse().ok())
            .unwrap_or(0);
        let now = std::time::SystemTime::now()
            .duration_since(std::time::UNIX_EPOCH)
            .map(|t| t.as_secs() as i64)
            .unwrap_or(0);
        if now < anchor {
            return Ok(0);
        }
        Ok((s.expires_at - s.server_time - (now - anchor)).max(0))
    }
    pub fn printer(&self) -> Result<PrinterSettings> {
        self.get("printer")?
            .map(|v| serde_json::from_str(&v).map_err(Error::from))
            .unwrap_or(Ok(PrinterSettings::default()))
    }
    pub fn save_printer(&self, p: &PrinterSettings) -> Result<()> {
        if !matches!(p.transport.as_str(), "windows" | "network")
            || !matches!(p.paper_mm, 58 | 80)
            || p.port == 0
            || p.queue.len() > 256
            || p.host.len() > 253
            || p.queue.chars().any(char::is_control)
            || p.host.chars().any(char::is_control)
        {
            return Err(Error::Rule("POS_PRINTER_INVALID"));
        }
        self.set("printer", &serde_json::to_string(p)?)
    }
    pub fn claim_print(&mut self, id: &str, duplicate: bool) -> Result<(String, Value)> {
        let operation = self.operation(id)?;
        if operation.operation.kind != "sale" {
            return Err(Error::Rule("PRINT_SALE_REQUIRED"));
        }
        let tx = self.db.transaction()?;
        let job = if duplicate {
            let job = Uuid::new_v4().to_string();
            let mut receipt = operation.receipt;
            receipt["duplicate"] = json!(true);
            receipt["provisional"] = json!(operation.state != "accepted");
            if let Some(r) = operation.result {
                receipt["server_reference"] = r["sale_id"].clone();
            }
            tx.execute(
                "INSERT INTO print_jobs(id,operation_id,receipt,state) VALUES(?,?,?,'queued')",
                params![job, id, serde_json::to_string(&receipt)?],
            )?;
            job
        } else {
            tx.query_row("SELECT id FROM print_jobs WHERE operation_id=? AND state='queued' ORDER BY rowid LIMIT 1",[id],|r|r.get::<_,String>(0)).optional()?.ok_or(Error::Rule("PRINT_REPRINT_CONFIRMATION_REQUIRED"))?
        };
        tx.execute("UPDATE print_jobs SET state='printing',attempts=attempts+1 WHERE id=? AND state='queued'",[&job])?;
        let raw = tx.query_row("SELECT receipt FROM print_jobs WHERE id=?", [&job], |r| {
            r.get::<_, String>(0)
        })?;
        tx.commit()?;
        let mut receipt: Value = serde_json::from_str(&raw)?;
        receipt["provisional"] = json!(operation.state != "accepted");
        if let Some(result) = self.operation(id)?.result {
            receipt["server_reference"] = result["sale_id"].clone();
        }
        Ok((job, receipt))
    }
    pub fn finish_print(&self, job: &str, success: bool) -> Result<()> {
        self.db.execute(
            "UPDATE print_jobs SET state=?,last_code=? WHERE id=? AND state='printing'",
            params![
                if success { "done" } else { "uncertain" },
                if success {
                    "PRINT_SENT"
                } else {
                    "PRINT_CHECK_PAPER_OR_CONNECTION"
                },
                job
            ],
        )?;
        Ok(())
    }
}
/// No arbitrary ESC/POS data comes from the webview. Control characters in names are stripped.
pub fn receipt_bytes(r: &Value, paper_mm: u16) -> Vec<u8> {
    let width = if paper_mm == 58 { 32 } else { 48 };
    let clean = |s: &str| {
        s.chars()
            .filter(|c| c.is_ascii() && !c.is_ascii_control())
            .collect::<String>()
    };
    let mut body = String::from("oneQay POS\n");
    if r["duplicate"] == true {
        body.push_str("SALINAN / DUPLICATE\n")
    };
    if r["provisional"] == true {
        body.push_str("LOKAL - MENUNGGU SINKRONISASI\n")
    };
    body.push_str(&format!(
        "{}\n{}\n",
        clean(r["reference"].as_str().unwrap_or("")),
        "-".repeat(width)
    ));
    if let Some(reference) = r["server_reference"].as_str() {
        body.push_str(&format!("SERVER: {}\n", clean(reference)));
    }
    if let Some(lines) = r["lines"].as_array() {
        for l in lines {
            body.push_str(&format!(
                "{}\n{} x {}\n",
                clean(l["name"].as_str().unwrap_or("Produk")),
                l["quantity"],
                l["price"]
            ))
        }
    }
    body.push_str(&format!(
        "{}\nTOTAL {} {}\nTUNAI {}\nKEMBALI {}\n\nTerima kasih / Thank you\n\n\n",
        "-".repeat(width),
        r["currency"].as_str().unwrap_or(""),
        display_money(
            r["total"].as_i64().unwrap_or(0),
            r["scale"].as_u64().unwrap_or(0) as u32
        ),
        display_money(
            r["tendered"].as_i64().unwrap_or(0),
            r["scale"].as_u64().unwrap_or(0) as u32
        ),
        display_money(
            r["change"].as_i64().unwrap_or(0),
            r["scale"].as_u64().unwrap_or(0) as u32
        )
    ));
    let mut out = vec![0x1b, 0x40];
    out.extend_from_slice(body.as_bytes());
    out.extend_from_slice(&[0x1d, 0x56, 0x42, 0]);
    out
}
fn display_money(value: i64, scale: u32) -> String {
    let divisor = 10_i64.pow(scale.min(6));
    if scale == 0 {
        value.to_string()
    } else {
        format!(
            "{}.{:0width$}",
            value / divisor,
            value % divisor,
            width = scale as usize
        )
    }
}

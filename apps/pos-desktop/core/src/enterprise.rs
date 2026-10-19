// Author by Lab | zefry
use super::*;
use base64::{engine::general_purpose::STANDARD, Engine};
impl Store {
    pub(crate) fn upgrade_enterprise(&self) -> Result<()> {
        self.db.execute_batch("CREATE TABLE IF NOT EXISTS kitchen_jobs(id TEXT PRIMARY KEY,operation_id TEXT NOT NULL,kitchen_id TEXT NOT NULL,receipt TEXT NOT NULL,state TEXT NOT NULL CHECK(state IN ('queued','printing','done','uncertain')),attempts INTEGER NOT NULL DEFAULT 0,FOREIGN KEY(operation_id) REFERENCES operations(id));PRAGMA user_version=2;")?;
        self.db.execute(
            "UPDATE kitchen_jobs SET state='uncertain' WHERE state='printing'",
            [],
        )?;
        Ok(())
    }
    pub fn active_shift(&self) -> Result<Option<Value>> {
        self.get("active_shift")?
            .filter(|s| s != "null")
            .map(|s| serde_json::from_str(&s).map_err(Error::from))
            .transpose()
    }
    pub fn set_order_context(&self, table: &str, order: &str) -> Result<()> {
        if table.chars().count() > 24
            || order.chars().count() > 32
            || table.chars().chain(order.chars()).any(char::is_control)
        {
            return Err(Error::Rule("POS_ORDER_INVALID"));
        }
        self.set("order_context", &json!([table, order]).to_string())
    }
    pub(crate) fn order_context(&self) -> Result<(String, String)> {
        let v: Value = self
            .get("order_context")?
            .map(|s| serde_json::from_str(&s))
            .transpose()?
            .unwrap_or(json!(["", ""]));
        Ok((
            v[0].as_str().unwrap_or("").into(),
            v[1].as_str().unwrap_or("").into(),
        ))
    }
    fn event_time(&self, s: &Snapshot, local_now: i64) -> Result<i64> {
        if self.get("authority_state")?.as_deref() == Some("blocked") {
            return Err(Error::Rule("POS_SIGN_IN_AGAIN"));
        }
        let anchor: i64 = self
            .get("clock_anchor")?
            .and_then(|s| s.parse().ok())
            .ok_or(Error::Rule("POS_CLOCK_INVALID"))?;
        let high: i64 = self
            .get("clock_high_water")?
            .and_then(|s| s.parse().ok())
            .unwrap_or(anchor);
        if local_now < anchor || local_now < high - 60 {
            return Err(Error::Rule("POS_CLOCK_MOVED_BACKWARD"));
        }
        let at = s
            .server_time
            .checked_add(local_now - anchor)
            .ok_or(Error::Rule("POS_CLOCK_INVALID"))?;
        if at > s.expires_at {
            return Err(Error::Rule("POS_OFFLINE_LEASE_EXPIRED"));
        }
        Ok(at)
    }
    pub fn shift_event(
        &mut self,
        id: &str,
        opening: bool,
        cash: i64,
        note: &str,
        local_now: i64,
    ) -> Result<OperationView> {
        if !id.starts_with("desk-")
            || Uuid::parse_str(&id[5..]).is_err()
            || !(0..=MAX_MONEY).contains(&cash)
            || note.chars().count() > 500
            || note.chars().any(char::is_control)
        {
            return Err(Error::Rule("POS_INPUT_INVALID"));
        }
        let kind = if opening { "shift_open" } else { "shift_close" };
        let exists: bool = self.db.query_row(
            "SELECT EXISTS(SELECT 1 FROM operations WHERE id=?)",
            [id],
            |r| r.get(0),
        )?;
        if exists {
            let old = self.operation(id)?;
            if old.operation.kind != kind
                || old.operation.tendered != cash
                || old.operation.note != note
            {
                return Err(Error::Rule("POS_OPERATION_CHANGED"));
            }
            return Ok(old);
        }
        let s = self.snapshot()?.ok_or(Error::Rule("POS_CONNECT_FIRST"))?;
        if s.protocol != 2 {
            return Err(Error::Rule("POS_UPGRADE_LOGIN_REQUIRED"));
        }
        let at = self.event_time(&s, local_now)?;
        let active = self.active_shift()?;
        if opening == active.is_some() {
            return Err(Error::Rule(if opening {
                "POS_CLOSE_SHIFT_FIRST"
            } else {
                "POS_OPEN_SHIFT_REQUIRED"
            }));
        }
        let shift = active
            .unwrap_or(json!({"id":id,"opening":cash,"opened_at":at,"cashier":s.cashier_name}));
        let shift_id = shift["id"]
            .as_str()
            .ok_or(Error::Rule("POS_SHIFT_INVALID"))?;
        let (sales,count):(i64,i64)=self.db.query_row("SELECT COALESCE(SUM(total),0),COUNT(*) FROM operations WHERE json_extract(payload,'$.kind')='sale' AND json_extract(payload,'$.shift_id')=?",[shift_id],|r|Ok((r.get(0)?,r.get(1)?)))?;
        let opening_cash = shift["opening"]
            .as_i64()
            .ok_or(Error::Rule("POS_SHIFT_INVALID"))?;
        let expected = opening_cash
            .checked_add(sales)
            .filter(|v| *v <= MAX_MONEY)
            .ok_or(Error::Rule("POS_AMOUNT_TOO_LARGE"))?;
        let op = Operation {
            created_at: at,
            kind: kind.into(),
            lease_id: s.lease_id.clone(),
            lines: vec![],
            operation_id: id.into(),
            tendered: cash,
            shift_id: shift_id.into(),
            table: String::new(),
            order: String::new(),
            note: note.into(),
        };
        let receipt = json!({"kind":kind,"reference":id,"shift_id":shift_id,"business":s.business_name,"cashier":s.cashier_name,"created_at":at,"opened_at":shift["opened_at"],"opening":opening_cash,"sales":sales,"transactions":count,"expected":expected,"counted":cash,"variance":cash-expected,"currency":s.catalog[0].currency,"scale":s.catalog[0].scale,"note":note,"lines":[],"provisional":true});
        let tx = self.db.transaction()?;
        tx.execute(
            "INSERT INTO operations(id,payload,state,total,receipt) VALUES(?,?,'pending',0,?)",
            params![id, serde_json::to_string(&op)?, receipt.to_string()],
        )?;
        tx.execute("INSERT INTO metadata(key,value) VALUES('active_shift',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value",[if opening{shift.to_string()}else{"null".into()}])?;
        tx.execute("INSERT INTO metadata(key,value) VALUES('clock_high_water',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value",[local_now.to_string()])?;
        if !opening {
            tx.execute(
                "INSERT INTO print_jobs(id,operation_id,receipt,state) VALUES(?,?,?,'queued')",
                params![Uuid::new_v4().to_string(), id, receipt.to_string()],
            )?;
        }
        tx.commit()?;
        self.operation(id)
    }
    pub fn metrics(&self) -> Result<Value> {
        let (gross,count,accepted,waiting):(i64,i64,i64,i64)=self.db.query_row("SELECT COALESCE(SUM(total),0),COUNT(*),COALESCE(SUM(CASE WHEN state='accepted' THEN total ELSE 0 END),0),COALESCE(SUM(CASE WHEN state!='accepted' THEN total ELSE 0 END),0) FROM operations WHERE json_extract(payload,'$.kind')='sale' AND date(json_extract(payload,'$.created_at'),'unixepoch','+7 hours')=date('now','+7 hours')",[],|r|Ok((r.get(0)?,r.get(1)?,r.get(2)?,r.get(3)?)))?;
        let mut buckets = vec![0i64; 12];
        let mut q=self.db.prepare("SELECT CAST(strftime('%H',json_extract(payload,'$.created_at'),'unixepoch','+7 hours') AS INTEGER)/2, SUM(total) FROM operations WHERE json_extract(payload,'$.kind')='sale' AND date(json_extract(payload,'$.created_at'),'unixepoch','+7 hours')=date('now','+7 hours') GROUP BY 1")?;
        for row in q.query_map([], |r| Ok((r.get::<_, u32>(0)?, r.get::<_, i64>(1)?)))? {
            let (i, v) = row?;
            if i < 12 {
                buckets[i as usize] = v;
            }
        }
        Ok(
            json!({"gross":gross,"count":count,"accepted":accepted,"waiting":waiting,"buckets":buckets,"timezone":"Asia/Jakarta"}),
        )
    }
    pub fn save_kitchen_printers(&self, value: &Value) -> Result<()> {
        let map = value
            .as_object()
            .ok_or(Error::Rule("POS_PRINTER_INVALID"))?;
        let s = self.snapshot()?.ok_or(Error::Rule("POS_CONNECT_FIRST"))?;
        let kitchens = s.settings["kitchens"]
            .as_array()
            .cloned()
            .unwrap_or_default();
        if map.len() > 12 {
            return Err(Error::Rule("POS_PRINTER_INVALID"));
        }
        for (id, v) in map {
            if !kitchens.iter().any(|k| k["id"] == *id) {
                return Err(Error::Rule("POS_KITCHEN_INVALID"));
            }
            let p: PrinterSettings = serde_json::from_value(v.clone())?;
            validate_kitchen_printer(&p)?;
        }
        self.set("kitchen_printers", &value.to_string())
    }
    pub fn kitchen_jobs(&self) -> Result<Vec<Value>> {
        let mut q=self.db.prepare("SELECT id,operation_id,kitchen_id,state,attempts FROM kitchen_jobs ORDER BY rowid DESC LIMIT 200")?;
        let mut out = vec![];
        for row in q.query_map([],|r|Ok(json!({"id":r.get::<_,String>(0)?,"operation_id":r.get::<_,String>(1)?,"kitchen_id":r.get::<_,String>(2)?,"state":r.get::<_,String>(3)?,"attempts":r.get::<_,i64>(4)?})))?{out.push(row?)}
        Ok(out)
    }
    pub fn claim_kitchen(&mut self, id: &str, duplicate: bool) -> Result<(PrinterSettings, Value)> {
        let (kitchen, raw, state): (String, String, String) = self.db.query_row(
            "SELECT kitchen_id,receipt,state FROM kitchen_jobs WHERE id=?",
            [id],
            |r| Ok((r.get(0)?, r.get(1)?, r.get(2)?)),
        )?;
        if state == "printing" || (!duplicate && state != "queued") {
            return Err(Error::Rule("PRINT_REPRINT_CONFIRMATION_REQUIRED"));
        }
        let map: Value = self
            .get("kitchen_printers")?
            .map(|s| serde_json::from_str(&s))
            .transpose()?
            .unwrap_or(json!({}));
        let p: PrinterSettings = serde_json::from_value(map[&kitchen].clone())
            .map_err(|_| Error::Rule("POS_KITCHEN_PRINTER_REQUIRED"))?;
        validate_kitchen_printer(&p)?;
        if (p.transport == "windows" && p.queue.is_empty())
            || (p.transport == "network" && p.host.is_empty())
        {
            return Err(Error::Rule("POS_KITCHEN_PRINTER_REQUIRED"));
        }
        let mut receipt: Value = serde_json::from_str(&raw)?;
        receipt["duplicate"] = json!(duplicate);
        self.db.execute(
            "UPDATE kitchen_jobs SET state='printing',attempts=attempts+1 WHERE id=?",
            [id],
        )?;
        Ok((p, receipt))
    }
    pub fn finish_kitchen(&self, id: &str, success: bool) -> Result<()> {
        self.db.execute(
            "UPDATE kitchen_jobs SET state=? WHERE id=? AND state='printing'",
            params![if success { "done" } else { "uncertain" }, id],
        )?;
        Ok(())
    }
    pub fn prepare_update(&self, path: &Path) -> Result<()> {
        if self.active_shift()?.is_some() {
            return Err(Error::Rule("UPDATE_CLOSE_SHIFT_FIRST"));
        }
        let unresolved:i64=self.db.query_row("SELECT COUNT(*) FROM operations WHERE state!='accepted' AND json_extract(payload,'$.kind')!='count'",[],|r|r.get(0))?;
        let printing:i64=self.db.query_row("SELECT (SELECT COUNT(*) FROM print_jobs WHERE state='printing')+(SELECT COUNT(*) FROM kitchen_jobs WHERE state='printing')",[],|r|r.get(0))?;
        if unresolved > 0 {
            return Err(Error::Rule("UPDATE_SYNC_AND_REVIEW_FIRST"));
        }
        if printing > 0 {
            return Err(Error::Rule("UPDATE_CHECK_PRINT_JOBS_FIRST"));
        }
        self.db.execute_batch("PRAGMA wal_checkpoint(FULL)")?;
        self.db
            .execute("VACUUM INTO ?", [path.to_string_lossy().as_ref()])?;
        Ok(())
    }
}
fn validate_kitchen_printer(p: &PrinterSettings) -> Result<()> {
    if !matches!(p.transport.as_str(), "windows" | "network")
        || !matches!(p.paper_mm, 58 | 80)
        || p.port == 0
        || p.drawer_enabled
        || p.queue.len() > 256
        || p.host.len() > 253
        || p.queue.chars().chain(p.host.chars()).any(char::is_control)
    {
        return Err(Error::Rule("POS_PRINTER_INVALID"));
    }
    Ok(())
}
pub(crate) fn enqueue_kitchens(
    tx: &rusqlite::Transaction<'_>,
    id: &str,
    receipt: &Value,
    settings: &Value,
) -> Result<()> {
    for kitchen in settings["kitchens"].as_array().into_iter().flatten() {
        let categories = kitchen["categories"]
            .as_array()
            .cloned()
            .unwrap_or_default();
        let lines: Vec<Value> = receipt["lines"]
            .as_array()
            .into_iter()
            .flatten()
            .filter(|line| categories.contains(&line["category"]))
            .cloned()
            .collect();
        if lines.is_empty() {
            continue;
        }
        let mut r = receipt.clone();
        r["lines"] = json!(lines);
        r["kitchen"] = kitchen["name"].clone();
        r["logo"] = Value::Null;
        tx.execute("INSERT INTO kitchen_jobs(id,operation_id,kitchen_id,receipt,state) VALUES(?,?,?,?,'queued')",params![Uuid::new_v4().to_string(),id,kitchen["id"].as_str().ok_or(Error::Rule("POS_KITCHEN_INVALID"))?,r.to_string()])?;
    }
    Ok(())
}
pub fn receipt_time(r: &Value) -> String {
    chrono::DateTime::from_timestamp(r["created_at"].as_i64().unwrap_or(0) + 25200, 0)
        .map(|t| t.format("%d/%m/%Y %H:%M:%S").to_string())
        .unwrap_or_default()
}
fn clean(s: &str) -> String {
    s.chars()
        .filter(|c| c.is_ascii() && !c.is_ascii_control())
        .collect()
}
fn compact_lines(s: &str, width: usize) -> String {
    let s = clean(s);
    let mut out = String::new();
    for part in s.as_bytes().chunks(width) {
        out.push_str(std::str::from_utf8(part).unwrap_or(""));
        out.push('\n');
    }
    out
}
pub fn logo_bytes(logo: &Value) -> Vec<u8> {
    let w = logo["width"].as_u64().unwrap_or(0);
    let h = logo["height"].as_u64().unwrap_or(0);
    if w == 0 || w > 256 || w % 8 != 0 || h == 0 || h > 96 {
        return vec![];
    }
    let data = match STANDARD.decode(logo["bits"].as_str().unwrap_or("")) {
        Ok(d) if d.len() == (w / 8 * h) as usize => d,
        _ => return vec![],
    };
    let mut bytes = vec![
        0x1b,
        0x61,
        1,
        0x1d,
        0x76,
        0x30,
        0,
        (w / 8) as u8,
        0,
        h as u8,
        0,
    ];
    bytes.extend(data);
    bytes.extend([10, 0x1b, 0x61, 0]);
    bytes
}
pub fn kitchen_bytes(r: &Value, paper: u16) -> Vec<u8> {
    let width = if paper == 58 { 32 } else { 48 };
    let mut body = String::new();
    if r["duplicate"] == true {
        body.push_str("SALINAN / DUPLICATE\n")
    };
    for s in [
        r["kitchen"].as_str().unwrap_or("DAPUR"),
        &format!(
            "Meja: {} | Order: {}",
            r["table"].as_str().unwrap_or("-"),
            r["order"].as_str().unwrap_or("-")
        ),
        &receipt_time(r),
        r["reference"].as_str().unwrap_or(""),
    ] {
        body.push_str(&compact_lines(s, width))
    }
    body.push_str(&"-".repeat(width));
    body.push('\n');
    for l in r["lines"].as_array().into_iter().flatten() {
        body.push_str(&compact_lines(
            &format!("{}x {}", l["quantity"], l["name"].as_str().unwrap_or("")),
            width,
        ))
    }
    let mut out = vec![0x1b, 0x40];
    out.extend(body.as_bytes());
    out.extend([10, 10, 0x1d, 0x56, 0x42, 0]);
    out
}
pub fn shift_report_bytes(r: &Value, paper: u16) -> Vec<u8> {
    let width = if paper == 58 { 32 } else { 48 };
    let mut body = String::from("LAPORAN TUTUP KASIR\n");
    if r["duplicate"] == true {
        body.push_str("SALINAN / DUPLICATE\n")
    };
    for line in [
        r["business"].as_str().unwrap_or("oneQay"),
        r["cashier"].as_str().unwrap_or(""),
        &receipt_time(r),
        r["reference"].as_str().unwrap_or(""),
    ] {
        body.push_str(&compact_lines(line, width))
    }
    body.push_str(&"-".repeat(width));
    body.push('\n');
    for (label, key) in [
        ("Kas awal", "opening"),
        ("Penjualan lokal", "sales"),
        ("Kas seharusnya", "expected"),
        ("Kas dihitung", "counted"),
        ("Selisih", "variance"),
    ] {
        body.push_str(&compact_lines(
            &format!(
                "{}: {} {}",
                label,
                r["currency"].as_str().unwrap_or(""),
                display_money(
                    r[key].as_i64().unwrap_or(0),
                    r["scale"].as_u64().unwrap_or(0) as u32
                )
            ),
            width,
        ));
    }
    body.push_str(&compact_lines(r["note"].as_str().unwrap_or(""), width));
    if r["provisional"] == true {
        body.push_str("LOKAL - MENUNGGU PENGESAHAN\n")
    }
    let mut out = vec![0x1b, 0x40];
    out.extend(body.as_bytes());
    out.extend([10, 10, 0x1d, 0x56, 0x42, 0]);
    out
}

#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]
// Author by Lab | zefry
mod auth;
mod hardware;
mod updates;
mod service_pos;
use oneqay_pos_core::{receipt_bytes, Line, PrinterSettings, Snapshot, Store, Workspace};
use serde_json::{json, Value};
use std::{
    io::Read,
    sync::{Arc, Mutex},
    time::{Duration, SystemTime, UNIX_EPOCH},
};
use tauri::{Manager, State};
type Shared = Arc<Mutex<Store>>;
type Result<T> = std::result::Result<T, String>;
fn now() -> i64 {
    SystemTime::now()
        .duration_since(UNIX_EPOCH)
        .map(|x| x.as_secs() as i64)
        .unwrap_or(0)
}
fn safe(e: oneqay_pos_core::Error) -> String {
    match e {
        oneqay_pos_core::Error::Rule(code) => code.into(),
        _ => format!("POS_LOCAL_DATABASE_ERROR · LOCAL-{}", uuid::Uuid::new_v4()),
    }
}
fn locked(db: &Shared) -> Result<std::sync::MutexGuard<'_, Store>> {
    db.lock().map_err(|_| "POS_LOCAL_DATABASE_BUSY".into())
}
async fn background<T: Send + 'static>(
    work: impl FnOnce() -> Result<T> + Send + 'static,
) -> Result<T> {
    tauri::async_runtime::spawn_blocking(work)
        .await
        .map_err(|_| "POS_WORKER_INTERRUPTED".to_string())?
}
fn request(server: &str, path: &str, token: Option<&str>, body: Option<Value>) -> Result<Value> {
    let client = reqwest::blocking::Client::builder()
        .timeout(Duration::from_secs(15))
        .connect_timeout(Duration::from_secs(5))
        .redirect(reqwest::redirect::Policy::none())
        .https_only(true)
        .build()
        .map_err(|_| "POS_NETWORK_INIT_FAILED")?;
    let url = format!("{server}/api/pos/desktop/{path}");
    let mut req = if let Some(body) = body {
        client.post(url).json(&body)
    } else {
        client.get(url)
    };
    req = req.header("Accept", "application/json").header(
        "X-Correlation-ID",
        format!("desktop-{}", uuid::Uuid::new_v4()),
    );
    if let Some(token) = token {
        req = req.bearer_auth(token)
    }
    let response = req.send().map_err(classify_request_error)?;
    let status = response.status();
    let correlation = response
        .headers()
        .get("X-Correlation-ID")
        .and_then(|v| v.to_str().ok())
        .filter(|s| {
            s.len() <= 128
                && s.bytes()
                    .all(|b| b.is_ascii_alphanumeric() || b"_.:-".contains(&b))
        })
        .unwrap_or("unavailable")
        .to_string();
    let mut bytes = Vec::new();
    response
        .take(20 * 1024 * 1024 + 1)
        .read_to_end(&mut bytes)
        .map_err(|_| "POS_NETWORK_RESPONSE_INTERRUPTED")?;
    if bytes.len() > 20 * 1024 * 1024 {
        return Err("POS_NETWORK_RESPONSE_TOO_LARGE".into());
    }
    if !status.is_success() {
        let code = if status.as_u16() == 401 {
            "POS_SIGN_IN_AGAIN"
        } else if status.as_u16() == 403 {
            "POS_PERMISSION_DENIED"
        } else if path.starts_with("v3/") && matches!(status.as_u16(),400|404|409|422) {
            "POS_SERVICE_REQUEST_REJECTED"
        } else if status.as_u16() == 409 {
            "POS_OPEN_SHIFT_AND_PREPARE_CATALOG"
        } else if status.as_u16() == 429 {
            "POS_SERVER_BUSY_RETRY_SHORTLY"
        } else {
            "POS_SERVER_REQUEST_FAILED"
        };
        return Err(format!("{code} · {correlation}"));
    }
    serde_json::from_slice(&bytes).map_err(|_| "POS_SERVER_RESPONSE_INVALID".into())
}
fn classify_request_error(error:reqwest::Error)->&'static str {
    // A rejected TLS peer must not unlock cached offline authentication.
    let mut source:Option<&(dyn std::error::Error+'static)>=Some(&error);
    for _ in 0..8 {
        let Some(current)=source else {break};
        let message=current.to_string().to_ascii_lowercase();
        if ["certificate","unknownissuer","tls","ssl","invalid peer"].iter().any(|part|message.contains(part)) {
            return "POS_TLS_CONNECTION_FAILED";
        }
        source=current.source();
    }
    if error.is_timeout()||error.is_connect(){"POS_NETWORK_UNAVAILABLE"}else{"POS_NETWORK_REQUEST_FAILED"}
}
fn is_network_unavailable(error:&str)->bool{matches!(error,"POS_NETWORK_UNAVAILABLE"|"POS_NETWORK_RESPONSE_INTERRUPTED")}
#[tauri::command]
fn app_version(app:tauri::AppHandle)->String {app.package_info().version.to_string()}
fn credential(db: &Store) -> Result<(String, String)> {
    let server = db.get("server").map_err(safe)?.ok_or("POS_CONNECT_FIRST")?;
    let raw = db
        .get("credential")
        .map_err(safe)?
        .ok_or("POS_CONNECT_FIRST")?;
    let bytes: Vec<u8> = serde_json::from_str(&raw).map_err(|_| "POS_CREDENTIAL_INVALID")?;
    let plain = hardware::unprotect(&bytes)?;
    let token = String::from_utf8(plain).map_err(|_| "POS_CREDENTIAL_INVALID")?;
    Ok((server, token))
}
#[tauri::command]
async fn workspace(
    state: State<'_, Shared>,
    security: State<'_, auth::Security>,
) -> Result<Workspace> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || {
        let store = locked(&db)?;
        auth::visible(&sec, &store)
    })
    .await
}
#[tauri::command]
async fn commit(
    state: State<'_, Shared>,
    security: State<'_, auth::Security>,
    id: String,
    kind: String,
    lines: Vec<Line>,
    tendered: i64,
    expected_total: i64,
    expected_revision: String,
    table: String,
    order: String,
) -> Result<Workspace> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || {
        let mut store = locked(&db)?;
        auth::require(&sec, &store, true)?;
        if kind == "sale" {
            let snapshot = store.snapshot().map_err(safe)?.ok_or("POS_CONNECT_FIRST")?;
            if snapshot.catalog_revision != expected_revision {
                return Err("POS_CART_CHANGED_REVIEW".into());
            }
            let products = store.products().map_err(safe)?;
            let mut calculated = 0i64;
            for l in &lines {
                let p = products
                    .iter()
                    .find(|p| p.id == l.product_id)
                    .ok_or("POS_PRODUCT_UNAVAILABLE")?;
                calculated = p
                    .price
                    .checked_mul(l.quantity)
                    .and_then(|v| calculated.checked_add(v))
                    .ok_or("POS_AMOUNT_TOO_LARGE")?;
            }
            if calculated != expected_total {
                return Err("POS_CART_CHANGED_REVIEW".into());
            }
        }
        store.set_order_context(&table, &order).map_err(safe)?;
        store
            .commit(&id, &kind, lines, tendered, now())
            .map_err(safe)?;
        auth::visible(&sec, &store)
    })
    .await
}
#[tauri::command]
async fn retry_invalid_sync(state: State<'_, Shared>, security: State<'_, auth::Security>) -> Result<Workspace> {
    let db=state.inner().clone(); let sec=security.inner().clone();
    background(move || {let mut store=locked(&db)?; auth::require(&sec,&store,false)?;
        store.retry_invalid_operations().map_err(safe)?; auth::visible(&sec,&store)
    }).await
}
#[tauri::command]
async fn synchronize(
    state: State<'_, Shared>,
    security: State<'_, auth::Security>,
    refresh: bool,
) -> Result<Workspace> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || {
        // Keep one profile selected for the entire request/acknowledgment boundary.
        let mut store = locked(&db)?;
        {
            let s = sec.lock().map_err(|_| "POS_SECURITY_BUSY")?;
            if !s.signed_in {
                return Err("POS_SIGN_IN_AGAIN".into());
            }
            if s.updating {
                return Err("UPDATE_IN_PROGRESS".into());
            }
        }
        if store.snapshot().map_err(safe)?.is_some_and(|v|matches!(v.settings["business_type"].as_str(),Some("laundry"|"boarding"|"homestay"))){let(_,token)=credential(&store)?;let raw=request(auth::SERVER,"v2/snapshot",Some(&token),None)?;let snapshot:Snapshot=serde_json::from_value(raw).map_err(|_|"POS_SNAPSHOT_INVALID")?;store.apply_snapshot(&snapshot,now()).map_err(safe)?;return auth::visible(&sec,&store)}
        let (_, token) = credential(&store)?;
        let batch = store.pending().map_err(safe)?;
        let response = request(
            auth::SERVER,
            "v2/sync",
            Some(&token),
            Some(json!({"operations":batch})),
        );
        let result = match response {
            Ok(v) => v,
            Err(e) => {
                if e.starts_with("POS_SIGN_IN_AGAIN") || e.starts_with("POS_PERMISSION_DENIED") {
                    store.set("authority_state", "blocked").map_err(safe)?;
                    let mut s = sec.lock().map_err(|_| "POS_SECURITY_BUSY")?;
                    s.signed_in = false;
                    s.is_locked = true;
                } else {
                    sec.lock().map_err(|_| "POS_SECURITY_BUSY")?.offline = is_network_unavailable(&e);
                }
                return Err(e);
            }
        };
        let results = result["results"]
            .as_array()
            .ok_or("POS_SYNC_RESPONSE_INVALID")?;
        if results.len() > batch.len()
            || results
                .iter()
                .enumerate()
                .any(|(i, r)| r["operation_id"].as_str() != Some(batch[i].operation_id.as_str()))
        {
            return Err("POS_SYNC_RESPONSE_INVALID".into());
        }
        store.acknowledge(results).map_err(safe)?;
        sec.lock().map_err(|_| "POS_SECURITY_BUSY")?.offline = false;
        if refresh
            || !batch.is_empty()
            || store
                .snapshot()
                .map_err(safe)?
                .map(|s| now() - s.server_time > 900)
                .unwrap_or(true)
        {
            let raw = request(auth::SERVER, "v2/snapshot", Some(&token), None)?;
            let snap: Snapshot =
                serde_json::from_value(raw.clone()).map_err(|_| "POS_SNAPSHOT_INVALID")?;
            auth::refresh_pin(&store, &raw)?;
            store.apply_snapshot(&snap, now()).map_err(safe)?;
            store.reconcile_server_shift(&raw["active_shift"]).map_err(safe)?;
        }
        auth::visible(&sec, &store)
    })
    .await
}
#[tauri::command]
async fn shift_event(
    state: State<'_, Shared>,
    security: State<'_, auth::Security>,
    id: String,
    opening: bool,
    cash: i64,
    note: String,
) -> Result<Workspace> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || {
        let mut store = locked(&db)?;
        auth::require(&sec, &store, true)?;
        if store.snapshot().map_err(safe)?.is_some_and(|v| matches!(v.settings["business_type"].as_str(), Some("laundry" | "boarding" | "homestay"))) { return Err("BUSINESS_WORKFLOW_MISMATCH".into()); }
        store
            .shift_event(&id, opening, cash, &note, now())
            .map_err(safe)?;
        auth::visible(&sec, &store)
    })
    .await
}
#[tauri::command]
async fn save_printer(
    state: State<'_, Shared>,
    security: State<'_, auth::Security>,
    settings: PrinterSettings,
) -> Result<Workspace> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || {
        let store = locked(&db)?;
        auth::require(&sec, &store, true)?;
        store.save_printer(&settings).map_err(safe)?;
        auth::visible(&sec, &store)
    })
    .await
}
#[tauri::command]
async fn save_kitchen_printers(
    state: State<'_, Shared>,
    security: State<'_, auth::Security>,
    settings: Value,
) -> Result<Workspace> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || {
        let store = locked(&db)?;
        auth::require(&sec, &store, true)?;
        store.save_kitchen_printers(&settings).map_err(safe)?;
        auth::visible(&sec, &store)
    })
    .await
}
#[tauri::command]
async fn printers() -> Result<Vec<String>> {
    background(hardware::printers).await
}
#[tauri::command]
async fn print_receipt(
    state: State<'_, Shared>,
    security: State<'_, auth::Security>,
    id: String,
    duplicate: bool,
) -> Result<()> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || {
        let mut store = locked(&db)?;
        auth::require(&sec, &store, true)?;
        let settings = store.printer().map_err(safe)?;
        let (job, r) = store.claim_print(&id, duplicate).map_err(safe)?;
        let mut bytes = receipt_bytes(&r, settings.paper_mm);
        if settings.drawer_enabled && !duplicate && r["kind"] == "sale" {
            bytes.extend([0x1b, 0x70, 0, 25, 250]);
        }
        let result = hardware::send(&settings, &bytes);
        store.finish_print(&job, result.is_ok()).map_err(safe)?;
        result
    })
    .await
}
#[tauri::command]
async fn print_kitchen(
    state: State<'_, Shared>,
    security: State<'_, auth::Security>,
    id: String,
    duplicate: bool,
) -> Result<Workspace> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || {
        let mut store = locked(&db)?;
        auth::require(&sec, &store, true)?;
        let (settings, r) = store.claim_kitchen(&id, duplicate).map_err(safe)?;
        let result = hardware::send(
            &settings,
            &oneqay_pos_core::kitchen_bytes(&r, settings.paper_mm),
        );
        store.finish_kitchen(&id, result.is_ok()).map_err(safe)?;
        result?;
        auth::visible(&sec, &store)
    })
    .await
}
#[tauri::command]
async fn test_printer(
    state: State<'_, Shared>,
    security: State<'_, auth::Security>,
    drawer: bool,
) -> Result<()> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move||{let store=locked(&db)?;auth::require(&sec,&store,true)?;let settings=store.printer().map_err(safe)?;if drawer{if !settings.drawer_enabled{return Err("DRAWER_NOT_ENABLED".into())}hardware::send(&settings,&[0x1b,0x70,0,25,250])}else{hardware::send(&settings,b"\x1b\x40oneQay POS\nUJI PRINTER / PRINTER TEST\n58 / 80 mm ESC/POS\n\n\n\x1d\x56\x42\0")}}).await
}
fn main() {
    tauri::Builder::default()
        .plugin(tauri_plugin_single_instance::init(|app, _, _| {
            if let Some(w) = app.get_webview_window("main") {
                let _ = w.set_focus();
            }
        }))
        .plugin(tauri_plugin_updater::Builder::new().build())
        .setup(|app| {
            let directory = app.path().app_local_data_dir()?;
            std::fs::create_dir_all(&directory)?;
            let store = Store::open(directory.join("pos.sqlite3"))?;
            app.manage(Arc::new(Mutex::new(store)));
            app.manage(Arc::new(Mutex::new(auth::Session::new(directory))));
            Ok(())
        })
        .invoke_handler(tauri::generate_handler![app_version,
            workspace,
            commit,
            synchronize,
            retry_invalid_sync,
            shift_event,
            save_printer,
            save_kitchen_printers,
            printers,
            print_receipt,
            print_kitchen,
            test_printer,
            auth::security_status,
            auth::login,
            auth::lock_pos,
            auth::unlock_pos,
            auth::activity,
            service_pos::service_workspace,
            service_pos::service_command,
            updates::check_update,
            updates::install_update
        ])
        .run(tauri::generate_context!())
        .expect("oneQay POS startup failed");
}

#[cfg(test)]
mod transport_tests {
    use super::*;
    #[test]
    fn blocked_protocol_is_not_offline_authentication_authority() {
        let error=reqwest::blocking::Client::builder().https_only(true).build().unwrap().get("http://127.0.0.1/").send().unwrap_err();
        assert_eq!(classify_request_error(error),"POS_NETWORK_REQUEST_FAILED");
        assert!(!is_network_unavailable("POS_NETWORK_REQUEST_FAILED"));
        assert!(!is_network_unavailable("POS_TLS_CONNECTION_FAILED"));
        assert!(!is_network_unavailable("POS_SIGN_IN_AGAIN · synthetic"));
    }
    #[test]
    fn unreachable_transport_can_use_existing_offline_authority() {
        let listener=std::net::TcpListener::bind("127.0.0.1:0").unwrap();let address=listener.local_addr().unwrap();drop(listener);
        let error=reqwest::blocking::Client::builder().timeout(Duration::from_millis(500)).build().unwrap().get(format!("http://{address}/")).send().unwrap_err();
        assert!(is_network_unavailable(classify_request_error(error)));
    }
}

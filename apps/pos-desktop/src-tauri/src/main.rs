#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]
// Author by Lab | zefry
mod hardware;
use oneqay_pos_core::{Store,Workspace,Snapshot,Line,PrinterSettings,receipt_bytes};
use serde_json::{json,Value};
use std::{sync::{Arc,Mutex},time::{Duration,SystemTime,UNIX_EPOCH},io::Read};
use tauri::{Manager,State};
type Shared=Arc<Mutex<Store>>;
type Result<T>=std::result::Result<T,String>;
fn now()->i64{SystemTime::now().duration_since(UNIX_EPOCH).map(|x|x.as_secs()as i64).unwrap_or(0)}
fn safe(e:oneqay_pos_core::Error)->String{match e{oneqay_pos_core::Error::Rule(code)=>code.into(),_=>format!("POS_LOCAL_DATABASE_ERROR · LOCAL-{}",uuid::Uuid::new_v4())}}
fn locked(db:&Shared)->Result<std::sync::MutexGuard<'_,Store>>{db.lock().map_err(|_|"POS_LOCAL_DATABASE_BUSY".into())}
async fn background<T:Send+'static>(work:impl FnOnce()->Result<T>+Send+'static)->Result<T>{tauri::async_runtime::spawn_blocking(work).await.map_err(|_|"POS_WORKER_INTERRUPTED".to_string())?}
fn server_url(raw:&str)->Result<String>{let url=url::Url::parse(raw).map_err(|_|"POS_SERVER_URL_INVALID")?;if url.scheme()!="https"||url.host_str().is_none()||!url.username().is_empty()||url.password().is_some()||url.query().is_some()||url.fragment().is_some()||url.path()!="/"{return Err("POS_SERVER_HTTPS_ORIGIN_REQUIRED".into())}Ok(url.as_str().trim_end_matches('/').into())}
fn request(server:&str,path:&str,token:Option<&str>,body:Option<Value>)->Result<Value>{
 let client=reqwest::blocking::Client::builder().timeout(Duration::from_secs(15)).connect_timeout(Duration::from_secs(5)).redirect(reqwest::redirect::Policy::none()).https_only(true).build().map_err(|_|"POS_NETWORK_INIT_FAILED")?;
 let url=format!("{server}/api/pos/desktop/{path}");let mut req=if let Some(body)=body{client.post(url).json(&body)}else{client.get(url)};req=req.header("Accept","application/json").header("X-Correlation-ID",format!("desktop-{}",uuid::Uuid::new_v4()));if let Some(token)=token{req=req.bearer_auth(token)}
 let response=req.send().map_err(|_|"POS_NETWORK_UNAVAILABLE")?;let status=response.status();let correlation=response.headers().get("X-Correlation-ID").and_then(|v|v.to_str().ok()).filter(|s|s.len()<=128&&s.bytes().all(|b|b.is_ascii_alphanumeric()||b"_.:-".contains(&b))).unwrap_or("unavailable").to_string();
 let mut bytes=Vec::new();response.take(20*1024*1024+1).read_to_end(&mut bytes).map_err(|_|"POS_NETWORK_RESPONSE_INTERRUPTED")?;if bytes.len()>20*1024*1024{return Err("POS_NETWORK_RESPONSE_TOO_LARGE".into())}let value:Value=serde_json::from_slice(&bytes).map_err(|_|"POS_SERVER_RESPONSE_INVALID")?;
 if !status.is_success(){let code=if status.as_u16()==401{"POS_SIGN_IN_AGAIN"}else if status.as_u16()==403{"POS_PERMISSION_DENIED"}else if status.as_u16()==409{"POS_OPEN_SHIFT_AND_PREPARE_CATALOG"}else if status.as_u16()==429{"POS_SERVER_BUSY_RETRY_SHORTLY"}else{"POS_SERVER_REQUEST_FAILED"};return Err(format!("{code} · {correlation}"))}Ok(value)
}
fn credential(db:&Store)->Result<(String,String)>{let server=db.get("server").map_err(safe)?.ok_or("POS_CONNECT_FIRST")?;let raw=db.get("credential").map_err(safe)?.ok_or("POS_CONNECT_FIRST")?;let bytes:Vec<u8>=serde_json::from_str(&raw).map_err(|_|"POS_CREDENTIAL_INVALID")?;let plain=hardware::unprotect(&bytes)?;let token=String::from_utf8(plain).map_err(|_|"POS_CREDENTIAL_INVALID")?;Ok((server,token))}
#[tauri::command]
async fn workspace(state:State<'_,Shared>)->Result<Workspace>{let db=state.inner().clone();background(move||locked(&db)?.workspace().map_err(safe)).await}
#[tauri::command]
async fn connect(state:State<'_,Shared>,server:String,code:String)->Result<Workspace>{let db=state.inner().clone();background(move||{
 let server=server_url(&server)?;{let store=locked(&db)?;if let Some(old)=store.get("server").map_err(safe)?{if old!=server{return Err("POS_SERVER_CHANGE_REQUIRES_NEW_WINDOWS_PROFILE".into())}}}
 let result=request(&server,"exchange",None,Some(json!({"code":code.trim().replace(' ',"").to_uppercase()})))?;let token=result["token"].as_str().filter(|t|t.len()==64).ok_or("POS_CREDENTIAL_INVALID")?;let protected=hardware::protect(token.as_bytes())?;let snapshot:Snapshot=serde_json::from_value(result["snapshot"].clone()).map_err(|_|"POS_SNAPSHOT_INVALID")?;
 let mut store=locked(&db)?;store.apply_snapshot(&snapshot,now()).map_err(safe)?;store.set("server",&server).map_err(safe)?;store.set("credential",&serde_json::to_string(&protected).map_err(|_|"POS_CREDENTIAL_INVALID")?).map_err(safe)?;store.workspace().map_err(safe)
 }).await}
#[tauri::command]
async fn commit(state:State<'_,Shared>,id:String,kind:String,lines:Vec<Line>,tendered:i64)->Result<Workspace>{let db=state.inner().clone();background(move||{let mut store=locked(&db)?;store.commit(&id,&kind,lines,tendered,now()).map_err(safe)?;store.workspace().map_err(safe)}).await}
#[tauri::command]
async fn synchronize(state:State<'_,Shared>,refresh:bool)->Result<Workspace>{let db=state.inner().clone();background(move||{
 let(server,token,batch)={let store=locked(&db)?;let(server,token)=credential(&store)?;(server,token,store.pending().map_err(safe)?)};
 let result=match request(&server,"sync",Some(&token),Some(json!({"operations":batch}))) {Ok(v)=>v,Err(e)=>{if e.starts_with("POS_SIGN_IN_AGAIN")||e.starts_with("POS_PERMISSION_DENIED"){locked(&db)?.set("authority_state","blocked").map_err(safe)?;}return Err(e)}};let results=result["results"].as_array().ok_or("POS_SYNC_RESPONSE_INVALID")?;
 let expected:std::collections::HashSet<_>=batch.iter().map(|o|o.operation_id.as_str()).collect();if results.iter().any(|r|!r["operation_id"].as_str().map(|id|expected.contains(id)).unwrap_or(false)){return Err("POS_SYNC_RESPONSE_INVALID".into())}
 {let mut store=locked(&db)?;store.acknowledge(results).map_err(safe)?;}
 let needs_refresh={let store=locked(&db)?;refresh||!batch.is_empty()||store.snapshot().map_err(safe)?.map(|s|now()-s.issued_at>900).unwrap_or(true)};
 if needs_refresh {let snapshot=request(&server,"snapshot",Some(&token),None)?;let snapshot:Snapshot=serde_json::from_value(snapshot).map_err(|_|"POS_SNAPSHOT_INVALID")?;locked(&db)?.apply_snapshot(&snapshot,now()).map_err(safe)?;}
 locked(&db)?.workspace().map_err(safe)
 }).await}
#[tauri::command]
async fn save_printer(state:State<'_,Shared>,settings:PrinterSettings)->Result<Workspace>{let db=state.inner().clone();background(move||{let store=locked(&db)?;store.save_printer(&settings).map_err(safe)?;store.workspace().map_err(safe)}).await}
#[tauri::command]
async fn printers()->Result<Vec<String>>{background(hardware::printers).await}
#[tauri::command]
async fn print_receipt(state:State<'_,Shared>,id:String,duplicate:bool)->Result<()> {let db=state.inner().clone();background(move||{
 let(settings,job,receipt)={let mut store=locked(&db)?;let settings=store.printer().map_err(safe)?;let(job,receipt)=store.claim_print(&id,duplicate).map_err(safe)?;(settings,job,receipt)};
 let mut bytes=receipt_bytes(&receipt,settings.paper_mm);if settings.drawer_enabled&&!duplicate{bytes.extend_from_slice(&[0x1b,0x70,0,25,250]);}let result=hardware::send(&settings,&bytes);locked(&db)?.finish_print(&job,result.is_ok()).map_err(safe)?;result
 }).await}
#[tauri::command]
async fn test_printer(state:State<'_,Shared>,drawer:bool)->Result<()> {let db=state.inner().clone();background(move||{let settings=locked(&db)?.printer().map_err(safe)?;if drawer{if !settings.drawer_enabled{return Err("DRAWER_NOT_ENABLED".into())}hardware::send(&settings,&[0x1b,0x70,0,25,250])}else{hardware::send(&settings,b"\x1b\x40oneQay POS\nUJI PRINTER / PRINTER TEST\n58 / 80 mm ESC/POS\n\n\n\x1d\x56\x42\0")}}).await}
fn main(){tauri::Builder::default().setup(|app|{let directory=app.path().app_local_data_dir()?;std::fs::create_dir_all(&directory)?;let store=Store::open(directory.join("pos.sqlite3"))?;app.manage(Arc::new(Mutex::new(store)));Ok(())}).invoke_handler(tauri::generate_handler![workspace,connect,commit,synchronize,save_printer,printers,print_receipt,test_printer]).run(tauri::generate_context!()).expect("oneQay POS startup failed");}

// Author by Lab | zefry. Credential material never crosses the webview boundary.
use crate::*;
use pbkdf2::pbkdf2_hmac;
use sha2::{Digest, Sha256};
use std::path::PathBuf;
use subtle::ConstantTimeEq;
pub const SERVER: &str = "https://oneqaydev.n07.my.id";
pub type Security = Arc<Mutex<Session>>;
pub struct Session {
    pub directory: PathBuf,
    pub signed_in: bool,
    pub is_locked: bool,
    pub login: String,
    pub profile: String,
    pub last_activity: i64,
    pub updating: bool,
    pub offline: bool,
}
impl Session {
    pub fn new(directory: PathBuf) -> Self {
        Self {
            directory,
            signed_in: false,
            is_locked: true,
            login: String::new(),
            profile: String::new(),
            last_activity: now(),
            updating: false,
            offline: false,
        }
    }
}
pub fn status(sec: &Security, store: &Store) -> Result<Value> {
    let mut s = sec.lock().map_err(|_| "POS_SECURITY_BUSY")?;
    let timeout = store
        .snapshot()
        .map_err(safe)?
        .and_then(|v| v.settings["auto_lock_seconds"].as_i64())
        .unwrap_or(300);
    if s.signed_in && timeout > 0 && now() - s.last_activity >= timeout {
        s.is_locked = true
    }
    Ok(
        json!({"signed_in":s.signed_in,"locked":s.is_locked,"login":s.login,"offline":s.offline,"updating":s.updating}),
    )
}
pub fn require(sec: &Security, store: &Store, touch: bool) -> Result<()> {
    let state = status(sec, store)?;
    if state["signed_in"] != true {
        return Err("POS_SIGN_IN_AGAIN".into());
    }
    if state["locked"] == true {
        return Err("POS_LOCKED".into());
    }
    let mut s = sec.lock().map_err(|_| "POS_SECURITY_BUSY")?;
    if s.updating {
        return Err("UPDATE_IN_PROGRESS".into());
    }
    if touch {
        s.last_activity = now()
    }
    Ok(())
}
pub fn visible(sec: &Security, store: &Store) -> Result<Workspace> {
    let state = status(sec, store)?;
    let mut w = store.workspace().map_err(safe)?;
    if state["signed_in"] != true || state["locked"] == true {
        w.snapshot = None;
        w.products.clear();
        w.operations.clear();
        w.shift = None;
        w.kitchen_jobs.clear();
        w.kitchen_printers = json!({});
        w.metrics = json!({});
        w.settings = serde_json::to_value(PrinterSettings::default()).unwrap();
        w.authorized = false;
        w.pending = 0;
        w.review = 0;
    }
    Ok(w)
}
fn verifier(secret: &str) -> Value {
    let salt = uuid::Uuid::new_v4().simple().to_string();
    let mut bytes = [0u8; 32];
    pbkdf2_hmac::<Sha256>(secret.as_bytes(), salt.as_bytes(), 210000, &mut bytes);
    json!({"salt":salt,"hash":hex::encode(bytes),"iterations":210000})
}
fn verify(secret: &str, value: &Value) -> bool {
    let salt = value["salt"].as_str().unwrap_or("");
    let expected = hex::decode(value["hash"].as_str().unwrap_or("")).unwrap_or_default();
    if salt.len() != 32 || expected.len() != 32 || value["iterations"] != 210000 {
        return false;
    }
    let mut bytes = [0u8; 32];
    pbkdf2_hmac::<Sha256>(secret.as_bytes(), salt.as_bytes(), 210000, &mut bytes);
    bool::from(bytes.ct_eq(expected.as_slice()))
}
fn secret(store: &Store) -> Result<Value> {
    let raw = store
        .get("cashier_secret")
        .map_err(safe)?
        .ok_or("POS_FIRST_LOGIN_REQUIRES_INTERNET")?;
    let bytes: Vec<u8> = serde_json::from_str(&raw).map_err(|_| "POS_CREDENTIAL_INVALID")?;
    serde_json::from_slice(&hardware::unprotect(&bytes)?)
        .map_err(|_| "POS_CREDENTIAL_INVALID".into())
}
fn save_secret(store: &Store, v: &Value) -> Result<()> {
    let bytes = hardware::protect(v.to_string().as_bytes())?;
    store
        .set(
            "cashier_secret",
            &serde_json::to_string(&bytes).map_err(|_| "POS_CREDENTIAL_INVALID")?,
        )
        .map_err(safe)
}
pub fn refresh_pin(store: &Store, snapshot: &Value) -> Result<()> {
    if snapshot["pin"].is_object() {
        let mut v = secret(store)?;
        v["pin"] = snapshot["pin"].clone();
        save_secret(store, &v)?;
    }
    Ok(())
}
fn allowed_attempt(store: &Store, key: &str) -> Result<()> {
    let until = store
        .get(&format!("{key}_until"))
        .map_err(safe)?
        .and_then(|s| s.parse::<i64>().ok())
        .unwrap_or(0);
    if until > now() {
        return Err("POS_TOO_MANY_ATTEMPTS_WAIT_5_MINUTES".into());
    }
    Ok(())
}
fn attempt(store: &Store, key: &str, success: bool) -> Result<()> {
    let mut n = store
        .get(&format!("{key}_attempts"))
        .map_err(safe)?
        .and_then(|s| s.parse::<i64>().ok())
        .unwrap_or(0);
    if success {
        n = 0;
        store.set(&format!("{key}_until"), "0").map_err(safe)?
    } else {
        n += 1;
        if n >= 5 {
            store
                .set(&format!("{key}_until"), &(now() + 300).to_string())
                .map_err(safe)?;
            n = 0;
        }
    }
    store
        .set(&format!("{key}_attempts"), &n.to_string())
        .map_err(safe)
}
#[tauri::command]
pub async fn security_status(
    state: State<'_, Shared>,
    security: State<'_, Security>,
) -> Result<Value> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || status(&sec, &*locked(&db)?)).await
}
#[tauri::command]
pub async fn login(
    state: State<'_, Shared>,
    security: State<'_, Security>,
    login: String,
    password: String,
    totp: String,
) -> Result<Workspace> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move||{
 let login=login.trim().to_lowercase();if login.len()>190||!login.contains('@')||password.len()>72||password.is_empty()||!(totp.is_empty()||(totp.len()==6&&totp.bytes().all(|b|b.is_ascii_digit()))){return Err("POS_LOGIN_INVALID".into())}
 let mut store=locked(&db)?;let mut session=sec.lock().map_err(|_|"POS_SECURITY_BUSY")?;
 if session.updating{return Err("UPDATE_IN_PROGRESS".into())}if session.signed_in&&session.login!=login&&store.active_shift().map_err(safe)?.is_some(){return Err("POS_CLOSE_SHIFT_FIRST".into())}
 let profile=format!("cashier-{}.sqlite3",hex::encode(Sha256::digest(login.as_bytes())));let mut target=Store::open(session.directory.join(&profile)).map_err(safe)?;allowed_attempt(&target,"login")?;
 let response=request(SERVER,"v2/login",None,Some(json!({"login":login,"password":password,"totp":totp})));
 match response {
  Ok(r)=>{let token=r["token"].as_str().filter(|t|t.len()==64).ok_or("POS_CREDENTIAL_INVALID")?;let snapshot:Snapshot=serde_json::from_value(r["snapshot"].clone()).map_err(|_|"POS_SNAPSHOT_INVALID")?;
   if let Some(active)=r["snapshot"]["active_shift"].as_object(){let local=active.get("local_id").and_then(|v|v.as_str()).unwrap_or("");if local.is_empty()||target.operation(local).is_err(){return Err("POS_EXISTING_SHIFT_CLOSE_ON_WEB".into())}}
   target.apply_snapshot(&snapshot,now()).map_err(safe)?;target.set("server",SERVER).map_err(safe)?;target.set("credential",&serde_json::to_string(&hardware::protect(token.as_bytes())?).map_err(|_|"POS_CREDENTIAL_INVALID")?).map_err(safe)?;
   save_secret(&target,&json!({"login":login,"password":verifier(&password),"pin":r["snapshot"]["pin"]}))?;session.offline=false;
  },
  Err(e) if e=="POS_NETWORK_UNAVAILABLE"||e=="POS_NETWORK_RESPONSE_INTERRUPTED"=>{
   if target.get("authority_state").map_err(safe)?.as_deref()==Some("blocked"){return Err("POS_SIGN_IN_AGAIN".into())}let saved=secret(&target)?;
   let valid=saved["login"]==login&&verify(&password,&saved["password"]);attempt(&target,"login",valid)?;if !valid{return Err("POS_LOGIN_INVALID".into())}if target.lease_remaining().map_err(safe)?<=0{return Err("POS_OFFLINE_LEASE_EXPIRED".into())}session.offline=true;
  },Err(e)=>{attempt(&target,"login",false)?;if e.starts_with("POS_SIGN_IN_AGAIN")||e.starts_with("POS_PERMISSION_DENIED"){target.set("authority_state","blocked").map_err(safe)?;}return Err(e)}
 }
 attempt(&target,"login",true)?;*store=target;session.signed_in=true;session.is_locked=false;session.login=login;session.profile=profile;session.last_activity=now();drop(session);visible(&sec,&store)
}).await
}
#[tauri::command]
pub async fn lock_pos(
    state: State<'_, Shared>,
    security: State<'_, Security>,
    logout: bool,
) -> Result<Workspace> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || {
        let store = locked(&db)?;
        {
            let mut s = sec.lock().map_err(|_| "POS_SECURITY_BUSY")?;
            if s.updating {
                return Err("UPDATE_IN_PROGRESS".into());
            }
            if logout && store.active_shift().map_err(safe)?.is_some() {
                return Err("POS_CLOSE_SHIFT_FIRST".into());
            }
            s.is_locked = true;
            if logout {
                s.signed_in = false;
            }
        }
        visible(&sec, &store)
    })
    .await
}
#[tauri::command]
pub async fn unlock_pos(
    state: State<'_, Shared>,
    security: State<'_, Security>,
    pin: String,
) -> Result<Workspace> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || {
        let store = locked(&db)?;
        allowed_attempt(&store, "pin")?;
        let mut s = sec.lock().map_err(|_| "POS_SECURITY_BUSY")?;
        if !s.signed_in {
            return Err("POS_SIGN_IN_AGAIN".into());
        }
        let valid = pin.len() == 6
            && pin.bytes().all(|b| b.is_ascii_digit())
            && verify(&pin, &secret(&store)?["pin"]);
        attempt(&store, "pin", valid)?;
        if !valid {
            return Err("POS_PIN_INVALID".into());
        }
        s.is_locked = false;
        s.last_activity = now();
        drop(s);
        visible(&sec, &store)
    })
    .await
}
#[tauri::command]
pub async fn activity(state: State<'_, Shared>, security: State<'_, Security>) -> Result<()> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || require(&sec, &*locked(&db)?, true)).await
}
#[cfg(test)]
mod tests {
    use super::*;
    #[test]
    fn verifier_roundtrip_and_pin_bounds() {
        let v = verifier("123456");
        assert!(verify("123456", &v));
        assert!(!verify("123457", &v));
        assert!(!verify("123456", &json!({"iterations":0})));
    }
}

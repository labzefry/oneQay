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
    pub auto_lock_override: Option<i64>,
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
            auto_lock_override: None,
        }
    }
}
pub fn status(sec: &Security, store: &Store) -> Result<Value> {
    let mut s = sec.lock().map_err(|_| "POS_SECURITY_BUSY")?;
    let timeout = s.auto_lock_override.unwrap_or_else(|| store
        .snapshot()
        .ok().flatten()
        .and_then(|v| v.settings["auto_lock_seconds"].as_i64())
        .filter(|v|remember::timeout_valid(*v))
        .unwrap_or(300));
    if s.signed_in && ((timeout > 0 && now() - s.last_activity >= timeout) || now() < s.last_activity) {
        s.is_locked = true;
        remember::remember(&s,store)?;
    }
    Ok(
        json!({"signed_in":s.signed_in,"locked":s.is_locked,"login":s.login,"offline":s.offline,"updating":s.updating,"auto_lock_seconds":timeout}),
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
        w.shift_status = "ready".into();
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
 if session.updating{return Err("UPDATE_IN_PROGRESS".into())}
 if session.signed_in{return Err("POS_LOGOUT_BEFORE_SWITCH".into())}
 let _=remember::flush(&session.directory);
 let profile=remember::profile(&login);let mut target=Store::open(session.directory.join(&profile)).map_err(safe)?;allowed_attempt(&target,"login")?;
 let response=request(SERVER,"v3/login",None,Some(json!({"login":login,"password":password,"totp":totp})));
 match response {
  Ok(r)=>{let token=r["token"].as_str().filter(|t|t.len()==64).ok_or("POS_CREDENTIAL_INVALID")?;let snapshot:Snapshot=serde_json::from_value(r["snapshot"].clone()).map_err(|_|"POS_SNAPSHOT_INVALID")?;
   target.apply_snapshot(&snapshot,now()).map_err(safe)?;target.reconcile_server_shift(&r["snapshot"]["active_shift"]).map_err(safe)?;target.set("server",SERVER).map_err(safe)?;target.set("credential",&serde_json::to_string(&hardware::protect(token.as_bytes())?).map_err(|_|"POS_CREDENTIAL_INVALID")?).map_err(safe)?;
   save_secret(&target,&json!({"login":login,"password":verifier(&password),"pin":r["snapshot"]["pin"],"persistent":true}))?;session.offline=false;
  },
  Err(e) if e=="POS_NETWORK_UNAVAILABLE"||e=="POS_NETWORK_RESPONSE_INTERRUPTED"=>{
   if remember::load(&session.directory)?.signed_out_profiles.contains(&profile){return Err("POS_FIRST_LOGIN_REQUIRES_INTERNET".into())}
   if matches!(target.get("authority_state").map_err(safe)?.as_deref(),Some("blocked"|"signed_out")){return Err("POS_SIGN_IN_AGAIN".into())}let saved=secret(&target)?;
   if saved["persistent"]!=true{return Err("POS_FIRST_LOGIN_REQUIRES_INTERNET".into())}
   let valid=saved["login"]==login&&verify(&password,&saved["password"]);attempt(&target,"login",valid)?;if !valid{return Err("POS_LOGIN_INVALID".into())}if target.lease_remaining().map_err(safe)?<=0{return Err("POS_OFFLINE_LEASE_EXPIRED".into())}session.offline=true;
  },Err(e)=>{if e.starts_with("POS_SIGN_IN_AGAIN")||e.starts_with("POS_PERMISSION_DENIED"){invalidate_profile(&mut session,&target,&profile)?;}attempt(&target,"login",false)?;return Err(e)}
 }
 attempt(&target,"login",true)?;*store=target;session.signed_in=true;session.is_locked=false;session.login=login;session.profile=profile;session.last_activity=now();session.auto_lock_override=remember::load(&session.directory)?.preferences.get(&session.profile).copied();remember::remember(&session,&store)?;drop(session);visible(&sec,&store)
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
            s.is_locked = true;
            if logout {
                local_logout(&mut s,&store,||store.set("authority_state","signed_out").map_err(safe))?;
                let _=remember::flush(&s.directory);
            } else { remember::remember(&s,&store)?; }
        }
        visible(&sec, &store)
    })
    .await
}
fn local_logout(s:&mut Session,store:&Store,write_profile:impl FnOnce()->Result<()>)->Result<()> {
    let token=credential(store).ok().map(|(_,t)|t);
    remember::forget(&s.directory,token)?;
    // The durable root tombstone is authoritative even if the profile write fails.
    s.signed_in=false;
    s.is_locked=true;
    write_profile()
}
fn invalidate_with(s:&mut Session,profile:&str,write_profile:impl FnOnce()->Result<()>)->Result<()> {
    s.signed_in=false;s.is_locked=true;
    let remembered=remember::deny_profile(&s.directory,profile);
    let blocked=write_profile();
    // Attempt both durable barriers: a failure in either store must not skip the other.
    remembered?;
    blocked
}
pub fn invalidate_profile(s:&mut Session,store:&Store,profile:&str)->Result<()> {
    invalidate_with(s,profile,||store.set("authority_state","blocked").map_err(safe))
}
pub fn request_authenticated(store:&Store,sec:&Security,path:&str,token:&str,body:Option<Value>)->Result<Value> {
    let result=request(SERVER,path,Some(token),body);
    if let Err(ref error)=result {
        if error.starts_with("POS_SIGN_IN_AGAIN")||error.starts_with("POS_PERMISSION_DENIED") {
            let mut s=sec.lock().map_err(|_|"POS_SECURITY_BUSY")?;let profile=s.profile.clone();
            invalidate_profile(&mut s,store,&profile)?;
        }
    }
    result
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
        remember::remember(&s,&store)?;
        drop(s);
        visible(&sec, &store)
    })
    .await
}
#[tauri::command]
pub async fn activity(state: State<'_, Shared>, security: State<'_, Security>) -> Result<()> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || {
        let store=locked(&db)?;
        require(&sec,&store,true)?;
        let s=sec.lock().map_err(|_|"POS_SECURITY_BUSY")?;
        remember::remember(&s,&store)
    }).await
}

#[tauri::command]
pub async fn save_security_settings(state:State<'_,Shared>,security:State<'_,Security>,seconds:i64)->Result<Value>{
    let db=state.inner().clone();let sec=security.inner().clone();
    background(move||{
        let store=locked(&db)?;require(&sec,&store,true)?;
        if !remember::timeout_valid(seconds){return Err("POS_LOCK_DURATION_INVALID".into())}
        let mut s=sec.lock().map_err(|_|"POS_SECURITY_BUSY")?;
        let mut saved=remember::load(&s.directory)?;
        saved.preferences.insert(s.profile.clone(),seconds);
        remember::save(&s.directory,&saved)?;
        s.auto_lock_override=Some(seconds);s.last_activity=now();remember::remember(&s,&store)?;
        drop(s);status(&sec,&store)
    }).await
}
fn resume_saved(store:&mut Store,s:&mut Session,fetch:impl FnOnce(&str)->Result<Value>)->Result<()> {
    let saved=remember::load(&s.directory)?;
    let Some(active)=saved.active else{return Ok(())};
    let mut target=Store::open(s.directory.join(&active.profile)).map_err(safe)?;
    let before=target.snapshot().map_err(safe)?.ok_or("POS_RESUME_STATE_INVALID")?;
    let cached=secret(&target)?;
    if before.scope!=active.scope || cached["login"]!=active.login || cached["persistent"]!=true
        || saved.signed_out_profiles.contains(&active.profile)
        || matches!(target.get("authority_state").map_err(safe)?.as_deref(),Some("blocked"|"signed_out")) {
        remember::forget(&s.directory,None)?;return Err("POS_SIGN_IN_AGAIN".into());
    }
    let (_,token)=credential(&target)?;
    match fetch(&token) {
        Ok(raw)=>{
            let snapshot:Snapshot=serde_json::from_value(raw.clone()).map_err(|_|"POS_SNAPSHOT_INVALID")?;
            if snapshot.scope!=active.scope{return Err("POS_RESUME_STATE_INVALID".into())}
            target.apply_snapshot(&snapshot,now()).map_err(safe)?;
            target.reconcile_server_shift(&raw["active_shift"]).map_err(safe)?;
            refresh_pin(&target,&raw)?;s.offline=false;
        },
        Err(e) if is_network_unavailable(&e)=>{
            if target.lease_remaining().map_err(safe)?<=0{return Err("POS_RECONNECT_TO_RESUME".into())}
            s.offline=true;
        },
        Err(e)=>{
            if e.starts_with("POS_SIGN_IN_AGAIN")||e.starts_with("POS_PERMISSION_DENIED"){
                invalidate_profile(s,&target,&active.profile)?;
            }
            return Err(e);
        }
    }
    s.login=active.login;s.profile=active.profile;s.auto_lock_override=saved.preferences.get(&s.profile).copied();
    let duration=s.auto_lock_override.or_else(||target.snapshot().ok().flatten().and_then(|v|v.settings["auto_lock_seconds"].as_i64())).filter(|v|remember::timeout_valid(*v)).unwrap_or(300);
    s.signed_in=true;s.is_locked=active.locked||now()<active.last_activity||(duration>0&&now()-active.last_activity>=duration);
    s.last_activity=active.last_activity;*store=target;remember::remember(s,store)?;
    Ok(())
}
#[tauri::command]
pub async fn resume_session(state:State<'_,Shared>,security:State<'_,Security>)->Result<Workspace>{
    let db=state.inner().clone();let sec=security.inner().clone();
    background(move||{
        let mut store=locked(&db)?;let mut s=sec.lock().map_err(|_|"POS_SECURITY_BUSY")?;
        if s.updating{return Err("UPDATE_IN_PROGRESS".into())}
        if !s.signed_in {
            let _=remember::flush(&s.directory);
            resume_saved(&mut store,&mut s,|token|request(SERVER,"v2/snapshot",Some(token),None))?;
        }
        drop(s);visible(&sec,&store)
    }).await
}

#[cfg(test)]
mod tests {
    use super::*;
    #[cfg(target_os="windows")]
    fn resume_fixture()->(PathBuf,Store,Session,Value) {
        let directory=std::env::temp_dir().join(format!("oneqay-resume-test-{}",uuid::Uuid::new_v4()));
        std::fs::create_dir_all(&directory).unwrap();let at=now();let login="synthetic-resume@example.test";
        let mut profile=Store::open(directory.join(remember::profile(login))).unwrap();
        let snapshot=Snapshot{protocol:2,cashier_name:"Synthetic cashier".into(),settings:json!({"business_type":"laundry","auto_lock_seconds":300}),lease_id:"b".repeat(32),scope:oneqay_pos_core::Scope{tenant_id:"test-tenant".into(),identity_id:"test-cashier".into(),organization_id:"test-org".into(),outlet_id:"test-outlet".into(),device_id:"test-device".into()},shift_id:String::new(),issued_at:at,expires_at:at+28800,server_time:at,catalog_revision:"fixture".into(),business_name:"Synthetic".into(),currency_symbol:"Rp".into(),can_count:false,catalog:vec![]};
        profile.apply_snapshot(&snapshot,at).unwrap();profile.set("server",SERVER).unwrap();
        profile.set("credential",&serde_json::to_string(&hardware::protect("a".repeat(64).as_bytes()).unwrap()).unwrap()).unwrap();
        save_secret(&profile,&json!({"login":login,"persistent":true,"pin":verifier("123456")})).unwrap();
        let mut old=Session::new(directory.clone());old.signed_in=true;old.is_locked=false;old.login=login.into();old.profile=remember::profile(login);
        remember::remember(&old,&profile).unwrap();drop(profile);
        let store=Store::open(directory.join("pos.sqlite3")).unwrap();let session=Session::new(directory.clone());
        (directory,store,session,serde_json::to_value(snapshot).unwrap())
    }
    #[cfg(target_os="windows")]
    #[test]
    fn resume_checks_server_and_only_uses_real_offline_transport() {
        for code in ["POS_NETWORK_UNAVAILABLE","POS_NETWORK_RESPONSE_INTERRUPTED","POS_TLS_CONNECTION_FAILED","POS_SIGN_IN_AGAIN · synthetic","POS_PERMISSION_DENIED · synthetic"] {
            let (dir,mut store,mut session,_)=resume_fixture();
            let result=resume_saved(&mut store,&mut session,|token|{assert_eq!(token,"a".repeat(64));Err(code.into())});
            assert_eq!(session.signed_in,is_network_unavailable(code));
            assert_eq!(result.is_ok(),is_network_unavailable(code));
            if code.starts_with("POS_SIGN_IN_AGAIN")||code.starts_with("POS_PERMISSION_DENIED") {
                assert!(remember::load(&dir).unwrap().active.is_none());
                resume_saved(&mut store,&mut session,|_|panic!("A revoked account must not be resumed offline")).unwrap();
                assert!(!session.signed_in);
            }
            drop(store);drop(session);std::fs::remove_dir_all(dir).unwrap();
        }
        let (dir,mut store,mut session,raw)=resume_fixture();
        resume_saved(&mut store,&mut session,|_|Ok(raw)).unwrap();assert!(session.signed_in&&!session.offline);
        drop(store);drop(session);std::fs::remove_dir_all(dir).unwrap();
    }
    #[cfg(target_os="windows")]
    #[test]
    fn expired_offline_resume_retains_account_and_manual_lock_survives() {
        let (dir,mut store,mut session,_)=resume_fixture();
        let mut saved=remember::load(&dir).unwrap();let active=saved.active.as_mut().unwrap();active.locked=true;
        saved.preferences.insert(active.profile.clone(),0);remember::save(&dir,&saved).unwrap();
        resume_saved(&mut store,&mut session,|_|Err("POS_NETWORK_UNAVAILABLE".into())).unwrap();
        assert!(session.signed_in&&session.is_locked);assert_eq!(session.auto_lock_override,Some(0));
        drop(store);drop(session);std::fs::remove_dir_all(dir).unwrap();
        let (dir,mut store,mut session,_)=resume_fixture();
        let active=remember::load(&dir).unwrap().active.unwrap();
        let target=Store::open(dir.join(&active.profile)).unwrap();target.set("clock_anchor",&(now()-30000).to_string()).unwrap();
        assert!(target.lease_remaining().unwrap()<=0);drop(target);
        assert_eq!(resume_saved(&mut store,&mut session,|_|Err("POS_NETWORK_UNAVAILABLE".into())).unwrap_err(),"POS_RECONNECT_TO_RESUME");
        assert!(!session.signed_in&&remember::load(&dir).unwrap().active.is_some());
        drop(store);drop(session);std::fs::remove_dir_all(dir).unwrap();
    }
    #[cfg(target_os="windows")]
    #[test]
    fn logout_profile_write_failure_cannot_restore_signed_in_account() {
        let (dir,mut store,mut session,raw)=resume_fixture();
        resume_saved(&mut store,&mut session,|_|Ok(raw)).unwrap();let profile=session.profile.clone();
        assert!(local_logout(&mut session,&store,||Err("SYNTHETIC_DISK_FAILURE".into())).is_err());
        assert!(!session.signed_in&&session.is_locked);
        let saved=remember::load(&dir).unwrap();assert!(saved.active.is_none());
        assert!(saved.signed_out_profiles.contains(&profile));assert_eq!(saved.pending_logout.len(),1);
        assert!(remember::remember(&session,&store).is_err());assert!(store.snapshot().unwrap().is_some());
        resume_saved(&mut store,&mut session,|_|panic!("Logged out must never auto-resume")).unwrap();
        assert!(!session.signed_in);
        drop(store);drop(session);std::fs::remove_dir_all(dir).unwrap();
    }
    #[cfg(target_os="windows")]
    #[test]
    fn known_denial_survives_profile_write_failure_without_touching_other_profiles() {
        let (dir,mut store,mut session,raw)=resume_fixture();
        resume_saved(&mut store,&mut session,|_|Ok(raw)).unwrap();let profile=session.profile.clone();
        assert!(invalidate_with(&mut session,&profile,||Err("SYNTHETIC_DISK_FAILURE".into())).is_err());
        assert!(!session.signed_in&&session.is_locked);
        let saved=remember::load(&dir).unwrap();assert!(saved.active.is_none());assert!(saved.signed_out_profiles.contains(&profile));
        resume_saved(&mut store,&mut session,|_|panic!("Known denial cannot fall back to cached resume")).unwrap();
        assert!(!session.signed_in);drop(store);drop(session);std::fs::remove_dir_all(dir).unwrap();
        let (dir,store,session,_)=resume_fixture();let active=remember::load(&dir).unwrap().active.unwrap().profile;
        let other=remember::profile("other-synthetic@example.test");remember::deny_profile(&dir,&other).unwrap();
        let saved=remember::load(&dir).unwrap();assert_eq!(saved.active.unwrap().profile,active);
        assert!(saved.signed_out_profiles.contains(&other));drop(store);drop(session);std::fs::remove_dir_all(dir).unwrap();
    }

    #[cfg(target_os="windows")]
    #[test]
    fn automatic_lock_off_does_not_cancel_manual_lock() {
        let directory=std::env::temp_dir().join(format!("oneqay-lock-test-{}",uuid::Uuid::new_v4()));
        std::fs::create_dir_all(&directory).unwrap();
        let login="synthetic-lock@example.test";
        let mut store=Store::open(directory.join(remember::profile(login))).unwrap();
        let at=now();
        let snapshot=Snapshot{protocol:2,cashier_name:"Synthetic cashier".into(),settings:json!({"business_type":"laundry","auto_lock_seconds":300}),lease_id:"a".repeat(32),scope:oneqay_pos_core::Scope{tenant_id:"test-tenant".into(),identity_id:"test-cashier".into(),organization_id:"test-org".into(),outlet_id:"test-outlet".into(),device_id:"test-device".into()},shift_id:String::new(),issued_at:at,expires_at:at+28800,server_time:at,catalog_revision:"fixture".into(),business_name:"Synthetic".into(),currency_symbol:"Rp".into(),can_count:false,catalog:vec![]};
        store.apply_snapshot(&snapshot,at).unwrap();
        let mut s=Session::new(directory.clone());s.signed_in=true;s.is_locked=false;s.login=login.into();s.profile=remember::profile(login);s.last_activity=at-301;s.auto_lock_override=Some(0);
        let sec=Arc::new(Mutex::new(s));
        assert_eq!(status(&sec,&store).unwrap()["locked"],false);
        sec.lock().unwrap().auto_lock_override=Some(300);
        assert_eq!(status(&sec,&store).unwrap()["locked"],true);
        sec.lock().unwrap().auto_lock_override=Some(0);
        assert_eq!(status(&sec,&store).unwrap()["locked"],true);
        assert!(remember::load(&directory).unwrap().active.unwrap().locked);
        assert!(require(&sec,&store,true).is_err());
        drop(store);drop(sec);std::fs::remove_dir_all(directory).unwrap();
    }

    #[test]
    fn verifier_roundtrip_and_pin_bounds() {
        let v = verifier("123456");
        assert!(verify("123456", &v));
        assert!(!verify("123457", &v));
        assert!(!verify("123456", &json!({"iterations":0})));
    }
}

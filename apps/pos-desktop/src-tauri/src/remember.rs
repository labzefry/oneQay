// Author by Lab | zefry. Windows-protected resume state; never exposed to the webview.
use crate::*;
use serde::{Deserialize, Serialize};
use std::{collections::BTreeMap, path::Path};
#[derive(Default, Serialize, Deserialize)]
pub struct Remembered {
    pub active: Option<Active>,
    #[serde(default)] pub preferences: BTreeMap<String,i64>,
    #[serde(default)] pub pending_logout: Vec<String>,
}
#[derive(Clone, Serialize, Deserialize)]
pub struct Active {
    pub profile: String,
    pub login: String,
    pub scope: oneqay_pos_core::Scope,
    pub locked: bool,
    pub last_activity: i64,
}
pub fn profile(login:&str)->String {
    use sha2::{Digest,Sha256};
    format!("cashier-{}.sqlite3",hex::encode(Sha256::digest(login.as_bytes())))
}
pub fn timeout_valid(seconds:i64)->bool {matches!(seconds,0|60|120|300|600|900|1800)}
pub fn load(directory:&Path)->Result<Remembered> {
    let db=Store::open(directory.join("pos.sqlite3")).map_err(safe)?;
    let Some(raw)=db.get("native_session_state").map_err(safe)? else{return Ok(Remembered::default())};
    let bytes:Vec<u8>=serde_json::from_str(&raw).map_err(|_|"POS_RESUME_STATE_INVALID")?;
    if bytes.len()>1024*1024{return Err("POS_RESUME_STATE_INVALID".into())}
    let saved:Remembered=serde_json::from_slice(&hardware::unprotect(&bytes)?).map_err(|_|"POS_RESUME_STATE_INVALID")?;
    if saved.pending_logout.len()>1024 || saved.pending_logout.iter().any(|t|t.len()!=64||!t.bytes().all(|b|b.is_ascii_hexdigit()))
        || saved.preferences.values().any(|v|!timeout_valid(*v)) {
        return Err("POS_RESUME_STATE_INVALID".into());
    }
    if let Some(a)=&saved.active {
        if a.login.is_empty() || a.login.len()>190 || a.profile!=profile(&a.login) || a.last_activity<=0 {
            return Err("POS_RESUME_STATE_INVALID".into());
        }
    }
    Ok(saved)
}
pub fn save(directory:&Path,saved:&Remembered)->Result<()> {
    let db=Store::open(directory.join("pos.sqlite3")).map_err(safe)?;
    let bytes=hardware::protect(&serde_json::to_vec(saved).map_err(|_|"POS_RESUME_STATE_INVALID")?)?;
    db.set("native_session_state",&serde_json::to_string(&bytes).map_err(|_|"POS_RESUME_STATE_INVALID")?).map_err(safe)
}
pub fn remember(session:&auth::Session,store:&Store)->Result<()> {
    let mut saved=load(&session.directory)?;
    let snapshot=store.snapshot().map_err(safe)?.ok_or("POS_CONNECT_FIRST")?;
    saved.active=Some(Active{profile:session.profile.clone(),login:session.login.clone(),scope:snapshot.scope,locked:session.is_locked,last_activity:session.last_activity});
    save(&session.directory,&saved)
}
pub fn forget(directory:&Path,token:Option<String>)->Result<()> {
    let mut saved=load(directory)?;
    saved.active=None;
    if let Some(t)=token {
        if !saved.pending_logout.contains(&t){saved.pending_logout.push(t)}
    }
    // One atomic SQLite metadata write clears auto-resume and retains revocation before network I/O.
    save(directory,&saved)
}
pub fn flush(directory:&Path)->Result<()> {
    let mut saved=load(directory)?;
    let candidates:Vec<String>=saved.pending_logout.iter().take(1).cloned().collect();
    let mut changed=false;
    for token in candidates {
        match request(auth::SERVER,"v3/logout",Some(&token),Some(json!({}))) {
            Ok(v) if v["logged_out"]==true=>{saved.pending_logout.retain(|t|t!=&token);changed=true;},
            _=>break,
        }
    }
    if changed{save(directory,&saved)?}
    Ok(())
}
#[cfg(test)]
mod tests {
    use super::*;
    #[test] fn profiles_cannot_be_paths_and_durations_are_bounded(){
        let p=profile("../../other@example.test");
        assert!(p.starts_with("cashier-")&&p.ends_with(".sqlite3")&&!p.contains('/')&&!p.contains('\\'));
        assert!(timeout_valid(0));assert!(timeout_valid(1800));assert!(!timeout_valid(-1));assert!(!timeout_valid(7));
    }
    #[cfg(target_os="windows")]
    #[test] fn logout_and_manual_lock_survive_windows_protected_roundtrip(){
        let dir=std::env::temp_dir().join(format!("oneqay-remember-{}",uuid::Uuid::new_v4()));
        std::fs::create_dir_all(&dir).unwrap();
        let scope=oneqay_pos_core::Scope{tenant_id:"test-tenant".into(),identity_id:"test-cashier".into(),organization_id:"test-org".into(),outlet_id:"test-outlet".into(),device_id:"test-device".into()};
        let mut saved=Remembered::default();
        let login="synthetic@example.test";
        saved.preferences.insert(profile(login),0);
        saved.active=Some(Active{profile:profile(login),login:login.into(),scope,locked:true,last_activity:now()});
        save(&dir,&saved).unwrap();let restored=load(&dir).unwrap();
        assert!(restored.active.unwrap().locked);assert_eq!(restored.preferences[&profile(login)],0);
        let token="a".repeat(64);forget(&dir,Some(token.clone())).unwrap();
        let logged_out=load(&dir).unwrap();assert!(logged_out.active.is_none());assert_eq!(logged_out.pending_logout,vec![token]);
        let mut invalid=load(&dir).unwrap();
        invalid.active=Some(Active{profile:"../other.sqlite3".into(),login:login.into(),scope:oneqay_pos_core::Scope{tenant_id:"test".into(),identity_id:"test".into(),organization_id:"test".into(),outlet_id:"test".into(),device_id:"test".into()},locked:false,last_activity:now()});
        save(&dir,&invalid).unwrap();assert!(load(&dir).is_err());
        std::fs::remove_dir_all(dir).unwrap();
    }
}

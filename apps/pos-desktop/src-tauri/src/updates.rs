// Author by Lab | zefry
use crate::*;
use tauri_plugin_updater::UpdaterExt;
use tauri::Emitter;
fn allowed(u: &tauri_plugin_updater::Update, current: &str) -> Result<()> {
    let next = semver::Version::parse(&u.version).map_err(|_| "UPDATE_VERSION_INVALID")?;
    let installed = semver::Version::parse(current).map_err(|_| "UPDATE_VERSION_INVALID")?;
    let url = &u.download_url;
    if next <= installed
        || !next.pre.is_empty()
        || !next.build.is_empty()
        || url.scheme() != "https"
        || url.host_str() != Some("github.com")
        || !url.path().starts_with(&format!(
            "/labzefry/oneQay/releases/download/pos-windows-v{next}/"
        ))
        || !url.path().ends_with("-setup.exe")
        || url.query().is_some()
        || url.fragment().is_some()
        || !url.username().is_empty()
        || url.password().is_some()
    {
        return Err("UPDATE_SOURCE_REJECTED".into());
    }
    Ok(())
}
#[tauri::command]
pub async fn check_update(app: tauri::AppHandle) -> Result<Value> {
    let current = app.package_info().version.to_string();
    let updater = app
        .updater_builder()
        .timeout(Duration::from_secs(20))
        .build()
        .map_err(|_| "UPDATE_CONFIGURATION_INVALID")?;
    let update = updater
        .check()
        .await
        .map_err(|_| "UPDATE_CHECK_UNAVAILABLE")?;
    match update {
        None => Ok(json!({"available":false,"current":current})),
        Some(u) => {
            allowed(&u, &current)?;
            Ok(
                json!({"available":true,"current":current,"version":u.version,"notes":u.body.unwrap_or_default()}),
            )
        }
    }
}
#[tauri::command]
pub async fn install_update(
    app: tauri::AppHandle,
    state: State<'_, Shared>,
    security: State<'_, auth::Security>,
    version: String,
) -> Result<()> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    {
        let store = locked(&db)?;
        // Trusted maintenance works before login; freeze mutations before backup.
        let mut session=sec.lock().map_err(|_|"POS_SECURITY_BUSY")?;
        if session.updating {return Err("UPDATE_IN_PROGRESS".into())}
        session.updating=true;
        drop(store);
    }
    let result = async {
        let updater = app
            .updater_builder()
            .timeout(Duration::from_secs(30))
            .build()
            .map_err(|_| "UPDATE_CONFIGURATION_INVALID")?;
        let update = updater
            .check()
            .await
            .map_err(|_| "UPDATE_CHECK_UNAVAILABLE")?
            .ok_or("UPDATE_NO_LONGER_AVAILABLE")?;
        allowed(&update, &app.package_info().version.to_string())?;
        if update.version != version {
            return Err("UPDATE_VERSION_CHANGED_CHECK_AGAIN".into());
        }
        // The plugin verifies the mandatory signature before returning these bytes.
        let mut downloaded=0u64;
        let bytes = update
            .download(|chunk,total|{downloaded=downloaded.saturating_add(chunk as u64);let _=app.emit("pos-update-progress",json!({"stage":"download","downloaded":downloaded,"total":total}));},||{let _=app.emit("pos-update-progress",json!({"stage":"verify"}));})
            .await
            .map_err(|_| "UPDATE_DOWNLOAD_OR_SIGNATURE_FAILED")?;
        let _=app.emit("pos-update-progress",json!({"stage":"backup"}));
        let db2 = db.clone();
        let sec2 = sec.clone();
        let backup_version = version.clone();
        background(move || {
            let store = locked(&db2)?;
            let s = sec2.lock().map_err(|_| "POS_SECURITY_BUSY")?;
            let destination =
                s.directory
                    .join("update-backups")
                    .join(format!("{}-{}", backup_version, now()));
            std::fs::create_dir_all(&destination).map_err(|_| "UPDATE_BACKUP_FAILED")?;
            for entry in std::fs::read_dir(&s.directory).map_err(|_| "UPDATE_BACKUP_FAILED")? {
                let entry = entry.map_err(|_| "UPDATE_BACKUP_FAILED")?;
                if !entry
                    .file_type()
                    .map_err(|_| "UPDATE_BACKUP_FAILED")?
                    .is_file()
                    || entry.path().extension().and_then(|s| s.to_str()) != Some("sqlite3")
                {
                    continue;
                }
                if entry.file_name().to_string_lossy() == s.profile {
                    store
                        .prepare_update(&destination.join(entry.file_name()))
                        .map_err(safe)?
                } else {
                    let profile = Store::open(entry.path()).map_err(safe)?;
                    profile
                        .prepare_update(&destination.join(entry.file_name()))
                        .map_err(safe)?;
                }
            }
            Ok(())
        })
        .await?;
        let _=app.emit("pos-update-progress",json!({"stage":"install"}));
        update
            .install(bytes)
            .map_err(|_| "UPDATE_INSTALL_FAILED".to_string())?;
        Ok(())
    }
    .await;
    if let Ok(mut s) = sec.lock() {
        s.updating = false;
    }
    result
}

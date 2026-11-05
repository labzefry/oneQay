// Author by Lab | zefry. Online-only QR settlement; no offline replay or local payment queue.
use crate::*;

#[tauri::command]
pub async fn menu_order(
    state: State<'_, Shared>,
    security: State<'_, auth::Security>,
    action: String,
    input: Value,
) -> Result<Value> {
    if !matches!(action.as_str(), "lookup" | "quote" | "settle" | "cancel" | "member-lookup" | "member-register")
        || !input.is_object() || input.to_string().len() > 16384
    {
        return Err("POS_MENU_REQUEST_INVALID".into());
    }
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || {
        let store = locked(&db)?;
        auth::require(&sec, &store, true)?;
        if action == "settle" && !store.pending().map_err(safe)?.is_empty() {
            return Err("POS_MENU_SHIFT_SYNC_REQUIRED".into());
        }
        let (_, token) = credential(&store)?;
        // Use the pinned first-party server. Neither URL nor credentials come from the webview.
        let path = format!("v3/menu-orders/{action}");
        let response = request(auth::SERVER, &path, Some(&token), Some(input));
        if let Err(ref error) = response {
            if error.starts_with("POS_SIGN_IN_AGAIN") || error.starts_with("POS_PERMISSION_DENIED") {
                store.set("authority_state", "blocked").map_err(safe)?;
                let mut session = sec.lock().map_err(|_| "POS_SECURITY_BUSY")?;
                session.signed_in = false;
                session.is_locked = true;
            }
        }
        response
    })
    .await
}

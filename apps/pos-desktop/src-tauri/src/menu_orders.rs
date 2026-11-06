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
        auth::request_authenticated(&store,&sec,&path,&token,Some(input))
    })
    .await
}

// Author by Lab | zefry. Separate durable service-POS outbox per cashier SQLite profile.
use crate::*;
fn service(store: &Store) -> Result<Snapshot> {
    let snapshot = store
        .snapshot()
        .map_err(safe)?
        .ok_or("POS_SNAPSHOT_REQUIRED")?;
    if !matches!(
        snapshot.settings["business_type"].as_str(),
        Some("laundry" | "boarding" | "homestay")
    ) {
        return Err("BUSINESS_WORKFLOW_MISMATCH".into());
    }
    Ok(snapshot)
}
fn queue(store: &Store) -> Result<Vec<Value>> {
    store
        .get("service_outbox")
        .map_err(safe)?
        .map(|v| serde_json::from_str(&v).map_err(|_| "POS_SERVICE_QUEUE_INVALID".to_string()))
        .unwrap_or(Ok(Vec::new()))
}
fn write(store: &Store, events: &[Value]) -> Result<()> {
    store
        .set(
            "service_outbox",
            &serde_json::to_string(events).map_err(|_| "POS_SERVICE_QUEUE_INVALID")?,
        )
        .map_err(safe)
}
fn checked_request(store:&Store,sec:&auth::Security,path:&str,token:&str,body:Option<Value>)->Result<Value>{
 let result=request(auth::SERVER,path,Some(token),body);
 if let Err(ref e)=result{if e.starts_with("POS_SIGN_IN_AGAIN")||e.starts_with("POS_PERMISSION_DENIED"){store.set("authority_state","blocked").map_err(safe)?;let mut security=sec.lock().map_err(|_|"POS_SECURITY_BUSY")?;security.signed_in=false;security.is_locked=true;}}
 result
}
fn flush(store: &Store, sec: &auth::Security) -> Result<()> {
    let (_, token) = credential(store)?;
    let mut events = queue(store)?;
    for n in 0..events.len() {
        if events[n]["state"] == "review" {
            return Ok(());
        }
        if events[n]["state"] != "pending" {
            continue;
        }
        let response = checked_request(
            store,sec,
            "v3/service",
            &token,
            Some(events[n]["request"].clone()),
        );
        match response {
            Ok(v) => {
                events[n]["state"] = json!(if v["record"]["rejected"] == true {
                    "cancelled"
                } else {
                    "accepted"
                });
                events[n]["result"] = v["record"].clone();
                store
                    .set("service_workspace", &v["workspace"].to_string())
                    .map_err(safe)?;
                write(store, &events)?;
            }
            Err(e) => {
                if e.starts_with("POS_SIGN_IN_AGAIN") || e.starts_with("POS_PERMISSION_DENIED") {
                    store.set("authority_state", "blocked").map_err(safe)?;
                    let mut s = sec.lock().map_err(|_| "POS_SECURITY_BUSY")?;
                    s.signed_in = false;
                    s.is_locked = true;
                } else if e.starts_with("POS_OPEN_SHIFT_AND_PREPARE_CATALOG")
                    || e.starts_with("POS_SERVICE_REQUEST_REJECTED")
                {
                    events[n]["state"] = json!("review");
                    events[n]["code"] = json!(e.clone());
                    write(store, &events)?;
                }
                return Err(e);
            }
        }
    }
    if events.len()>400 {let from=events.len().saturating_sub(200);events=events.into_iter().enumerate().filter(|(i,e)|*i>=from||matches!(e["state"].as_str(),Some("pending"|"review"))).map(|(_,e)|e).collect();write(store,&events)?;}
    Ok(())
}
fn workspace(store: &Store) -> Result<Value> {
    let snapshot = service(store)?;
    let raw = store.get("service_workspace").map_err(safe)?;
    let mut v = match raw {
        Some(raw) => serde_json::from_str::<Value>(&raw).map_err(|_| "POS_SERVICE_DATA_INVALID")?,
        None => snapshot.settings["workflow_snapshot"].clone(),
    };
    let events = queue(store)?;
    // Project pending cash operations locally. They remain provisional until server acknowledgement.
    for event in events.iter().filter(|e| e["state"] == "pending") {
        let command = &event["request"]["command"];
        match command["action"].as_str() {
            Some("shift.open") => {
                v["shift"] = json!({"id":"local-pending","opening_atomic":command["opening_atomic"],"provisional":true})
            }
            Some("shift.close") => v["shift"] = Value::Null,
            Some("payment.record") => {
                if let Some(invoices) = v["invoices"].as_array_mut() {
                    for invoice in invoices {
                        if invoice["id"] == command["invoice_id"] {
                            let paid = invoice["paid_atomic"].as_i64().unwrap_or(0);
                            let amount = command["amount_atomic"].as_i64().unwrap_or(0);
                            invoice["paid_atomic"] = json!(paid.saturating_add(amount));
                            invoice["provisional"] = json!(true);
                        }
                    }
                }
            }
            _ => {}
        }
    }
    if let Some(closed) = events
        .iter()
        .rev()
        .find(|e| e["state"] == "accepted" && e["request"]["command"]["action"] == "shift.close")
    {
        v["last_closing"] = closed["result"].clone();
    }
    v["local_queue"] = json!(events
        .iter()
        .filter(|v| v["state"] == "pending" || v["state"] == "review")
        .collect::<Vec<_>>());
    Ok(v)
}
#[tauri::command]
pub async fn service_workspace(
    page:Option<u32>,
    state: State<'_, Shared>,
    security: State<'_, auth::Security>,
) -> Result<Value> {
    let page=page.unwrap_or(1);if page==0||page>100000{return Err("POS_PAGE_INVALID".into())}
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || {
        let store = locked(&db)?;
        auth::require(&sec, &store, false)?;
        service(&store)?;
        match flush(&store, &sec) {
            Ok(()) => {}
            Err(e) if is_network_unavailable(&e) => {
                let mut w = workspace(&store)?;
                w["connection"] = json!("offline");
                return Ok(w);
            }
            Err(e) => return Err(e),
        }
        let (_, token) = credential(&store)?;
        match checked_request(&store,&sec, &format!("v3/service?page={page}"), &token, None) {
            Ok(v) => {
                store
                    .set("service_workspace", &v.to_string())
                    .map_err(safe)?;
                let mut w = workspace(&store)?;
                w["connection"] = json!("online");
                Ok(w)
            }
            Err(e) if is_network_unavailable(&e) => {
                let mut w = workspace(&store)?;
                w["connection"] = json!("offline");
                Ok(w)
            }
            Err(e) => Err(e),
        }
    })
    .await
}
#[tauri::command]
pub async fn service_command(
    state: State<'_, Shared>,
    security: State<'_, auth::Security>,
    input: Value,
) -> Result<Value> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move||{let store=locked(&db)?;auth::require(&sec,&store,true)?;let snapshot=service(&store)?;
 if store.lease_remaining().map_err(safe)?<=0{return Err("POS_OFFLINE_LEASE_EXPIRED".into())}if input.to_string().len()>8192||input["operation_id"].as_str().and_then(|v|uuid::Uuid::parse_str(v).ok()).is_none(){return Err("POS_SERVICE_INPUT_INVALID".into())}
 // Resource/rate administration remains exclusively in the web application.
 if !matches!(input["action"].as_str(),Some("order.create"|"order.transition"|"invoice.create"|"payment.record"|"shift.open"|"shift.close")){return Err("POS_WEB_ADMIN_REQUIRED".into())}
 let mut events=queue(&store)?;if events.iter().any(|e|e["state"]=="review"){return Err("POS_SERVICE_REVIEW_REQUIRED".into())}
 let id=input["operation_id"].clone();if let Some(old)=events.iter().find(|e|e["request"]["command"]["operation_id"]==id){if old["request"]["command"]!=input{return Err("POS_OPERATION_PAYLOAD_CHANGED".into())}}
 else {if events.iter().filter(|v|v["state"]=="pending").count()>=100{return Err("POS_SERVICE_QUEUE_FULL".into())}if events.iter().any(|e|e["state"]=="pending"&&e["request"]["command"]["action"]=="shift.close"){return Err("POS_SERVICE_CLOSURE_PENDING".into())}events.push(json!({"state":"pending","request":{"command":input,"lease_id":snapshot.lease_id,"created_at":snapshot.server_time+now()-store.get("clock_anchor").map_err(safe)?.and_then(|v|v.parse::<i64>().ok()).unwrap_or(now())}}));write(&store,&events)?;}
 match flush(&store,&sec){Ok(())=>{let events=queue(&store)?;let event=events.iter().find(|e|e["request"]["command"]["operation_id"]==id).ok_or("POS_SERVICE_QUEUE_INVALID")?;Ok(json!({"record":event["result"],"workspace":workspace(&store)?}))},Err(e)if is_network_unavailable(&e)=>Ok(json!({"queued":true,"operation_id":id})),Err(e)=>Err(e)}
 }).await
}

#[tauri::command]
pub async fn service_reconcile(
    state: State<'_, Shared>,
    security: State<'_, auth::Security>,
    operation_id: String,
) -> Result<Value> {
    let db = state.inner().clone();
    let sec = security.inner().clone();
    background(move || {
        let store = locked(&db)?;
        auth::require(&sec, &store, true)?;
        service(&store)?;
        let mut events = queue(&store)?;
        let index = events
            .iter()
            .position(|e| {
                e["request"]["command"]["operation_id"].as_str() == Some(operation_id.as_str())
                    && matches!(e["state"].as_str(), Some("review" | "pending"))
            })
            .ok_or("POS_SERVICE_OPERATION_NOT_FOUND")?;
        let (_, token) = credential(&store)?;
        let result = checked_request(
            &store,&sec,
            "v3/service/reconcile",
            &token,
            Some(events[index]["request"]["command"].clone()),
        )?;
        events[index]["state"] = json!(if result["record"]["rejected"] == true {
            "cancelled"
        } else {
            "accepted"
        });
        events[index]["result"] = result["record"].clone();
        write(&store, &events)?;
        store
            .set("service_workspace", &result["workspace"].to_string())
            .map_err(safe)?;
        Ok(json!({"record":result["record"],"workspace":workspace(&store)?}))
    })
    .await
}

// Author by Lab | zefry. Separate durable service-POS outbox per cashier SQLite profile.
use crate::*;
fn service(store:&Store)->Result<Snapshot>{
 let snapshot=store.snapshot().map_err(safe)?.ok_or("POS_SNAPSHOT_REQUIRED")?;
 if !matches!(snapshot.settings["business_type"].as_str(),Some("laundry"|"boarding"|"homestay")){return Err("BUSINESS_WORKFLOW_MISMATCH".into())}Ok(snapshot)
}
fn queue(store:&Store)->Result<Vec<Value>>{store.get("service_outbox").map_err(safe)?.map(|v|serde_json::from_str(&v).map_err(|_|"POS_SERVICE_QUEUE_INVALID".to_string())).unwrap_or(Ok(Vec::new()))}
fn write(store:&Store,events:&[Value])->Result<()>{store.set("service_outbox",&serde_json::to_string(events).map_err(|_|"POS_SERVICE_QUEUE_INVALID")?).map_err(safe)}
fn flush(store:&Store,sec:&auth::Security)->Result<()> {
 let(_,token)=credential(store)?;let mut events=queue(store)?;
 for n in 0..events.len(){
  if events[n]["state"]=="review"{return Err("POS_SERVICE_REVIEW_REQUIRED".into())}
  if events[n]["state"]!="pending"{continue}
  let response=request(auth::SERVER,"v3/service",Some(&token),Some(events[n]["request"].clone()));
  match response {
   Ok(v)=>{events[n]["state"]=json!("accepted");events[n]["result"]=v["record"].clone();store.set("service_workspace",&v["workspace"].to_string()).map_err(safe)?;write(store,&events)?;},
   Err(e)=>{
    if e.starts_with("POS_SIGN_IN_AGAIN")||e.starts_with("POS_PERMISSION_DENIED"){store.set("authority_state","blocked").map_err(safe)?;let mut s=sec.lock().map_err(|_|"POS_SECURITY_BUSY")?;s.signed_in=false;s.is_locked=true;}
    else if e.starts_with("POS_OPEN_SHIFT_AND_PREPARE_CATALOG")||e.starts_with("POS_SERVICE_REQUEST_REJECTED") {events[n]["state"]=json!("review");events[n]["code"]=json!(e.clone());write(store,&events)?;}
    return Err(e)
   }
  }
 }Ok(())
}
fn workspace(store:&Store)->Result<Value>{let snapshot=service(store)?;let raw=store.get("service_workspace").map_err(safe)?;let mut v=match raw{Some(raw)=>serde_json::from_str::<Value>(&raw).map_err(|_|"POS_SERVICE_DATA_INVALID")?,None=>snapshot.settings["workflow_snapshot"].clone()};let events=queue(store)?;v["local_queue"]=json!(events.iter().filter(|v|v["state"]!="accepted").collect::<Vec<_>>());Ok(v)}
#[tauri::command]
pub async fn service_workspace(state:State<'_,Shared>,security:State<'_,auth::Security>)->Result<Value>{let db=state.inner().clone();let sec=security.inner().clone();background(move||{let store=locked(&db)?;auth::require(&sec,&store,false)?;service(&store)?;
 match flush(&store,&sec){Ok(())=>{},Err(e)if is_network_unavailable(&e)=>{let mut w=workspace(&store)?;w["connection"]=json!("offline");return Ok(w)},Err(e)=>return Err(e)}
 let(_,token)=credential(&store)?;match request(auth::SERVER,"v3/service",Some(&token),None){Ok(v)=>{store.set("service_workspace",&v.to_string()).map_err(safe)?;let mut w=workspace(&store)?;w["connection"]=json!("online");Ok(w)},Err(e)if is_network_unavailable(&e)=>{let mut w=workspace(&store)?;w["connection"]=json!("offline");Ok(w)},Err(e)=>Err(e)}
 }).await}
#[tauri::command]
pub async fn service_command(state:State<'_,Shared>,security:State<'_,auth::Security>,input:Value)->Result<Value>{let db=state.inner().clone();let sec=security.inner().clone();background(move||{let store=locked(&db)?;auth::require(&sec,&store,true)?;let snapshot=service(&store)?;
 if store.lease_remaining().map_err(safe)?<=0{return Err("POS_OFFLINE_LEASE_EXPIRED".into())}if input.to_string().len()>8192||input["operation_id"].as_str().and_then(|v|uuid::Uuid::parse_str(v).ok()).is_none(){return Err("POS_SERVICE_INPUT_INVALID".into())}
 // Resource/rate administration remains exclusively in the web application.
 if !matches!(input["action"].as_str(),Some("order.create"|"order.transition"|"invoice.create"|"payment.record"|"shift.open"|"shift.close")){return Err("POS_WEB_ADMIN_REQUIRED".into())}
 let mut events=queue(&store)?;if events.iter().any(|e|e["state"]=="review"){return Err("POS_SERVICE_REVIEW_REQUIRED".into())}
 let id=input["operation_id"].clone();if let Some(old)=events.iter().find(|e|e["request"]["command"]["operation_id"]==id){if old["request"]["command"]!=input{return Err("POS_OPERATION_PAYLOAD_CHANGED".into())}}
 else {if events.iter().filter(|v|v["state"]=="pending").count()>=100{return Err("POS_SERVICE_QUEUE_FULL".into())}if events.iter().any(|e|e["state"]=="pending"&&e["request"]["command"]["action"]=="shift.close"){return Err("POS_SERVICE_CLOSURE_PENDING".into())}events.push(json!({"state":"pending","request":{"command":input,"lease_id":snapshot.lease_id,"created_at":snapshot.server_time+now()-store.get("clock_anchor").map_err(safe)?.and_then(|v|v.parse::<i64>().ok()).unwrap_or(now())}}));write(&store,&events)?;}
 match flush(&store,&sec){Ok(())=>{let events=queue(&store)?;let event=events.iter().find(|e|e["request"]["command"]["operation_id"]==id).ok_or("POS_SERVICE_QUEUE_INVALID")?;Ok(json!({"record":event["result"],"workspace":workspace(&store)?}))},Err(e)if is_network_unavailable(&e)=>Ok(json!({"queued":true,"operation_id":id})),Err(e)=>Err(e)}
 }).await}

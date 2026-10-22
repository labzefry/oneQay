use oneqay_pos_core::*;
use serde_json::json;
fn fixture() -> Snapshot {
    Snapshot {
        protocol: 1,
        cashier_name: "Kasir".into(),
        settings: json!({}),
        lease_id: "a".repeat(32),
        scope: Scope {
            tenant_id: "t".into(),
            identity_id: "i".into(),
            organization_id: "o".into(),
            outlet_id: "out".into(),
            device_id: "dev".into(),
        },
        shift_id: "shift".into(),
        issued_at: 1000,
        expires_at: 29800,
        server_time: 1000,
        catalog_revision: "r".into(),
        business_name: "Warung".into(),
        currency_symbol: "Rp".into(),
        can_count: true,
        catalog: vec![Product {
            id: "p".into(),
            name: "Kopi\x1bp".into(),
            price: 15000,
            currency: "IDR".into(),
            scale: 0,
            stock: 10,
            tracks_stock: true,
            barcode: "123".into(),
            category: "Minuman".into(),
        }],
    }
}
fn db() -> Store {
    let mut s = Store::open(":memory:").unwrap();
    s.apply_snapshot(&fixture(), 1000).unwrap();
    s
}
fn id() -> String {
    format!("desk-{}", uuid::Uuid::new_v4())
}
fn lines(q: i64) -> Vec<Line> {
    vec![Line {
        product_id: "p".into(),
        quantity: q,
    }]
}
#[test]
fn sale_is_atomic_and_replay_does_not_decrement_twice() {
    let mut s = db();
    let id = id();
    s.commit(&id, "sale", lines(2), 50000, 1001).unwrap();
    s.commit(&id, "sale", lines(2), 50000, 1002).unwrap();
    assert_eq!(s.products().unwrap()[0].stock, 8);
    assert_eq!(s.pending().unwrap().len(), 1);
    assert_eq!(s.operation(&id).unwrap().receipt["change"], 20000);
    assert!(s.commit(&id, "sale", lines(3), 50000, 1002).is_err());
}
#[test]
fn cash_stock_and_quantity_errors_preserve_state() {
    let mut s = db();
    assert!(s.commit(&id(), "sale", lines(2), 1, 1001).is_err());
    assert!(s.commit(&id(), "sale", lines(11), 999999, 1001).is_err());
    assert!(s.commit(&id(), "sale", lines(-1), 999999, 1001).is_err());
    assert_eq!(s.products().unwrap()[0].stock, 10);
    assert!(s.pending().unwrap().is_empty());
}
#[test]
fn restart_preserves_sale_and_outbox() {
    let d = tempfile::tempdir().unwrap();
    let path = d.path().join("pos.db");
    let id = id();
    {
        let mut s = Store::open(&path).unwrap();
        s.apply_snapshot(&fixture(), 1000).unwrap();
        s.commit(&id, "sale", lines(1), 15000, 1001).unwrap();
    }
    let s = Store::open(&path).unwrap();
    assert_eq!(s.pending().unwrap()[0].operation_id, id);
    assert_eq!(s.products().unwrap()[0].stock, 9);
}
#[test]
fn failed_print_job_insert_rolls_back_sale_stock_and_outbox() {
    let d = tempfile::tempdir().unwrap();
    let path = d.path().join("pos.db");
    let mut s = Store::open(&path).unwrap();
    s.apply_snapshot(&fixture(), 1000).unwrap();
    let external = rusqlite::Connection::open(&path).unwrap();
    external.execute_batch("CREATE TRIGGER injected_disk_failure BEFORE INSERT ON print_jobs BEGIN SELECT RAISE(ABORT,'synthetic write failure'); END;").unwrap();
    assert!(s.commit(&id(), "sale", lines(1), 15000, 1001).is_err());
    assert!(s.pending().unwrap().is_empty());
    assert_eq!(s.products().unwrap()[0].stock, 10);
}
#[test]
fn expiry_clock_rollback_and_account_switch_are_blocked() {
    let mut s = db();
    assert!(s.commit(&id(), "sale", lines(1), 15000, 29801).is_err());
    s.commit(&id(), "sale", lines(1), 15000, 1500).unwrap();
    assert!(s.commit(&id(), "sale", lines(1), 15000, 1100).is_err());
    let mut next = fixture();
    next.scope.tenant_id = "other".into();
    assert!(s.apply_snapshot(&next, 1501).is_err());
    assert_eq!(s.snapshot().unwrap().unwrap().scope.tenant_id, "t");
}
#[test]
fn partial_ack_and_conflict_preserve_records_and_stock() {
    let mut s = db();
    let a = id();
    let b = id();
    s.commit(&a, "sale", lines(1), 15000, 1001).unwrap();
    s.commit(&b, "sale", lines(2), 30000, 1002).unwrap();
    s.acknowledge(&[
        json!({"operation_id":a,"state":"accepted","code":"POS_SYNC_ACCEPTED","sale_id":"sale-a"}),
    ])
    .unwrap();
    assert_eq!(s.pending().unwrap().len(), 1);
    s.acknowledge(&[json!({"operation_id":b,"state":"conflict","code":"POS_STOCK_CHANGED"})])
        .unwrap();
    let mut snap = fixture();
    snap.catalog[0].stock = 9;
    s.apply_snapshot(&snap, 1003).unwrap();
    assert_eq!(s.products().unwrap()[0].stock, 7);
    assert_eq!(s.operations().unwrap().len(), 2);
    assert_eq!(s.workspace().unwrap().review, 1);
}
#[test]
fn malformed_batch_ack_rolls_back_entire_batch() {
    let mut s = db();
    let a = id();
    s.commit(&a, "sale", lines(1), 15000, 1001).unwrap();
    assert!(s
        .acknowledge(&[
            json!({"operation_id":a,"state":"accepted","code":"OK","sale_id":"a"}),
            json!({"operation_id":"b","state":"lost"})
        ])
        .is_err());
    assert_eq!(s.pending().unwrap().len(), 1);
}
#[test]
fn count_does_not_change_sellable_stock() {
    let mut s = db();
    s.commit(&id(), "count", lines(0), 0, 1001).unwrap();
    assert_eq!(s.products().unwrap()[0].stock, 10);
    assert_eq!(s.pending().unwrap()[0].kind, "count");
}
#[test]
fn crashed_print_requires_explicit_copy() {
    let d = tempfile::tempdir().unwrap();
    let path = d.path().join("pos.db");
    let id = id();
    {
        let mut s = Store::open(&path).unwrap();
        s.apply_snapshot(&fixture(), 1000).unwrap();
        s.commit(&id, "sale", lines(1), 15000, 1001).unwrap();
        s.claim_print(&id, false).unwrap();
    }
    let mut s = Store::open(&path).unwrap();
    assert!(s.claim_print(&id, false).is_err());
    let (_, r) = s.claim_print(&id, true).unwrap();
    assert_eq!(r["duplicate"], true);
}
#[test]
fn receipt_removes_control_sequences() {
    let mut s = db();
    let id = id();
    s.commit(&id, "sale", lines(1), 15000, 1001).unwrap();
    let bytes = receipt_bytes(&s.operation(&id).unwrap().receipt, 80);
    assert!(!bytes.windows(2).any(|w| w == [0x1b, 0x70]));
    assert!(String::from_utf8_lossy(&bytes).contains("LOKAL"));
}
#[test]
fn old_receipt_is_replayable_after_history_window() {
    let mut s = db();
    let old = id();
    s.commit(&old, "count", lines(0), 0, 1001).unwrap();
    for _ in 0..1001 {
        s.commit(&id(), "count", lines(0), 0, 1001).unwrap();
    }
    assert_eq!(s.operations().unwrap().len(), 1000);
    assert_eq!(s.operation(&old).unwrap().operation.operation_id, old);
    assert!(s.commit(&old, "count", lines(0), 0, 1002).is_ok());
}

#[test]
fn known_revocation_blocks_new_sales_but_preserves_pending() {
    let mut s = db();
    s.commit(&id(), "sale", lines(1), 15000, 1001).unwrap();
    s.set("authority_state", "blocked").unwrap();
    assert!(s.commit(&id(), "sale", lines(1), 15000, 1002).is_err());
    assert_eq!(s.pending().unwrap().len(), 1);
    assert!(!s.workspace().unwrap().authorized);
    s.apply_snapshot(&fixture(), 1002).unwrap();
    assert!(s.workspace().unwrap().authorized);
}
#[test]
fn count_capability_is_required_locally() {
    let mut s = db();
    let mut snap = fixture();
    snap.can_count = false;
    s.apply_snapshot(&snap, 1000).unwrap();
    assert!(s.commit(&id(), "count", lines(0), 0, 1001).is_err());
    assert!(s.pending().unwrap().is_empty());
}

#[test]
fn decimal_receipt_prices_use_catalog_currency_scale() {
    let bytes = receipt_bytes(
        &json!({"currency":"USD","scale":2,"lines":[{"name":"Coffee","price":1250,"quantity":2}],"total":2500,"tendered":3000,"change":500}),
        80,
    );
    let text = String::from_utf8_lossy(&bytes);
    assert!(text.contains("2 x 12.50"));
    assert!(text.contains("TOTAL USD 25.00"));
    assert!(text.contains("KEMBALI 5.00"));
}

fn enterprise() -> Snapshot {
    let mut s = fixture();
    s.protocol = 2;
    s.shift_id = String::new();
    s.settings = json!({"show_logo":true,"logo":{"width":8,"height":1,"bits":"/w=="},"footer":"Sampai jumpa","auto_lock_seconds":60,"kitchens":[{"id":"bar","name":"BAR","categories":["Minuman"]},{"id":"food","name":"DAPUR","categories":["Makanan"]}]});
    s
}
#[test]
fn offline_shift_sale_close_restart_is_fifo_and_exactly_once() {
    let d = tempfile::tempdir().unwrap();
    let path = d.path().join("cashier.sqlite3");
    let open = id();
    let sale = id();
    let close = id();
    {
        let mut s = Store::open(&path).unwrap();
        s.apply_snapshot(&enterprise(), 1000).unwrap();
        assert!(s.commit(&id(), "sale", lines(1), 15000, 1001).is_err());
        s.shift_event(&open, true, 100000, "", 1001).unwrap();
        s.shift_event(&open, true, 100000, "", 1001).unwrap();
        assert!(s.shift_event(&id(), true, 1, "", 1001).is_err());
        s.set_order_context("04", "A-001").unwrap();
        s.commit(&sale, "sale", lines(2), 50000, 1002).unwrap();
        let r = s
            .shift_event(&close, false, 129000, "Selisih dihitung", 1003)
            .unwrap();
        assert_eq!(r.receipt["expected"], 130000);
        assert_eq!(r.receipt["variance"], -1000);
        assert!(s.active_shift().unwrap().is_none());
        s.shift_event(&close, false, 129000, "Selisih dihitung", 1004)
            .unwrap();
        assert!(s.shift_event(&close, false, 130000, "", 1004).is_err());
    }
    let mut s = Store::open(&path).unwrap();
    let batch = s.pending().unwrap();
    assert_eq!(
        batch.iter().map(|o| o.kind.as_str()).collect::<Vec<_>>(),
        vec!["shift_open", "sale", "shift_close"]
    );
    assert_eq!(batch[1].shift_id, open);
    assert_eq!(batch[1].table, "04");
    assert_eq!(s.products().unwrap()[0].stock, 8);
    s.acknowledge(&[
        json!({"operation_id":open,"state":"accepted","code":"OK","shift_id":"server-shift"}),
        json!({"operation_id":sale,"state":"accepted","code":"OK","sale_id":"server-sale"}),
        json!({"operation_id":close,"state":"pending","code":"POS_SHIFT_REQUIRES_REVIEW"}),
    ])
    .unwrap();
    assert_eq!(s.pending().unwrap().len(), 1);
    s.shift_event(&id(), true, 1000, "", 1005).unwrap();
    assert_eq!(s.pending().unwrap().len(), 2);
}
#[test]
fn kitchen_routing_and_crash_do_not_duplicate_tickets() {
    let d = tempfile::tempdir().unwrap();
    let path = d.path().join("pos.sqlite3");
    let mut s = Store::open(&path).unwrap();
    s.apply_snapshot(&enterprise(), 1000).unwrap();
    s.shift_event(&id(), true, 0, "", 1001).unwrap();
    s.set_order_context("T-9", "O-7").unwrap();
    let sale = id();
    s.commit(&sale, "sale", lines(1), 15000, 1002).unwrap();
    assert_eq!(s.kitchen_jobs().unwrap().len(), 1);
    let job = s.kitchen_jobs().unwrap()[0]["id"]
        .as_str()
        .unwrap()
        .to_owned();
    assert!(s.claim_kitchen(&job, false).is_err());
    let mut p = PrinterSettings::default();
    p.queue = "Kitchen USB".into();
    p.paper_mm = 58;
    s.save_kitchen_printers(&json!({"bar":p})).unwrap();
    let (_, r) = s.claim_kitchen(&job, false).unwrap();
    let bytes = kitchen_bytes(&r, 58);
    let text = String::from_utf8_lossy(&bytes);
    assert!(text.contains("T-9") && text.contains("O-7"));
    assert!(!text.contains("15000"));
    assert!(!bytes.windows(3).any(|w| w == [0x1d, 0x76, 0x30]));
    drop(s);
    let mut s = Store::open(&path).unwrap();
    assert_eq!(s.kitchen_jobs().unwrap()[0]["state"], "uncertain");
    assert!(s.claim_kitchen(&job, false).is_err());
    let (_, copy) = s.claim_kitchen(&job, true).unwrap();
    assert_eq!(copy["duplicate"], true);
    s.finish_kitchen(&job, true).unwrap();
    assert_eq!(s.kitchen_jobs().unwrap()[0]["state"], "done");
}
#[test]
fn bounded_logo_is_only_in_customer_bill() {
    let mut s = Store::open(":memory:").unwrap();
    s.apply_snapshot(&enterprise(), 1000).unwrap();
    s.shift_event(&id(), true, 0, "", 1001).unwrap();
    let sale = id();
    s.commit(&sale, "sale", lines(1), 15000, 1002).unwrap();
    let bill = receipt_bytes(&s.operation(&sale).unwrap().receipt, 58);
    assert!(bill.windows(3).any(|w| w == [0x1d, 0x76, 0x30]));
    assert!(String::from_utf8_lossy(&bill).contains("Sampai jumpa"));
    let r = s.shift_event(&id(), false, 15000, "", 1003).unwrap();
    let close = receipt_bytes(&r.receipt, 58);
    assert!(!close.windows(3).any(|w| w == [0x1d, 0x76, 0x30]));
    assert!(logo_bytes(&json!({"width":99999,"height":99999,"bits":"/w=="})).is_empty());
    let mut prefs = enterprise();
    prefs.settings["show_logo"] = json!(false);
    s.apply_snapshot(&prefs, 1004).unwrap();
    s.shift_event(&id(), true, 0, "", 1004).unwrap();
    let sale = id();
    s.commit(&sale, "sale", lines(1), 15000, 1005).unwrap();
    assert!(s.operation(&sale).unwrap().receipt["logo"].is_null());
}
#[test]
fn update_backs_up_pending_cash_and_active_shifts_without_rewriting_them() {
    let d = tempfile::tempdir().unwrap();
    let path = d.path().join("pos.sqlite3");
    let mut s = Store::open(&path).unwrap();
    s.apply_snapshot(&enterprise(), 1000).unwrap();
    let open = id();
    s.shift_event(&open, true, 50000, "", 1001).unwrap();
    let backup=d.path().join(format!("pending-{}.sqlite3",s.operations().unwrap().len()));s.prepare_update(&backup).unwrap();
    let restored=Store::open(&backup).unwrap();assert_eq!(serde_json::to_value(restored.operations().unwrap()).unwrap(),serde_json::to_value(s.operations().unwrap()).unwrap());assert_eq!(restored.active_shift().unwrap(),s.active_shift().unwrap());
    let close = id();
    s.shift_event(&close, false, 50000, "", 1002).unwrap();
    let backup=d.path().join(format!("pending-{}.sqlite3",s.operations().unwrap().len()));s.prepare_update(&backup).unwrap();
    let restored=Store::open(&backup).unwrap();assert_eq!(serde_json::to_value(restored.operations().unwrap()).unwrap(),serde_json::to_value(s.operations().unwrap()).unwrap());assert_eq!(restored.active_shift().unwrap(),s.active_shift().unwrap());
    s.acknowledge(&[
        json!({"operation_id":open,"state":"accepted","code":"OK","shift_id":"server"}),
        json!({"operation_id":close,"state":"accepted","code":"OK","shift_id":"server"}),
    ])
    .unwrap();
    let backup = d.path().join("backup.sqlite3");
    s.prepare_update(&backup).unwrap();
    let restored = Store::open(&backup).unwrap();
    assert_eq!(restored.operations().unwrap().len(), 2);
    assert_eq!(restored.snapshot().unwrap().unwrap().cashier_name, "Kasir");
    assert!(s.prepare_update(&backup).is_err());
}
#[test]
fn shift_ack_without_server_reference_is_rejected() {
    let mut s = Store::open(":memory:").unwrap();
    s.apply_snapshot(&enterprise(), 1000).unwrap();
    let id = id();
    s.shift_event(&id, true, 0, "", 1001).unwrap();
    assert!(s
        .acknowledge(&[json!({"operation_id":id,"state":"accepted","code":"OK"})])
        .is_err());
    assert_eq!(s.pending().unwrap().len(), 1);
}
#[test]
fn unlimited_stock_is_explicit_and_survives_offline_reopen_and_replay() {
    let mut s = Store::open(":memory:").unwrap();
    let mut snapshot = fixture();
    snapshot.catalog[0].stock = 0;
    snapshot.catalog[0].tracks_stock = false;
    s.apply_snapshot(&snapshot, 1000).unwrap();
    let operation = id();
    s.commit(&operation, "sale", lines(3), 50000, 1001).unwrap();
    s.commit(&operation, "sale", lines(3), 50000, 1002).unwrap();
    assert_eq!(s.products().unwrap()[0].stock, 0);
    assert!(!s.products().unwrap()[0].tracks_stock);
    assert_eq!(s.pending().unwrap().len(), 1);
    s.apply_snapshot(&snapshot, 1003).unwrap();
    s.commit(&id(), "sale", lines(3), 50000, 1004).unwrap();
    assert_eq!(s.products().unwrap()[0].stock, 0);
    assert_eq!(s.pending().unwrap().len(), 2);
    let mut legacy = serde_json::to_value(&snapshot.catalog[0]).unwrap();
    legacy.as_object_mut().unwrap().remove("tracks_stock");
    let product: Product = serde_json::from_value(legacy).unwrap();
    assert!(product.tracks_stock, "legacy snapshots must never become unlimited implicitly");
}

#[test]
fn explicit_invalid_retry_preserves_payload_receipt_and_stock() {
    let mut s=Store::open(":memory:").unwrap();s.apply_snapshot(&enterprise(),1000).unwrap();
    let opening=id();s.shift_event(&opening,true,1000,"",1001).unwrap();
    s.acknowledge(&[json!({"operation_id":opening,"state":"rejected","code":"POS_INVALID_OPERATION"})]).unwrap();
    let sale=id();s.commit(&sale,"sale",lines(1),50000,1002).unwrap();
    s.acknowledge(&[json!({"operation_id":sale,"state":"rejected","code":"POS_INVALID_OPERATION"})]).unwrap();
    let before=s.operation(&sale).unwrap();let stock=s.products().unwrap()[0].stock;
    assert_eq!(s.retry_invalid_operations().unwrap(),2);assert_eq!(s.retry_invalid_operations().unwrap(),0);
    let after=s.operation(&sale).unwrap();assert_eq!(serde_json::to_value(before.operation).unwrap(),serde_json::to_value(after.operation).unwrap());
    assert_eq!(before.receipt,after.receipt);assert_eq!(stock,s.products().unwrap()[0].stock);
    assert_eq!(s.pending().unwrap()[0].operation_id,opening);
    s.acknowledge(&[json!({"operation_id":opening,"state":"rejected","code":"POS_LEASE_NOT_FOUND"})]).unwrap();
    assert_eq!(s.retry_invalid_operations().unwrap(),0);
}

#[test]
fn remote_shift_keeps_login_separate_from_register_ownership() {
 let mut s=Store::open(":memory:").unwrap();s.apply_snapshot(&enterprise(),1000).unwrap();
 s.reconcile_server_shift(&json!({"id":"server-shift","identity_id":"other","local_id":null})).unwrap();assert_eq!(s.workspace().unwrap().shift_status,"other_cashier");assert!(s.workspace().unwrap().authorized);assert!(s.shift_event(&id(),true,1000,"",1001).is_err());assert!(s.operations().unwrap().is_empty());
 s.reconcile_server_shift(&json!({"id":"server-shift","identity_id":"i","local_id":null})).unwrap();assert_eq!(s.workspace().unwrap().shift_status,"recovery_required");
 s.reconcile_server_shift(&serde_json::Value::Null).unwrap();let open=id();s.shift_event(&open,true,1000,"",1001).unwrap();
 s.acknowledge(&[json!({"operation_id":open,"state":"accepted","code":"OK","shift_id":"server-shift"})]).unwrap();s.reconcile_server_shift(&json!({"id":"server-shift","identity_id":"i","local_id":open})).unwrap();assert_eq!(s.workspace().unwrap().shift_status,"ready");
 s.shift_event(&id(),false,1000,"",1002).unwrap();s.reconcile_server_shift(&json!({"id":"server-shift","identity_id":"i","local_id":open})).unwrap();assert_eq!(s.workspace().unwrap().shift_status,"closing");assert!(s.shift_event(&id(),true,1000,"",1003).is_err());s.reconcile_server_shift(&serde_json::Value::Null).unwrap();assert_eq!(s.workspace().unwrap().shift_status,"ready");
}
#[test]
fn separate_profiles_preserve_pending_payloads_receipts_and_shift() {
 let d=tempfile::tempdir().unwrap();let first=d.path().join("cashier-one.sqlite3");let second=d.path().join("cashier-two.sqlite3");let payload;
 {let mut a=Store::open(&first).unwrap();a.apply_snapshot(&enterprise(),1000).unwrap();a.shift_event(&id(),true,50000,"",1001).unwrap();a.commit(&id(),"sale",lines(1),15000,1002).unwrap();payload=serde_json::to_value(a.operations().unwrap()).unwrap();}
 let mut b=Store::open(&second).unwrap();let mut snap=enterprise();snap.scope.identity_id="second".into();b.apply_snapshot(&snap,1003).unwrap();assert!(b.pending().unwrap().is_empty());assert!(b.active_shift().unwrap().is_none());let a=Store::open(&first).unwrap();assert_eq!(payload,serde_json::to_value(a.operations().unwrap()).unwrap());assert_eq!(a.pending().unwrap().len(),2);assert!(a.active_shift().unwrap().is_some());
}

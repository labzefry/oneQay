use oneqay_pos_core::*;
use serde_json::json;
fn fixture() -> Snapshot {
    Snapshot {
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

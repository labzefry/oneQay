from pathlib import Path

void_path = Path('apps/web/tests/pos-sale-void-durable.php')
refund_path = Path('apps/web/tests/pos-sale-cash-refund-durable.php')
void_text = void_path.read_text()
refund_text = refund_path.read_text()

void_replacements = [
    (
"""$connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-source-alpha')
    ->update(['active_slot' => null]);
$shiftBeforeVoid = (array) $connection->table('oneqay_pos_shifts')
""",
"""$shiftBeforeVoid = (array) $connection->table('oneqay_pos_shifts')
""",
    ),
    (
"""$assert($shiftAfterVoid === $shiftBeforeVoid, 'void mutated shift evidence');

$replay = $voids->execute($voidCommand, 'correlation-void-replay');
""",
"""$assert($shiftAfterVoid === $shiftBeforeVoid, 'void mutated shift evidence');

$connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-source-alpha')
    ->update(['active_slot' => null]);
$assert(
    $connection->table('oneqay_pos_shifts')
        ->where('tenant_id', 'tenant-alpha')
        ->where('operation_id', 'shift-source-alpha')
        ->value('active_slot') === null,
    'shift close fixture did not release active slot',
);

$replay = $voids->execute($voidCommand, 'correlation-void-replay');
""",
    ),
    (
"""$assert($connection->table('oneqay_pos_sale_events')->where('sale_id',$successReceipt->saleId())->where('event_type','VOIDED')->count() === 1, 'replay duplicated VOIDED event');

try {
    $voids->execute(
        new SaleVoidCommand('void-operation-success', $otherReceipt->saleId()),
""",
"""$assert($connection->table('oneqay_pos_sale_events')->where('sale_id',$successReceipt->saleId())->where('event_type','VOIDED')->count() === 1, 'replay duplicated VOIDED event');

$productCBeforePostCloseVoid = (int) $connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id', 'tenant-alpha')
    ->where('outlet_id', 'outlet-alpha')
    ->where('product_id', 'product-c')
    ->value('available_quantity');
try {
    $voids->execute(
        new SaleVoidCommand('void-post-close-new-operation', $otherReceipt->saleId()),
        'correlation-post-close-new-void',
    );
    $assert(false, 'new void accepted after bound shift became inactive');
} catch (PosTransactionViolation) {}
$assert(
    $connection->table('oneqay_pos_sale_voids')->where('sale_id', $otherReceipt->saleId())->count() === 0,
    'post-close denied void persisted evidence',
);
$assert(
    (int) $connection->table('oneqay_pos_sale_catalog_items')
        ->where('tenant_id', 'tenant-alpha')
        ->where('outlet_id', 'outlet-alpha')
        ->where('product_id', 'product-c')
        ->value('available_quantity') === $productCBeforePostCloseVoid,
    'post-close denied void mutated inventory',
);

try {
    $voids->execute(
        new SaleVoidCommand('void-operation-success', $otherReceipt->saleId()),
""",
    ),
    (
"""} catch (PosTransactionViolation) {}

$connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id','tenant-alpha')
    ->where('outlet_id','outlet-alpha')
    ->where('product_id','product-b')
    ->delete();
""",
"""} catch (PosTransactionViolation) {}

$connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-source-alpha')
    ->update(['active_slot' => 1]);

$connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id','tenant-alpha')
    ->where('outlet_id','outlet-alpha')
    ->where('product_id','product-b')
    ->delete();
""",
    ),
    (
"""$assert($connection->table('oneqay_pos_sale_voids')->where('sale_id',$overflowReceipt->saleId())->count() === 0, 'overflow persisted correction');

$setContext('cashier-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', 'device-alpha');
""",
"""$assert($connection->table('oneqay_pos_sale_voids')->where('sale_id',$overflowReceipt->saleId())->count() === 0, 'overflow persisted correction');

$connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-source-alpha')
    ->update(['active_slot' => null]);

$setContext('cashier-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', 'device-alpha');
""",
    ),
]

refund_replacements = [
    (
"""$successVoid = $voids->execute(
    new SaleVoidCommand('void-refund-success', $successReceipt->saleId()),
    'correlation-void-refund-success',
);
$externalVoid = $voids->execute(
""",
"""$successVoid = $voids->execute(
    new SaleVoidCommand('void-refund-success', $successReceipt->saleId()),
    'correlation-void-refund-success',
);
$otherVoid = $voids->execute(
    new SaleVoidCommand('void-refund-other', $otherReceipt->saleId()),
    'correlation-void-refund-other',
);
$externalVoid = $voids->execute(
""",
    ),
    (
"""$connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-source-refund')
    ->update(['active_slot' => null]);
$shiftBeforeRefund = (array) $connection->table('oneqay_pos_shifts')
""",
"""$shiftBeforeRefund = (array) $connection->table('oneqay_pos_shifts')
""",
    ),
    (
"""    'refund changed stock after JRN-007 restoration',
);

$replay = $refunds->record($refundCommand, 'correlation-refund-replay');
""",
"""    'refund changed stock after JRN-007 restoration',
);

$connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-source-refund')
    ->update(['active_slot' => null]);
$assert(
    $connection->table('oneqay_pos_shifts')
        ->where('tenant_id', 'tenant-alpha')
        ->where('operation_id', 'shift-source-refund')
        ->value('active_slot') === null,
    'shift close fixture did not release refund active slot',
);

$productOtherBeforePostCloseRefund = (int) $connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id', 'tenant-alpha')
    ->where('outlet_id', 'outlet-alpha')
    ->where('product_id', 'product-other')
    ->value('available_quantity');
try {
    $refunds->record(
        new SaleCashRefundCommand('refund-post-close-new-operation', $otherReceipt->saleId()),
        'correlation-refund-post-close-new',
    );
    $assert(false, 'new refund accepted after bound shift became inactive');
} catch (PosTransactionViolation) {}
$assert(
    $connection->table('oneqay_pos_sale_cash_refunds')->where('sale_id', $otherReceipt->saleId())->count() === 0,
    'post-close denied refund persisted evidence',
);
$assert(
    (int) $connection->table('oneqay_pos_sale_catalog_items')
        ->where('tenant_id', 'tenant-alpha')
        ->where('outlet_id', 'outlet-alpha')
        ->where('product_id', 'product-other')
        ->value('available_quantity') === $productOtherBeforePostCloseRefund,
    'post-close denied refund mutated inventory',
);

$replay = $refunds->record($refundCommand, 'correlation-refund-replay');
""",
    ),
]

for old, new in void_replacements:
    if void_text.count(old) != 1:
        raise SystemExit('Void regression exact replacement anchor mismatch.')
    void_text = void_text.replace(old, new, 1)

for old, new in refund_replacements:
    if refund_text.count(old) != 1:
        raise SystemExit('Refund regression exact replacement anchor mismatch.')
    refund_text = refund_text.replace(old, new, 1)

void_path.write_text(void_text)
refund_path.write_text(refund_text)

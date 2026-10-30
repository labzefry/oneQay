<?php
// Author by Lab | zefry. Full synthetic checkout transaction: never contacts a live merchant.
declare(strict_types=1);
$checkoutFinished=false;
register_shutdown_function(static function()use(&$checkoutFinished):void{
    if(!$checkoutFinished){fwrite(STDERR,"MENU_GUEST_CHECKOUT_NOT_COMPLETED\n");exit(1);}
});
require __DIR__.'/menu-guest-orders.php';
$contexts->setVerified($businessOwner);
$checkout=new App\Infrastructure\Business\LaravelMenuGuestCheckout(
    $db,$app->make(App\Application\Business\BusinessAccess::class),
    $app->make(App\Application\Pos\CompleteSale::class)
);
$checkoutScope=[
    'tenant_id'=>$businessOwner->tenantId()->value(),
    'organization_id'=>$businessOwner->organizationId()->value(),
    'outlet_id'=>$businessOwner->outletId()->value(),
];
$device=$businessOwner->deviceId()->value();
$actor=$businessOwner->identityId()->value();
$shifts=$db->table('oneqay_pos_shifts')->where($checkoutScope)->where('device_id',$device);
if(!(clone $shifts)->where('active_slot',1)->exists()) {
    $db->table('oneqay_pos_shifts')->insert($checkoutScope+[
        'shift_id'=>bin2hex(random_bytes(16)),
        'operation_id'=>'qr-test-shift-open-001',
        'payload_fingerprint'=>str_repeat('a',64),
        'actor_identity_id'=>$actor,'device_id'=>$device,'active_slot'=>1,
        'correlation_id'=>'qr-test-shift-open-001','opened_at_unix'=>time(),
    ]);
}
$price=$db->table('oneqay_pos_sale_catalog_items')->where('tenant_id',$t)
    ->where('outlet_id',$checkoutScope['outlet_id'])->where('product_id','desktop-coffee');
$price->update(['available_quantity'=>10,'active'=>true,'unit_price_atomic'=>15000]);
$forSale=array_replace($base,['operation_id'=>bin2hex(random_bytes(16))]);
$pending=$guest->submit($host,$forSale);
$q=$checkout->quote($pending['order_id']);
$assert($q['total_atomic']==='30000'&&count($q['lines'])===1,'cashier obtains pending canonical quote');
$before=$db->table('oneqay_pos_sales')->where('tenant_id',$t)->count();
$price->update(['unit_price_atomic'=>16000]);
$settlement=['order_id'=>$pending['order_id'],'operation_id'=>'qr-confirm-payment-001',
    'tender_category'=>'CASH','tendered_atomic'=>35000,'expected_total_atomic'=>30000];
$expect(fn()=> $checkout->settle($settlement,'qr-confirm-correlation-001'),'ORDER_PRICE_CHANGED');
$assert($db->table('oneqay_pos_sales')->where('tenant_id',$t)->count()===$before
    && $db->table('oneqay_menu_guest_orders')->where('tenant_id',$t)->where('id',$pending['order_id'])->value('status')==='pending',
    'stale customer quote creates no sale and leaves order unpaid');
$reprice=$checkout->quote($pending['order_id']);
$assert($reprice['total_atomic']==='32000'&&$reprice['previous_total_atomic']==='30000','cashier must confirm newly priced basket');
$settlement['expected_total_atomic']=32000;
$paid=$checkout->settle($settlement,'qr-confirm-correlation-001');
$assert($paid['status']==='paid'&&$paid['total_atomic']==='32000'&&$paid['replayed']===false,
    'canonical POS completion atomically marks guest order paid');
$assert($db->table('oneqay_pos_sales')->where('tenant_id',$t)->count()===$before+1
    && $db->table('oneqay_menu_guest_orders')->where('tenant_id',$t)->where('id',$pending['order_id'])->value('sale_id')===$paid['sale_id'],
    'single order references one existing POS receipt');
$assert((int)$price->value('available_quantity')===8,'stock deducted once using canonical POS sale');
$again=$checkout->settle($settlement,'qr-confirm-correlation-001');
$assert($again['replayed']===true&&$again['sale_id']===$paid['sale_id']&&
    $db->table('oneqay_pos_sales')->where('tenant_id',$t)->count()===$before+1&&
    (int)$price->value('available_quantity')===8,'payment replay cannot duplicate sale or stock movement');
$expect(fn()=> $checkout->settle(array_replace($settlement,['operation_id'=>'qr-confirm-another-002']),'qr-confirm-correlation-001'),'ORDER_ALREADY_PAID');
$expect(fn()=> $checkout->settle(array_replace($settlement,['tendered_atomic'=>40000]),'qr-confirm-correlation-001'),'ORDER_ALREADY_PAID');
$expect(fn()=> $checkout->cancel($pending['order_id'],'qr-cancel-too-late-001'),'ORDER_NOT_PENDING');
$next=$guest->submit($host,array_replace($base,['operation_id'=>bin2hex(random_bytes(16))]));
$void=$checkout->cancel($next['order_id'],'qr-cancel-pending-001');
$assert($void['status']==='cancelled'&&!$void['replayed'],'unpaid guest order cancels without sale');
$assert($checkout->cancel($next['order_id'],'qr-cancel-pending-001')['replayed'],'identical cancellation replay does not mutate');
$expect(fn()=> $checkout->settle(array_replace($settlement,['order_id'=>$next['order_id']]),'qr-confirm-correlation-002'),'ORDER_NOT_PENDING');
$assert($db->table('oneqay_pos_sales')->where('tenant_id',$t)->count()===$before+1,'cancel never creates or refunds POS sale');
$contexts->clear();
try { $checkout->quote($pending['order_id']); $assert(false,'anonymous staff quote accepted'); }
catch(App\Application\Business\BusinessViolation $e) {$assert($e->reason==='CONTEXT_REQUIRED','anonymous staff quote denied');}
$contexts->setVerified($businessOwner);
$checkoutFinished=true;
echo 'MENU_GUEST_CHECKOUT_'.$engine.'_PASS assertions='.$n.PHP_EOL;

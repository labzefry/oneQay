<?php
// Author by Lab | zefry. Synthetic DB only; no public order route, payments or real customers.
declare(strict_types=1);
$guestFinished=false;
register_shutdown_function(static function () use (&$guestFinished): void {
    if (!$guestFinished) { fwrite(STDERR,"MENU_GUEST_ORDERS_NOT_COMPLETED\n"); exit(1); }
});
require __DIR__.'/menu-book-catalog.php';
(require __DIR__.'/../database/module-migrations/business/2026_10_30_000003_create_menu_guest_orders.php')->up();

$contexts->setVerified($businessOwner);
$guest = new App\Infrastructure\Business\LaravelMenuGuestOrders(
    $db,$app->make('encrypter'),$app->make(App\Application\Business\BusinessAccess::class)
);
$scope = [
    'tenant_id'=>$businessOwner->tenantId()->value(),
    'organization_id'=>$businessOwner->organizationId()->value(),
    'outlet_id'=>$businessOwner->outletId()->value(),
];
$host = ['status'=>'active']+$scope;
$catalog = $db->table('oneqay_pos_sale_catalog_items')->where('tenant_id',$scope['tenant_id'])
    ->where('outlet_id',$scope['outlet_id'])->where('product_id','desktop-coffee');
$catalog->update(['active'=>true,'available_quantity'=>10,'unit_price_atomic'=>15000]);
$current = $menu->item('desktop-coffee');
$menu->assign('guest-order-restore-item-001',[
    'product_id'=>'desktop-coffee','category_id'=>$first['id'],
    'visible'=>true,'spotlight'=>false,'version'=>$current['version'],
],'guest-order-test');
$publication = $menu->publication();
$menu->savePublication('guest-order-publish-001',[
    'title'=>'Guest order test menu','description'=>'Synthetic only',
    'action'=>'publish','version'=>$publication['version']
],'guest-order-test');

$base = [
    'operation_id'=>bin2hex(random_bytes(16)),
    'identity_mode'=>'guest','customer_name'=>'Guest QR Synthetic',
    'phone'=>'+628123456780','email'=>'guest-qr@example.test',
    'lines'=>[['product_id'=>'desktop-coffee','quantity'=>2]],
];
$expect = static function (callable $attempt,string $reason) use (&$assert): void {
    try { $attempt(); $assert(false,'expected rejection '.$reason); }
    catch (App\Application\Business\BusinessViolation $e) {
        $assert($e->reason===$reason,'rejected with '.$reason);
    }
};
$firstOrder=$guest->submit($host,$base);
$assert($firstOrder['status']==='pending_unpaid'
    && $firstOrder['total_atomic']==='30000'
    && preg_match('/\A[a-f0-9]{64}\z/D',$firstOrder['order_token'])===1,
    'order uses authoritative catalog prices and unguessable QR');
$assert($firstOrder['table_label']===null,'table number really optional');
$assert($guest->submit($host,$base)===$firstOrder,'exact replay returns same order without duplicate');
$assert($db->table('oneqay_menu_guest_orders')->where('tenant_id',$scope['tenant_id'])
    ->where('id',$firstOrder['order_id'])->count()===1
    && $db->table('oneqay_menu_guest_order_lines')->where('tenant_id',$scope['tenant_id'])
    ->where('order_id',$firstOrder['order_id'])->count()===1,'replay did not duplicate records');
$saved=$db->table('oneqay_menu_guest_orders')->where('tenant_id',$scope['tenant_id'])
    ->where('id',$firstOrder['order_id'])->first();
$assert(!str_contains($saved->name_ciphertext,$base['customer_name'])
    && !str_contains($saved->phone_ciphertext,$base['phone'])
    && !str_contains($saved->email_ciphertext,$base['email'])
    && !str_contains(json_encode($firstOrder,JSON_THROW_ON_ERROR),$base['phone']),
    'customer contact is encrypted at rest and excluded from anonymous confirmation');
$found=$guest->findByQr($firstOrder['order_token']);
$assert($found!==null && $found['customer_name']===$base['customer_name']
    && $found['lines'][0]['quantity']==2,'authorized cashier can resolve scoped QR');
$assert(count($guest->findByPhone($base['phone']))===1,'authorized staff exact-phone lookup');
$assert($guest->findByQr(str_repeat('a',64))===null,'unguessable unrelated token denied');
$expect(fn()=> $guest->submit($host,array_replace($base,['lines'=>[['product_id'=>'desktop-coffee','quantity'=>3]]])),'ORDER_OPERATION_CONFLICT');
$expect(fn()=> $guest->submit($host,array_replace($base,['operation_id'=>bin2hex(random_bytes(16)),'lines'=>[['product_id'=>'not-on-menu','quantity'=>1]]])),'PRODUCT_NOT_ON_PUBLISHED_MENU');
$expect(fn()=> $guest->submit($host,array_replace($base,['operation_id'=>bin2hex(random_bytes(16)),'identity_mode'=>'member'])),'MEMBERSHIP_AUTH_REQUIRED');
$expect(fn()=> $guest->submit($host,array_replace($base,['operation_id'=>bin2hex(random_bytes(16)),'total_atomic'=>1])),'INVALID_FIELDS');
$expect(fn()=> $guest->submit($host,array_replace($base,['operation_id'=>bin2hex(random_bytes(16)),'phone'=>'08123456780'])),'PHONE_E164_REQUIRED');
$expect(fn()=> $guest->submit($host,array_replace($base,['operation_id'=>bin2hex(random_bytes(16)),'lines'=>[['product_id'=>'desktop-coffee','quantity'=>1],['product_id'=>'desktop-coffee','quantity'=>1]]])),'DUPLICATE_ORDER_PRODUCT');
$catalog->update(['available_quantity'=>0]);
$expect(fn()=> $guest->submit($host,array_replace($base,['operation_id'=>bin2hex(random_bytes(16))])),'ORDER_STOCK_UNAVAILABLE');
$catalog->update(['available_quantity'=>10,'unit_price_atomic'=>18000]);
$assert($guest->submit($host,$base)['total_atomic']==='30000','replay never silently reprices submitted order');
$fresh=$guest->submit($host,array_replace($base,['operation_id'=>bin2hex(random_bytes(16)),'table_label'=>'Meja dekat jendela']));
$assert($fresh['total_atomic']==='36000' && $fresh['table_label']==='Meja dekat jendela',
    'fresh order uses changed server price and optional free-text table');
$catalog->update(['unit_price_atomic'=>15000]);
$db->table('oneqay_menu_guest_orders')->where('tenant_id',$scope['tenant_id'])
    ->where('id',$firstOrder['order_id'])->update(['token_expires_at'=>time()-1]);
$assert($guest->findByQr($firstOrder['order_token'])===null,'expired QR cannot retrieve order');
$expect(fn()=> $guest->submit($host,$base),'ORDER_TOKEN_EXPIRED');
$contexts->clear();
try { $guest->findByPhone($base['phone']); $assert(false,'anonymous order search allowed'); }
catch (App\Application\Business\BusinessViolation $e) {
    $assert($e->reason==='CONTEXT_REQUIRED','anonymous phone search denied');
}
$contexts->setVerified($businessOwner);
$otherHost=['status'=>'active','tenant_id'=>'foreign-tenant','organization_id'=>'foreign-org','outlet_id'=>'foreign-outlet'];
$expect(fn()=> $guest->submit($otherHost,array_replace($base,['operation_id'=>bin2hex(random_bytes(16))])),'BUSINESS_HOST_REQUIRED');
$assert($db->table('oneqay_menu_guest_orders')->where('tenant_id',$scope['tenant_id'])->count()===2,
    'foreign host and failed requests created no orders');
$reset = $app->make(App\Infrastructure\Platform\ControlPlaneBusinessReset::class);
$plan = $reset->preview(array_replace($b,['status'=>'suspended','updated_at'=>time()-120]),'transactions');
$tables=array_column($plan['entries'],'table');
$assert(in_array('oneqay_menu_guest_order_lines',$tables,true)
    && in_array('oneqay_menu_guest_orders',$tables,true)
    && array_search('oneqay_menu_guest_order_lines',$tables,true)<array_search('oneqay_menu_guest_orders',$tables,true),
    'transaction reset tracks guest lines before parent records');
$assert(!Illuminate\Support\Facades\Route::has('menu.public.order'),'public order route remains disabled pending cashier settlement');
$guestFinished=true;
echo 'MENU_GUEST_ORDERS_'.$engine.'_PASS assertions='.$n.PHP_EOL;

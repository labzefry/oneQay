<?php
// Author by Lab | zefry. Source-only pending order inbox: NEVER settle or mark a sale paid here.
declare(strict_types=1);

namespace App\Infrastructure\Business;

use App\Application\Business\{BusinessAccess,BusinessInput,BusinessViolation};
use Illuminate\Database\Connection;
use Illuminate\Encryption\Encrypter;

final readonly class LaravelMenuGuestOrders
{
    private const MAX_TOTAL = 1000000000000;
    private const TOKEN_TTL = 900;
    private const PHONE_SEARCH_TTL = 86400;

    public function __construct(
        private Connection $db,
        private Encrypter $cipher,
        private BusinessAccess $access,
    ) {}

    /** Only the verified active business-host middleware may provide this argument. */
    private function hostScope(array $business): array
    {
        if (($business['status'] ?? null) !== 'active') throw new BusinessViolation('BUSINESS_NOT_ACTIVE',404);
        $scope = [];
        foreach (['tenant_id','organization_id','outlet_id'] as $key) {
            if (!is_string($business[$key] ?? null) || !preg_match('/\A[A-Za-z0-9._:-]{1,64}\z/D',$business[$key])) {
                throw new BusinessViolation('BUSINESS_HOST_REQUIRED',404);
            }
            $scope[$key] = $business[$key];
        }
        return $scope;
    }

    private function phone(mixed $raw): string
    {
        $phone = BusinessInput::text($raw,17);
        if (!preg_match('/\A\+[1-9][0-9]{7,15}\z/D',$phone)) throw new BusinessViolation('PHONE_E164_REQUIRED');
        return $phone;
    }

    private function secretHash(string $text): string
    {
        return hash_hmac('sha256',$text,$this->cipher->getKey());
    }

    private function assertReady(): void
    {
        foreach (['oneqay_menu_guest_orders','oneqay_menu_guest_order_lines','oneqay_menu_book_publication'] as $table) {
            if (!$this->db->getSchemaBuilder()->hasTable($table)) throw new BusinessViolation('MENU_GUEST_SCHEMA_REQUIRED',503);
        }
    }

    /** Creates an UNPAID guest order. Customer-submitted amounts, tenant IDs and status are never accepted. */
    public function submit(array $verifiedHostBusiness, array $input): array
    {
        $scope = $this->hostScope($verifiedHostBusiness);
        BusinessInput::fields($input,['operation_id','identity_mode','customer_name','phone','email','lines'],['table_label']);
        if ($input['identity_mode'] !== 'guest') throw new BusinessViolation('MEMBERSHIP_AUTH_REQUIRED',403);
        $operation = $input['operation_id'];
        if (!is_string($operation) || !preg_match('/\A[a-f0-9]{32}\z/D',$operation)) throw new BusinessViolation('MENU_OPERATION_ID_INVALID');
        $name = BusinessInput::text($input['customer_name'],120);
        $phone = $this->phone($input['phone']);
        $email = mb_strtolower(BusinessInput::text($input['email'],254),'UTF-8');
        if (filter_var($email,FILTER_VALIDATE_EMAIL) === false) throw new BusinessViolation('EMAIL_INVALID');
        $table = BusinessInput::text($input['table_label'] ?? '',32,true);
        if (preg_match('/[\x00-\x1F\x7F]/',$table)) throw new BusinessViolation('TABLE_LABEL_INVALID');
        $requested = BusinessInput::rows($input['lines'],50);
        $lines = []; $seen = [];
        foreach ($requested as $line) {
            BusinessInput::fields($line,['product_id','quantity']);
            $product = BusinessInput::id($line['product_id']);
            if (isset($seen[$product])) throw new BusinessViolation('DUPLICATE_ORDER_PRODUCT');
            $seen[$product] = true;
            $lines[] = ['product_id'=>$product,'quantity'=>BusinessInput::integer($line['quantity'],1,99)];
        }
        $fingerprint = BusinessInput::fingerprint(compact('name','phone','email','table','lines'));
        $operationHash = $this->secretHash($operation);
        $this->assertReady();

        return $this->db->transaction(function () use ($scope,$name,$phone,$email,$table,$lines,$fingerprint,$operationHash): array {
            // Business-level lock serializes a replay and the published menu snapshot.
            $org = $this->db->table('oneqay_organizations')
                ->where('tenant_id',$scope['tenant_id'])->where('id',$scope['organization_id'])
                ->lockForUpdate()->first();
            if (!$org) throw new BusinessViolation('BUSINESS_HOST_REQUIRED',404);
            $query = $this->db->table('oneqay_menu_guest_orders')->where($scope)
                ->where('operation_hash',$operationHash);
            $old = (clone $query)->first();
            if ($old) {
                if (!hash_equals($old->payload_fingerprint,$fingerprint)) throw new BusinessViolation('ORDER_OPERATION_CONFLICT',409);
                if ($old->status !== 'pending' || (int)$old->token_expires_at <= time()) throw new BusinessViolation('ORDER_TOKEN_EXPIRED',410);
                return $this->result($old);
            }
            $publication = $this->db->table('oneqay_menu_book_publication')->where($scope)
                ->where('published',true)->first(['published_snapshot']);
            if (!$publication) throw new BusinessViolation('MENU_NOT_PUBLISHED',404);
            try {$snapshot = json_decode($publication->published_snapshot ?? '',true,32,JSON_THROW_ON_ERROR);}
            catch (\JsonException) {throw new BusinessViolation('MENU_SNAPSHOT_INVALID',503);}
            if (!is_array($snapshot) || ($snapshot['format'] ?? null) !== 1
                || !is_array($snapshot['items'] ?? null) || !array_is_list($snapshot['items'])
                || count($snapshot['items']) > 500) throw new BusinessViolation('MENU_SNAPSHOT_INVALID',503);
            $allowed = [];
            foreach ($snapshot['items'] as $item) {
                if (!is_array($item) || !is_string($item['product_id'] ?? null)) throw new BusinessViolation('MENU_SNAPSHOT_INVALID',503);
                $allowed[$item['product_id']] = true;
            }
            $ids = array_column($lines,'product_id');
            foreach ($ids as $id) if (!isset($allowed[$id])) throw new BusinessViolation('PRODUCT_NOT_ON_PUBLISHED_MENU',409);
            $catalogQuery = $this->db->table('oneqay_pos_sale_catalog_items')
                ->where('tenant_id',$scope['tenant_id'])->where('outlet_id',$scope['outlet_id'])
                ->where('active',true)->whereIn('product_id',$ids);
            $hasStockFlag = $this->db->getSchemaBuilder()->hasColumn('oneqay_pos_sale_catalog_items','tracks_stock');
            $columns = ['product_id','display_name','unit_price_atomic','available_quantity','currency','currency_scale'];
            if ($hasStockFlag) $columns[] = 'tracks_stock';
            $catalog = $catalogQuery->lockForUpdate()->get($columns)->keyBy('product_id');
            $total = 0; $currency = null; $scale = null; $savedLines = [];
            foreach ($lines as $index=>$line) {
                $product = $catalog->get($line['product_id']);
                if (!$product) throw new BusinessViolation('ORDER_PRODUCT_UNAVAILABLE',409);
                if ((!$hasStockFlag || (bool)$product->tracks_stock) && (int)$product->available_quantity < $line['quantity']) {
                    throw new BusinessViolation('ORDER_STOCK_UNAVAILABLE',409);
                }
                $unit = BusinessInput::integer((string)$product->unit_price_atomic,0,self::MAX_TOTAL);
                $itemCurrency = BusinessInput::currency($product->currency);
                $itemScale = BusinessInput::integer((string)$product->currency_scale,0,4);
                if ($currency !== null && ($currency !== $itemCurrency || $scale !== $itemScale)) {
                    throw new BusinessViolation('ORDER_CURRENCY_MISMATCH',409);
                }
                $currency = $itemCurrency; $scale = $itemScale;
                $amount = BusinessInput::multiply($unit,$line['quantity']);
                if ($amount > self::MAX_TOTAL - $total) throw new BusinessViolation('ORDER_TOTAL_TOO_LARGE',422);
                $total += $amount;
                $savedLines[] = [
                    'line_no'=>$index+1,'product_id'=>$line['product_id'],
                    'product_name'=>BusinessInput::text($product->display_name,160),
                    'quantity'=>$line['quantity'],'unit_price_atomic'=>$unit,
                    'line_total_atomic'=>$amount,
                ];
            }
            if ($this->db->table('oneqay_menu_guest_orders')->where($scope)->where('status','pending')
                ->where('created_at','>=',time()-self::PHONE_SEARCH_TTL)->count() >= 500) {
                throw new BusinessViolation('ORDER_INBOX_LIMIT',429);
            }
            $token = bin2hex(random_bytes(32));
            $created = time();
            $orderId = bin2hex(random_bytes(16));
            $record = $scope + [
                'id'=>$orderId,'operation_hash'=>$operationHash,'payload_fingerprint'=>$fingerprint,
                'token_hash'=>$this->secretHash($token),'token_ciphertext'=>$this->cipher->encryptString($token),
                'name_ciphertext'=>$this->cipher->encryptString($name),
                'phone_ciphertext'=>$this->cipher->encryptString($phone),
                'email_ciphertext'=>$this->cipher->encryptString($email),
                'phone_lookup_hash'=>$this->secretHash($phone),
                'table_label'=>$table === '' ? null : $table,
                'status'=>'pending','total_atomic'=>$total,'currency'=>$currency,
                'currency_scale'=>$scale,'created_at'=>$created,
                'token_expires_at'=>$created+self::TOKEN_TTL,
            ];
            $this->db->table('oneqay_menu_guest_orders')->insert($record);
            foreach ($savedLines as $savedLine) {
                $this->db->table('oneqay_menu_guest_order_lines')
                    ->insert(['tenant_id'=>$scope['tenant_id'],'order_id'=>$orderId]+$savedLine);
            }
            return $this->result((object)$record);
        },3);
    }

    private function result(object $order): array
    {
        return [
            'status'=>'pending_unpaid','order_id'=>$order->id,
            'order_token'=>$this->cipher->decryptString($order->token_ciphertext),
            'token_expires_at'=>(int)$order->token_expires_at,
            'total_atomic'=>(string)$order->total_atomic,'currency'=>$order->currency,
            'currency_scale'=>(int)$order->currency_scale,
            'table_label'=>$order->table_label,
        ];
    }

    private function cashierScope(): array
    {
        $s = $this->access->require('pos.sale.complete');
        return ['tenant_id'=>$s->tenantId()->value(),'organization_id'=>$s->organizationId()->value(),'outlet_id'=>$s->outletId()->value()];
    }

    /** Cashier-only, tenant-bound retrieval. Does not authorize payment or change order state. */
    public function findByQr(string $token): ?array
    {
        $scope=$this->cashierScope();
        if (!preg_match('/\A[a-f0-9]{64}\z/D',$token)) return null;
        $order=$this->db->table('oneqay_menu_guest_orders')->where($scope)
            ->where('token_hash',$this->secretHash($token))->where('status','pending')
            ->where('token_expires_at','>',time())->first();
        return $order?$this->cashierView($order):null;
    }

    /** Staff lookup requires an exact normalized phone, never public name enumeration. */
    public function findByPhone(string $phone): array
    {
        $scope=$this->cashierScope();
        $number=$this->phone($phone);
        $rows=$this->db->table('oneqay_menu_guest_orders')->where($scope)
            ->where('phone_lookup_hash',$this->secretHash($number))->where('status','pending')
            ->where('created_at','>=',time()-self::PHONE_SEARCH_TTL)
            ->orderByDesc('created_at')->orderBy('id')->limit(10)->get();
        return $rows->map(fn($row)=>$this->cashierView($row))->all();
    }

    private function cashierView(object $order): array
    {
        $items=$this->db->table('oneqay_menu_guest_order_lines')
            ->where('tenant_id',$order->tenant_id)->where('order_id',$order->id)
            ->orderBy('line_no')->get(['product_id','product_name','quantity','unit_price_atomic','line_total_atomic'])
            ->map(fn($row)=>(array)$row)->all();
        return [
            'order_id'=>$order->id,'status'=>$order->status,
            'customer_name'=>$this->cipher->decryptString($order->name_ciphertext),
            'table_label'=>$order->table_label,
            'total_atomic'=>(string)$order->total_atomic,'currency'=>$order->currency,
            'currency_scale'=>(int)$order->currency_scale,'created_at'=>(int)$order->created_at,
            'lines'=>$items,
        ];
    }
}

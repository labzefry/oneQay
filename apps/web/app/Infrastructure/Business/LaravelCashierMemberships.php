<?php
// Author by Lab | zefry. Cashier enrollment is not proof of email ownership or member login.
declare(strict_types=1);

namespace App\Infrastructure\Business;

use App\Application\Business\{BusinessAccess,BusinessInput,BusinessRepository,BusinessViolation,PartnerManagement};
use App\Application\Organization\VerifiedOrganizationalContext;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\UniqueConstraintViolationException;

final readonly class LaravelCashierMemberships
{
    public function __construct(
        private DatabaseManager $database,
        private Encrypter $cipher,
        private BusinessAccess $access,
        private BusinessRepository $records,
        private PartnerManagement $partners,
    ) {}

    private function scope(VerifiedOrganizationalContext $context): array
    {
        return ['tenant_id'=>$context->tenantId()->value(),'organization_id'=>$context->organizationId()->value()];
    }

    private function enabled(): void
    {
        if (!config('oneqay.cashier_membership.enabled',false)
            || !$this->database->connection()->getSchemaBuilder()->hasTable('oneqay_cashier_memberships')) {
            throw new BusinessViolation('MEMBERSHIP_UNAVAILABLE',503);
        }
    }

    private function phone(mixed $value): string
    {
        $v=BusinessInput::text($value,32);
        if (!preg_match('/\A\+?[0-9 .()-]+\z/D',$v)) throw new BusinessViolation('MEMBER_PHONE_INVALID');
        $v=str_replace([' ','.','(',')','-'],'',$v);
        if (str_starts_with($v,'0')) $v='+62'.substr($v,1);
        elseif (str_starts_with($v,'62')) $v='+'.$v;
        if (!preg_match('/\A\+[1-9][0-9]{7,14}\z/D',$v)) throw new BusinessViolation('MEMBER_PHONE_INVALID');
        return $v;
    }

    private function email(mixed $value): string
    {
        $v=strtolower(BusinessInput::text($value,254));
        if (!filter_var($v,FILTER_VALIDATE_EMAIL)) throw new BusinessViolation('MEMBER_EMAIL_INVALID');
        return $v;
    }

    private function key(array $scope,string $kind,string $value): string
    {
        return hash_hmac('sha256',json_encode([$scope,$kind,$value],JSON_THROW_ON_ERROR),$this->cipher->getKey());
    }

    private function profile(array $scope,object $row,bool $lock=false): array
    {
        $q=$this->database->connection()->table('oneqay_business_partners')->where($scope)
            ->where('outlet_id',$row->outlet_id)->where('id',$row->customer_id)->where('kind','CUSTOMER')->where('active',true);
        if ($lock) $q->lockForUpdate();
        $customer=$q->first(['name']);
        if (!$customer) throw new BusinessViolation('MEMBER_NOT_ACTIVE',409);
        return ['id'=>$row->id,'name'=>$customer->name,'status'=>'cashier_enrolled'];
    }

    public function lookup(array $input): ?array
    {
        $context=$this->access->require('pos.sale.complete');
        $this->enabled();
        BusinessInput::fields($input,['kind','value']);
        $kind=$input['kind'];
        $value=match($kind) {
            'phone'=>$this->phone($input['value']),
            'email'=>$this->email($input['value']),
            default=>throw new BusinessViolation('MEMBER_LOOKUP_INVALID'),
        };
        $scope=$this->scope($context);
        $row=$this->database->connection()->table('oneqay_cashier_memberships')->where($scope)
            ->where($kind.'_key',$this->key($scope,$kind,$value))->first();
        return $row?$this->profile($scope,$row):null;
    }

    public function register(array $input,string $correlation): array
    {
        $context=$this->access->require('pos.sale.complete');
        $this->enabled();
        BusinessInput::fields($input,['operation_id','name','phone','email']);
        $scope=$this->scope($context);
        $op=BusinessInput::id($input['operation_id']);
        $name=BusinessInput::text($input['name'],160);
        $phone=$this->phone($input['phone']);
        $email=$this->email($input['email']);
        $keys=['phone_key'=>$this->key($scope,'phone',$phone),'email_key'=>$this->key($scope,'email',$email)];
        $db=$this->database->connection();
        try {
            // The existing operation journal and unique contacts serialize retries and competing registrations.
            $receipt=$this->records->once($context,'member:'.$op,'cashier.member.register',
                $keys+['name_key'=>$this->key($scope,'name',$name)],$correlation,function()use($db,$scope,$context,$name,$phone,$email,$keys) {
                    $already=$db->table('oneqay_cashier_memberships')->where($scope)->where(fn($q)=>$q->where('phone_key',$keys['phone_key'])->orWhere('email_key',$keys['email_key']))->exists();
                    if ($already) throw new BusinessViolation('MEMBER_CONTACT_EXISTS',409);

                    $matches=$db->table('oneqay_business_partners')->where($scope)->where('kind','CUSTOMER')
                        ->where(fn($q)=>$q->where('phone',$phone)->orWhereRaw('LOWER(email) = ?',[$email]))
                        ->orderBy('outlet_id')->orderBy('id')->limit(2)->lockForUpdate()->get(['id','email','phone','active','outlet_id']);
                    if ($matches->count()>1) throw new BusinessViolation('MEMBER_CUSTOMER_MATCH_CONFLICT',409);
                    $customer=$matches->first();
                    if ($customer && (!$customer->active || $customer->phone!==$phone || strtolower($customer->email)!==$email)) {
                        throw new BusinessViolation('MEMBER_CUSTOMER_MATCH_CONFLICT',409);
                    }
                    if (!$customer) {
                        $customer=(object)$this->partners->save($context,'CUSTOMER',[
                            'code'=>'MEM-'.bin2hex(random_bytes(8)),'name'=>$name,'phone'=>$phone,'email'=>$email,
                            'address'=>'','active'=>true,'marketing_consent'=>false,'consent_source'=>'',
                        ]);
                        $customer->outlet_id=$context->outletId()->value();
                    }
                    $id=bin2hex(random_bytes(16));
                    $db->table('oneqay_cashier_memberships')->insert($scope+$keys+[
                        'id'=>$id,'outlet_id'=>$customer->outlet_id,'customer_id'=>$customer->id,'registered_at'=>time(),
                    ]);
                    return ['id'=>$id];
                });
        } catch (UniqueConstraintViolationException) {
            throw new BusinessViolation('MEMBER_CONTACT_EXISTS',409);
        }
        return $this->requireActive($receipt['id']);
    }

    public function requireActive(mixed $memberId,bool $lock=false): array
    {
        $context=$this->access->require('pos.sale.complete');
        $this->enabled();
        $id=BusinessInput::text($memberId,32);
        if (!preg_match('/\A[a-f0-9]{32}\z/D',$id)) throw new BusinessViolation('MEMBER_ID_INVALID');
        $scope=$this->scope($context);
        $q=$this->database->connection()->table('oneqay_cashier_memberships')->where($scope)->where('id',$id);
        if ($lock) $q->lockForUpdate();
        $row=$q->first();
        if (!$row) throw new BusinessViolation('MEMBER_NOT_FOUND',404);
        return $this->profile($scope,$row,$lock);
    }

    // Called in the same transaction that inserts the canonical sale.
    public function attachToSale(string $memberId,string $saleId): void
    {
        $context=$this->access->require('pos.sale.complete');
        $this->database->connection()->table('oneqay_cashier_member_sales')->insert(
            $this->scope($context)+['member_id'=>$memberId,'sale_id'=>$saleId,'attached_at'=>time()]
        );
    }
}

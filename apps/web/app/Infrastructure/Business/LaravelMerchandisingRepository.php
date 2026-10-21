<?php
declare(strict_types=1);
namespace App\Infrastructure\Business;
use App\Application\Business\{BusinessInput,BusinessViolation,MerchandisingRepository};
use App\Application\Organization\VerifiedOrganizationalContext;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
// Author by Lab | zefry. No tenant, organization or outlet supplied by clients.
final readonly class LaravelMerchandisingRepository implements MerchandisingRepository {
    public function __construct(private Connection $db) {}
    private function settingsQuery(VerifiedOrganizationalContext $s):Builder {return $this->db->table('oneqay_business_settings')->where('tenant_id',$s->tenantId()->value())->where('organization_id',$s->organizationId()->value());}
    public function settings(VerifiedOrganizationalContext $s,bool $lock=false):array {
        $q=$this->settingsQuery($s);if($lock)$q->lockForUpdate();$row=$q->first();return $row?(array)$row:['business_name'=>'oneQay','currency'=>'IDR','symbol'=>'Rp','scale'=>0,'version'=>0];
    }
    public function skuExists(VerifiedOrganizationalContext $s,string $sku):bool {
        return $this->db->table('oneqay_business_product_profiles')->where('tenant_id',$s->tenantId()->value())->where('organization_id',$s->organizationId()->value())->where('outlet_id',$s->outletId()?->value())->where('sku',$sku)->exists();
    }
    public function saveSettings(VerifiedOrganizationalContext $s,array $data,int $version):array {
        $q=$this->settingsQuery($s);$row=(clone $q)->lockForUpdate()->first();
        if (($row?(int)$row->version:0)!==$version)throw new BusinessViolation('RECORD_CHANGED',409);
        $data+=['version'=>$version+1,'updated_by'=>$s->identityId()->value(),'updated_at_unix'=>time()];
        if($row){if($q->where('version',$version)->update($data)!==1)throw new BusinessViolation('RECORD_CHANGED',409);}
        else $q->insert($data+['tenant_id'=>$s->tenantId()->value(),'organization_id'=>$s->organizationId()->value()]);
        return $this->settings($s);
    }
    private function query(VerifiedOrganizationalContext $s):Builder {
        return $this->db->table('oneqay_pos_sale_catalog_items as c')->leftJoin('oneqay_business_product_profiles as p',function($j)use($s):void {$j->on('p.tenant_id','=','c.tenant_id')->on('p.outlet_id','=','c.outlet_id')->on('p.product_id','=','c.product_id')->where('p.organization_id',$s->organizationId()->value());})
            ->leftJoin('oneqay_pos_inventory_baselines as b',function($j)use($s):void {$j->on('b.tenant_id','=','c.tenant_id')->on('b.outlet_id','=','c.outlet_id')->on('b.product_id','=','c.product_id')->where('b.organization_id',$s->organizationId()->value());})
            ->where('c.tenant_id',$s->tenantId()->value())->where('c.outlet_id',$s->outletId()?->value());
    }
    private function columns():array {return ['c.product_id','c.display_name','c.unit_price_atomic','c.available_quantity','c.currency','c.currency_scale','c.active',...($this->db->getSchemaBuilder()->hasColumn('oneqay_pos_sale_catalog_items','tracks_stock')?['c.tracks_stock']:[]),'p.sku','p.barcode','p.category','p.brand','p.variant','p.cost_atomic','p.wholesale_atomic','p.version','p.updated_at_unix','b.product_id as baseline_product_id'];}
    private function row(object $r):array {
        $v=(array)$r;$v['stock_mode']=(bool)($r->tracks_stock??true)?'tracked':'unlimited';$v['sku']=$v['sku']??$v['product_id'];foreach(['barcode','category','brand','variant']as$k)$v[$k]=$v[$k]??'';
        foreach(['unit_price_atomic','cost_atomic','wholesale_atomic','available_quantity']as$k)$v[$k]=(string)($v[$k]??0);
        $v['currency_scale']=(int)$v['currency_scale'];$v['version']=(int)($v['version']??0);$v['active']=(bool)$v['active'];$v['stock_ready']=$v['baseline_product_id']!==null;unset($v['baseline_product_id']);return $v;
    }
    public function products(VerifiedOrganizationalContext $s,array $f,int $page):array {
        if($page<1||$page>10000)throw new BusinessViolation('INVALID_PAGE');$q=$this->query($s);
        $term='%'.str_replace(['\\','%','_'],['\\\\','\\%','\\_'],BusinessInput::text($f['q']??'',120,true)).'%';
        $q->where(fn($q)=>$q->where('c.display_name','like',$term)->orWhere('c.product_id','like',$term)->orWhere('p.sku','like',$term)->orWhere('p.barcode','like',$term));
        foreach(['category','brand']as$k)if(($f[$k]??'')!=='')$q->where('p.'.$k,BusinessInput::text($f[$k],120));
        if(($f['status']??'')!==''){if(!in_array($f['status'],['active','inactive'],true))throw new BusinessViolation('INVALID_FILTER');$q->where('c.active',$f['status']==='active');}
        $count=(clone $q)->count();$rows=$q->orderBy('c.display_name')->orderBy('c.product_id')->offset(($page-1)*50)->limit(50)->get($this->columns())->all();
        $meta=$this->db->table('oneqay_business_product_profiles')->where('tenant_id',$s->tenantId()->value())->where('organization_id',$s->organizationId()->value())->where('outlet_id',$s->outletId()?->value());
        return ['data'=>array_map($this->row(...),$rows),'page'=>$page,'has_more'=>$page*50<$count,'total'=>$count,'categories'=>$this->categories($s,$meta),'brands'=>(clone $meta)->where('brand','<>','')->distinct()->orderBy('brand')->pluck('brand')->all()];
    }
    private function categories(VerifiedOrganizationalContext $s,Builder $meta):array {
        $names=(clone $meta)->where('category','<>','')->distinct()->pluck('category')->all();
        if($this->db->getSchemaBuilder()->hasTable('oneqay_business_product_categories'))$names=array_merge($names,$this->db->table('oneqay_business_product_categories')->where('tenant_id',$s->tenantId()->value())->where('organization_id',$s->organizationId()->value())->where('outlet_id',$s->outletId()?->value())->pluck('name')->all());
        $names=array_values(array_unique($names));sort($names,SORT_NATURAL|SORT_FLAG_CASE);return $names;
    }
    public function createCategory(VerifiedOrganizationalContext $s,string $name):array {
        if(!$this->db->getSchemaBuilder()->hasTable('oneqay_business_product_categories'))throw new BusinessViolation('CATEGORY_SCHEMA_REQUIRED',503);
        $key=hash('sha256',mb_strtolower($name,'UTF-8'));$scope=['tenant_id'=>$s->tenantId()->value(),'organization_id'=>$s->organizationId()->value(),'outlet_id'=>$s->outletId()?->value(),'name_key'=>$key];
        $this->db->table('oneqay_business_product_categories')->insertOrIgnore($scope+['name'=>$name,'created_by'=>$s->identityId()->value(),'created_at'=>time()]);
        return ['name'=>$this->db->table('oneqay_business_product_categories')->where($scope)->value('name')];
    }
    public function product(VerifiedOrganizationalContext $s,string $id,bool $lock=false):?array {
        $q=$this->query($s)->where('c.product_id',$id);if($lock)$q->lockForUpdate();$r=$q->first($this->columns());return $r?$this->row($r):null;
    }
    public function saveProfile(VerifiedOrganizationalContext $s,array $data,?int $version):void {
        $q=$this->db->table('oneqay_business_product_profiles')->where('tenant_id',$s->tenantId()->value())->where('organization_id',$s->organizationId()->value())->where('outlet_id',$s->outletId()?->value())->where('product_id',$data['product_id']);
        $catalog=$this->db->table('oneqay_pos_sale_catalog_items')->where('tenant_id',$s->tenantId()->value())->where('outlet_id',$s->outletId()?->value())->where('product_id',$data['product_id']);
        $mode=$data['stock_mode']??'tracked';unset($data['stock_mode']);
        if($this->db->getSchemaBuilder()->hasColumn('oneqay_pos_sale_catalog_items','tracks_stock'))$catalog->update(['tracks_stock'=>$mode==='tracked']);
        elseif($mode!=='tracked')throw new BusinessViolation('STOCK_SCHEMA_REQUIRED',503);
        $old=(clone $q)->lockForUpdate()->first();if($version!==null&&($old?(int)$old->version:0)!==$version)throw new BusinessViolation('RECORD_CHANGED',409);
        $data+=['updated_by'=>$s->identityId()->value(),'updated_at_unix'=>time(),'version'=>($old?(int)$old->version:0)+1];
        if($old)$q->update($data);else $q->insert($data+['tenant_id'=>$s->tenantId()->value(),'organization_id'=>$s->organizationId()->value(),'outlet_id'=>$s->outletId()?->value()]);
    }
}

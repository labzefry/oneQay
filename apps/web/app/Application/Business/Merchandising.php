<?php
declare(strict_types=1);
namespace App\Application\Business;
use App\Application\Pos\{PrepareCatalogItem,CatalogPreparationCommand,EstablishInventoryBaseline,InventoryBaselineCommand};
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Domain\Pos\{ProductId,Money};
use InvalidArgumentException;
// Author by Lab | zefry. Import preview is read-only; commit is one scoped transaction.
final readonly class Merchandising {
    public function __construct(private MerchandisingRepository $catalog,private BusinessRepository $operations,private BusinessAccess $access,private PrepareCatalogItem $prepare,private EstablishInventoryBaseline $baseline) {}
    public function preferences():array {
        $s=$this->access->context();$v=$this->catalog->settings($s);
        return array_intersect_key($v,array_flip(['business_name','currency','symbol','scale','version']));
    }
    public function settings():array { $this->access->require('authorization.policy.manage');return $this->preferences(); }
    public function saveSettings(string $operation,array $data,string $correlation):array {
        $s=$this->access->require('authorization.policy.manage');BusinessInput::fields($data,['currency','symbol','business_name','version']);
        $version=BusinessInput::integer($data['version'],0,1000000000);unset($data['version']);$v=CurrencyPreference::validate($data);
        return $this->operations->once($s,$operation,'settings.save',$v+['version'=>$version],$correlation,fn()=> $this->catalog->saveSettings($s,$v,$version));
    }
    public function products(array $filters=[],int $page=1):array {
        $s=$this->access->require('pos.catalog.prepare');return $this->catalog->products($s,$filters,$page);
    }
    private function normalize(array $row,array $settings):array {
        BusinessInput::fields($row,['sku','name','price','opening_quantity'],['barcode','category','brand','variant','cost','wholesale','active','product_id','version']);
        $sku=BusinessInput::text($row['sku'],64);$id=isset($row['product_id'])?BusinessInput::text($row['product_id'],64):strtolower($sku);
        try {$id=ProductId::fromString($id)->value();}catch(InvalidArgumentException){throw new BusinessViolation('INVALID_PRODUCT_CODE');}
        $v=['product_id'=>$id,'sku'=>$sku,'display_name'=>BusinessInput::text($row['name'],160),'opening_quantity'=>BusinessInput::integer($row['opening_quantity'],0,1000000000),'active'=>isset($row['active'])?BusinessInput::boolean($row['active']):true];
        foreach(['barcode'=>64,'category'=>120,'brand'=>120,'variant'=>120]as$key=>$max)$v[$key]=BusinessInput::text($row[$key]??'',$max,true);
        foreach(['price'=>'unit_price_atomic','cost'=>'cost_atomic','wholesale'=>'wholesale_atomic']as$key=>$target)$v[$target]=CurrencyPreference::atomic($row[$key]??'0',(int)$settings['scale']);
        $v+=['currency'=>$settings['currency'],'currency_scale'=>(int)$settings['scale']];if(isset($row['version']))$v['version']=BusinessInput::integer($row['version'],0,1000000000);
        return $v;
    }
    public function preview(array $rows):array {
        $s=$this->access->require('pos.catalog.prepare');$this->access->require('pos.inventory.baseline');
        $rows=BusinessInput::rows($rows,500);$settings=$this->preferences();$valid=[];$errors=[];$seen=[];
        foreach($rows as$i=>$row){try {
            $v=$this->normalize($row,$settings);if(isset($seen[$v['product_id']])||isset($seen['sku:'.$v['sku']]))throw new BusinessViolation('DUPLICATE_PRODUCT_IN_FILE');
            if($this->catalog->product($s,$v['product_id'])!==null||$this->catalog->skuExists($s,$v['sku']))throw new BusinessViolation('PRODUCT_ALREADY_EXISTS');
            $seen[$v['product_id']]=true;$seen['sku:'.$v['sku']]=true;$valid[]=$v;
        }catch(BusinessViolation $e){$errors[]=['row'=>$i+2,'code'=>$e->reason];}}
        return ['rows'=>$valid,'errors'=>$errors,'count'=>count($rows),'can_commit'=>$errors===[],'settings_version'=>(int)$settings['version'],'preview_digest'=>BusinessInput::fingerprint([$settings,$valid])];
    }
    public function import(string $operation,array $rows,string $digest,int $settingsVersion,string $correlation):array {
        $s=$this->access->require('pos.catalog.prepare');$this->access->require('pos.inventory.baseline');BusinessInput::text($operation,80);
        // Replay lookup before existence validation: a lost response must not duplicate stock.
        return $this->operations->once($s,$operation,'products.import',['rows'=>$rows,'digest'=>$digest,'settings_version'=>$settingsVersion],$correlation,function()use($rows,$digest,$settingsVersion,$s,$operation,$correlation):array {
            $settings=$this->catalog->settings($s,true);if((int)$settings['version']!==$settingsVersion)throw new BusinessViolation('RECORD_CHANGED',409);
            $preview=$this->preview($rows);if(!$preview['can_commit'])throw new BusinessViolation('IMPORT_VALIDATION_FAILED');
            if($preview['settings_version']!==$settingsVersion||!hash_equals($preview['preview_digest'],$digest))throw new BusinessViolation('IMPORT_PREVIEW_CHANGED',409);
            foreach($preview['rows']as$i=>$v)$this->write($s,$v,$operation.':'.($i+1),$correlation,null,true);
            return ['id'=>$operation,'imported'=>count($preview['rows']),'currency'=>$settings['currency'],'scale'=>(int)$settings['scale']];
        });
    }
    public function saveProduct(string $operation,array $data,string $correlation):array {
        $s=$this->access->require('pos.catalog.prepare');BusinessInput::text($operation,80);
        return $this->operations->once($s,$operation,'product.save',$data,$correlation,function()use($s,$operation,$data,$correlation):array {
            $id=ProductId::fromString((string)($data['product_id']??strtolower((string)($data['sku']??''))))->value();$old=$this->catalog->product($s,$id,true);
            if($old&&!isset($data['version']))throw new BusinessViolation('PRODUCT_ALREADY_EXISTS');
            $settings=$old?['currency'=>$old['currency'],'scale'=>$old['currency_scale']]:$this->preferences();$v=$this->normalize($data,$settings);
            if(!$old)$this->access->require('pos.inventory.baseline');
            if($old&&$v['opening_quantity']!==0)throw new BusinessViolation('OPENING_STOCK_ALREADY_ESTABLISHED');
            $this->write($s,$v,$operation,$correlation,$v['version']??null,!$old);
            return $this->catalog->product($s,$id)??throw new BusinessViolation('PRODUCT_NOT_FOUND');
        });
    }
    private function write(VerifiedOrganizationalContext $s,array $v,string $operation,string $correlation,?int $version,bool $new):void {
        $this->prepare->prepare(new CatalogPreparationCommand($operation.':catalog',ProductId::fromString($v['product_id']),$v['display_name'],Money::fromAtomicUnits((int)$v['unit_price_atomic'],$v['currency'],$v['currency_scale']),$v['active']),$correlation);
        if($new)$this->baseline->establish(new InventoryBaselineCommand($operation.':opening',ProductId::fromString($v['product_id']),$v['opening_quantity']),$correlation);
        $profile=array_intersect_key($v,array_flip(['product_id','sku','barcode','category','brand','variant','cost_atomic','wholesale_atomic']));$this->catalog->saveProfile($s,$profile,$version);
    }
}

<?php
declare(strict_types=1);
namespace App\Infrastructure\Platform;
use App\Application\Authorization\{DurableRolePermissionRepository,PermissionIdentifier};
use App\Application\Organization\VerifiedOrganizationalContext;
use Illuminate\Database\Connection;
// Author by Lab | zefry. A feature setting may deny a canonical permission; it can never grant one.
final readonly class ControlPlaneFeatures implements DurableRolePermissionRepository {
 public function __construct(private DurableRolePermissionRepository $inner,private Connection $db){}
 public function allows(VerifiedOrganizationalContext $context,PermissionIdentifier $permission):bool{
  if(!$this->inner->allows($context,$permission))return false;
  $p=$permission->value();$features=[];if(str_starts_with($p,'pos.'))$features[]='pos';
  foreach(['catalog'=>'pos.catalog.','inventory'=>'pos.inventory.','refunds'=>'pos.sale.refund','reports'=>'pos.reporting.','suppliers'=>'business.suppliers.','purchases'=>'business.purchases.','customers'=>'business.customers.','finance'=>'business.finance.','people'=>'business.people.']as$key=>$prefix)if(str_starts_with($p,$prefix))$features[]=$key;
  if(!$features)return true;
  try{
   $global=json_decode((string)$this->db->table('oneqay_platform_control_settings')->where('id',1)->value('configuration'),true,32,JSON_THROW_ON_ERROR);
   $b=$this->db->table('oneqay_platform_businesses')->where('tenant_id',$context->tenantId()->value())->first(['id','status']);
   if($b!==null&&$b->status!=='active')return false;
   $raw=$b?$this->db->table('oneqay_platform_business_controls')->where('business_id',$b->id)->value('features'):null;$local=$raw===null?[]:json_decode($raw,true,16,JSON_THROW_ON_ERROR);
   foreach($features as$f)if(($global['features'][$f]??true)!==true||($local[$f]??true)!==true)return false;
   return true;
  }catch(\Throwable){return false;}
 }
}

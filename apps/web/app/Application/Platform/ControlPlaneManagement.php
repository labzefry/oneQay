<?php
declare(strict_types=1);
namespace App\Application\Platform;
// Author by Lab | zefry. Platform ownership is host-bound and independent of tenant administrator roles.
final readonly class ControlPlaneManagement {
 public const FEATURES=['pos','catalog','inventory','refunds','reports','suppliers','purchases','customers','finance','people'];
 public function __construct(private PlatformOwnerAccess $access,private PlatformStore $platform,private ControlPlaneStore $store){}
 public function dashboard():array{$this->access->require();return $this->store->overview();}
 public function detail(string $id):array{$this->access->require();return $this->store->detail($this->platform->business($id));}
 public function resetPreview(string $id,string $scope):array{$owner=$this->access->require();$b=$this->platform->business($id);if($b['tenant_id']===$owner->tenantId()->value())throw new PlatformViolation('OWNER_TENANT_PROTECTED',403);return $this->store->execute('business.reset_preview',['scope'=>$scope],$b,$owner->identityId()->value());}
 public function preview(string $id):array{$this->access->require();return $this->store->preview($id);}
 public function command(array $in):array{
  $owner=$this->access->require();$actor=$owner->tenantId()->value().'/'.$owner->identityId()->value();
  $action=BusinessActivation::text($in['action']??null,32);$op=BusinessActivation::text($in['operation_id']??null,128);$reason=BusinessActivation::text($in['reason']??null,300);
  if(!preg_match('/\A[a-zA-Z0-9_:-]{8,128}\z/D',$op))throw new PlatformViolation('INVALID_OPERATION');
  $fields=match($action){
   'settings.save'=>['configuration'],
   'business.reset_data'=>['id','scope','review_digest','reviewed','offline_reconciled','backup_acknowledged','confirmation'],
   'business.configure'=>['id','name','locale','plan','notes','features'],
   'security.totp_reset'=>['id','identity_id','identity_verified'],
   'security.sessions_revoke'=>['id'],
   'error.update'=>['id','state','resolution_note'],
   'campaign.save'=>['id','name','subject','body','locale','channel'],
   'campaign.queue'=>['id','review_digest','reviewed','due_at'],
   'campaign.cancel'=>['id'],
   'shift.recover'=>['id','shift_id','review_digest','reviewed','offline_reconciled'],
   'shift.recover_initial'=>['shift_id','review_digest','reviewed','offline_reconciled'],
   'pilot.verify'=>['id','shift_id','evidence_digest','receipt_reference','real_cash_confirmed'],
   'pilot.verify_initial'=>['shift_id','evidence_digest','receipt_reference','real_cash_confirmed'],
   default=>throw new PlatformViolation('UNKNOWN_ACTION'),
  };
  if(array_diff(array_keys($in),['action','operation_id','reason','version',...$fields]))throw new PlatformViolation('UNKNOWN_FIELDS');
  if(!is_int($in['version']??null)||$in['version']<0)throw new PlatformViolation('INVALID_VERSION');
  ksort($in);
  return $this->platform->once($actor,$op,$in,function()use($in,$action,$reason,$actor,$owner):array{
   $b=null;$target='';
   if(str_starts_with($action,'business.')||str_starts_with($action,'security.')||in_array($action,['pilot.verify','shift.recover'],true)){
    $target=BusinessActivation::text($in['id']??null,32);$b=$this->platform->business($target,true);
    if($b['tenant_id']===$owner->tenantId()->value())throw new PlatformViolation('OWNER_TENANT_PROTECTED',403);
    if((int)$b['version']!==$in['version'])throw new PlatformViolation('STALE_VERSION',409);
   }
   if($action==='security.totp_reset'&&($in['identity_verified']??false)!==true)throw new PlatformViolation('IDENTITY_VERIFICATION_REQUIRED');
   $result=$this->store->execute($action,$in,$b,$actor);
   if(str_starts_with($action,'security.'))$this->platform->update($target,$in['version'],['updated_at'=>time()]);
   $auditReason=$reason;
   if($action==='business.reset_data')$auditReason=substr('Backup '.$result['backup_reference'].' · '.$reason,0,300);
   if($action==='security.totp_reset')$auditReason=substr($in['identity_id'].' · '.$reason,0,300);
   $this->platform->audit($actor,$action,$target,$auditReason,time());return $result;
  });
 }
 public static function features(mixed $in):array{
  if(!is_array($in)||array_diff(array_keys($in),self::FEATURES))throw new PlatformViolation('INVALID_FEATURE_CONFIGURATION');
  foreach($in as$v)if(!is_bool($v))throw new PlatformViolation('INVALID_FEATURE_CONFIGURATION');
  return array_replace(array_fill_keys(self::FEATURES,true),$in);
 }
}

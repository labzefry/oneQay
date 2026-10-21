<?php
declare(strict_types=1);
namespace App\Application\Platform;
// Author by Lab | zefry. A failed independent maintenance phase must not starve transactional SMTP.
final class ControlWorkCycle {
 public function run(callable $mail,callable $health,callable $campaigns):array {
  $result=['at'=>time(),'failed'=>false];
  foreach(['mail'=>$mail,'health'=>$health,'campaigns'=>$campaigns]as$phase=>$work){
   try{$value=$work();$result[$phase]=['state'=>'completed'];if($phase==='mail')$result['mail_processed']=$value;if($phase==='campaigns')$result['campaign_recipients_processed']=$value;}
   catch(\Throwable){$result['failed']=true;$result[$phase]=['state'=>'failed','code'=>'CONTROL_'.strtoupper($phase).'_UNAVAILABLE'];}
  }return $result;
 }
}

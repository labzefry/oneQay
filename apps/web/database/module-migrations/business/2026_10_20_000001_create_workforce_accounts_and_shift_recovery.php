<?php
// Author by Lab | zefry. Additive module only; financial evidence is never deleted.
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('oneqay_business_job_titles',function(Blueprint $t):void{$t->string('tenant_id',64);$t->string('id',32);$t->string('name',120);$t->string('description',1000);$t->unsignedBigInteger('created_at');$t->primary(['tenant_id','id']);$t->unique(['tenant_id','name'],'uq_staff_job_name');});
  Schema::create('oneqay_business_staff_accounts',function(Blueprint $t):void{$t->string('tenant_id',64);$t->string('employee_id',32);$t->string('identity_id',96);$t->string('login',190);$t->primary(['tenant_id','employee_id']);$t->unique(['tenant_id','identity_id'],'uq_staff_identity');$t->unique('login');});
  Schema::create('oneqay_pos_shift_recoveries',function(Blueprint $t):void{$t->string('tenant_id',64);$t->string('shift_id',32);$t->string('actor',200);$t->string('reason',300);$t->char('review_digest',64);$t->longText('snapshot');$t->unsignedBigInteger('recovered_at');$t->primary(['tenant_id','shift_id']);});
  if(Schema::hasTable('oneqay_desktop_cashiers')){
   foreach(\Illuminate\Support\Facades\DB::table('oneqay_desktop_cashiers')->orderBy('login')->cursor()as$c){
    $id=substr(hash('sha256',$c->tenant_id.'|'.$c->identity_id),0,32);$now=time();
    \Illuminate\Support\Facades\DB::table('oneqay_business_employees')->insert(['tenant_id'=>$c->tenant_id,'organization_id'=>$c->organization_id,'outlet_id'=>$c->outlet_id,'id'=>$id,'code'=>'POS-'.substr($c->identity_id,-16),'name'=>$c->name,'department'=>'Operations','job_title'=>'Cashier','start_date'=>gmdate('Y-m-d'),'active'=>(bool)$c->enabled,'created_by'=>$c->identity_id,'version'=>1,'created_at_unix'=>$now,'updated_at_unix'=>$now]);
    \Illuminate\Support\Facades\DB::table('oneqay_business_staff_accounts')->insert(['tenant_id'=>$c->tenant_id,'employee_id'=>$id,'identity_id'=>$c->identity_id,'login'=>$c->login]);
   }
  }
 }
 public function down():void {throw new LogicException('Forward-only: staff identity links and incident evidence require reviewed retention.');}
};

<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema,DB};
// Author by Lab | zefry. Additive module-owned schema, existing identity and business records are preserved.
return new class extends Migration {
 public function up():void {
  Schema::create('oneqay_platform_control_settings',function(Blueprint $t){$t->unsignedInteger('id')->primary();$t->unsignedInteger('version');$t->text('configuration');});
  DB::table('oneqay_platform_control_settings')->insert(['id'=>1,'version'=>1,'configuration'=>'{}']);
  Schema::create('oneqay_platform_business_controls',function(Blueprint $t){$t->char('business_id',32)->primary();$t->text('features');$t->string('plan',32);$t->string('notes',1000);});
  Schema::create('oneqay_platform_http_metrics',function(Blueprint $t){$t->unsignedBigInteger('bucket')->primary();foreach(['requests','client_errors','server_errors','total_ms','max_ms','le50','le100','le250','le500','le750','le1000','le3000','gt3000']as$c)$t->unsignedBigInteger($c)->default(0);});
  Schema::create('oneqay_platform_errors',function(Blueprint $t){$t->char('id',64)->primary();$t->string('code',64);$t->string('route',128);$t->unsignedSmallInteger('http_status');$t->string('correlation_id',128);$t->string('state',16);$t->unsignedInteger('version');$t->unsignedBigInteger('occurrences');$t->unsignedBigInteger('first_seen');$t->unsignedBigInteger('last_seen')->index();$t->string('resolution_note',500)->default('');});
  Schema::create('oneqay_platform_health_samples',function(Blueprint $t){$t->bigIncrements('id');$t->unsignedBigInteger('sampled_at')->index();$t->text('measurements');});
  Schema::create('oneqay_platform_marketing_preferences',function(Blueprint $t){$t->char('business_id',32)->primary();$t->boolean('subscribed')->default(false);$t->unsignedBigInteger('updated_at');$t->string('source',40);});
  Schema::create('oneqay_platform_campaigns',function(Blueprint $t){$t->char('id',32)->primary();$t->unsignedInteger('version');$t->string('name',120);$t->string('subject',200);$t->text('body');$t->string('locale',3);$t->string('channel',8);$t->string('status',16)->index();$t->unsignedBigInteger('due_at')->nullable();$t->char('review_digest',64)->nullable();$t->unsignedInteger('recipient_count')->default(0);$t->unsignedBigInteger('created_at');$t->unsignedBigInteger('updated_at');});
  Schema::create('oneqay_platform_campaign_recipients',function(Blueprint $t){$t->char('campaign_id',32);$t->char('business_id',32);$t->char('recipient_hash',64);$t->char('message_id',32)->nullable()->index();$t->string('state',16);$t->primary(['campaign_id','business_id'],'pk_platform_campaign_recipient');});
  Schema::create('oneqay_platform_pilot_reviews',function(Blueprint $t){$t->char('id',32)->primary();$t->char('business_id',32)->nullable();$t->string('tenant_id',64);$t->char('shift_id',32);$t->char('source_commit',40);$t->char('evidence_digest',64);$t->string('receipt_reference',160);$t->string('actor',200);$t->unsignedBigInteger('verified_at');$t->unique(['tenant_id','shift_id'],'uq_platform_pilot_shift');});
 }
 public function down():void{throw new LogicException('Forward-only control plane migration; retention review required.');}
};

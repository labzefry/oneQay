<?php
// Author by Lab | zefry. Additive; no historical business/schema rewrite.
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('oneqay_business_profiles',function(Blueprint $t){$t->string('business_id',32)->primary();$t->string('tenant_id',96)->unique();$t->string('business_type',16);$t->boolean('owner_totp_required')->default(true);$t->unsignedInteger('version')->default(1);});
  Schema::create('oneqay_business_domains',function(Blueprint $t){$t->string('slug',40)->primary();$t->string('business_id',32)->index();$t->boolean('active');$t->unsignedBigInteger('created_at');$t->unsignedBigInteger('retired_at')->nullable();});
  Schema::create('oneqay_business_handoffs',function(Blueprint $t){$t->string('token_hash',64)->primary();$t->string('business_id',32);$t->string('hostname',254);$t->text('encrypted_session');$t->unsignedBigInteger('expires_at')->index();$t->unsignedBigInteger('consumed_at')->nullable();});
  Schema::create('oneqay_platform_branding',function(Blueprint $t){$t->unsignedInteger('id')->primary();$t->string('media_id',32)->nullable();$t->unsignedInteger('version');});
 }
 public function down():void{throw new LogicException('Business domains remain quarantined; forward-only migration.');}
};

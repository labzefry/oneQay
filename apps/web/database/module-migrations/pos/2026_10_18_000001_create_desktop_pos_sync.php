<?php
// Author by Lab | zefry
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('oneqay_desktop_pairings',function(Blueprint $t):void{
   $t->char('code_hash',64)->primary();$t->text('authority');$t->unsignedBigInteger('expires_at');$t->unsignedBigInteger('consumed_at')->nullable();
  });
  Schema::create('oneqay_desktop_credentials',function(Blueprint $t):void{
   $t->char('token_hash',64)->primary();$t->text('authority');$t->unsignedBigInteger('expires_at');$t->unsignedBigInteger('revoked_at')->nullable();
  });
  Schema::create('oneqay_desktop_leases',function(Blueprint $t):void{
   $t->char('id',32)->primary();$t->char('scope_hash',64);$t->string('tenant_id',64);$t->string('shift_id',64);$t->char('catalog_revision',64);$t->longText('snapshot');$t->unsignedBigInteger('issued_at');$t->unsignedBigInteger('expires_at');$t->index(['tenant_id','expires_at'],'ix_desktop_lease_expiry');
  });
  Schema::create('oneqay_desktop_operations',function(Blueprint $t):void{
   $t->string('tenant_id',64);$t->string('operation_id',128);$t->char('scope_hash',64);$t->char('fingerprint',64);$t->string('organization_id',64);$t->string('outlet_id',64);$t->string('device_id',64);$t->string('identity_id',96);$t->string('kind',16);$t->string('state',24);$t->text('result');$t->text('payload');$t->unsignedBigInteger('created_at');$t->primary(['tenant_id','operation_id'],'pk_desktop_operation');
  });
 }
 public function down():void {throw new LogicException('Forward-only: preserve unsynchronized operation receipts.');}
};

<?php
// Author by Lab | zefry
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('oneqay_desktop_cashiers',function(Blueprint $t):void {$t->string('login',190)->primary();$t->string('name',96);$t->string('tenant_id',64);$t->string('identity_id',96);$t->string('organization_id',64);$t->string('outlet_id',64);$t->string('device_id',64);$t->boolean('enabled')->default(true);$t->text('pin_verifier')->nullable();$t->unsignedInteger('version')->default(1);$t->unsignedBigInteger('updated_at');$t->unique(['tenant_id','identity_id'],'uq_desktop_cashier_identity');});
  Schema::create('oneqay_desktop_preferences',function(Blueprint $t):void {$t->string('tenant_id',64);$t->string('organization_id',64);$t->string('outlet_id',64);$t->longText('settings');$t->unsignedInteger('version');$t->primary(['tenant_id','organization_id','outlet_id'],'pk_desktop_preferences');});
  Schema::create('oneqay_desktop_events',function(Blueprint $t):void {$t->string('tenant_id',64);$t->string('operation_id',128);$t->char('scope_hash',64);$t->char('fingerprint',64);$t->string('local_shift_id',64);$t->string('kind',24);$t->string('state',24);$t->longText('payload');$t->longText('result');$t->unsignedBigInteger('created_at');$t->primary(['tenant_id','operation_id'],'pk_desktop_events');$t->index(['tenant_id','local_shift_id'],'ix_desktop_event_shift');});
 }
 public function down():void {throw new LogicException('Forward-only: retain credentials and shift synchronization evidence.');}
};

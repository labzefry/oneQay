<?php
// Author by Lab | zefry. Diagnostic metadata only; never store credentials or duplicate receipts.
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {Schema::create('oneqay_desktop_sync_diagnostics',function(Blueprint $t):void {
  $t->string('tenant_id',64);$t->string('operation_id',128);$t->string('device_id',64);$t->string('identity_id',96);$t->string('kind',24);$t->string('state',24);$t->string('code',64);$t->string('correlation_id',128);$t->unsignedBigInteger('last_seen');$t->primary(['tenant_id','operation_id'],'pk_desktop_sync_diagnostic');$t->index('last_seen','ix_desktop_diagnostic_time');
 });}
 public function down():void {throw new LogicException('Forward-only diagnostic schema.');}
};

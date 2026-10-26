<?php
// Author by Lab | zefry. Separate module-owned tables; no F&B sale/catalog reuse.
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  foreach(['laundry','boarding','homestay']as$module){
   Schema::create('oneqay_'.$module.'_resources',function(Blueprint $t)use($module){$t->string('id',32)->primary();foreach(['tenant_id','organization_id','outlet_id']as$k)$t->string($k,96);$t->string('name',100);$t->string('unit',12);$t->unsignedBigInteger('rate_atomic');$t->string('currency',3);$t->unsignedTinyInteger('scale');$t->boolean('enabled');$t->unsignedInteger('version');$t->index(['tenant_id','organization_id','outlet_id'],'scope_'.$module.'_resources');});
   Schema::create('oneqay_'.$module.'_orders',function(Blueprint $t)use($module){$t->string('id',32)->primary();foreach(['tenant_id','organization_id','outlet_id']as$k)$t->string($k,96);$t->string('resource_id',32);$t->string('customer_name',100);$t->string('contact',32);$t->string('status',20);$t->string('start_date',10);$t->string('end_date',10);$t->unsignedBigInteger('quantity_milli');$t->unsignedBigInteger('rate_atomic');$t->unsignedBigInteger('total_atomic');$t->string('currency',3);$t->unsignedTinyInteger('scale');$t->unsignedInteger('version');$t->string('created_by',96);$t->unsignedBigInteger('created_at');$t->index(['tenant_id','organization_id','outlet_id','resource_id'],'scope_'.$module.'_orders');});
   Schema::create('oneqay_'.$module.'_invoices',function(Blueprint $t)use($module){$t->string('id',32)->primary();$t->string('tenant_id',96);$t->string('order_id',32);$t->string('period',10);$t->unsignedBigInteger('total_atomic');$t->unsignedBigInteger('paid_atomic');$t->unique(['tenant_id','order_id','period'],'period_'.$module.'_invoice');});
   Schema::create('oneqay_'.$module.'_shifts',function(Blueprint $t)use($module){$t->string('id',32)->primary();foreach(['tenant_id','organization_id','outlet_id','identity_id','device_id']as$k)$t->string($k,96);$t->unsignedTinyInteger('active_slot')->nullable();$t->unsignedBigInteger('opening_atomic');$t->unsignedBigInteger('closing_atomic')->nullable();$t->unsignedBigInteger('opened_at');$t->unsignedBigInteger('closed_at')->nullable();$t->unique(['tenant_id','outlet_id','device_id','active_slot'],'active_'.$module.'_shift');});
   Schema::create('oneqay_'.$module.'_payments',function(Blueprint $t)use($module){$t->string('id',32)->primary();$t->string('tenant_id',96);$t->string('invoice_id',32);$t->string('shift_id',32);$t->unsignedBigInteger('amount_atomic');$t->unsignedBigInteger('occurred_at');$t->string('actor',96);$t->index(['tenant_id','shift_id'],'shift_'.$module.'_payments');});
  }
 }
 public function down():void{throw new LogicException('Transaction journals are retained; forward-only migration.');}
};

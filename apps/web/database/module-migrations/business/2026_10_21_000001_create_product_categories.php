<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Author by Lab | zefry. A category can exist before its first product; legacy product category names are preserved.
return new class extends Migration {
 public function up():void {
  Schema::create('oneqay_business_product_categories',static function(Blueprint $t):void {
   $t->string('tenant_id',64);$t->string('organization_id',64);$t->string('outlet_id',64);$t->string('name',120);$t->char('name_key',64);$t->string('created_by',96);$t->unsignedBigInteger('created_at');
   $t->primary(['tenant_id','organization_id','outlet_id','name_key'],'pk_biz_product_category');
   $t->foreign(['tenant_id','organization_id'],'fk_product_category_org')->references(['tenant_id','id'])->on('oneqay_organizations')->restrictOnDelete()->restrictOnUpdate();
   $t->foreign(['tenant_id','outlet_id'],'fk_product_category_outlet')->references(['tenant_id','id'])->on('oneqay_outlets')->restrictOnDelete()->restrictOnUpdate();
  });
 }
 public function down():void {throw new RuntimeException('Preserve business categories; rollback application code only.');}
};

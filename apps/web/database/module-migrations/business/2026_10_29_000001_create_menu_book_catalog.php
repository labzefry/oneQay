<?php
// Author by Lab | zefry. Menu classification is separate from inventory categories.
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('oneqay_menu_book_categories',static function(Blueprint $t):void {
   $t->string('tenant_id',64);$t->string('organization_id',64);$t->string('outlet_id',64);$t->char('id',32);$t->string('name',120);$t->char('name_key',64);$t->unsignedBigInteger('created_at');
   $t->primary(['tenant_id','organization_id','outlet_id','id'],'pk_menu_book_categories');
   $t->unique(['tenant_id','organization_id','outlet_id','name_key'],'uq_menu_book_category_name');
   $t->foreign(['tenant_id','organization_id'],'fk_menu_book_org')->references(['tenant_id','id'])->on('oneqay_organizations')->restrictOnDelete()->restrictOnUpdate();
   $t->foreign(['tenant_id','outlet_id'],'fk_menu_book_outlet')->references(['tenant_id','id'])->on('oneqay_outlets')->restrictOnDelete()->restrictOnUpdate();
  });
  Schema::create('oneqay_menu_book_publication',static function(Blueprint $t):void {
   $t->string('tenant_id',64);$t->string('organization_id',64);$t->string('outlet_id',64);$t->string('title',160);$t->string('description',500);$t->boolean('published');$t->unsignedInteger('version');$t->unsignedBigInteger('updated_at');
   $t->primary(['tenant_id','organization_id','outlet_id'],'pk_menu_book_publication');
   $t->foreign(['tenant_id','organization_id'],'fk_menu_publication_org')->references(['tenant_id','id'])->on('oneqay_organizations')->restrictOnDelete()->restrictOnUpdate();
   $t->foreign(['tenant_id','outlet_id'],'fk_menu_publication_outlet')->references(['tenant_id','id'])->on('oneqay_outlets')->restrictOnDelete()->restrictOnUpdate();
  });
  Schema::create('oneqay_menu_book_items',static function(Blueprint $t):void {
   $t->string('tenant_id',64);$t->string('organization_id',64);$t->string('outlet_id',64);$t->string('product_id',64);$t->char('category_id',32);$t->boolean('visible');$t->boolean('spotlight');$t->unsignedInteger('version');$t->unsignedBigInteger('updated_at');
   $t->primary(['tenant_id','organization_id','outlet_id','product_id'],'pk_menu_book_items');
   $t->foreign(['tenant_id','organization_id','outlet_id','category_id'],'fk_menu_book_category')->references(['tenant_id','organization_id','outlet_id','id'])->on('oneqay_menu_book_categories')->restrictOnDelete()->restrictOnUpdate();
   $t->foreign(['tenant_id','outlet_id','product_id'],'fk_menu_book_product')->references(['tenant_id','outlet_id','product_id'])->on('oneqay_pos_sale_catalog_items')->restrictOnDelete()->restrictOnUpdate();
  });
 }
 public function down():void {throw new RuntimeException('Preserve menu book data; use a reviewed forward repair.');}
};

<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Author by Lab | zefry. Additive only; existing catalog/stock/financial rows remain intact.
return new class extends Migration {
    public function up(): void {
        Schema::create('oneqay_business_settings',static function(Blueprint $t):void {
            $t->string('tenant_id',64);$t->string('organization_id',64);$t->string('business_name',160);$t->char('currency',3);$t->string('symbol',16);$t->unsignedTinyInteger('scale');$t->unsignedInteger('version');$t->string('updated_by',96);$t->unsignedBigInteger('updated_at_unix');
            $t->primary(['tenant_id','organization_id'],'pk_biz_settings');
            $t->foreign(['tenant_id','organization_id'],'fk_biz_settings_org')->references(['tenant_id','id'])->on('oneqay_organizations')->restrictOnDelete()->restrictOnUpdate();
            $t->foreign(['tenant_id','updated_by'],'fk_biz_settings_actor')->references(['tenant_id','id'])->on('oneqay_identities')->restrictOnDelete()->restrictOnUpdate();
        });
        Schema::create('oneqay_business_product_profiles',static function(Blueprint $t):void {
            $t->string('tenant_id',64);$t->string('organization_id',64);$t->string('outlet_id',64);$t->string('product_id',64);$t->string('sku',64);$t->string('barcode',64);$t->string('category',120);$t->string('brand',120);$t->string('variant',120);$t->unsignedBigInteger('cost_atomic');$t->unsignedBigInteger('wholesale_atomic');$t->unsignedInteger('version');$t->string('updated_by',96);$t->unsignedBigInteger('updated_at_unix');
            $t->primary(['tenant_id','organization_id','outlet_id','product_id'],'pk_biz_product_profile');
            $t->unique(['tenant_id','organization_id','outlet_id','sku'],'uq_biz_product_sku');
            $t->index(['tenant_id','organization_id','outlet_id','category'],'ix_biz_product_category');
            $t->foreign(['tenant_id','outlet_id','product_id'],'fk_biz_product_catalog')->references(['tenant_id','outlet_id','product_id'])->on('oneqay_pos_sale_catalog_items')->restrictOnDelete()->restrictOnUpdate();
            $t->foreign(['tenant_id','organization_id'],'fk_biz_product_org')->references(['tenant_id','id'])->on('oneqay_organizations')->restrictOnDelete()->restrictOnUpdate();
            $t->foreign(['tenant_id','updated_by'],'fk_biz_product_actor')->references(['tenant_id','id'])->on('oneqay_identities')->restrictOnDelete()->restrictOnUpdate();
        });
    }
    public function down():void { throw new RuntimeException('Business merchandising rollback never drops customer data.'); }
};

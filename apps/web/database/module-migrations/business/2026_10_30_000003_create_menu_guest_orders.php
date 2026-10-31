<?php
// Author by Lab | zefry. Additive QR guest order inbox. No payment or sale is created here.
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('oneqay_menu_guest_orders', static function (Blueprint $t): void {
            $t->string('tenant_id',64);
            $t->string('organization_id',64);
            $t->string('outlet_id',64);
            $t->char('id',32);
            $t->char('operation_hash',64);
            $t->char('payload_fingerprint',64);
            $t->char('token_hash',64);
            $t->text('token_ciphertext');
            $t->text('name_ciphertext');
            $t->text('phone_ciphertext');
            $t->text('email_ciphertext');
            $t->char('phone_lookup_hash',64);
            $t->string('table_label',32)->nullable();
            $t->string('status',20);
            // Cashier settlement is one DB transaction with the canonical sale receipt.
            $t->char('settlement_operation_hash',64)->nullable();
            $t->char('settlement_payload_hash',64)->nullable();
            $t->string('sale_id',32)->nullable();
            $t->unsignedBigInteger('settled_total_atomic')->nullable();
            $t->unsignedBigInteger('settled_at')->nullable();
            $t->char('cancel_operation_hash',64)->nullable();
            $t->unsignedBigInteger('cancelled_at')->nullable();
            $t->unsignedBigInteger('total_atomic');
            $t->char('currency',3);
            $t->unsignedTinyInteger('currency_scale');
            $t->unsignedBigInteger('created_at');
            $t->unsignedBigInteger('token_expires_at');
            $t->primary(['tenant_id','id'],'pk_menu_guest_orders');
            $t->unique(['tenant_id','outlet_id','operation_hash'],'uq_menu_guest_op');
            $t->unique(['tenant_id','outlet_id','token_hash'],'uq_menu_guest_token');
            $t->unique(['tenant_id','sale_id'],'uq_menu_guest_sale');
            $t->foreign(['tenant_id','sale_id'],'fk_menu_guest_sale')
                ->references(['tenant_id','sale_id'])->on('oneqay_pos_sales')->restrictOnDelete()->restrictOnUpdate();
            $t->index(['tenant_id','outlet_id','phone_lookup_hash','created_at'],'ix_menu_guest_phone');
            $t->foreign(['tenant_id','organization_id'],'fk_menu_guest_org')
                ->references(['tenant_id','id'])->on('oneqay_organizations')->restrictOnDelete()->restrictOnUpdate();
            $t->foreign(['tenant_id','outlet_id'],'fk_menu_guest_outlet')
                ->references(['tenant_id','id'])->on('oneqay_outlets')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('oneqay_menu_guest_order_lines', static function (Blueprint $t): void {
            $t->string('tenant_id',64);
            $t->char('order_id',32);
            $t->unsignedSmallInteger('line_no');
            $t->string('product_id',64);
            $t->string('product_name',160);
            $t->unsignedSmallInteger('quantity');
            $t->unsignedBigInteger('unit_price_atomic');
            $t->unsignedBigInteger('line_total_atomic');
            $t->primary(['tenant_id','order_id','line_no'],'pk_menu_guest_lines');
            $t->foreign(['tenant_id','order_id'],'fk_menu_guest_lines_order')
                ->references(['tenant_id','id'])->on('oneqay_menu_guest_orders')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('QR order data is forward-only: use an authorized, reviewed recovery.');
    }
};

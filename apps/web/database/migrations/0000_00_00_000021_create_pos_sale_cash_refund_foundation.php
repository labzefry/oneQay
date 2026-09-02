<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Author by Lab | zefry
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oneqay_pos_sale_cash_refunds', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('refund_id', 32);
            $table->string('operation_id', 128);
            $table->char('payload_fingerprint', 64);
            $table->string('sale_id', 32);
            $table->string('void_id', 32);
            $table->string('actor_identity_id', 96);
            $table->string('organization_id', 64);
            $table->string('outlet_id', 64);
            $table->string('device_id', 64);
            $table->unsignedBigInteger('refunded_atomic');
            $table->char('currency', 3);
            $table->unsignedTinyInteger('currency_scale');
            $table->string('tender_category', 32);
            $table->string('evidence_mode', 32);
            $table->string('correlation_id', 128);
            $table->unsignedBigInteger('refunded_at_unix');

            $table->primary(['tenant_id', 'refund_id'], 'pk_pos_sale_cash_refunds');
            $table->unique(['tenant_id', 'operation_id'], 'uq_pos_sale_cash_refunds_operation');
            $table->unique(['tenant_id', 'sale_id'], 'uq_pos_sale_cash_refunds_sale');
            $table->unique(['tenant_id', 'void_id'], 'uq_pos_sale_cash_refunds_void');
            $table->index(['tenant_id', 'outlet_id', 'refunded_at_unix'], 'ix_pos_sale_cash_refunds_outlet_time');

            $table->foreign(['tenant_id', 'sale_id'], 'fk_pos_sale_cash_refunds_sale')
                ->references(['tenant_id', 'sale_id'])->on('oneqay_pos_sales')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'void_id'], 'fk_pos_sale_cash_refunds_void')
                ->references(['tenant_id', 'void_id'])->on('oneqay_pos_sale_voids')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'actor_identity_id'], 'fk_pos_sale_cash_refunds_actor')
                ->references(['tenant_id', 'id'])->on('oneqay_identities')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'organization_id'], 'fk_pos_sale_cash_refunds_org')
                ->references(['tenant_id', 'id'])->on('oneqay_organizations')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'outlet_id'], 'fk_pos_sale_cash_refunds_outlet')
                ->references(['tenant_id', 'id'])->on('oneqay_outlets')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'device_id'], 'fk_pos_sale_cash_refunds_device')
                ->references(['tenant_id', 'id'])->on('oneqay_devices')
                ->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

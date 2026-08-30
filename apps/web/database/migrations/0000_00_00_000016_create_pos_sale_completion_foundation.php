<?php

declare(strict_types=1);

// Sprint47 JRN-004 JRN-006 compatibility preservation anchor.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Author by Lab | zefry
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oneqay_pos_sale_catalog_items', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('outlet_id', 64);
            $table->string('product_id', 64);
            $table->string('display_name', 160);
            $table->unsignedBigInteger('unit_price_atomic');
            $table->char('currency', 3);
            $table->unsignedTinyInteger('currency_scale');
            $table->unsignedBigInteger('available_quantity');
            $table->boolean('active')->default(true);
            $table->primary(['tenant_id', 'outlet_id', 'product_id'], 'pk_pos_sale_catalog_items');
            $table->foreign(['tenant_id', 'outlet_id'], 'fk_pos_sale_catalog_outlet')
                ->references(['tenant_id', 'id'])->on('oneqay_outlets')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('oneqay_pos_sales', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('sale_id', 32);
            $table->string('operation_id', 128);
            $table->char('payload_fingerprint', 64);
            $table->string('actor_identity_id', 96);
            $table->string('organization_id', 64);
            $table->string('outlet_id', 64);
            $table->string('device_id', 64);
            $table->unsignedBigInteger('total_atomic');
            $table->char('currency', 3);
            $table->unsignedTinyInteger('currency_scale');
            $table->string('tender_category', 32);
            $table->string('evidence_mode', 32);
            $table->unsignedBigInteger('applied_atomic');
            $table->unsignedBigInteger('change_atomic');
            $table->string('correlation_id', 128);
            $table->unsignedBigInteger('completed_at_unix');
            $table->primary(['tenant_id', 'sale_id'], 'pk_pos_sales');
            $table->unique(['tenant_id', 'operation_id'], 'uq_pos_sales_operation');
            $table->index(['tenant_id', 'outlet_id', 'completed_at_unix'], 'ix_pos_sales_outlet_time');
            $table->foreign(['tenant_id', 'actor_identity_id'], 'fk_pos_sales_actor')
                ->references(['tenant_id', 'id'])->on('oneqay_identities')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'organization_id'], 'fk_pos_sales_organization')
                ->references(['tenant_id', 'id'])->on('oneqay_organizations')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'outlet_id'], 'fk_pos_sales_outlet')
                ->references(['tenant_id', 'id'])->on('oneqay_outlets')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'device_id'], 'fk_pos_sales_device')
                ->references(['tenant_id', 'id'])->on('oneqay_devices')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('oneqay_pos_sale_lines', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('sale_id', 32);
            $table->unsignedSmallInteger('line_no');
            $table->string('product_id', 64);
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_atomic');
            $table->unsignedBigInteger('line_total_atomic');
            $table->char('currency', 3);
            $table->unsignedTinyInteger('currency_scale');
            $table->primary(['tenant_id', 'sale_id', 'line_no'], 'pk_pos_sale_lines');
            $table->foreign(['tenant_id', 'sale_id'], 'fk_pos_sale_lines_sale')
                ->references(['tenant_id', 'sale_id'])->on('oneqay_pos_sales')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('oneqay_pos_sale_events', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->char('event_id', 32);
            $table->string('sale_id', 32);
            $table->string('operation_id', 128);
            $table->string('actor_identity_id', 96);
            $table->string('event_type', 32);
            $table->string('correlation_id', 128);
            $table->unsignedBigInteger('occurred_at_unix');
            $table->primary(['tenant_id', 'event_id'], 'pk_pos_sale_events');
            $table->index(['tenant_id', 'sale_id', 'occurred_at_unix'], 'ix_pos_sale_events_sale');
            $table->index(['tenant_id', 'operation_id'], 'ix_pos_sale_events_operation');
            $table->foreign(['tenant_id', 'sale_id'], 'fk_pos_sale_events_sale')
                ->references(['tenant_id', 'sale_id'])->on('oneqay_pos_sales')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'actor_identity_id'], 'fk_pos_sale_events_actor')
                ->references(['tenant_id', 'id'])->on('oneqay_identities')
                ->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

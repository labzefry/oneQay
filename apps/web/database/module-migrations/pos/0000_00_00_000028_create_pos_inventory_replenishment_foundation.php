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
        Schema::create('oneqay_pos_inventory_replenishments', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->char('replenishment_id', 32);
            $table->string('operation_id', 128);
            $table->char('payload_fingerprint', 64);
            $table->string('actor_identity_id', 96);
            $table->string('organization_id', 64);
            $table->string('outlet_id', 64);
            $table->string('device_id', 64);
            $table->string('product_id', 64);
            $table->unsignedBigInteger('before_available_quantity');
            $table->unsignedBigInteger('replenished_quantity');
            $table->unsignedBigInteger('after_available_quantity');
            $table->string('correlation_id', 128);
            $table->unsignedBigInteger('occurred_at_unix');

            $table->primary(['tenant_id', 'replenishment_id'], 'pk_pos_inventory_replenishments');
            $table->unique(['tenant_id', 'operation_id'], 'uq_pos_inventory_replenishments_operation');
            $table->index(['tenant_id', 'outlet_id', 'product_id', 'occurred_at_unix'], 'ix_pos_inventory_replenishments_product_time');

            $table->foreign(['tenant_id', 'actor_identity_id'], 'fk_pos_inventory_replenishments_actor')
                ->references(['tenant_id', 'id'])->on('oneqay_identities')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'organization_id'], 'fk_pos_inventory_replenishments_org')
                ->references(['tenant_id', 'id'])->on('oneqay_organizations')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'outlet_id'], 'fk_pos_inventory_replenishments_outlet')
                ->references(['tenant_id', 'id'])->on('oneqay_outlets')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'device_id'], 'fk_pos_inventory_replenishments_device')
                ->references(['tenant_id', 'id'])->on('oneqay_devices')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'outlet_id', 'product_id'], 'fk_pos_inventory_replenishments_catalog')
                ->references(['tenant_id', 'outlet_id', 'product_id'])->on('oneqay_pos_sale_catalog_items')
                ->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

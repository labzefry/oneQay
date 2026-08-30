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
        Schema::create('oneqay_pos_catalog_preparation_journal', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->char('mutation_id', 32);
            $table->string('operation_id', 128);
            $table->char('payload_fingerprint', 64);
            $table->string('actor_identity_id', 96);
            $table->string('organization_id', 64);
            $table->string('outlet_id', 64);
            $table->string('device_id', 64);
            $table->string('product_id', 64);
            $table->boolean('before_exists');
            $table->string('before_display_name', 160)->nullable();
            $table->unsignedBigInteger('before_unit_price_atomic')->nullable();
            $table->char('before_currency', 3)->nullable();
            $table->unsignedTinyInteger('before_currency_scale')->nullable();
            $table->boolean('before_sellable')->nullable();
            $table->string('after_display_name', 160);
            $table->unsignedBigInteger('after_unit_price_atomic');
            $table->char('after_currency', 3);
            $table->unsignedTinyInteger('after_currency_scale');
            $table->boolean('after_sellable');
            $table->string('correlation_id', 128);
            $table->unsignedBigInteger('occurred_at_unix');

            $table->primary(['tenant_id', 'mutation_id'], 'pk_pos_catalog_prep_journal');
            $table->unique(['tenant_id', 'operation_id'], 'uq_pos_catalog_prep_operation');
            $table->index(
                ['tenant_id', 'outlet_id', 'product_id', 'occurred_at_unix'],
                'ix_pos_catalog_prep_product_time',
            );
            $table->foreign(['tenant_id', 'actor_identity_id'], 'fk_pos_catalog_prep_actor')
                ->references(['tenant_id', 'id'])->on('oneqay_identities')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'organization_id'], 'fk_pos_catalog_prep_org')
                ->references(['tenant_id', 'id'])->on('oneqay_organizations')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'outlet_id'], 'fk_pos_catalog_prep_outlet')
                ->references(['tenant_id', 'id'])->on('oneqay_outlets')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'device_id'], 'fk_pos_catalog_prep_device')
                ->references(['tenant_id', 'id'])->on('oneqay_devices')
                ->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

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
        Schema::create('oneqay_pos_shifts', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->char('shift_id', 32);
            $table->string('operation_id', 128);
            $table->char('payload_fingerprint', 64);
            $table->string('actor_identity_id', 96);
            $table->string('organization_id', 64);
            $table->string('outlet_id', 64);
            $table->string('device_id', 64);
            $table->unsignedTinyInteger('active_slot')->nullable();
            $table->string('correlation_id', 128);
            $table->unsignedBigInteger('opened_at_unix');

            $table->primary(['tenant_id', 'shift_id'], 'pk_pos_shifts');
            $table->unique(['tenant_id', 'operation_id'], 'uq_pos_shifts_operation');
            $table->unique(
                ['tenant_id', 'outlet_id', 'device_id', 'active_slot'],
                'uq_pos_shifts_active_context',
            );
            $table->index(['tenant_id', 'outlet_id', 'opened_at_unix'], 'ix_pos_shifts_outlet_time');

            $table->foreign(['tenant_id', 'actor_identity_id'], 'fk_pos_shifts_actor')
                ->references(['tenant_id', 'id'])->on('oneqay_identities')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'organization_id'], 'fk_pos_shifts_org')
                ->references(['tenant_id', 'id'])->on('oneqay_organizations')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'outlet_id'], 'fk_pos_shifts_outlet')
                ->references(['tenant_id', 'id'])->on('oneqay_outlets')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'device_id'], 'fk_pos_shifts_device')
                ->references(['tenant_id', 'id'])->on('oneqay_devices')
                ->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

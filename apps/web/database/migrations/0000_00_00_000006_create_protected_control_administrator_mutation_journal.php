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
        Schema::create('oneqay_protected_control_admin_mutations', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('mutation_id', 64);
            $table->string('actor_identity_id', 96);
            $table->string('operation', 48);
            $table->string('target_identity_id', 96);
            $table->string('role_id', 64);
            $table->string('permission_id', 96);
            $table->char('payload_fingerprint', 64);
            $table->string('outcome', 16);
            $table->unsignedBigInteger('occurred_at_unix');

            $table->primary(['tenant_id', 'mutation_id'], 'pk_oneqay_protected_control_admin_mutations');
            $table->foreign('tenant_id', 'fk_protected_control_admin_tenant')
                ->references('id')->on('oneqay_tenants');
            $table->foreign(['tenant_id', 'actor_identity_id'], 'fk_protected_control_admin_actor')
                ->references(['tenant_id', 'id'])->on('oneqay_identities');
            $table->foreign(['tenant_id', 'target_identity_id'], 'fk_protected_control_admin_target')
                ->references(['tenant_id', 'id'])->on('oneqay_identities');
            $table->foreign(['tenant_id', 'role_id'], 'fk_protected_control_admin_role')
                ->references(['tenant_id', 'id'])->on('oneqay_roles');
            $table->foreign(
                ['tenant_id', 'role_id', 'permission_id'],
                'fk_protected_control_admin_permission',
            )->references(['tenant_id', 'role_id', 'permission_id'])->on('oneqay_role_permissions');
            $table->index(['tenant_id', 'actor_identity_id'], 'idx_protected_control_admin_actor');
            $table->index(['tenant_id', 'target_identity_id'], 'idx_protected_control_admin_target');
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

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
        Schema::create('oneqay_policy_mutations', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('mutation_id', 64);
            $table->string('actor_identity_id', 96);
            $table->string('operation', 32);
            $table->string('scope_type', 16);
            $table->string('organization_id', 64)->nullable();
            $table->string('outlet_id', 64)->nullable();
            $table->string('device_id', 64)->nullable();
            $table->string('target_identity_id', 96)->nullable();
            $table->string('role_id', 64);
            $table->string('permission_id', 96)->nullable();
            $table->char('payload_fingerprint', 64);
            $table->string('outcome', 16);
            $table->unsignedBigInteger('occurred_at_unix');

            $table->primary(['tenant_id', 'mutation_id'], 'pk_oneqay_policy_mutations');
            $table->foreign('tenant_id', 'fk_policy_mutation_tenant')
                ->references('id')->on('oneqay_tenants');
            $table->foreign(['tenant_id', 'actor_identity_id'], 'fk_policy_mutation_actor')
                ->references(['tenant_id', 'id'])->on('oneqay_identities');
            $table->index(['tenant_id', 'actor_identity_id'], 'idx_policy_mutation_actor');
            $table->index(['tenant_id', 'role_id'], 'idx_policy_mutation_role');
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

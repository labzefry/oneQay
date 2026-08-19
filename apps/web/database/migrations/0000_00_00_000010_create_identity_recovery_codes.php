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
        Schema::create('oneqay_identity_recovery_codes', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->char('code_id', 32);
            $table->string('identity_id', 96);
            $table->char('code_selector', 22);
            $table->char('secret_digest', 64);
            $table->unsignedBigInteger('issued_at_unix');
            $table->unsignedBigInteger('consumed_at_unix')->nullable();
            $table->unsignedBigInteger('revoked_at_unix')->nullable();

            $table->primary(['tenant_id', 'code_id'], 'pk_identity_recovery_codes');
            $table->unique('code_selector', 'uq_identity_recovery_selector');
            $table->foreign(['tenant_id', 'identity_id'], 'fk_identity_recovery_code_identity')
                ->references(['tenant_id', 'id'])->on('oneqay_identities')
                ->onUpdate('restrict')->onDelete('restrict');
            $table->index(['tenant_id', 'identity_id'], 'idx_identity_recovery_identity');
            $table->index(
                ['tenant_id', 'identity_id', 'consumed_at_unix', 'revoked_at_unix'],
                'idx_identity_recovery_state',
            );
        });

        Schema::create('oneqay_identity_recovery_audit', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->char('audit_id', 32);
            $table->string('identity_id', 96);
            $table->string('event_type', 32);
            $table->char('code_id', 32)->nullable();
            $table->string('correlation_id', 128);
            $table->unsignedBigInteger('occurred_at_unix');

            $table->primary(['tenant_id', 'audit_id'], 'pk_identity_recovery_audit');
            $table->foreign(['tenant_id', 'identity_id'], 'fk_identity_recovery_audit_identity')
                ->references(['tenant_id', 'id'])->on('oneqay_identities')
                ->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['tenant_id', 'code_id'], 'fk_identity_recovery_audit_code')
                ->references(['tenant_id', 'code_id'])->on('oneqay_identity_recovery_codes')
                ->onUpdate('restrict')->onDelete('restrict');
            $table->index(
                ['tenant_id', 'identity_id', 'occurred_at_unix'],
                'idx_identity_recovery_audit_identity',
            );
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

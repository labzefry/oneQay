<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Author by Lab | zefry
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oneqay_identity_totp_factors', function (Blueprint $table): void {
            $table->unsignedBigInteger('factor_epoch')->default(0);
        });

        Schema::create('oneqay_identity_totp_recovery_codes', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->char('code_id', 32);
            $table->string('identity_id', 96);
            $table->unsignedBigInteger('factor_epoch');
            $table->string('code_selector', 22);
            $table->char('secret_digest', 64);
            $table->unsignedBigInteger('issued_at_unix');
            $table->unsignedBigInteger('consumed_at_unix')->nullable();
            $table->unsignedBigInteger('revoked_at_unix')->nullable();

            $table->primary(['tenant_id', 'code_id'], 'pk_identity_totp_recovery_codes');
            $table->unique('code_selector', 'uq_identity_totp_recovery_selector');
            $table->index(['tenant_id', 'identity_id', 'factor_epoch'], 'ix_identity_totp_recovery_owner_epoch');

            $table->foreign(['tenant_id', 'identity_id'], 'fk_identity_totp_recovery_identity')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_identities')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });

        Schema::create('oneqay_identity_totp_recovery_audit', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->char('audit_id', 32);
            $table->string('identity_id', 96);
            $table->string('event_type', 32);
            $table->char('code_id', 32)->nullable();
            $table->unsignedBigInteger('factor_epoch');
            $table->string('correlation_id', 128);
            $table->unsignedBigInteger('occurred_at_unix');

            $table->primary(['tenant_id', 'audit_id'], 'pk_identity_totp_recovery_audit');
            $table->index(['tenant_id', 'identity_id', 'occurred_at_unix'], 'ix_identity_totp_recovery_audit_owner');
            $table->index(['tenant_id', 'code_id'], 'ix_identity_totp_recovery_audit_code');

            $table->foreign(['tenant_id', 'identity_id'], 'fk_identity_totp_recovery_audit_identity')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_identities')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

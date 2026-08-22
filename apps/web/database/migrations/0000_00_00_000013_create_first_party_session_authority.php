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
        Schema::create('oneqay_identity_first_party_sessions', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->char('authority_id', 32);
            $table->string('public_handle', 43);
            $table->string('identity_id', 96);
            $table->string('organization_id', 64);
            $table->string('outlet_id', 64)->nullable();
            $table->string('device_id', 64)->nullable();
            $table->unsignedBigInteger('credential_epoch');
            $table->unsignedBigInteger('factor_epoch')->nullable();
            $table->unsignedBigInteger('issued_at_unix');
            $table->unsignedBigInteger('last_seen_at_unix');
            $table->unsignedBigInteger('expires_at_unix');
            $table->unsignedBigInteger('revoked_at_unix')->nullable();

            $table->primary(['tenant_id', 'authority_id'], 'pk_identity_first_party_sessions');
            $table->unique('public_handle', 'uq_identity_first_party_session_handle');
            $table->index(['tenant_id', 'identity_id', 'revoked_at_unix'], 'ix_identity_first_party_session_owner_active');
            $table->index(['tenant_id', 'identity_id', 'expires_at_unix'], 'ix_identity_first_party_session_owner_expiry');

            $table->foreign(['tenant_id', 'identity_id'], 'fk_identity_first_party_session_identity')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_identities')
                ->restrictOnDelete()
                ->restrictOnUpdate();
            $table->foreign(['tenant_id', 'organization_id'], 'fk_identity_first_party_session_org')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_organizations')
                ->restrictOnDelete()
                ->restrictOnUpdate();
            $table->foreign(['tenant_id', 'outlet_id'], 'fk_identity_first_party_session_outlet')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_outlets')
                ->restrictOnDelete()
                ->restrictOnUpdate();
            $table->foreign(['tenant_id', 'device_id'], 'fk_identity_first_party_session_device')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_devices')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });

        Schema::create('oneqay_identity_first_party_session_audit', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->char('audit_id', 32);
            $table->string('identity_id', 96);
            $table->char('actor_authority_id', 32)->nullable();
            $table->char('target_authority_id', 32);
            $table->string('event_type', 32);
            $table->string('correlation_id', 128);
            $table->unsignedBigInteger('occurred_at_unix');

            $table->primary(['tenant_id', 'audit_id'], 'pk_identity_first_party_session_audit');
            $table->index(['tenant_id', 'identity_id', 'occurred_at_unix'], 'ix_identity_first_party_session_audit_owner');
            $table->index(['tenant_id', 'target_authority_id'], 'ix_identity_first_party_session_audit_target');

            $table->foreign(['tenant_id', 'identity_id'], 'fk_identity_first_party_session_audit_identity')
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

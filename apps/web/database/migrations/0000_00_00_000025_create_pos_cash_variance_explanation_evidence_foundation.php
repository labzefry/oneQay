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
        Schema::create('oneqay_pos_cash_variance_explanation_evidence', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('evidence_id', 32);
            $table->string('operation_id', 128);
            $table->char('payload_fingerprint', 64);
            $table->char('shift_id', 32);
            $table->string('opening_cash_evidence_id', 32);
            $table->string('closing_cash_evidence_id', 32);
            $table->string('actor_identity_id', 96);
            $table->string('organization_id', 64);
            $table->string('outlet_id', 64);
            $table->unsignedBigInteger('cutoff_at_unix');
            $table->unsignedBigInteger('expected_cash_atomic');
            $table->unsignedBigInteger('observed_closing_cash_atomic');
            $table->bigInteger('variance_atomic');
            $table->string('variance_direction', 8);
            $table->char('currency', 3);
            $table->unsignedTinyInteger('currency_scale');
            $table->text('explanation_text');
            $table->string('correlation_id', 128);
            $table->unsignedBigInteger('recorded_at_unix');

            $table->primary(['tenant_id', 'evidence_id'], 'pk_pos_var_explain');
            $table->unique(['tenant_id', 'operation_id'], 'uq_pos_var_explain_operation');
            $table->unique(['tenant_id', 'closing_cash_evidence_id'], 'uq_pos_var_explain_closing');
            $table->index(
                ['tenant_id', 'outlet_id', 'recorded_at_unix'],
                'ix_pos_var_explain_outlet_time',
            );

            $table->foreign(['tenant_id', 'shift_id'], 'fk_pos_var_explain_shift')
                ->references(['tenant_id', 'shift_id'])->on('oneqay_pos_shifts')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'opening_cash_evidence_id'], 'fk_pos_var_explain_opening')
                ->references(['tenant_id', 'evidence_id'])->on('oneqay_pos_shift_opening_cash_evidence')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'closing_cash_evidence_id'], 'fk_pos_var_explain_closing')
                ->references(['tenant_id', 'evidence_id'])->on('oneqay_pos_shift_closing_cash_evidence')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'actor_identity_id'], 'fk_pos_var_explain_actor')
                ->references(['tenant_id', 'id'])->on('oneqay_identities')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'organization_id'], 'fk_pos_var_explain_org')
                ->references(['tenant_id', 'id'])->on('oneqay_organizations')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'outlet_id'], 'fk_pos_var_explain_outlet')
                ->references(['tenant_id', 'id'])->on('oneqay_outlets')
                ->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

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
        $connection = Schema::getConnection();
        $driver = strtolower($connection->getDriverName());

        if (! in_array($driver, ['mysql', 'sqlite'], true)) {
            throw new LogicException('Final Shift Close evidence migration supports only canonical MySQL-compatible or isolated SQLite CI targets.');
        }

        Schema::create('oneqay_pos_shift_close_evidence', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('evidence_id', 32);
            $table->string('operation_id', 128);
            $table->char('payload_fingerprint', 64);
            $table->char('shift_id', 32);
            $table->string('opening_cash_evidence_id', 32);
            $table->string('closing_cash_evidence_id', 32);
            $table->string('closer_actor_identity_id', 96);
            $table->string('organization_id', 64);
            $table->string('outlet_id', 64);
            $table->string('device_id', 64);
            $table->unsignedBigInteger('cutoff_at_unix');
            $table->unsignedBigInteger('expected_cash_atomic');
            $table->unsignedBigInteger('observed_closing_cash_atomic');
            $table->bigInteger('variance_atomic');
            $table->string('variance_direction', 8);
            $table->char('currency', 3);
            $table->unsignedTinyInteger('currency_scale');
            $table->string('review_evidence_id', 32)->nullable();
            $table->string('review_outcome', 24)->nullable();
            $table->string('correlation_id', 128);
            $table->unsignedBigInteger('closed_at_unix');

            $table->primary(['tenant_id', 'evidence_id'], 'pk_pos_shift_close_final');
            $table->unique(['tenant_id', 'operation_id'], 'uq_pos_shift_close_operation');
            $table->unique(['tenant_id', 'shift_id'], 'uq_pos_shift_close_shift');
            $table->index(
                ['tenant_id', 'outlet_id', 'closed_at_unix'],
                'ix_pos_shift_close_outlet_time',
            );

            $table->foreign(['tenant_id', 'shift_id'], 'fk_pos_shift_close_shift')
                ->references(['tenant_id', 'shift_id'])->on('oneqay_pos_shifts')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'opening_cash_evidence_id'], 'fk_pos_shift_close_opening')
                ->references(['tenant_id', 'evidence_id'])->on('oneqay_pos_shift_opening_cash_evidence')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'closing_cash_evidence_id'], 'fk_pos_shift_close_closing')
                ->references(['tenant_id', 'evidence_id'])->on('oneqay_pos_shift_closing_cash_evidence')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'closer_actor_identity_id'], 'fk_pos_shift_close_actor')
                ->references(['tenant_id', 'id'])->on('oneqay_identities')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'organization_id'], 'fk_pos_shift_close_org')
                ->references(['tenant_id', 'id'])->on('oneqay_organizations')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'outlet_id'], 'fk_pos_shift_close_outlet')
                ->references(['tenant_id', 'id'])->on('oneqay_outlets')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'device_id'], 'fk_pos_shift_close_device')
                ->references(['tenant_id', 'id'])->on('oneqay_devices')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_id', 'review_evidence_id'], 'fk_pos_shift_close_review')
                ->references(['tenant_id', 'review_evidence_id'])
                ->on('oneqay_pos_cash_variance_review_decision_evidence')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        $validVarianceReview = "((variance_direction = 'MATCH' AND variance_atomic = 0 AND review_evidence_id IS NULL AND review_outcome IS NULL)"
            ." OR (variance_direction = 'OVER' AND variance_atomic > 0 AND review_evidence_id IS NOT NULL AND review_outcome = 'REVIEW_ACCEPTED')"
            ." OR (variance_direction = 'SHORT' AND variance_atomic < 0 AND review_evidence_id IS NOT NULL AND review_outcome = 'REVIEW_ACCEPTED'))";

        if ($driver === 'mysql') {
            $connection->statement(
                'ALTER TABLE oneqay_pos_shift_close_evidence '
                .'ADD CONSTRAINT chk_pos_shift_close_variance_review CHECK '.$validVarianceReview
            );
        } else {
            $connection->statement(
                'CREATE TRIGGER chk_pos_shift_close_variance_review_insert '
                .'BEFORE INSERT ON oneqay_pos_shift_close_evidence '
                .'FOR EACH ROW WHEN NOT '
                .str_replace(
                    ['variance_direction', 'variance_atomic', 'review_evidence_id', 'review_outcome'],
                    ['NEW.variance_direction', 'NEW.variance_atomic', 'NEW.review_evidence_id', 'NEW.review_outcome'],
                    $validVarianceReview,
                )
                ." BEGIN SELECT RAISE(ABORT, 'final shift close variance/review violation'); END"
            );
            $connection->statement(
                'CREATE TRIGGER chk_pos_shift_close_variance_review_update '
                .'BEFORE UPDATE OF variance_direction, variance_atomic, review_evidence_id, review_outcome '
                .'ON oneqay_pos_shift_close_evidence '
                .'FOR EACH ROW WHEN NOT '
                .str_replace(
                    ['variance_direction', 'variance_atomic', 'review_evidence_id', 'review_outcome'],
                    ['NEW.variance_direction', 'NEW.variance_atomic', 'NEW.review_evidence_id', 'NEW.review_outcome'],
                    $validVarianceReview,
                )
                ." BEGIN SELECT RAISE(ABORT, 'final shift close variance/review violation'); END"
            );
        }
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

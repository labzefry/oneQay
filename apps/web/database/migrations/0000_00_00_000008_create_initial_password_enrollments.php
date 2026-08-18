<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Author by Lab | zefry
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oneqay_initial_password_enrollments', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('enrollment_id', 64);
            $table->string('actor_identity_id', 96);
            $table->string('target_identity_id', 96);
            $table->char('token_digest', 64);
            $table->unsignedBigInteger('issued_at_unix');
            $table->unsignedBigInteger('expires_at_unix');
            $table->unsignedBigInteger('consumed_at_unix')->nullable();
            $table->unsignedTinyInteger('active_marker')->nullable();

            $table->primary(['tenant_id', 'enrollment_id'], 'pk_initial_password_enrollment');
            $table->unique(
                ['tenant_id', 'target_identity_id', 'active_marker'],
                'uq_initial_password_enrollment_active_target',
            );

            $table->foreign(
                ['tenant_id', 'actor_identity_id'],
                'fk_initial_password_enrollment_actor',
            )->references(['tenant_id', 'id'])
                ->on('oneqay_identities')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign(
                ['tenant_id', 'target_identity_id'],
                'fk_initial_password_enrollment_target',
            )->references(['tenant_id', 'id'])
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

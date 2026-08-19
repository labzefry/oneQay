<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Author by Lab | zefry
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oneqay_identity_totp_factors', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('identity_id', 96);
            $table->text('secret_ciphertext');
            $table->unsignedBigInteger('created_at_unix');
            $table->unsignedBigInteger('confirmed_at_unix')->nullable();
            $table->unsignedBigInteger('last_accepted_time_step')->nullable();

            $table->primary(['tenant_id', 'identity_id'], 'pk_identity_totp_factors');

            $table->foreign(
                ['tenant_id', 'identity_id'],
                'fk_identity_totp_factor_identity',
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

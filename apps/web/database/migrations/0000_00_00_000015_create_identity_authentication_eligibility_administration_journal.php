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
        Schema::create('oneqay_identity_authentication_eligibility_mutations', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('mutation_id', 64);
            $table->string('actor_identity_id', 96);
            $table->string('target_identity_id', 96);
            $table->string('operation', 16);
            $table->char('payload_fingerprint', 64);
            $table->string('outcome', 16);
            $table->unsignedBigInteger('occurred_at_unix');

            $table->primary(
                ['tenant_id', 'mutation_id'],
                'pk_oneqay_identity_authentication_eligibility_mutations',
            );
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

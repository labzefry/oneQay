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
        Schema::table('oneqay_pos_shifts', function (Blueprint $table): void {
            $table->unique(
                ['tenant_id', 'shift_id', 'organization_id', 'outlet_id', 'device_id'],
                'uq_pos_shifts_binding_context',
            );
        });

        Schema::table('oneqay_pos_sales', function (Blueprint $table): void {
            $table->char('shift_id', 32)->nullable()->after('device_id');
            $table->index(
                ['tenant_id', 'shift_id', 'completed_at_unix'],
                'ix_pos_sales_shift_time',
            );
            $table->foreign(
                ['tenant_id', 'shift_id', 'organization_id', 'outlet_id', 'device_id'],
                'fk_pos_sales_shift_context',
            )
                ->references(['tenant_id', 'shift_id', 'organization_id', 'outlet_id', 'device_id'])
                ->on('oneqay_pos_shifts')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

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
        Schema::table('oneqay_devices', function (Blueprint $table): void {
            $table->unique(
                ['tenant_id', 'organization_id', 'outlet_id', 'id'],
                'uq_oneqay_device_organization_outlet',
            );
        });

        Schema::create('oneqay_outlet_access_grants', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('identity_id', 96);
            $table->string('organization_id', 64);
            $table->string('outlet_id', 64);
            $table->primary(
                ['tenant_id', 'identity_id', 'organization_id', 'outlet_id'],
                'pk_oneqay_outlet_access_grants',
            );
            $table->foreign(
                ['tenant_id', 'identity_id', 'organization_id'],
                'fk_outlet_access_membership',
            )
                ->references(['tenant_id', 'identity_id', 'organization_id'])
                ->on('oneqay_identity_organizations');
            $table->foreign(
                ['tenant_id', 'organization_id', 'outlet_id'],
                'fk_outlet_access_outlet',
            )
                ->references(['tenant_id', 'organization_id', 'id'])
                ->on('oneqay_outlets');
        });

        Schema::create('oneqay_device_access_grants', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('identity_id', 96);
            $table->string('organization_id', 64);
            $table->string('outlet_id', 64);
            $table->string('device_id', 64);
            $table->primary(
                ['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id'],
                'pk_oneqay_device_access_grants',
            );
            $table->foreign(
                ['tenant_id', 'identity_id', 'organization_id', 'outlet_id'],
                'fk_device_access_outlet_grant',
            )
                ->references(['tenant_id', 'identity_id', 'organization_id', 'outlet_id'])
                ->on('oneqay_outlet_access_grants');
            $table->foreign(
                ['tenant_id', 'organization_id', 'outlet_id', 'device_id'],
                'fk_device_access_device',
            )
                ->references(['tenant_id', 'organization_id', 'outlet_id', 'id'])
                ->on('oneqay_devices');
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

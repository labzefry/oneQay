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
        Schema::create('oneqay_tenants', function (Blueprint $table): void {
            $table->string('id', 64);
            $table->primary('id', 'pk_oneqay_tenants');
        });

        Schema::create('oneqay_identities', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('id', 96);
            $table->primary(['tenant_id', 'id'], 'pk_oneqay_identities');
            $table->foreign('tenant_id', 'fk_identity_tenant')
                ->references('id')
                ->on('oneqay_tenants');
        });

        Schema::create('oneqay_organizations', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('id', 64);
            $table->primary(['tenant_id', 'id'], 'pk_oneqay_organizations');
            $table->foreign('tenant_id', 'fk_organization_tenant')
                ->references('id')
                ->on('oneqay_tenants');
        });

        Schema::create('oneqay_identity_organizations', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('identity_id', 96);
            $table->string('organization_id', 64);
            $table->primary(
                ['tenant_id', 'identity_id', 'organization_id'],
                'pk_oneqay_identity_organizations',
            );
            $table->foreign('tenant_id', 'fk_membership_tenant')
                ->references('id')
                ->on('oneqay_tenants');
            $table->foreign(['tenant_id', 'identity_id'], 'fk_membership_identity')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_identities');
            $table->foreign(['tenant_id', 'organization_id'], 'fk_membership_organization')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_organizations');
        });

        Schema::create('oneqay_outlets', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('id', 64);
            $table->string('organization_id', 64);
            $table->primary(['tenant_id', 'id'], 'pk_oneqay_outlets');
            $table->unique(
                ['tenant_id', 'organization_id', 'id'],
                'uq_oneqay_outlet_organization',
            );
            $table->foreign('tenant_id', 'fk_outlet_tenant')
                ->references('id')
                ->on('oneqay_tenants');
            $table->foreign(['tenant_id', 'organization_id'], 'fk_outlet_organization')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_organizations');
        });

        Schema::create('oneqay_devices', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('id', 64);
            $table->string('organization_id', 64);
            $table->string('outlet_id', 64);
            $table->primary(['tenant_id', 'id'], 'pk_oneqay_devices');
            $table->foreign('tenant_id', 'fk_device_tenant')
                ->references('id')
                ->on('oneqay_tenants');
            $table->foreign(['tenant_id', 'organization_id'], 'fk_device_organization')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_organizations');
            $table->foreign(['tenant_id', 'outlet_id'], 'fk_device_outlet')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_outlets');
            $table->foreign(
                ['tenant_id', 'organization_id', 'outlet_id'],
                'fk_device_outlet_organization',
            )
                ->references(['tenant_id', 'organization_id', 'id'])
                ->on('oneqay_outlets');
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

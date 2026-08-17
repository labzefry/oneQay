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
        Schema::create('oneqay_roles', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('id', 64);
            $table->primary(['tenant_id', 'id'], 'pk_oneqay_roles');
            $table->foreign('tenant_id', 'fk_role_tenant')
                ->references('id')
                ->on('oneqay_tenants');
        });

        Schema::create('oneqay_role_permissions', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('role_id', 64);
            $table->string('permission_id', 96);
            $table->primary(
                ['tenant_id', 'role_id', 'permission_id'],
                'pk_oneqay_role_permissions',
            );
            $table->foreign(['tenant_id', 'role_id'], 'fk_role_permission_role')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_roles');
        });

        Schema::create('oneqay_tenant_role_assignments', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('identity_id', 96);
            $table->string('role_id', 64);
            $table->primary(
                ['tenant_id', 'identity_id', 'role_id'],
                'pk_oneqay_tenant_role_assignments',
            );
            $table->foreign(['tenant_id', 'identity_id'], 'fk_tenant_role_identity')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_identities');
            $table->foreign(['tenant_id', 'role_id'], 'fk_tenant_role_role')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_roles');
        });

        Schema::create('oneqay_organization_role_assignments', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('identity_id', 96);
            $table->string('organization_id', 64);
            $table->string('role_id', 64);
            $table->primary(
                ['tenant_id', 'identity_id', 'organization_id', 'role_id'],
                'pk_oneqay_organization_role_assignments',
            );
            $table->foreign(
                ['tenant_id', 'identity_id', 'organization_id'],
                'fk_organization_role_membership',
            )
                ->references(['tenant_id', 'identity_id', 'organization_id'])
                ->on('oneqay_identity_organizations');
            $table->foreign(['tenant_id', 'role_id'], 'fk_organization_role_role')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_roles');
        });

        Schema::create('oneqay_outlet_role_assignments', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('identity_id', 96);
            $table->string('organization_id', 64);
            $table->string('outlet_id', 64);
            $table->string('role_id', 64);
            $table->primary(
                ['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'role_id'],
                'pk_oneqay_outlet_role_assignments',
            );
            $table->foreign(
                ['tenant_id', 'identity_id', 'organization_id', 'outlet_id'],
                'fk_outlet_role_access',
            )
                ->references(['tenant_id', 'identity_id', 'organization_id', 'outlet_id'])
                ->on('oneqay_outlet_access_grants');
            $table->foreign(['tenant_id', 'role_id'], 'fk_outlet_role_role')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_roles');
        });

        Schema::create('oneqay_device_role_assignments', function (Blueprint $table): void {
            $table->string('tenant_id', 64);
            $table->string('identity_id', 96);
            $table->string('organization_id', 64);
            $table->string('outlet_id', 64);
            $table->string('device_id', 64);
            $table->string('role_id', 64);
            $table->primary(
                ['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id', 'role_id'],
                'pk_oneqay_device_role_assignments',
            );
            $table->foreign(
                ['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id'],
                'fk_device_role_access',
            )
                ->references(['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id'])
                ->on('oneqay_device_access_grants');
            $table->foreign(['tenant_id', 'role_id'], 'fk_device_role_role')
                ->references(['tenant_id', 'id'])
                ->on('oneqay_roles');
        });
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

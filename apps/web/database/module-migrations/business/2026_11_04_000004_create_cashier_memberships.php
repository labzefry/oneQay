<?php
// Author by Lab | zefry. Additive cashier membership and immutable sale attribution.
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('oneqay_cashier_memberships', static function (Blueprint $t): void {
            $t->string('tenant_id',64);
            $t->string('organization_id',64);
            $t->string('outlet_id',64);
            $t->char('id',32);
            $t->char('customer_id',32);
            $t->char('phone_key',64);
            $t->char('email_key',64);
            $t->unsignedBigInteger('registered_at');
            $t->primary(['tenant_id','organization_id','id'],'pk_cashier_member');
            $t->unique(['tenant_id','organization_id','phone_key'],'uq_cashier_member_phone');
            $t->unique(['tenant_id','organization_id','email_key'],'uq_cashier_member_email');
            $t->unique(['tenant_id','organization_id','outlet_id','customer_id'],'uq_cashier_member_customer');
            $t->foreign(['tenant_id','organization_id','outlet_id','customer_id'],'fk_cashier_member_customer')
                ->references(['tenant_id','organization_id','outlet_id','id'])->on('oneqay_business_partners')->restrictOnDelete();
        });
        Schema::create('oneqay_cashier_member_sales', static function (Blueprint $t): void {
            $t->string('tenant_id',64);
            $t->string('organization_id',64);
            $t->char('member_id',32);
            $t->string('sale_id',32);
            $t->unsignedBigInteger('attached_at');
            $t->primary(['tenant_id','sale_id'],'pk_cashier_member_sale');
            $t->foreign(['tenant_id','sale_id'],'fk_cashier_member_sale')
                ->references(['tenant_id','sale_id'])->on('oneqay_pos_sales')->restrictOnDelete();
            $t->foreign(['tenant_id','organization_id','member_id'],'fk_cashier_sale_member')
                ->references(['tenant_id','organization_id','id'])->on('oneqay_cashier_memberships')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        throw new LogicException('Cashier membership records are durable. Use a verified recovery procedure.');
    }
};

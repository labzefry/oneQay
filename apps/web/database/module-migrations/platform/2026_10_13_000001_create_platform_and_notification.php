<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
// Author by Lab | zefry. Additive module schema; historical migrations remain immutable.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('oneqay_platform_businesses', function (Blueprint $t): void {
            $t->string('id',32)->primary(); $t->string('name',160); $t->string('email',254); $t->string('locale',2);
            foreach (['tenant_id','identity_id','organization_id','outlet_id','device_id'] as $key) $t->string($key,96);
            $t->unique('tenant_id'); $t->string('status',16)->index(); $t->unsignedInteger('version');
            $t->string('token_hash',64)->nullable()->unique(); $t->unsignedBigInteger('token_expires_at')->nullable();
            $t->unsignedBigInteger('created_at'); $t->unsignedBigInteger('updated_at');
        });
        Schema::create('oneqay_platform_operations', function (Blueprint $t): void {
            $t->string('id',64)->primary(); $t->string('fingerprint',64); $t->text('result');
        });
        Schema::create('oneqay_platform_audit', function (Blueprint $t): void {
            $t->bigIncrements('id'); $t->string('actor',200); $t->string('action',32); $t->string('business_id',32);
            $t->string('reason',300); $t->unsignedBigInteger('occurred_at');
        });
        Schema::create('oneqay_mail_settings', function (Blueprint $t): void {
            $t->unsignedInteger('id')->primary(); $t->unsignedInteger('version'); $t->text('configuration');
            $t->unsignedBigInteger('paused_until')->default(0); $t->unsignedInteger('failures')->default(0);
            $t->unsignedBigInteger('minute_slot')->default(0); $t->unsignedInteger('minute_count')->default(0);
            $t->unsignedBigInteger('heartbeat_at')->default(0);
        });
        DB::table('oneqay_mail_settings')->insert(['id'=>1,'version'=>1,'configuration'=>'{}']);
        Schema::create('oneqay_mail_messages', function (Blueprint $t): void {
            $t->string('id',32)->primary(); $t->string('tenant_id',96)->index(); $t->string('dedupe_key',64)->unique();
            $t->string('fingerprint',64); $t->string('recipient_hash',64)->index(); $t->text('payload');
            $t->string('status',24)->index(); $t->unsignedInteger('attempts')->default(0); $t->string('safe_code',64)->nullable();
            $t->unsignedBigInteger('next_attempt_at'); $t->string('lease',32)->nullable(); $t->unsignedBigInteger('lease_until')->nullable();
            $t->unsignedBigInteger('created_at'); $t->unsignedBigInteger('updated_at');
            $t->index(['status','next_attempt_at']);
        });
        Schema::create('oneqay_mail_suppressions', function (Blueprint $t): void {
            $t->string('recipient_hash',64)->primary(); $t->string('reason',32); $t->unsignedBigInteger('created_at');
        });
        Schema::create('oneqay_mail_events', function (Blueprint $t): void {
            $t->string('id',64)->primary(); $t->string('message_id',32)->index(); $t->string('event',32);
            $t->string('safe_code',64); $t->unsignedBigInteger('occurred_at');
        });
    }
    public function down(): void { throw new RuntimeException('Platform and mail journals require a separately reviewed data-retention migration.'); }
};

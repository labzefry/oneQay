<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Author by Lab | zefry. Additive module schema; never rewrites POS or identity tables.
return new class extends Migration
{
    public function up(): void
    {
        $definitions = [
            'partners' => static function (Blueprint $t): void {
                $t->string('kind', 16); $t->string('code', 48); $t->string('name', 160);
                $t->string('email', 254)->default(''); $t->string('phone', 48)->default('');
                $t->text('address'); $t->boolean('active'); $t->boolean('marketing_consent');
                $t->string('consent_source', 160); $t->unsignedBigInteger('consent_at');
                $t->unique(['tenant_id', 'organization_id', 'outlet_id', 'kind', 'code'], 'uq_biz_partner_code');
            },
            'purchases' => static function (Blueprint $t): void {
                $t->char('supplier_id', 32); $t->string('reference', 80); $t->string('status', 24);
                $t->char('currency', 3); $t->unsignedTinyInteger('scale'); $t->unsignedBigInteger('total_atomic');
                $t->text('lines'); $t->text('note'); $t->string('approved_by', 96)->nullable();
            },
            'receipts' => static function (Blueprint $t): void {
                $t->char('purchase_id', 32); $t->string('reference', 80); $t->text('lines');
                $t->unsignedBigInteger('total_atomic'); $t->char('currency', 3); $t->unsignedTinyInteger('scale');
            },
            'invoices' => static function (Blueprint $t): void {
                $t->string('kind', 4); $t->char('partner_id', 32); $t->string('reference', 80);
                $t->char('receipt_id', 32)->nullable(); $t->date('issue_date'); $t->date('due_date');
                $t->char('currency', 3); $t->unsignedTinyInteger('scale');
                $t->unsignedBigInteger('total_atomic'); $t->unsignedBigInteger('paid_atomic')->default(0);
                $t->string('status', 16); $t->text('description');
                $t->unique(['tenant_id', 'organization_id', 'outlet_id', 'receipt_id'], 'uq_biz_invoice_receipt');
                $t->unique(['tenant_id', 'organization_id', 'outlet_id', 'partner_id', 'reference'], 'uq_biz_invoice_reference');
            },
            'settlements' => static function (Blueprint $t): void {
                $t->char('invoice_id', 32); $t->unsignedBigInteger('amount_atomic');
                $t->date('paid_date'); $t->string('reference', 120); $t->string('cash_account', 32);
            },
            'journals' => static function (Blueprint $t): void {
                $t->string('source', 120); $t->date('posting_date'); $t->string('description', 240);
                $t->char('currency', 3); $t->unsignedTinyInteger('scale'); $t->unsignedBigInteger('total_atomic');
                $t->text('lines'); $t->char('reversal_of', 32)->nullable();
                $t->unique(['tenant_id', 'organization_id', 'outlet_id', 'source'], 'uq_biz_journal_source');
                $t->unique(['tenant_id', 'organization_id', 'outlet_id', 'reversal_of'], 'uq_biz_journal_reversal');
                $t->index(['tenant_id', 'outlet_id', 'posting_date'], 'ix_biz_journal_date');
            },
            'interactions' => static function (Blueprint $t): void {
                $t->char('customer_id', 32); $t->string('channel', 24); $t->text('note'); $t->date('follow_up_date')->nullable();
            },
            'employees' => static function (Blueprint $t): void {
                $t->string('code', 48); $t->string('name', 160); $t->string('department', 120);
                $t->string('job_title', 120); $t->date('start_date'); $t->boolean('active');
                $t->unique(['tenant_id', 'organization_id', 'outlet_id', 'code'], 'uq_biz_employee_code');
            },
            'attendance' => static function (Blueprint $t): void {
                $t->char('employee_id', 32); $t->date('work_date'); $t->unsignedBigInteger('clock_in');
                $t->unsignedBigInteger('clock_out')->nullable(); $t->text('note');
                $t->unique(['tenant_id', 'organization_id', 'outlet_id', 'employee_id', 'work_date'], 'uq_biz_attendance_day');
            },
            'leave' => static function (Blueprint $t): void {
                $t->char('employee_id', 32); $t->date('start_date'); $t->date('end_date');
                $t->text('reason'); $t->string('status', 16); $t->string('approved_by', 96)->nullable();
            },
            'events' => static function (Blueprint $t): void {
                $t->string('action', 64); $t->string('resource_id', 64); $t->string('operation_id', 128);
                $t->string('correlation_id', 128); $t->char('payload_fingerprint', 64);
                $t->index(['tenant_id', 'outlet_id', 'created_at_unix'], 'ix_biz_event_time');
            },
        ];
        foreach ($definitions as $name => $define) {
            Schema::create('oneqay_business_'.$name, function (Blueprint $t) use ($name, $define): void {
                $t->string('tenant_id', 64); $t->string('organization_id', 64); $t->string('outlet_id', 64);
                $t->char('id', 32); $t->string('created_by', 96); $t->unsignedInteger('version')->default(1);
                $t->unsignedBigInteger('created_at_unix'); $t->unsignedBigInteger('updated_at_unix');
                $t->primary(['tenant_id', 'organization_id', 'outlet_id', 'id'], 'pk_biz_'.$name);
                $t->foreign(['tenant_id', 'organization_id'], 'fk_biz_'.$name.'_org')->references(['tenant_id', 'id'])->on('oneqay_organizations')->restrictOnDelete()->restrictOnUpdate();
                $t->foreign(['tenant_id', 'outlet_id'], 'fk_biz_'.$name.'_out')->references(['tenant_id', 'id'])->on('oneqay_outlets')->restrictOnDelete()->restrictOnUpdate();
                $t->foreign(['tenant_id', 'created_by'], 'fk_biz_'.$name.'_actor')->references(['tenant_id', 'id'])->on('oneqay_identities')->restrictOnDelete()->restrictOnUpdate();
                $define($t);
            });
        }
        foreach ([['purchases', 'supplier_id', 'partners'], ['receipts', 'purchase_id', 'purchases'], ['invoices', 'partner_id', 'partners'], ['invoices', 'receipt_id', 'receipts'], ['settlements', 'invoice_id', 'invoices'], ['interactions', 'customer_id', 'partners'], ['attendance', 'employee_id', 'employees'], ['leave', 'employee_id', 'employees'], ['journals', 'reversal_of', 'journals']] as [$table, $field, $parent]) {
            Schema::table('oneqay_business_'.$table, static function (Blueprint $t) use ($table, $field, $parent): void {
                $t->foreign(['tenant_id', 'organization_id', 'outlet_id', $field], 'fk_biz_'.$table.'_'.$field)->references(['tenant_id', 'organization_id', 'outlet_id', 'id'])->on('oneqay_business_'.$parent)->restrictOnDelete()->restrictOnUpdate();
            });
        }
        Schema::create('oneqay_business_operations', static function (Blueprint $t): void {
            $t->string('tenant_id', 64); $t->string('operation_id', 128); $t->char('fingerprint', 64);
            $t->text('result')->nullable(); $t->unsignedBigInteger('created_at_unix');
            $t->primary(['tenant_id', 'operation_id'], 'pk_biz_operations');
        });
    }

    public function down(): void
    {
        throw new LogicException('Business journals are durable. Restore a verified database backup instead of deleting operational records.');
    }
};

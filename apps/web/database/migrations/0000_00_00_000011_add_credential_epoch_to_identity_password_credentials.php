<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Author by Lab | zefry
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oneqay_identity_password_credentials', function (Blueprint $table): void {
            $table->unsignedBigInteger('credential_epoch')->default(0);
        });

        $credentials = DB::table('oneqay_identity_password_credentials')
            ->select(['tenant_id', 'identity_id'])
            ->orderBy('tenant_id')
            ->orderBy('identity_id')
            ->get();

        foreach ($credentials as $credential) {
            if (! is_object($credential)
                || ! is_string($credential->tenant_id ?? null)
                || $credential->tenant_id === ''
                || ! is_string($credential->identity_id ?? null)
                || $credential->identity_id === '') {
                throw new LogicException('Credential epoch backfill encountered invalid credential identity.');
            }

            $historicalCount = DB::table('oneqay_identity_recovery_audit')
                ->where('tenant_id', $credential->tenant_id)
                ->where('identity_id', $credential->identity_id)
                ->where('event_type', 'password_reset_completed')
                ->count();

            if (! is_int($historicalCount) || $historicalCount < 0) {
                throw new LogicException('Credential epoch backfill produced an invalid historical count.');
            }

            $updated = DB::table('oneqay_identity_password_credentials')
                ->where('tenant_id', $credential->tenant_id)
                ->where('identity_id', $credential->identity_id)
                ->update(['credential_epoch' => $historicalCount]);

            if ($updated !== 1 && $historicalCount !== 0) {
                throw new LogicException('Credential epoch backfill failed to update the exact credential row.');
            }
        }
    }

    public function down(): void
    {
        throw new LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};

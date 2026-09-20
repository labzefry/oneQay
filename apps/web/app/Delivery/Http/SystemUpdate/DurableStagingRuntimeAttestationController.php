<?php

declare(strict_types=1);

namespace App\Delivery\Http\SystemUpdate;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Author by Lab | zefry
final class DurableStagingRuntimeAttestationController
{
    public function __invoke(Request $request): JsonResponse
    {
        $enabled = (bool) config('oneqay.durable_runtime_attestation.enabled', false);
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        abort_unless($enabled && $runtime === 'durable-staging', 404);

        $expectedToken = (string) config('oneqay.durable_runtime_attestation.token', '');
        $providedToken = $request->bearerToken();
        if ($expectedToken === ''
            || strlen($expectedToken) < 16
            || strlen($expectedToken) > 4096
            || ! is_string($providedToken)
            || ! hash_equals($expectedToken, $providedToken)) {
            abort(404);
        }

        $environmentId = trim((string) config('oneqay.durable_runtime_attestation.environment_id', ''));
        $source = strtolower(trim((string) config('oneqay.durable_runtime_attestation.running_source_commit', '')));
        $artifact = strtolower(trim((string) config('oneqay.durable_runtime_attestation.running_artifact_sha256', '')));
        if (preg_match('/\A[a-z0-9][a-z0-9-]{2,62}\z/', $environmentId) !== 1
            || preg_match('/\A[0-9a-f]{40}\z/', $source) !== 1
            || preg_match('/\A[0-9a-f]{64}\z/', $artifact) !== 1) {
            abort(503);
        }

        $capabilities = [
            'durable_persistence_enabled',
            'durable_session_control_enabled',
            'durable_authorization_enabled',
            'durable_transaction_boundary_enabled',
            'durable_pos_persistence_enabled',
            'authenticated_configuration_mutation_channel',
            'read_before_write_read_after_supported',
            'verified_flag_rollback_supported',
        ];

        foreach ($capabilities as $capability) {
            if ((bool) config('oneqay.durable_runtime_attestation.'.$capability, false) !== true) {
                abort(503);
            }
        }

        if (filter_var(env('ONEQAY_PRODUCTION_DATA_ALLOWED', false), FILTER_VALIDATE_BOOL) !== false) {
            abort(503);
        }

        return response()->json([
            'schema_version' => 1,
            'environment_id' => $environmentId,
            'runtime_class' => 'durable-staging',
            'runtime_model' => 'NON_SYNTHETIC_DURABLE_RUNTIME',
            'environment_isolation' => 'ISOLATED_NON_PRODUCTION',
            'serving_application_runtime' => true,
            'synthetic_fixture_runtime' => false,
            'production_traffic_served' => false,
            'durable_persistence_enabled' => true,
            'durable_session_control_enabled' => true,
            'durable_authorization_enabled' => true,
            'durable_transaction_boundary_enabled' => true,
            'durable_pos_persistence_enabled' => true,
            'exact_running_source_commit' => $source,
            'exact_running_artifact_sha256' => $artifact,
            'authenticated_configuration_mutation_channel' => true,
            'read_before_write_read_after_supported' => true,
            'non_mutating_health_attestation_supported' => true,
            'verified_flag_rollback_supported' => true,
            'activation_authority_binding' => 'SEPARATE_EXPLICIT_ACTIVATION_AUTHORITY',
            'feature_activation_state' => 'INACTIVE',
            'secrets_embedded' => false,
        ], 200, [
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

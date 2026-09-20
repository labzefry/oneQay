<?php

declare(strict_types=1);

namespace App\Delivery\Http\SystemUpdate;

use App\Infrastructure\SystemUpdate\Development\DevelopmentUpdaterViolation;
use App\Infrastructure\SystemUpdate\Development\GovernedDevelopmentUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Author by Lab | zefry
final class DevelopmentUpdateRequestController
{
    public function __construct(private readonly GovernedDevelopmentUpdateRequest $requests)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $payload = $request->except('_token');
        $keys = array_keys($payload);
        sort($keys, SORT_STRING);
        if ($keys !== ['candidate_fingerprint', 'confirmation', 'operator_token', 'totp_code']) {
            return $this->denied();
        }

        $operatorToken = $payload['operator_token'] ?? null;
        $totpCode = $payload['totp_code'] ?? null;
        $confirmation = $payload['confirmation'] ?? null;
        $candidateFingerprint = $payload['candidate_fingerprint'] ?? null;
        if (! is_string($operatorToken)
            || ! is_string($totpCode)
            || ! is_string($confirmation)
            || ! is_string($candidateFingerprint)
            || preg_match('/\A[0-9a-f]{64}\z/', $candidateFingerprint) !== 1
            || ! hash_equals('INSTALL_EXACT_GOVERNED_DEVELOPMENT_RELEASE', $confirmation)) {
            return $this->denied();
        }

        try {
            $result = $this->requests->create($operatorToken, $totpCode, $candidateFingerprint, time());

            return redirect()
                ->route('system-update.page')
                ->with('development_update_feedback', [
                    'state' => 'REQUEST_ACCEPTED',
                    'request_id' => $result['request_id'],
                    'message' => 'Governed development update request accepted. The private cPanel Cron worker will process it.',
                ]);
        } catch (DevelopmentUpdaterViolation) {
            return $this->denied();
        }
    }

    private function denied(): RedirectResponse
    {
        return redirect()
            ->route('system-update.page')
            ->with('development_update_feedback', [
                'state' => 'REQUEST_DENIED',
                'request_id' => null,
                'message' => 'Development update request denied.',
            ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Delivery\Preview;

use App\Application\Identity\IdentityContextViolation;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Preview\PreviewProfile;
use App\Application\Preview\TechnicalPreviewJourney;
use App\Application\Preview\TechnicalPreviewRuntimePolicy;
use App\Application\Preview\TechnicalPreviewVarianceReviewJourney;
use App\Application\Tenancy\MissingTenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class TechnicalPreviewVarianceReviewController
{
    private const PRINCIPAL_SESSION = 'oneqay.preview.principal';
    private const CONTEXT_SESSION = 'oneqay.preview.context_selected';
    private const RECONCILIATION_SESSION = 'oneqay.preview.cash_reconciliation';
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function recordExplanation(
        Request $request,
        TechnicalPreviewJourney $journey,
        TechnicalPreviewVarianceReviewJourney $reviews,
    ): RedirectResponse {
        $this->assertEnabled();
        $operator = $this->verifiedOperator($request, $journey);
        if ($operator instanceof RedirectResponse) {
            return $operator;
        }

        $reconciliation = $request->session()->get(self::RECONCILIATION_SESSION);
        if (! is_array($reconciliation)) {
            return redirect()->route('preview.pos');
        }

        $closingEvidenceId = $reconciliation['closing_cash_evidence_id'] ?? null;
        if (! is_string($closingEvidenceId) || trim($closingEvidenceId) === '') {
            $request->session()->forget(self::RECONCILIATION_SESSION);
            return redirect()->route('preview.pos');
        }

        $operationId = 'preview-varexp-'.substr(hash('sha256', implode('|', [
            $operator->tenantId(),
            $operator->outletId(),
            $closingEvidenceId,
        ])), 0, 32);
        $correlationId = $this->correlationId($request, 'explanation', $closingEvidenceId);

        try {
            $explanation = $reviews->recordExplanation(
                $operator,
                $reconciliation,
                $operationId,
                (string) $request->input('explanation_text', ''),
                $correlationId,
                time(),
            );
            $reconciliation['explanation'] = $explanation;
            $request->session()->put(self::RECONCILIATION_SESSION, $reconciliation);
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|PosTransactionViolation) {
            return redirect()->route('preview.reconciliation')
                ->withErrors(['explanation' => 'Synthetic variance explanation was rejected safely.']);
        }

        return redirect()->route('preview.reconciliation');
    }

    public function reviewDecision(
        Request $request,
        TechnicalPreviewJourney $journey,
        TechnicalPreviewVarianceReviewJourney $reviews,
    ): RedirectResponse {
        $this->assertEnabled();
        $operator = $this->verifiedOperator($request, $journey);
        if ($operator instanceof RedirectResponse) {
            return $operator;
        }

        $reconciliation = $request->session()->get(self::RECONCILIATION_SESSION);
        if (! is_array($reconciliation)) {
            return redirect()->route('preview.pos');
        }

        $closingEvidenceId = $reconciliation['closing_cash_evidence_id'] ?? null;
        $explanation = $reconciliation['explanation'] ?? null;
        $explanationEvidenceId = is_array($explanation) ? ($explanation['evidence_id'] ?? null) : null;
        if (
            ! is_string($closingEvidenceId)
            || trim($closingEvidenceId) === ''
            || ! is_string($explanationEvidenceId)
            || trim($explanationEvidenceId) === ''
        ) {
            return redirect()->route('preview.reconciliation')
                ->withErrors(['review' => 'A canonical synthetic variance explanation is required before review.']);
        }

        $operationId = 'preview-varrev-'.substr(hash('sha256', implode('|', [
            $operator->tenantId(),
            $operator->outletId(),
            $closingEvidenceId,
            $explanationEvidenceId,
        ])), 0, 32);
        $correlationId = $this->correlationId($request, 'review', $closingEvidenceId);

        try {
            $review = $reviews->reviewDecision(
                $operator,
                $reconciliation,
                $operationId,
                (string) $request->input('review_outcome', ''),
                $correlationId,
                time(),
            );
            $reconciliation['review'] = $review;
            $request->session()->put(self::RECONCILIATION_SESSION, $reconciliation);
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|PosTransactionViolation) {
            return redirect()->route('preview.reconciliation')
                ->withErrors(['review' => 'Synthetic variance review decision was rejected safely.']);
        }

        return redirect()->route('preview.reconciliation');
    }

    private function verifiedOperator(
        Request $request,
        TechnicalPreviewJourney $journey,
    ): PreviewProfile|RedirectResponse {
        $principalId = $request->session()->get(self::PRINCIPAL_SESSION);
        $operator = is_string($principalId) ? $journey->profile($principalId) : null;
        if (! $operator instanceof PreviewProfile) {
            return redirect()->route('preview.index');
        }
        if ($request->session()->get(self::CONTEXT_SESSION) !== true) {
            return redirect()->route('preview.context');
        }

        return $operator;
    }

    private function correlationId(Request $request, string $kind, string $closingEvidenceId): string
    {
        $candidate = $request->attributes->get('oneqay.correlation_id');
        if (is_string($candidate) && preg_match(self::IDENTIFIER_PATTERN, $candidate) === 1) {
            return $candidate;
        }

        return 'preview-'.$kind.'-correlation-'.substr(hash('sha256', $closingEvidenceId), 0, 24);
    }

    private function assertEnabled(): void
    {
        abort_unless(
            TechnicalPreviewRuntimePolicy::permits(
                enabled: (bool) config('oneqay.technical_preview.enabled', false),
                runtimeClass: (string) config('oneqay.runtime_class', ''),
                sessionDriver: (string) config('session.driver', ''),
                sessionLifetimeMinutes: (int) config('session.lifetime', 0),
                sessionEncrypted: (bool) config('session.encrypt', false),
                sessionSecure: (bool) config('session.secure', false),
                sessionHttpOnly: (bool) config('session.http_only', false),
                sessionSameSite: (string) config('session.same_site', ''),
                sessionDomain: config('session.domain'),
                sessionPath: (string) config('session.path', ''),
                sessionCookie: (string) config('session.cookie', ''),
            ),
            404,
        );
    }
}

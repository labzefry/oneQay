<?php

declare(strict_types=1);

namespace App\Delivery\Preview;

use App\Application\Identity\IdentityContextViolation;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Preview\TechnicalPreviewJourney;
use App\Application\Tenancy\MissingTenantContext;
use App\Infrastructure\Persistence\PreviewDatabaseQualification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Author by Lab | zefry
final class TechnicalPreviewDatabaseQualificationController
{
    private const PRINCIPAL_SESSION = 'oneqay.preview.principal';
    private const CONTEXT_SESSION = 'oneqay.preview.context_selected';

    public function __invoke(
        Request $request,
        TechnicalPreviewJourney $journey,
        PreviewDatabaseQualification $qualification,
    ): JsonResponse|RedirectResponse {
        $this->assertEnabled();

        $principalId = $request->session()->get(self::PRINCIPAL_SESSION);
        $profile = is_string($principalId) ? $journey->profile($principalId) : null;
        if ($profile === null) {
            return redirect()->route('preview.index');
        }

        if ($request->session()->get(self::CONTEXT_SESSION) !== true) {
            return redirect()->route('preview.context');
        }

        try {
            $journey->catalog($profile);
        } catch (IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation) {
            $request->session()->forget(self::CONTEXT_SESSION);
            return redirect()->route('preview.context');
        }

        $config = config('oneqay.preview_database_qualification', []);
        abort_unless(is_array($config) && ($config['enabled'] ?? false) === true, 404);

        $result = $qualification->qualify($config);
        $result['correlation_id'] = (string) $request->attributes->get(
            'oneqay.correlation_id',
            'preview-correlation-missing',
        );

        return response()
            ->json($result, ($result['status'] ?? null) === 'qualified' ? 200 : 503)
            ->header('Cache-Control', 'no-store, private');
    }

    private function assertEnabled(): void
    {
        $runtimeClass = strtolower((string) env('ONEQAY_RUNTIME_CLASS', ''));
        $enabled = filter_var(env('ONEQAY_TECHNICAL_PREVIEW_ENABLED', false), FILTER_VALIDATE_BOOL);

        abort_unless($enabled && in_array($runtimeClass, ['local', 'test', 'testing', 'ci', 'preview'], true), 404);
    }
}

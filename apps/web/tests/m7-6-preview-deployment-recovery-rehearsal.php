<?php

declare(strict_types=1);

use App\Application\SystemUpdate\Rehearsal\SystemUpdatePreviewRehearsalAuthorization;
use App\Application\SystemUpdate\Rehearsal\SystemUpdatePreviewRehearsalDriver;
use App\Application\SystemUpdate\Rehearsal\SystemUpdatePreviewRehearsalPhase;
use App\Application\SystemUpdate\Rehearsal\SystemUpdatePreviewRehearsalPlan;
use App\Application\SystemUpdate\Rehearsal\SystemUpdatePreviewRehearsalRunner;
use App\Application\SystemUpdate\Rehearsal\SystemUpdatePreviewTargetQualification;
use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;
use App\Application\SystemUpdate\SystemUpdateHealthResult;
use App\Application\SystemUpdate\SystemUpdatePreparedRelease;
use App\Application\SystemUpdate\SystemUpdateReleaseIdentity;
use App\Infrastructure\SystemUpdate\Activation\SystemUpdateAtomicJsonFile;
use App\Infrastructure\SystemUpdate\Rehearsal\FilesystemSystemUpdatePreviewRehearsalEvidenceStore;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException("M7.6 rehearsal regression failed: {$case}");
    }
};

$expectDenied = static function (callable $attempt, string $safeCode, string $case) use ($assert): void {
    try {
        $attempt();
        $assert(false, $case);
    } catch (SystemUpdateControlPlaneViolation $violation) {
        $assert($violation->safeCode() === $safeCode, $case.' safe code');
        $assert($violation->getMessage() === 'System update control plane request denied.', $case.' generic message');
    }
};

$removeTree = static function (string $path) use (&$removeTree): void {
    if (! file_exists($path) && ! is_link($path)) {
        return;
    }
    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry !== '.' && $entry !== '..') {
            $removeTree($path.'/'.$entry);
        }
    }
    @rmdir($path);
};

$identity = static function (string $hex, string $digest): SystemUpdateReleaseIdentity {
    $source = str_repeat($hex, 40);
    return new SystemUpdateReleaseIdentity(
        'm76-preview-'.substr($source, 0, 12),
        $source,
        str_repeat($digest, 64),
    );
};

$driverFactory = static function (bool $candidateHealthy, bool $baselineHealthy): SystemUpdatePreviewRehearsalDriver {
    return new class($candidateHealthy, $baselineHealthy) implements SystemUpdatePreviewRehearsalDriver {
        /** @var list<string> */
        public array $calls = [];

        public function __construct(
            private readonly bool $candidateHealthy,
            private readonly bool $baselineHealthy,
        ) {
        }

        public function preflight(SystemUpdatePreviewRehearsalPlan $plan): void { $this->calls[] = 'preflight'; }
        public function verifyRecoveryCheckpoint(SystemUpdatePreviewRehearsalPlan $plan): void { $this->calls[] = 'recovery'; }
        public function stageCandidate(SystemUpdatePreviewRehearsalPlan $plan): void { $this->calls[] = 'stage'; }
        public function activateCandidate(SystemUpdatePreviewRehearsalPlan $plan): void { $this->calls[] = 'activate'; }

        public function verifyCandidateHealth(SystemUpdatePreviewRehearsalPlan $plan): SystemUpdateHealthResult
        {
            $this->calls[] = 'candidate-health';
            return $this->candidateHealthy
                ? SystemUpdateHealthResult::healthy($plan->candidateRelease()->releaseId())
                : SystemUpdateHealthResult::unhealthy('candidate_readiness_failed', $plan->candidateRelease()->releaseId());
        }

        public function rollbackToBaseline(SystemUpdatePreviewRehearsalPlan $plan): void { $this->calls[] = 'rollback'; }

        public function verifyBaselineHealth(SystemUpdatePreviewRehearsalPlan $plan): SystemUpdateHealthResult
        {
            $this->calls[] = 'baseline-health';
            return $this->baselineHealthy
                ? SystemUpdateHealthResult::healthy($plan->baselineRelease()->releaseId())
                : SystemUpdateHealthResult::unhealthy('baseline_readiness_failed', $plan->baselineRelease()->releaseId());
        }
    };
};

$root = sys_get_temp_dir().'/oneqay-m76-rehearsal-'.bin2hex(random_bytes(8));
mkdir($root, 0700, true);

try {
    $evidenceStore = new FilesystemSystemUpdatePreviewRehearsalEvidenceStore(
        $root,
        new SystemUpdateAtomicJsonFile(),
    );

    $baseline = $identity('a', '1');
    $candidate = $identity('b', '2');
    $fingerprint = str_repeat('c', 64);
    $target = new SystemUpdatePreviewTargetQualification(
        'preview-target-qualified',
        'm75-evidence-aaaaaaaaaaaaaaaa',
        $fingerprint,
        true,
        true,
    );
    $authorization = new SystemUpdatePreviewRehearsalAuthorization(
        'm76-auth-aaaaaaaaaaaaaaaa',
        $fingerprint,
        1_786_920_000,
    );

    $plan = new SystemUpdatePreviewRehearsalPlan(
        'm76-op-aaaaaaaaaaaaaaaa',
        $target,
        $baseline,
        $candidate,
    );

    $healthyDriver = $driverFactory(true, true);
    $healthyRunner = new SystemUpdatePreviewRehearsalRunner($healthyDriver, $evidenceStore);
    $healthyOutcome = $healthyRunner->run($plan, $authorization, 1_786_920_100);

    $assert($healthyOutcome->terminalPhase() === SystemUpdatePreviewRehearsalPhase::COMPLETED, 'RUN-001 completed');
    $assert($healthyOutcome->activeRelease()->equals($baseline), 'RUN-002 baseline restored after deliberate rollback');
    $assert($healthyOutcome->safeCode() === 'deployment_and_rollback_rehearsed', 'RUN-003 safe outcome');
    $assert(
        $healthyDriver->calls === ['preflight', 'recovery', 'stage', 'activate', 'candidate-health', 'rollback', 'baseline-health'],
        'RUN-004 exact rehearsal sequence',
    );

    $evidencePath = $root.'/deployment-state/m7-6-rehearsals/'.$plan->operationId().'.json';
    $assert(is_file($evidencePath), 'EVIDENCE-001 persisted');
    $evidenceRaw = (string) file_get_contents($evidencePath);
    $evidence = json_decode($evidenceRaw, true, 64, JSON_THROW_ON_ERROR);
    $assert(($evidence['target']['runtime_class'] ?? null) === 'preview', 'EVIDENCE-002 preview only');
    $assert(($evidence['target']['synthetic_only'] ?? null) === true, 'EVIDENCE-003 synthetic only');
    $assert(($evidence['target']['production'] ?? null) === false, 'EVIDENCE-004 production false');
    $assert(($evidence['migration_classification'] ?? null) === 'NO_SCHEMA_CHANGE', 'EVIDENCE-005 no schema change');

    foreach (['password', 'db_password', 'api_token', 'secret_value', '.env', 'public_html', '/home/', 'hostname', 'credential'] as $forbidden) {
        $assert(! str_contains(strtolower($evidenceRaw), $forbidden), 'SAFE-001 excludes '.$forbidden);
    }

    $unhealthyPlan = new SystemUpdatePreviewRehearsalPlan(
        'm76-op-bbbbbbbbbbbbbbbb',
        $target,
        $baseline,
        $candidate,
    );
    $unhealthyDriver = $driverFactory(false, true);
    $unhealthyOutcome = (new SystemUpdatePreviewRehearsalRunner($unhealthyDriver, $evidenceStore))
        ->run($unhealthyPlan, $authorization, 1_786_920_200);
    $assert($unhealthyOutcome->safeCode() === 'candidate_unhealthy_recovered', 'RECOVERY-001 unhealthy candidate recovered');
    $assert($unhealthyOutcome->activeRelease()->equals($baseline), 'RECOVERY-002 baseline active');

    $mismatchAuthorization = new SystemUpdatePreviewRehearsalAuthorization(
        'm76-auth-bbbbbbbbbbbbbbbb',
        str_repeat('d', 64),
        1_786_920_000,
    );
    $mismatchDriver = $driverFactory(true, true);
    $mismatchPlan = new SystemUpdatePreviewRehearsalPlan(
        'm76-op-cccccccccccccccc',
        $target,
        $baseline,
        $candidate,
    );
    $expectDenied(
        static fn () => (new SystemUpdatePreviewRehearsalRunner($mismatchDriver, $evidenceStore))
            ->run($mismatchPlan, $mismatchAuthorization, 1_786_920_100),
        'm76_deployment_authorization_target_mismatch',
        'AUTH-001 target binding required',
    );
    $assert($mismatchDriver->calls === [], 'AUTH-002 mismatch denied before driver');

    $staleAuthorization = new SystemUpdatePreviewRehearsalAuthorization(
        'm76-auth-cccccccccccccccc',
        $fingerprint,
        1_786_910_000,
    );
    $staleDriver = $driverFactory(true, true);
    $stalePlan = new SystemUpdatePreviewRehearsalPlan(
        'm76-op-dddddddddddddddd',
        $target,
        $baseline,
        $candidate,
    );
    $expectDenied(
        static fn () => (new SystemUpdatePreviewRehearsalRunner($staleDriver, $evidenceStore))
            ->run($stalePlan, $staleAuthorization, 1_786_920_100),
        'm76_deployment_authorization_stale',
        'AUTH-003 stale authorization denied',
    );
    $assert($staleDriver->calls === [], 'AUTH-004 stale denied before driver');

    $unqualifiedTarget = new SystemUpdatePreviewTargetQualification(
        'preview-target-unqualified',
        'm75-evidence-bbbbbbbbbbbbbbbb',
        str_repeat('e', 64),
        false,
        true,
    );
    $unqualifiedPlan = new SystemUpdatePreviewRehearsalPlan(
        'm76-op-eeeeeeeeeeeeeeee',
        $unqualifiedTarget,
        $baseline,
        $candidate,
    );
    $unqualifiedAuth = new SystemUpdatePreviewRehearsalAuthorization(
        'm76-auth-dddddddddddddddd',
        $unqualifiedTarget->fingerprint(),
        1_786_920_000,
    );
    $unqualifiedDriver = $driverFactory(true, true);
    $expectDenied(
        static fn () => (new SystemUpdatePreviewRehearsalRunner($unqualifiedDriver, $evidenceStore))
            ->run($unqualifiedPlan, $unqualifiedAuth, 1_786_920_100),
        'm76_target_not_qualified',
        'TARGET-001 qualification required',
    );
    $assert($unqualifiedDriver->calls === [], 'TARGET-002 unqualified denied before driver');

    $nonSyntheticTarget = new SystemUpdatePreviewTargetQualification(
        'preview-target-realdata',
        'm75-evidence-cccccccccccccccc',
        str_repeat('f', 64),
        true,
        false,
    );
    $nonSyntheticPlan = new SystemUpdatePreviewRehearsalPlan(
        'm76-op-ffffffffffffffff',
        $nonSyntheticTarget,
        $baseline,
        $candidate,
    );
    $nonSyntheticAuth = new SystemUpdatePreviewRehearsalAuthorization(
        'm76-auth-eeeeeeeeeeeeeeee',
        $nonSyntheticTarget->fingerprint(),
        1_786_920_000,
    );
    $expectDenied(
        static fn () => (new SystemUpdatePreviewRehearsalRunner($driverFactory(true, true), $evidenceStore))
            ->run($nonSyntheticPlan, $nonSyntheticAuth, 1_786_920_100),
        'm76_synthetic_preview_required',
        'TARGET-003 non-synthetic denied',
    );

    $rollbackFailurePlan = new SystemUpdatePreviewRehearsalPlan(
        'm76-op-1111111111111111',
        $target,
        $baseline,
        $candidate,
    );
    $expectDenied(
        static fn () => (new SystemUpdatePreviewRehearsalRunner($driverFactory(true, false), $evidenceStore))
            ->run($rollbackFailurePlan, $authorization, 1_786_920_300),
        'm76_rollback_health_failed',
        'RECOVERY-003 unhealthy baseline is terminal failure',
    );

    $expectDenied(
        static fn () => new SystemUpdatePreviewRehearsalPlan(
            'm76-op-2222222222222222',
            $target,
            $baseline,
            $candidate,
            'REQUIRES_SCHEMA_CHANGE',
        ),
        'schema_change_not_supported',
        'SCHEMA-001 schema change rejected',
    );

    echo "M7.6 Preview Deployment / Recovery Rehearsal foundation regression passed.\n";
} finally {
    $removeTree($root);
}

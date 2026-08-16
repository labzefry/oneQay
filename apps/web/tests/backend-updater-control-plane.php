<?php

declare(strict_types=1);

use App\Application\SystemUpdate\Security\PrivilegedUpdateAuthorization;
use App\Application\SystemUpdate\Security\PrivilegedUpdateCapability;
use App\Application\SystemUpdate\SystemUpdateControlPlane;
use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;
use App\Application\SystemUpdate\SystemUpdateOperationState;
use App\Application\SystemUpdate\SystemUpdateStateMachine;
use App\Domain\Identity\PlatformIdentityId;
use App\Infrastructure\SystemUpdate\ConfiguredSystemUpdateFeatureGate;
use App\Infrastructure\SystemUpdate\DisabledSystemUpdateOperationStateStore;
use App\Infrastructure\SystemUpdate\UnavailableSystemUpdateReleaseAvailabilityProbe;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assertControlPlane = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException("Backend updater control-plane regression failed: {$case}");
    }
};

$expectDenied = static function (callable $attempt, string $expectedCode, string $case) use ($assertControlPlane): void {
    try {
        $attempt();
        $assertControlPlane(false, $case);
    } catch (SystemUpdateControlPlaneViolation $violation) {
        $assertControlPlane($violation->safeCode() === $expectedCode, $case.' safe code');
        $assertControlPlane(
            $violation->getMessage() === 'System update control plane request denied.',
            $case.' generic message',
        );
    }
};

$states = array_map(
    static fn (SystemUpdateOperationState $state): string => $state->value,
    SystemUpdateOperationState::cases(),
);
$assertControlPlane($states === [
    'IDLE',
    'CHECKING',
    'AVAILABLE',
    'DOWNLOADING',
    'VERIFYING',
    'STAGED',
    'PREFLIGHTING',
    'READY_TO_APPLY',
    'APPLYING_SHARED_CONFIGURATION',
    'PREPARING_PUBLIC_SURFACE',
    'SWITCHING',
    'VERIFYING_HEALTH',
    'SUCCEEDED',
    'ROLLING_BACK',
    'ROLLED_BACK',
    'FAILED',
], 'STATE-001 ADR state set remains exact');

$machine = new SystemUpdateStateMachine();
$assertControlPlane(
    $machine->allows(SystemUpdateOperationState::IDLE, SystemUpdateOperationState::CHECKING),
    'STATE-002 valid IDLE to CHECKING transition',
);
$assertControlPlane(
    ! $machine->allows(SystemUpdateOperationState::IDLE, SystemUpdateOperationState::SWITCHING),
    'STATE-003 direct IDLE to SWITCHING denied',
);
$expectDenied(
    static fn () => $machine->assertAllowed(SystemUpdateOperationState::IDLE, SystemUpdateOperationState::SWITCHING),
    'invalid_state_transition',
    'STATE-004 invalid transition fails closed',
);

$disabledPlane = new SystemUpdateControlPlane(
    new ConfiguredSystemUpdateFeatureGate(false, false),
    new DisabledSystemUpdateOperationStateStore(),
    new UnavailableSystemUpdateReleaseAvailabilityProbe(),
);
$disabledStatus = $disabledPlane->status()->toSafeArray();
$assertControlPlane($disabledStatus['control_plane'] === 'DISABLED', 'FLAG-001 control plane defaults disabled');
$assertControlPlane($disabledStatus['install'] === 'DISABLED', 'FLAG-002 install disabled');
$assertControlPlane($disabledStatus['state'] === 'IDLE', 'STATE-005 disabled store remains IDLE');
$assertControlPlane($disabledStatus['active_operation'] === false, 'STATE-006 no active operation');
$assertControlPlane($disabledStatus['activation_supported'] === false, 'BOUNDARY-001 activation unsupported');
$assertControlPlane($disabledStatus['schema_change_supported'] === false, 'BOUNDARY-002 schema change unsupported');
$assertControlPlane($disabledStatus['deployment_authorized'] === false, 'BOUNDARY-003 deployment not authorized');
$expectDenied(
    static fn () => $disabledPlane->checkAvailability(),
    'control_plane_disabled',
    'FLAG-003 disabled check denied',
);
$expectDenied(
    static fn () => $disabledPlane->requestInstall(null),
    'control_plane_disabled',
    'FLAG-004 disabled install denied before authorization',
);

$readOnlyPlane = new SystemUpdateControlPlane(
    new ConfiguredSystemUpdateFeatureGate(true, false),
    new DisabledSystemUpdateOperationStateStore(),
    new UnavailableSystemUpdateReleaseAvailabilityProbe(),
);
$readOnlyStatus = $readOnlyPlane->checkAvailability()->toSafeArray();
$assertControlPlane($readOnlyStatus['control_plane'] === 'ENABLED', 'READ-001 read-only control plane may be explicitly enabled');
$assertControlPlane($readOnlyStatus['install'] === 'DISABLED', 'READ-002 install remains disabled');
$assertControlPlane($readOnlyStatus['release_check']['status'] === 'UNAVAILABLE', 'READ-003 no network release adapter is invented');

$authorization = new PrivilegedUpdateAuthorization(
    PlatformIdentityId::fromString('synthetic-platform-admin'),
    PrivilegedUpdateCapability::INSTALL,
    1_786_900_000,
);
$expectDenied(
    static fn () => $readOnlyPlane->requestInstall($authorization),
    'install_disabled',
    'INSTALL-001 privileged authorization cannot bypass hard install gate',
);

$serialized = json_encode([$disabledStatus, $readOnlyStatus], JSON_THROW_ON_ERROR);
foreach (['password', 'secret', 'token', '.env', '/home/', 'public_html'] as $forbidden) {
    $assertControlPlane(
        ! str_contains(strtolower($serialized), strtolower($forbidden)),
        'SAFE-001 safe status excludes restricted operational detail: '.$forbidden,
    );
}

fwrite(STDOUT, "Backend updater control-plane regression passed.\n");

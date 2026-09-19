<?php

declare(strict_types=1);

use App\Infrastructure\Installation\PrebootInstallationCompletionHandoff;
use App\Infrastructure\Installation\PrebootTechnicalPreviewActivationRequest;
use App\Infrastructure\Installation\PrebootTechnicalPreviewActivationAuthorityReadiness;
use App\Infrastructure\Installation\PrebootInstallationConfiguration;
use App\Infrastructure\Installation\PrebootRuntimeConfigurationPostPromotionVerification;
use App\Infrastructure\Installation\PrebootRuntimeConfigurationPromotionExecution;
use App\Infrastructure\Installation\PrebootRuntimeConfigurationPromotionReadiness;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint195 regression failed: '.$message);
    }
};

$root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s195-'.bin2hex(random_bytes(8));
$shared = $root.DIRECTORY_SEPARATOR.'shared';
$install = $shared.DIRECTORY_SEPARATOR.'install';
$runtime = $shared.DIRECTORY_SEPARATOR.'runtime';
$releaseId = 'm75-preview-195abc195abc';
$now = 1790005000;
$installationToken = 's195-install.'.str_repeat('H', 48);
$approvalToken = 's195-approve.'.str_repeat('J', 48);
$technicalPreviewApprovalToken = 's195-preview.'.str_repeat('K', 48);
$password = 'sprint195-db-secret';

$remove = null;
$remove = static function (string $path) use (&$remove): void {
    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }
    if (! is_dir($path)) {
        return;
    }
    foreach (new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS) as $item) {
        $remove($item->getPathname());
    }
    @rmdir($path);
};

$writeJson = static function (string $path, array $payload): void {
    $encoded = json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    if (file_put_contents($path, $encoded, LOCK_EX) === false) {
        throw new RuntimeException('Unable to write Sprint195 JSON fixture.');
    }
    @chmod($path, 0600);
};

try {
    $assert(mkdir($install, 0700, true), 'private install boundary could not be created.');

    $writeJson($install.DIRECTORY_SEPARATOR.'authority.json', [
        'schema_version' => 1,
        'product' => 'oneQay',
        'release_id' => $releaseId,
        'token_sha256' => hash('sha256', $installationToken),
        'expires_at' => $now + 900,
        'activation_authorized' => false,
        'attribution' => 'Lab | zefry',
    ]);

    $installer = new PrebootInstallationConfiguration(
        $shared,
        $releaseId,
        $now,
        static fn (array $configuration): array => [
            'ready' => true,
            'facts' => [
                'connected' => true,
                'engine' => 'mysql',
                'server_version' => '8.0.36',
                'charset' => 'utf8mb4',
                'timezone' => '+00:00',
                'schema_state' => 'empty',
                'least_privilege' => true,
            ],
        ],
    );

    $prepared = $installer->prepare([
        'installation_token' => $installationToken,
        'app_url' => 'https://preview.example.test',
        'db_host' => 'db.internal.example',
        'db_port' => '3306',
        'db_database' => 'oneqay_preview',
        'db_username' => 'oneqay_runtime',
        'db_password' => $password,
    ]);
    $assert(($prepared['promotion_request_pending'] ?? false) === true, 'promotion request fixture was not created.');

    $pendingPath = $runtime.DIRECTORY_SEPARATOR.'.env.pending';
    $activePath = $runtime.DIRECTORY_SEPARATOR.'.env';
    $handoffPath = $install.DIRECTORY_SEPARATOR.'activation-readiness.json';
    $requestPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-promotion-request.json';
    $authorityPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-promotion-authority.json';
    $receiptPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-promotion-execution.json';
    $verificationPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-post-promotion-verification.json';
    $completionPath = $install.DIRECTORY_SEPARATOR.'installation-completion.json';

    $pendingRaw = (string) file_get_contents($pendingPath);
    $handoffRaw = (string) file_get_contents($handoffPath);
    $requestRaw = (string) file_get_contents($requestPath);
    $request = json_decode($requestRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($request), 'promotion request fixture is invalid.');

    $authority = [
        'schema_version' => 1,
        'product' => 'oneQay',
        'authority_id' => 'promotion-authority-'.str_repeat('7', 24),
        'authority_state' => 'GRANTED',
        'scope' => 'PROMOTE_VERIFIED_PENDING_ENVIRONMENT_TO_ACTIVE_RUNTIME_CONFIGURATION',
        'request_id' => (string) $request['request_id'],
        'release_id' => $releaseId,
        'pending_environment_sha256' => hash('sha256', $pendingRaw),
        'activation_readiness_sha256' => hash('sha256', $handoffRaw),
        'promotion_request_sha256' => hash('sha256', $requestRaw),
        'approval_token_sha256' => hash('sha256', $approvalToken),
        'authorized_at_unix' => $now - 10,
        'expires_at_unix' => $now + 300,
        'single_use' => true,
        'promotion_authorized' => true,
        'migration_execution_authorized' => false,
        'technical_preview_authorized' => false,
        'production_authorized' => false,
        'updater_authorized' => false,
        'deployment_authorized' => false,
        'attribution' => 'Lab | zefry',
    ];
    $writeJson($authorityPath, $authority);

    $readiness = new PrebootRuntimeConfigurationPromotionReadiness($shared, $releaseId, $now);
    $assert(($readiness->attest($approvalToken)['state'] ?? null) === 'PROMOTION_EXECUTION_READY_NOT_EXECUTED', 'readiness fixture failed.');

    $executor = new PrebootRuntimeConfigurationPromotionExecution($shared, $releaseId, $now);
    $assert(($executor->execute($approvalToken)['state'] ?? null) === 'RUNTIME_CONFIGURATION_PROMOTED_NOT_ACTIVATED', 'promotion execution fixture failed.');

    $verification = new PrebootRuntimeConfigurationPostPromotionVerification($shared, $releaseId, $now);
    $assert(($verification->verify()['state'] ?? null) === 'RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED', 'post-promotion verification fixture failed.');

    $activeRaw = (string) file_get_contents($activePath);
    $receiptRaw = (string) file_get_contents($receiptPath);
    $verificationRaw = (string) file_get_contents($verificationPath);

    $completion = new PrebootInstallationCompletionHandoff($shared, $releaseId, $now);
    $before = $completion->inspect();
    $assert(($before['state'] ?? null) === 'INSTALLATION_COMPLETION_READY_TO_SEAL', 'completion was not ready to seal.');
    $assert(($before['complete'] ?? true) === false, 'unsealed completion reported complete.');

    $sealed = $completion->seal();
    $assert(($sealed['state'] ?? null) === 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED', 'completion state invalid.');
    $assert(($sealed['complete'] ?? false) === true, 'completion did not report complete.');
    $assert(($sealed['activation_authorized'] ?? true) === false, 'completion granted activation authority.');
    $assert(is_file($completionPath), 'completion evidence was not written.');

    $permissions = fileperms($completionPath);
    $assert(is_int($permissions) && ($permissions & 0077) === 0, 'completion evidence is not private.');

    $completionRaw = (string) file_get_contents($completionPath);
    $payload = json_decode($completionRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($payload), 'completion evidence is invalid JSON.');
    $assert(($payload['release_id'] ?? null) === $releaseId, 'completion release binding invalid.');
    $assert(($payload['request_id'] ?? null) === $request['request_id'], 'completion request binding invalid.');
    $assert(($payload['authority_id'] ?? null) === $authority['authority_id'], 'completion authority binding invalid.');
    $assert(($payload['execution_receipt_sha256'] ?? null) === hash('sha256', $receiptRaw), 'completion receipt binding invalid.');
    $assert(($payload['post_promotion_verification_sha256'] ?? null) === hash('sha256', $verificationRaw), 'completion verification binding invalid.');
    $assert(($payload['active_environment_sha256'] ?? null) === hash('sha256', $activeRaw), 'completion active binding invalid.');
    $assert(($payload['installer_reentry_state'] ?? null) === 'READ_ONLY_COMPLETION', 'installer re-entry state invalid.');

    foreach ([
        'migration_execution_authorized',
        'technical_preview_authorized',
        'persistence_authorized',
        'production_authorized',
        'updater_authorized',
        'deployment_authorized',
    ] as $flag) {
        $assert(($payload[$flag] ?? null) === false, 'completion crossed '.$flag.' boundary.');
    }

    foreach ([$password, $installationToken, $approvalToken, 'oneqay_runtime'] as $secret) {
        $assert(! str_contains($completionRaw, $secret), 'plaintext secret leaked into completion evidence.');
    }

    $assert((string) file_get_contents($activePath) === $activeRaw, 'completion mutated active environment.');
    $assert((string) file_get_contents($receiptPath) === $receiptRaw, 'completion mutated execution receipt.');
    $assert((string) file_get_contents($verificationPath) === $verificationRaw, 'completion mutated verification evidence.');

    $inspection = $completion->inspect();
    $assert(($inspection['state'] ?? null) === 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED', 'valid completion not recognized.');
    $assert(($inspection['complete'] ?? false) === true, 'valid completion not reported complete.');

    $replayed = $completion->seal();
    $assert(($replayed['completion_sha256'] ?? null) === hash('sha256', $completionRaw), 'completion replay changed evidence.');

    if (file_put_contents($activePath, $activeRaw."\n# tampered\n", LOCK_EX) === false) {
        throw new RuntimeException('Unable to tamper active environment fixture.');
    }
    @chmod($activePath, 0600);
    $assert(($completion->inspect()['state'] ?? null) === 'INSTALLATION_COMPLETION_NOT_READY', 'active tamper did not invalidate completion context.');

    if (file_put_contents($activePath, $activeRaw, LOCK_EX) === false) {
        throw new RuntimeException('Unable to restore active environment fixture.');
    }
    @chmod($activePath, 0600);

    $tamperedVerification = json_decode($verificationRaw, true, 32, JSON_THROW_ON_ERROR);
    $tamperedVerification['authority_id'] = 'promotion-authority-'.str_repeat('8', 24);
    $writeJson($verificationPath, $tamperedVerification);
    $assert(($completion->inspect()['state'] ?? null) === 'INSTALLATION_COMPLETION_NOT_READY', 'verification tamper did not invalidate completion context.');

    if (file_put_contents($verificationPath, $verificationRaw, LOCK_EX) === false) {
        throw new RuntimeException('Unable to restore verification fixture.');
    }
    @chmod($verificationPath, 0600);

    $wrongRelease = new PrebootInstallationCompletionHandoff($shared, 'm75-preview-ffffffffffff', $now);
    $assert(($wrongRelease->inspect()['state'] ?? null) === 'INSTALLATION_COMPLETION_NOT_READY', 'wrong release did not fail closed.');

    $activationRequestPath = $install.DIRECTORY_SEPARATOR.'technical-preview-activation-request.json';
    $activationRequest = new PrebootTechnicalPreviewActivationRequest($shared, $releaseId, $now);

    $activationBefore = $activationRequest->inspect();
    $assert(($activationBefore['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_READY_TO_CREATE', 'activation request was not ready after completion.');
    $assert(($activationBefore['request_ready'] ?? true) === false, 'uncreated activation request reported ready.');
    $assert(($activationBefore['technical_preview_authorized'] ?? true) === false, 'activation request inspection granted Technical Preview authority.');

    $completionRawBeforeActivationRequest = (string) file_get_contents($completionPath);
    $activeRawBeforeActivationRequest = (string) file_get_contents($activePath);

    $activationCreated = $activationRequest->create();
    $assert(($activationCreated['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_PENDING_APPROVAL', 'activation request state invalid.');
    $assert(($activationCreated['request_ready'] ?? false) === true, 'activation request was not reported ready.');
    $assert(($activationCreated['technical_preview_authorized'] ?? true) === false, 'activation request creation granted Technical Preview authority.');
    $assert(is_file($activationRequestPath), 'activation request evidence was not written.');

    $activationPermissions = fileperms($activationRequestPath);
    $assert(is_int($activationPermissions) && ($activationPermissions & 0077) === 0, 'activation request evidence is not private.');

    $activationRaw = (string) file_get_contents($activationRequestPath);
    $activationPayload = json_decode($activationRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($activationPayload), 'activation request evidence is invalid JSON.');
    $assert(($activationPayload['release_id'] ?? null) === $releaseId, 'activation request release binding invalid.');
    $assert(($activationPayload['active_environment_sha256'] ?? null) === hash('sha256', $activeRawBeforeActivationRequest), 'activation request active binding invalid.');
    $assert(($activationPayload['installation_completion_sha256'] ?? null) === hash('sha256', $completionRawBeforeActivationRequest), 'activation request completion binding invalid.');
    $assert(($activationPayload['created_at_unix'] ?? null) === $now, 'activation request audit time invalid.');
    $assert(($activationPayload['requested_operation'] ?? null) === 'ENABLE_SYNTHETIC_TECHNICAL_PREVIEW_RUNTIME', 'activation request operation invalid.');
    $assert(($activationPayload['requested_runtime_envelope']['runtime_class'] ?? null) === 'preview', 'activation request runtime class invalid.');
    $assert(($activationPayload['requested_runtime_envelope']['technical_preview_enabled'] ?? null) === true, 'activation request did not describe requested preview enablement.');
    $assert(($activationPayload['requested_runtime_envelope']['persistence_enabled'] ?? null) === false, 'activation request crossed persistence boundary.');
    $assert(($activationPayload['requested_runtime_envelope']['system_update_control_plane_enabled'] ?? null) === false, 'activation request crossed updater boundary.');
    $assert(($activationPayload['required_authority']['state'] ?? null) === 'NOT_GRANTED', 'activation request granted authority.');
    $assert(($activationPayload['required_authority']['separate_operational_authority_required'] ?? null) === true, 'activation request lost separate authority requirement.');

    foreach ([
        'migration_execution_authorized',
        'technical_preview_authorized',
        'persistence_authorized',
        'production_authorized',
        'updater_authorized',
        'deployment_authorized',
    ] as $flag) {
        $assert(($activationPayload[$flag] ?? null) === false, 'activation request crossed '.$flag.' boundary.');
    }

    foreach ([$password, $installationToken, $approvalToken, 'oneqay_runtime'] as $secret) {
        $assert(! str_contains($activationRaw, $secret), 'plaintext secret leaked into activation request evidence.');
    }

    $assert((string) file_get_contents($activePath) === $activeRawBeforeActivationRequest, 'activation request mutated active environment.');
    $assert((string) file_get_contents($completionPath) === $completionRawBeforeActivationRequest, 'activation request mutated completion evidence.');

    $activationInspection = $activationRequest->inspect();
    $assert(($activationInspection['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_PENDING_APPROVAL', 'valid activation request not recognized.');
    $assert(($activationInspection['technical_preview_authorized'] ?? true) === false, 'pending activation request reported authorized.');

    $activationReplay = $activationRequest->create();
    $assert(($activationReplay['request_sha256'] ?? null) === hash('sha256', $activationRaw), 'activation request replay changed evidence.');

    $tamperedActivation = $activationPayload;
    $tamperedActivation['requested_runtime_envelope']['persistence_enabled'] = true;
    $writeJson($activationRequestPath, $tamperedActivation);
    $assert(($activationRequest->inspect()['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_INVALID', 'activation request tamper did not fail closed.');
    $activationTamperDenied = false;
    try {
        $activationRequest->create();
    } catch (RuntimeException $exception) {
        $activationTamperDenied = $exception->getMessage() === 'technical_preview_activation_request_invalid';
    }
    $assert($activationTamperDenied, 'tampered activation request was not denied.');

    if (file_put_contents($activationRequestPath, $activationRaw, LOCK_EX) === false) {
        throw new RuntimeException('Unable to restore activation request fixture.');
    }
    @chmod($activationRequestPath, 0600);

    $tamperedCompletionForActivation = json_decode($completionRawBeforeActivationRequest, true, 32, JSON_THROW_ON_ERROR);
    $tamperedCompletionForActivation['authority_id'] = 'promotion-authority-'.str_repeat('9', 24);
    $writeJson($completionPath, $tamperedCompletionForActivation);
    $assert(($activationRequest->inspect()['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_NOT_READY', 'completion tamper did not invalidate activation request eligibility.');

    if (file_put_contents($completionPath, $completionRawBeforeActivationRequest, LOCK_EX) === false) {
        throw new RuntimeException('Unable to restore completion fixture after activation request tamper test.');
    }
    @chmod($completionPath, 0600);

    $wrongActivationRelease = new PrebootTechnicalPreviewActivationRequest($shared, 'm75-preview-ffffffffffff', $now);
    $assert(($wrongActivationRelease->inspect()['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_NOT_READY', 'wrong release activation request did not fail closed.');

    $activationSchemaPath = __DIR__.'/../../../tools/installation/technical-preview-activation-request.schema.json';
    $activationSchema = json_decode((string) file_get_contents($activationSchemaPath), true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($activationSchema), 'activation request schema invalid.');
    $assert(($activationSchema['properties']['request_state']['const'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_PENDING_APPROVAL', 'activation request schema state invalid.');
    $assert(($activationSchema['properties']['requested_operation']['const'] ?? null) === 'ENABLE_SYNTHETIC_TECHNICAL_PREVIEW_RUNTIME', 'activation request schema operation invalid.');
    $assert(($activationSchema['properties']['required_authority']['properties']['state']['const'] ?? null) === 'NOT_GRANTED', 'activation request schema authority boundary invalid.');
    $assert(($activationSchema['properties']['technical_preview_authorized']['const'] ?? null) === false, 'activation request schema crossed Technical Preview authority boundary.');
    $assert(($activationSchema['properties']['production_authorized']['const'] ?? null) === false, 'activation request schema crossed Production authority boundary.');

    $technicalPreviewAuthorityPath = $install.DIRECTORY_SEPARATOR.'technical-preview-activation-authority.json';
    $technicalPreviewReadinessPath = $install.DIRECTORY_SEPARATOR.'technical-preview-activation-readiness.json';
    $authorityReadiness = new PrebootTechnicalPreviewActivationAuthorityReadiness($shared, $releaseId, $now);

    $missingAuthority = $authorityReadiness->inspect();
    $assert(($missingAuthority['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY_MISSING', 'missing Technical Preview authority was not reported.');
    $assert(($missingAuthority['execution_ready'] ?? true) === false, 'missing authority reported execution ready.');

    $technicalPreviewAuthority = [
        'schema_version' => 1,
        'product' => 'oneQay',
        'authority_id' => 'technical-preview-authority-'.str_repeat('a', 24),
        'authority_state' => 'GRANTED',
        'scope' => 'ENABLE_SYNTHETIC_TECHNICAL_PREVIEW_RUNTIME',
        'request_id' => (string) $activationPayload['request_id'],
        'release_id' => $releaseId,
        'active_environment_sha256' => hash('sha256', $activeRawBeforeActivationRequest),
        'installation_completion_sha256' => hash('sha256', $completionRawBeforeActivationRequest),
        'activation_request_sha256' => hash('sha256', $activationRaw),
        'approval_token_sha256' => hash('sha256', $technicalPreviewApprovalToken),
        'authorized_at_unix' => $now - 10,
        'expires_at_unix' => $now + 300,
        'single_use' => true,
        'technical_preview_authorized' => true,
        'persistence_authorized' => false,
        'migration_execution_authorized' => false,
        'production_authorized' => false,
        'updater_authorized' => false,
        'deployment_authorized' => false,
        'target_environment_preflight_required' => true,
        'attribution' => 'Lab | zefry',
    ];
    $writeJson($technicalPreviewAuthorityPath, $technicalPreviewAuthority);

    $tokenRequired = $authorityReadiness->inspect();
    $assert(($tokenRequired['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY_TOKEN_REQUIRED', 'valid authority did not require token qualification.');
    $assert(($tokenRequired['authority_present'] ?? false) === true, 'valid authority not reported present.');
    $assert(($tokenRequired['token_required'] ?? false) === true, 'valid authority did not require token.');
    $assert(($tokenRequired['execution_ready'] ?? true) === false, 'unqualified authority reported execution ready.');

    $wrongTokenDenied = false;
    try {
        $authorityReadiness->qualifyAndAttest('wrong-preview-token-'.str_repeat('X', 40));
    } catch (RuntimeException $exception) {
        $wrongTokenDenied = $exception->getMessage() === 'technical_preview_activation_authority_denied';
    }
    $assert($wrongTokenDenied, 'wrong Technical Preview approval token was not denied.');
    $assert(! is_file($technicalPreviewReadinessPath), 'wrong token created readiness evidence.');

    $activeBeforeReadiness = (string) file_get_contents($activePath);
    $completionBeforeReadiness = (string) file_get_contents($completionPath);
    $activationRequestBeforeReadiness = (string) file_get_contents($activationRequestPath);

    $qualified = $authorityReadiness->qualifyAndAttest($technicalPreviewApprovalToken);
    $assert(($qualified['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_READY_NOT_EXECUTED', 'Technical Preview activation readiness state invalid.');
    $assert(($qualified['execution_ready'] ?? false) === true, 'qualified authority did not become execution ready.');
    $assert(($qualified['activation_executed'] ?? true) === false, 'qualification executed Technical Preview activation.');
    $assert(is_file($technicalPreviewReadinessPath), 'Technical Preview readiness evidence was not written.');

    $readinessPermissions = fileperms($technicalPreviewReadinessPath);
    $assert(is_int($readinessPermissions) && ($readinessPermissions & 0077) === 0, 'Technical Preview readiness evidence is not private.');

    $technicalPreviewReadinessRaw = (string) file_get_contents($technicalPreviewReadinessPath);
    $technicalPreviewReadiness = json_decode($technicalPreviewReadinessRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($technicalPreviewReadiness), 'Technical Preview readiness evidence is invalid JSON.');
    $assert(($technicalPreviewReadiness['release_id'] ?? null) === $releaseId, 'readiness release binding invalid.');
    $assert(($technicalPreviewReadiness['request_id'] ?? null) === $activationPayload['request_id'], 'readiness request binding invalid.');
    $assert(($technicalPreviewReadiness['authority_id'] ?? null) === $technicalPreviewAuthority['authority_id'], 'readiness authority binding invalid.');
    $assert(($technicalPreviewReadiness['active_environment_sha256'] ?? null) === hash('sha256', $activeBeforeReadiness), 'readiness active environment binding invalid.');
    $assert(($technicalPreviewReadiness['installation_completion_sha256'] ?? null) === hash('sha256', $completionBeforeReadiness), 'readiness completion binding invalid.');
    $assert(($technicalPreviewReadiness['activation_request_sha256'] ?? null) === hash('sha256', $activationRequestBeforeReadiness), 'readiness request digest invalid.');
    $assert(($technicalPreviewReadiness['activation_authority_sha256'] ?? null) === hash('sha256', (string) file_get_contents($technicalPreviewAuthorityPath)), 'readiness authority digest invalid.');
    $assert(($technicalPreviewReadiness['authority_qualified'] ?? false) === true, 'readiness did not record qualified authority.');
    $assert(($technicalPreviewReadiness['activation_executed'] ?? true) === false, 'readiness crossed activation execution boundary.');

    $executionContract = is_array($technicalPreviewReadiness['execution_contract'] ?? null)
        ? $technicalPreviewReadiness['execution_contract']
        : [];
    foreach ([
        'target_environment_preflight_required',
        'https_required',
        'single_instance_required',
        'private_file_session_directory_required',
        'runtime_envelope_revalidation_required',
        'preview_off_switch_verification_required',
        'post_activation_health_check_required',
        'rollback_recovery_required',
        'synthetic_data_only',
        'production_data_forbidden',
        'consume_authority_on_success',
        'consume_readiness_on_success',
    ] as $requiredTrue) {
        $assert(($executionContract[$requiredTrue] ?? false) === true, 'readiness lost '.$requiredTrue.'.');
    }
    $assert(($executionContract['migration_execution_required'] ?? true) === false, 'readiness requires migration execution.');

    foreach ([
        'persistence_authorized',
        'migration_execution_authorized',
        'production_authorized',
        'updater_authorized',
        'deployment_authorized',
    ] as $flag) {
        $assert(($technicalPreviewReadiness[$flag] ?? null) === false, 'readiness crossed '.$flag.' boundary.');
    }

    foreach ([$password, $installationToken, $approvalToken, $technicalPreviewApprovalToken, 'oneqay_runtime'] as $secret) {
        $assert(! str_contains($technicalPreviewReadinessRaw, $secret), 'plaintext secret leaked into Technical Preview readiness evidence.');
    }

    $assert((string) file_get_contents($activePath) === $activeBeforeReadiness, 'authority qualification mutated active environment.');
    $assert((string) file_get_contents($completionPath) === $completionBeforeReadiness, 'authority qualification mutated completion evidence.');
    $assert((string) file_get_contents($activationRequestPath) === $activationRequestBeforeReadiness, 'authority qualification mutated activation request evidence.');

    $readyInspection = $authorityReadiness->inspect();
    $assert(($readyInspection['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_READY_NOT_EXECUTED', 'durable activation readiness was not recognized.');
    $assert(($readyInspection['execution_ready'] ?? false) === true, 'durable readiness not reported ready.');
    $assert(($readyInspection['activation_executed'] ?? true) === false, 'readiness inspection reported activation executed.');

    $replayedReadiness = $authorityReadiness->qualifyAndAttest($technicalPreviewApprovalToken);
    $assert(($replayedReadiness['qualification_fingerprint'] ?? null) === $qualified['qualification_fingerprint'], 'readiness replay changed qualification fingerprint.');
    $assert((string) file_get_contents($technicalPreviewReadinessPath) === $technicalPreviewReadinessRaw, 'readiness replay changed durable evidence.');

    $tamperedReadiness = $technicalPreviewReadiness;
    $tamperedReadiness['execution_contract']['migration_execution_required'] = true;
    $writeJson($technicalPreviewReadinessPath, $tamperedReadiness);
    $assert(($authorityReadiness->inspect()['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_READINESS_INVALID', 'tampered readiness did not fail closed.');

    if (file_put_contents($technicalPreviewReadinessPath, $technicalPreviewReadinessRaw, LOCK_EX) === false) {
        throw new RuntimeException('Unable to restore Technical Preview readiness fixture.');
    }
    @chmod($technicalPreviewReadinessPath, 0600);

    $authorityRaw = (string) file_get_contents($technicalPreviewAuthorityPath);
    @unlink($technicalPreviewReadinessPath);
    $tamperedAuthority = $technicalPreviewAuthority;
    $tamperedAuthority['active_environment_sha256'] = str_repeat('0', 64);
    $writeJson($technicalPreviewAuthorityPath, $tamperedAuthority);
    $assert(($authorityReadiness->inspect()['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY_INVALID', 'tampered authority did not fail closed.');

    if (file_put_contents($technicalPreviewAuthorityPath, $authorityRaw, LOCK_EX) === false) {
        throw new RuntimeException('Unable to restore Technical Preview authority fixture.');
    }
    @chmod($technicalPreviewAuthorityPath, 0600);

    $authorityReadiness->qualifyAndAttest($technicalPreviewApprovalToken);
    $expiredReadiness = new PrebootTechnicalPreviewActivationAuthorityReadiness($shared, $releaseId, $now + 400);
    $assert(($expiredReadiness->inspect()['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_READINESS_EXPIRED', 'expired Technical Preview authority/readiness did not fail closed.');

    $wrongAuthorityRelease = new PrebootTechnicalPreviewActivationAuthorityReadiness($shared, 'm75-preview-ffffffffffff', $now);
    $assert(($wrongAuthorityRelease->inspect()['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_NOT_READY', 'wrong release authority readiness did not fail closed.');

    $authoritySchemaPath = __DIR__.'/../../../tools/installation/technical-preview-activation-authority.schema.json';
    $authoritySchema = json_decode((string) file_get_contents($authoritySchemaPath), true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($authoritySchema), 'Technical Preview authority schema invalid.');
    $assert(($authoritySchema['properties']['scope']['const'] ?? null) === 'ENABLE_SYNTHETIC_TECHNICAL_PREVIEW_RUNTIME', 'authority schema scope invalid.');
    $assert(($authoritySchema['properties']['technical_preview_authorized']['const'] ?? null) === true, 'authority schema does not represent separate Technical Preview authority.');
    $assert(($authoritySchema['properties']['target_environment_preflight_required']['const'] ?? null) === true, 'authority schema lost target preflight requirement.');
    $assert(($authoritySchema['properties']['production_authorized']['const'] ?? null) === false, 'authority schema crossed Production boundary.');

    $readinessSchemaPath = __DIR__.'/../../../tools/installation/technical-preview-activation-readiness.schema.json';
    $readinessSchema = json_decode((string) file_get_contents($readinessSchemaPath), true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($readinessSchema), 'Technical Preview readiness schema invalid.');
    $assert(($readinessSchema['properties']['readiness_state']['const'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_READY_NOT_EXECUTED', 'readiness schema state invalid.');
    $assert(($readinessSchema['properties']['activation_executed']['const'] ?? null) === false, 'readiness schema crossed activation execution boundary.');
    $assert(($readinessSchema['properties']['execution_contract']['properties']['target_environment_preflight_required']['const'] ?? null) === true, 'readiness schema lost target preflight requirement.');
    $assert(($readinessSchema['properties']['migration_execution_authorized']['const'] ?? null) === false, 'readiness schema crossed migration boundary.');

    $schemaPath = __DIR__.'/../../../tools/installation/installation-completion-handoff.schema.json';
    $schema = json_decode((string) file_get_contents($schemaPath), true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($schema), 'completion schema invalid.');
    $assert(($schema['properties']['completion_state']['const'] ?? null) === 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED', 'schema completion state invalid.');
    $assert(($schema['properties']['installer_reentry_state']['const'] ?? null) === 'READ_ONLY_COMPLETION', 'schema re-entry state invalid.');
    $assert(($schema['properties']['technical_preview_authorized']['const'] ?? null) === false, 'schema crossed Technical Preview boundary.');
    $assert(($schema['properties']['production_authorized']['const'] ?? null) === false, 'schema crossed Production boundary.');

    $source = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Installation/PrebootInstallationCompletionHandoff.php');
    $activationSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Installation/PrebootTechnicalPreviewActivationRequest.php');
    $authorityReadinessSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Installation/PrebootTechnicalPreviewActivationAuthorityReadiness.php');
    $publicInstaller = (string) file_get_contents(__DIR__.'/../../../tools/installation/public-installer.php');

    foreach (['Artisan::call', 'requestInstall(', 'checkAvailability('] as $forbidden) {
        $assert(! str_contains($source, $forbidden), 'completion source contains forbidden primitive '.$forbidden.'.');
        $assert(! str_contains($activationSource, $forbidden), 'activation request source contains forbidden primitive '.$forbidden.'.');
        $assert(! str_contains($authorityReadinessSource, $forbidden), 'authority readiness source contains forbidden primitive '.$forbidden.'.');
        $assert(! str_contains($publicInstaller, $forbidden), 'installer contains forbidden primitive '.$forbidden.'.');
    }

    $assert(str_contains($publicInstaller, 'PrebootInstallationCompletionHandoff.php'), 'installer does not register completion handoff.');
    $assert(str_contains($publicInstaller, '$completionResult = $completionHandoff->seal();'), 'installer does not seal completion.');
    $assert(str_contains($publicInstaller, 'seal_installation_completion'), 'installer lacks completion retry action.');
    $assert(str_contains($publicInstaller, 'SEAL_INSTALLATION_COMPLETION'), 'installer lacks exact completion confirmation.');
    $assert(str_contains($publicInstaller, 'COMPLETE / NOT ACTIVATED'), 'installer lacks complete-not-activated state.');
    $assert(str_contains($publicInstaller, 'PrebootTechnicalPreviewActivationRequest.php'), 'installer does not register activation request.');
    $assert(str_contains($publicInstaller, 'create_technical_preview_activation_request'), 'installer lacks guarded activation request action.');
    $assert(str_contains($publicInstaller, 'REQUEST_TECHNICAL_PREVIEW_ACTIVATION'), 'installer lacks exact activation request confirmation.');
    $assert(str_contains($publicInstaller, 'PENDING APPROVAL / NOT AUTHORIZED'), 'installer lacks pending-not-authorized activation request state.');
    $assert(str_contains($publicInstaller, 'PrebootTechnicalPreviewActivationAuthorityReadiness.php'), 'installer does not register activation authority readiness.');
    $assert(str_contains($publicInstaller, 'qualify_technical_preview_activation_authority'), 'installer lacks authority qualification action.');
    $assert(str_contains($publicInstaller, 'QUALIFY_TECHNICAL_PREVIEW_ACTIVATION'), 'installer lacks exact authority qualification confirmation.');
    $assert(str_contains($publicInstaller, 'QUALIFIED / READY / NOT ACTIVATED'), 'installer lacks qualified-ready-not-activated state.');

    fwrite(STDOUT, "Sprint195 Technical Preview activation authority readiness regression passed.\n");
} finally {
    $remove($root);
}

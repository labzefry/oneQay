<?php

declare(strict_types=1);

// Author by Lab | zefry

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Permissions-Policy: camera=(), geolocation=(), microphone=(), payment=(), usb=()');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");
if (($_SERVER['HTTPS'] ?? '') !== '' && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
    header('Strict-Transport-Security: max-age=31536000');
}

$releaseId = '__ONEQAY_RELEASE_ID__';
$accountHome = dirname(__DIR__, 2);
$appRoot = $accountHome.'/oneqay-preview/releases/'.$releaseId.'/apps/web';
$installerSources = [
    $appRoot.'/app/Infrastructure/Installation/PrebootDatabaseCompatibilityVerification.php',
    $appRoot.'/app/Infrastructure/Installation/PrebootInstallationActivationReadiness.php',
    $appRoot.'/app/Infrastructure/Installation/PrebootRuntimeConfigurationPromotionRequest.php',
    $appRoot.'/app/Infrastructure/Installation/PrebootRuntimeConfigurationPromotionQualification.php',
    $appRoot.'/app/Infrastructure/Installation/PrebootRuntimeConfigurationPromotionReadiness.php',
    $appRoot.'/app/Infrastructure/Installation/PrebootRuntimeConfigurationPromotionExecution.php',
    $appRoot.'/app/Infrastructure/Installation/PrebootRuntimeConfigurationPostPromotionVerification.php',
    $appRoot.'/app/Infrastructure/Installation/PrebootInstallationCompletionHandoff.php',
    $appRoot.'/app/Infrastructure/Installation/PrebootInstallationConfiguration.php',
];
$sharedRoot = $accountHome.'/oneqay-preview/shared';

foreach ($installerSources as $installerSource) {
    if (! is_file($installerSource) || is_link($installerSource) || ! is_readable($installerSource)) {
        http_response_code(503);
        echo 'oneQay installer unavailable';
        exit;
    }

    require_once $installerSource;
}

$nowUnix = time();
$installer = new \App\Infrastructure\Installation\PrebootInstallationConfiguration($sharedRoot, $releaseId, $nowUnix);
$qualification = new \App\Infrastructure\Installation\PrebootRuntimeConfigurationPromotionQualification($sharedRoot, $releaseId, $nowUnix);
$readiness = new \App\Infrastructure\Installation\PrebootRuntimeConfigurationPromotionReadiness($sharedRoot, $releaseId, $nowUnix);
$execution = new \App\Infrastructure\Installation\PrebootRuntimeConfigurationPromotionExecution($sharedRoot, $releaseId, $nowUnix);
$postPromotionVerification = new \App\Infrastructure\Installation\PrebootRuntimeConfigurationPostPromotionVerification($sharedRoot, $releaseId, $nowUnix);
$completionHandoff = new \App\Infrastructure\Installation\PrebootInstallationCompletionHandoff($sharedRoot, $releaseId, $nowUnix);
$state = [
    'state' => 'UNAVAILABLE',
    'can_prepare' => false,
    'activation_handoff_ready' => false,
    'promotion_request_pending' => false,
    'activation_authorized' => false,
];
$qualificationState = [
    'state' => 'PROMOTION_REQUEST_NOT_READY',
    'authority_present' => false,
    'token_required' => false,
    'promotion_qualified' => false,
    'promotion_executed' => false,
];
$readinessState = [
    'state' => 'PROMOTION_READINESS_MISSING',
    'execution_ready' => false,
    'request_id' => '',
    'authority_id' => '',
    'promotion_executed' => false,
];
$result = null;
$qualificationResult = null;
$readinessResult = null;
$executionResult = null;
$postPromotionVerificationResult = null;
$postPromotionVerificationState = [
    'state' => 'POST_PROMOTION_VERIFICATION_NOT_READY',
    'verified' => false,
    'request_id' => '',
    'authority_id' => '',
    'active_environment_sha256' => '',
    'activation_authorized' => false,
];
$completionResult = null;
$completionState = [
    'state' => 'INSTALLATION_COMPLETION_NOT_READY',
    'complete' => false,
    'request_id' => '',
    'authority_id' => '',
    'active_environment_sha256' => '',
    'activation_authorized' => false,
];
$errorCode = null;

try {
    $state = $installer->inspect();
    if (($state['promotion_request_pending'] ?? false) === true) {
        $qualificationState = $qualification->inspect();
        $readinessState = $readiness->inspect();
    }
    if (($state['state'] ?? null) === 'ACTIVE_ENV_PRESENT') {
        $postPromotionVerificationState = $postPromotionVerification->inspect();
        $completionState = $completionHandoff->inspect();
    }

    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if (! in_array($method, ['GET', 'POST'], true)) {
        http_response_code(405);
        header('Allow: GET, POST');
        throw new RuntimeException('unsupported_request_method');
    }

    if ($method === 'POST') {
        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        if ($contentLength <= 0 || $contentLength > 8192) {
            throw new RuntimeException('invalid_request_size');
        }

        $action = (string) ($_POST['action'] ?? 'prepare_configuration');

        if ($action === 'prepare_configuration') {
            $result = $installer->prepare($_POST);
            $state = $installer->inspect();
            if (($state['promotion_request_pending'] ?? false) === true) {
                $qualificationState = $qualification->inspect();
            }
        } elseif ($action === 'qualify_promotion_authority') {
            $approvalToken = $_POST['approval_token'] ?? null;
            if (! is_string($approvalToken)) {
                throw new RuntimeException('promotion_authority_denied');
            }

            $qualificationResult = $qualification->qualify($approvalToken);
            $readinessResult = $readiness->attest($approvalToken);
            $qualificationState = $qualification->inspect();
            $readinessState = $readiness->inspect();
        } elseif ($action === 'execute_runtime_promotion') {
            $approvalToken = $_POST['approval_token'] ?? null;
            $confirmation = $_POST['promotion_confirmation'] ?? null;

            if (! is_string($approvalToken) || ! is_string($confirmation)) {
                throw new RuntimeException('promotion_execution_denied');
            }

            if (! hash_equals('PROMOTE_RUNTIME_CONFIGURATION', $confirmation)) {
                throw new RuntimeException('promotion_execution_confirmation_invalid');
            }

            $executionResult = $execution->execute($approvalToken);
            $state = $installer->inspect();
            $postPromotionVerificationResult = $postPromotionVerification->verify();
            $postPromotionVerificationState = $postPromotionVerification->inspect();
            $completionResult = $completionHandoff->seal();
            $completionState = $completionHandoff->inspect();
        } elseif ($action === 'verify_promoted_runtime_configuration') {
            $confirmation = $_POST['verification_confirmation'] ?? null;
            if (! is_string($confirmation)
                || ! hash_equals('VERIFY_RUNTIME_CONFIGURATION', $confirmation)) {
                throw new RuntimeException('post_promotion_verification_confirmation_invalid');
            }

            $postPromotionVerificationResult = $postPromotionVerification->verify();
            $state = $installer->inspect();
            $postPromotionVerificationState = $postPromotionVerification->inspect();
            $completionResult = $completionHandoff->seal();
            $completionState = $completionHandoff->inspect();
        } elseif ($action === 'seal_installation_completion') {
            $confirmation = $_POST['completion_confirmation'] ?? null;
            if (! is_string($confirmation)
                || ! hash_equals('SEAL_INSTALLATION_COMPLETION', $confirmation)) {
                throw new RuntimeException('installation_completion_confirmation_invalid');
            }

            $completionResult = $completionHandoff->seal();
            $state = $installer->inspect();
            $postPromotionVerificationState = $postPromotionVerification->inspect();
            $completionState = $completionHandoff->inspect();
        } else {
            throw new RuntimeException('unsupported_installation_action');
        }
    }
} catch (\RuntimeException $exception) {
    $errorCode = $exception->getMessage();
    if (! preg_match('/\A[a-z0-9_]{3,80}\z/', $errorCode)) {
        $errorCode = 'installation_request_denied';
    }
} catch (\Throwable) {
    $errorCode = 'installation_request_denied';
}

$stateCode = (string) ($state['state'] ?? 'UNAVAILABLE');
$canPrepare = ($state['can_prepare'] ?? false) === true;
$handoffReady = ($state['activation_handoff_ready'] ?? false) === true;
$promotionRequestPending = ($state['promotion_request_pending'] ?? false) === true;
$promotionAuthorityState = (string) ($qualificationState['state'] ?? 'PROMOTION_REQUEST_NOT_READY');
$promotionAuthorityPresent = ($qualificationState['authority_present'] ?? false) === true;
$promotionAuthorityTokenRequired = ($qualificationState['token_required'] ?? false) === true;
$promotionQualified = is_array($qualificationResult)
    && ($qualificationResult['promotion_qualified'] ?? false) === true
    && ($qualificationResult['promotion_executed'] ?? true) === false;
$promotionExecutionReady = ($readinessState['execution_ready'] ?? false) === true
    || (is_array($readinessResult)
        && ($readinessResult['state'] ?? null) === 'PROMOTION_EXECUTION_READY_NOT_EXECUTED'
        && ($readinessResult['promotion_executed'] ?? true) === false);
$promotionReadinessState = (string) ($readinessState['state'] ?? 'PROMOTION_READINESS_MISSING');
$runtimePromoted = is_array($executionResult)
    && ($executionResult['state'] ?? null) === 'RUNTIME_CONFIGURATION_PROMOTED_NOT_ACTIVATED'
    && ($executionResult['technical_preview_authorized'] ?? true) === false
    && ($executionResult['production_authorized'] ?? true) === false;
$runtimeConfigured = $runtimePromoted || $stateCode === 'ACTIVE_ENV_PRESENT';
$postPromotionVerificationCode = (string) ($postPromotionVerificationState['state'] ?? 'POST_PROMOTION_VERIFICATION_NOT_READY');
$runtimeVerified = ($postPromotionVerificationState['verified'] ?? false) === true
    || (is_array($postPromotionVerificationResult)
        && ($postPromotionVerificationResult['state'] ?? null) === 'RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED'
        && ($postPromotionVerificationResult['activation_authorized'] ?? true) === false);
$completionCode = (string) ($completionState['state'] ?? 'INSTALLATION_COMPLETION_NOT_READY');
$installationComplete = ($completionState['complete'] ?? false) === true
    || (is_array($completionResult)
        && ($completionResult['state'] ?? null) === 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED'
        && ($completionResult['activation_authorized'] ?? true) === false);
$prepared = is_array($result) && ($result['prepared'] ?? false) === true;

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function stateTitle(string $state): string
{
    return match ($state) {
        'READY_FOR_CONFIGURATION' => 'Ready to prepare configuration',
        'PENDING_CONFIGURATION_VERIFIED' => 'Configuration verified and prepared',
        'PENDING_CONFIGURATION_PRESENT' => 'Configuration prepared',
        'ACTIVE_ENV_PRESENT' => 'Runtime already configured',
        'AUTHORITY_MISSING' => 'Installation authority required',
        'AUTHORITY_EXPIRED' => 'Installation authority expired',
        'AUTHORITY_INVALID' => 'Installation authority invalid',
        default => 'Installer unavailable',
    };
}

function stateDescription(string $state): string
{
    return match ($state) {
        'READY_FOR_CONFIGURATION' => 'The private one-time installation authority is valid. Submit the secure runtime configuration below.',
        'PENDING_CONFIGURATION_VERIFIED' => 'Database compatibility is verified, the pending configuration is sealed to this exact governed release, and a promotion request is waiting for separate approval. Runtime promotion remains locked.',
        'PENDING_CONFIGURATION_PRESENT' => 'A private .env.pending file exists without Sprint185 database verification evidence. Activation remains locked.',
        'ACTIVE_ENV_PRESENT' => 'An active shared runtime environment already exists. This pre-boot installer will not overwrite it.',
        'AUTHORITY_MISSING' => 'Place a valid private authority.json in the shared install boundary before submitting configuration.',
        'AUTHORITY_EXPIRED' => 'The private installation authority is no longer valid. Provision a new one-time authority.',
        'AUTHORITY_INVALID' => 'The private installation authority does not match the governed release contract.',
        default => 'The installer failed closed. Review the private server-side installation boundary.',
    };
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>oneQay Installation</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --ink: #172033;
            --muted: #687386;
            --line: #dfe5ee;
            --panel: #ffffff;
            --soft: #f5f7fb;
            --accent: #1f4c8f;
            --success: #1d6f42;
            --warning: #8a5a00;
            --danger: #9b2c2c;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(180deg, #f7f9fc 0%, #eef2f8 100%);
            color: var(--ink);
        }
        .shell { width: min(1080px, calc(100% - 32px)); margin: 0 auto; padding: 32px 0 56px; }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 28px; }
        .brand { font-weight: 800; letter-spacing: -0.03em; font-size: 24px; }
        .brand small { display: block; margin-top: 4px; font-size: 12px; font-weight: 600; color: var(--muted); letter-spacing: .08em; text-transform: uppercase; }
        .chip { border: 1px solid var(--line); background: var(--panel); border-radius: 999px; padding: 8px 12px; font-size: 12px; font-weight: 700; color: var(--muted); }
        .hero { background: var(--panel); border: 1px solid var(--line); border-radius: 22px; padding: 28px; box-shadow: 0 14px 40px rgba(28, 43, 69, .06); }
        .eyebrow { margin: 0 0 8px; color: var(--accent); font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        h1 { margin: 0; font-size: clamp(28px, 4vw, 42px); letter-spacing: -0.04em; }
        .lead { margin: 12px 0 0; max-width: 780px; color: var(--muted); line-height: 1.65; }
        .status { margin-top: 22px; display: grid; grid-template-columns: 1fr auto; gap: 20px; align-items: center; padding: 18px; border: 1px solid var(--line); border-radius: 16px; background: var(--soft); }
        .status strong { display: block; font-size: 17px; }
        .status p { margin: 6px 0 0; color: var(--muted); line-height: 1.5; font-size: 14px; }
        .status-code { font: 700 12px ui-monospace, SFMono-Regular, Menlo, monospace; color: var(--accent); white-space: nowrap; }
        .notice { margin-top: 18px; padding: 14px 16px; border-radius: 14px; font-size: 14px; line-height: 1.55; }
        .notice.success { background: #edf8f1; color: var(--success); border: 1px solid #ccebd7; }
        .notice.warning { background: #fff8e6; color: var(--warning); border: 1px solid #ead9a6; }
        .notice.error { background: #fff1f1; color: var(--danger); border: 1px solid #f0cccc; }
        .grid { margin-top: 22px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .field { display: grid; gap: 7px; }
        .field.full { grid-column: 1 / -1; }
        label { font-size: 13px; font-weight: 750; color: #344055; }
        input {
            width: 100%;
            border: 1px solid #cfd7e4;
            border-radius: 12px;
            padding: 12px 13px;
            font: inherit;
            background: #fff;
            color: var(--ink);
            outline: none;
        }
        input:focus { border-color: #7b9bc9; box-shadow: 0 0 0 3px rgba(31, 76, 143, .10); }
        .help { margin: 0; font-size: 12px; color: var(--muted); line-height: 1.45; }
        .actions { margin-top: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; padding-top: 20px; border-top: 1px solid var(--line); }
        .actions p { margin: 0; color: var(--muted); font-size: 13px; max-width: 650px; line-height: 1.5; }
        button { border: 0; border-radius: 12px; background: var(--accent); color: white; padding: 12px 18px; font: inherit; font-weight: 800; cursor: pointer; }
        .boundary { margin-top: 18px; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .boundary div { background: var(--soft); border: 1px solid var(--line); border-radius: 14px; padding: 14px; }
        .boundary span { display: block; color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: .06em; font-weight: 800; }
        .boundary strong { display: block; margin-top: 5px; font-size: 13px; }
        footer { margin-top: 18px; text-align: center; color: var(--muted); font-size: 12px; }
        @media (max-width: 760px) {
            .grid, .boundary { grid-template-columns: 1fr; }
            .status { grid-template-columns: 1fr; }
            .actions { align-items: stretch; flex-direction: column; }
            button { width: 100%; }
        }
    </style>
</head>
<body>
<main class="shell">
    <header class="topbar">
        <div class="brand">oneQay<small>Secure pre-boot installation</small></div>
        <span class="chip"><?= e($releaseId) ?></span>
    </header>

    <section class="hero">
        <p class="eyebrow">Controlled installation preparation</p>
        <h1>Prepare runtime configuration</h1>
        <p class="lead">
            This pre-boot surface verifies database connectivity and compatibility before preparing the private shared runtime configuration for the exact governed release.
            It does not activate Technical Preview, run migrations, deploy a release, or grant Production authority.
        </p>

        <div class="status">
            <div>
                <strong><?= e(stateTitle($stateCode)) ?></strong>
                <p><?= e(stateDescription($stateCode)) ?></p>
            </div>
            <span class="status-code"><?= e($stateCode) ?></span>
        </div>

        <?php if ($installationComplete): ?>
            <div class="notice success">
                Installation configuration is <strong>COMPLETE / NOT ACTIVATED</strong>. The completion handoff is private and exact-bound to the verified active runtime configuration, promotion execution receipt, and post-promotion verification evidence. Application activation remains separately governed.
            </div>
        <?php elseif ($runtimeVerified): ?>
            <div class="notice success">
                Runtime configuration is <strong>VERIFIED / NOT ACTIVATED</strong>. Active <strong>.env</strong> is exact-bound to the private promotion execution receipt and the governed release. Migration, Technical Preview, Production, deployment, and updater authority remain unchanged.
            </div>
        <?php elseif ($errorCode !== null): ?>
            <div class="notice error">
                Request denied safely. Code: <strong><?= e($errorCode) ?></strong>
            </div>
        <?php elseif ($runtimePromoted): ?>
            <div class="notice warning">
                Runtime configuration is <strong>PROMOTED / NOT ACTIVATED</strong>, but post-promotion verification is not yet complete. Application activation remains locked.
            </div>
        <?php elseif ($promotionExecutionReady): ?>
            <div class="notice success">
                Promotion is <strong>EXECUTION READY / NOT EXECUTED</strong>. Durable private readiness evidence is sealed to the exact release, request, authority, pending configuration, and handoff. Active <strong>.env</strong> remains unchanged.
            </div>
        <?php elseif ($promotionQualified): ?>
            <div class="notice success">
                Promotion authority is <strong>QUALIFIED / NOT EXECUTED</strong>. Exact request, release, pending digest, handoff digest, authority lifetime, and one-time approval token all match. Active <strong>.env</strong> remains unchanged.
            </div>
        <?php elseif ($prepared): ?>
            <div class="notice success">
                Configuration was verified, sealed to the exact governed release, and a private promotion request was created for separate approval. Runtime promotion remains locked.
            </div>
        <?php elseif ($promotionRequestPending && $stateCode === 'PENDING_CONFIGURATION_VERIFIED'): ?>
            <div class="notice success">
                Runtime configuration promotion request is <strong>PENDING APPROVAL</strong>. It is bound to the exact release and sealed pending configuration; no promotion authority has been granted.
            </div>
        <?php elseif ($handoffReady && $stateCode === 'PENDING_CONFIGURATION_VERIFIED'): ?>
            <div class="notice success">
                Activation-readiness handoff is sealed and tamper-evident. A separate operational authority is still required before any runtime promotion.
            </div>
        <?php endif; ?>

        <?php if ($canPrepare): ?>
            <form method="post" autocomplete="off">
                <input type="hidden" name="action" value="prepare_configuration">
                <div class="grid">
                    <div class="field full">
                        <label for="app_url">Application HTTPS URL</label>
                        <input id="app_url" name="app_url" type="url" required maxlength="255" placeholder="https://preview.example.com" autocomplete="off">
                        <p class="help">HTTPS only. Query strings, embedded credentials, and fragments are rejected.</p>
                    </div>

                    <div class="field">
                        <label for="db_host">Database host</label>
                        <input id="db_host" name="db_host" type="text" required maxlength="255" placeholder="127.0.0.1" autocomplete="off">
                    </div>

                    <div class="field">
                        <label for="db_port">Database port</label>
                        <input id="db_port" name="db_port" type="number" min="1" max="65535" required value="3306" autocomplete="off">
                    </div>

                    <div class="field">
                        <label for="db_database">Database name</label>
                        <input id="db_database" name="db_database" type="text" required maxlength="64" autocomplete="off">
                    </div>

                    <div class="field">
                        <label for="db_username">Database username</label>
                        <input id="db_username" name="db_username" type="text" required maxlength="128" autocomplete="off">
                    </div>

                    <div class="field full">
                        <label for="db_password">Database password</label>
                        <input id="db_password" name="db_password" type="password" required maxlength="1024" autocomplete="new-password">
                        <p class="help">The password is written only to the private pending environment file and is never echoed back.</p>
                    </div>

                    <div class="field full">
                        <label for="installation_token">One-time installation token</label>
                        <input id="installation_token" name="installation_token" type="password" required minlength="32" maxlength="256" autocomplete="one-time-code">
                        <p class="help">Must match the SHA-256 authority stored in the private install boundary for this exact release.</p>
                    </div>
                </div>

                <div class="actions">
                    <p>
                        Submission first performs a read-only database compatibility verification, then creates <strong>.env.pending</strong> only when verification succeeds.
                        No active <strong>.env</strong> is created by this step.
                    </p>
                    <button type="submit">Verify &amp; prepare configuration</button>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($promotionRequestPending && $promotionAuthorityTokenRequired && ! $promotionExecutionReady): ?>
            <form method="post" autocomplete="off">
                <input type="hidden" name="action" value="qualify_promotion_authority">
                <div class="grid">
                    <div class="field full">
                        <label for="approval_token">Promotion approval token</label>
                        <input id="approval_token" name="approval_token" type="password" required minlength="32" maxlength="256" autocomplete="one-time-code">
                        <p class="help">Out-of-band one-time token bound to the private promotion authority for this exact release and request. Qualification does not execute promotion.</p>
                    </div>
                </div>
                <div class="actions">
                    <p>
                        This step verifies authority and seals durable execution-readiness evidence only. It does not copy <strong>.env.pending</strong> to <strong>.env</strong>, execute migrations, or enable Technical Preview.
                    </p>
                    <button type="submit">Qualify promotion authority</button>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($promotionExecutionReady && ! $runtimePromoted): ?>
            <div class="notice warning">
                This is the final configuration-promotion step only. It creates the active runtime <strong>.env</strong> from the exact verified pending bytes, but it does <strong>not</strong> run migrations or activate Technical Preview/Production.
            </div>
            <form method="post" autocomplete="off">
                <input type="hidden" name="action" value="execute_runtime_promotion">
                <div class="grid">
                    <div class="field full">
                        <label for="execution_approval_token">Promotion approval token</label>
                        <input id="execution_approval_token" name="approval_token" type="password" required minlength="32" maxlength="256" autocomplete="one-time-code">
                        <p class="help">Re-enter the out-of-band token. The executor re-validates exact authority and readiness immediately before promotion.</p>
                    </div>
                    <div class="field full">
                        <label for="promotion_confirmation">Explicit confirmation</label>
                        <input id="promotion_confirmation" name="promotion_confirmation" type="text" required maxlength="64" autocomplete="off" placeholder="PROMOTE_RUNTIME_CONFIGURATION">
                        <p class="help">Type exactly <strong>PROMOTE_RUNTIME_CONFIGURATION</strong>. This prevents accidental promotion from a stale or unintended operator session.</p>
                    </div>
                </div>
                <div class="actions">
                    <p>
                        Promotion preserves verified configuration bytes exactly and writes a private execution receipt. Application activation remains separately governed.
                    </p>
                    <button type="submit">Promote runtime configuration</button>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($runtimeConfigured && ! $runtimeVerified && $postPromotionVerificationCode === 'RUNTIME_CONFIGURATION_VERIFICATION_REQUIRED'): ?>
            <div class="notice warning">
                Active runtime configuration exists, but the exact post-promotion verification evidence is missing. Activation remains locked until the active configuration and execution receipt are re-verified.
            </div>
            <form method="post" autocomplete="off">
                <input type="hidden" name="action" value="verify_promoted_runtime_configuration">
                <div class="grid">
                    <div class="field full">
                        <label for="verification_confirmation">Verification confirmation</label>
                        <input id="verification_confirmation" name="verification_confirmation" type="text" required maxlength="64" autocomplete="off" placeholder="VERIFY_RUNTIME_CONFIGURATION">
                        <p class="help">Type exactly <strong>VERIFY_RUNTIME_CONFIGURATION</strong>. This verifies evidence only and does not activate the application.</p>
                    </div>
                </div>
                <div class="actions">
                    <p>
                        Verification checks the private execution receipt, exact active configuration SHA-256, release binding, pending-file absence, and all fail-closed runtime flags.
                    </p>
                    <button type="submit">Verify promoted runtime configuration</button>
                </div>
            </form>
        <?php elseif ($runtimeConfigured && ! $runtimeVerified && $postPromotionVerificationCode === 'RUNTIME_CONFIGURATION_VERIFICATION_INVALID'): ?>
            <div class="notice error">
                Post-promotion verification is <strong>INVALID</strong>. The installer has failed closed; application activation remains unavailable until the private runtime evidence is repaired through a governed recovery process.
            </div>
        <?php endif; ?>

        <?php if ($runtimeVerified && ! $installationComplete && $completionCode === 'INSTALLATION_COMPLETION_READY_TO_SEAL'): ?>
            <div class="notice warning">
                Runtime configuration verification succeeded, but installation completion evidence is not yet sealed. Application activation remains locked.
            </div>
            <form method="post" autocomplete="off">
                <input type="hidden" name="action" value="seal_installation_completion">
                <div class="grid">
                    <div class="field full">
                        <label for="completion_confirmation">Completion confirmation</label>
                        <input id="completion_confirmation" name="completion_confirmation" type="text" required maxlength="64" autocomplete="off" placeholder="SEAL_INSTALLATION_COMPLETION">
                        <p class="help">Type exactly <strong>SEAL_INSTALLATION_COMPLETION</strong>. This seals evidence only and does not activate the application.</p>
                    </div>
                </div>
                <div class="actions">
                    <p>
                        Completion binds the active configuration, execution receipt, and post-promotion verification into a private read-only installation handoff.
                    </p>
                    <button type="submit">Seal installation completion</button>
                </div>
            </form>
        <?php elseif ($runtimeConfigured && $completionCode === 'INSTALLATION_COMPLETION_INVALID'): ?>
            <div class="notice error">
                Installation completion evidence is <strong>INVALID</strong>. The installer remains fail-closed and application activation is unavailable.
            </div>
        <?php endif; ?>

        <?php if ($promotionRequestPending && ! $promotionAuthorityTokenRequired && ! $promotionQualified && ! $runtimeConfigured): ?>
            <div class="notice">
                Promotion authority status: <strong><?= e($promotionAuthorityState) ?></strong>. A separately provisioned exact-bound authority is required before qualification.
            </div>
        <?php endif; ?>

        <div class="boundary" aria-label="Operational safety boundaries">
            <div><span>Activation handoff</span><strong><?= $handoffReady ? 'SEALED / NOT AUTHORIZED' : 'NOT READY' ?></strong></div>
            <div><span>Promotion request</span><strong><?= $promotionRequestPending ? 'PENDING APPROVAL' : 'NOT READY' ?></strong></div>
            <div><span>Promotion authority</span><strong><?=
                $runtimePromoted
                    ? 'CONSUMED / RECEIPT-BOUND'
                    : ($promotionExecutionReady
                        ? 'QUALIFIED / ATTESTED'
                        : ($promotionQualified
                            ? 'QUALIFIED / NOT EXECUTED'
                            : ($promotionAuthorityTokenRequired
                                ? 'TOKEN REQUIRED'
                                : ($promotionAuthorityPresent ? 'PRESENT / INVALID' : 'NOT GRANTED'))))
            ?></strong></div>
            <div><span>Execution readiness</span><strong><?=
                $runtimePromoted
                    ? 'CONSUMED / PROMOTED'
                    : ($promotionExecutionReady ? 'READY / NOT EXECUTED' : e($promotionReadinessState))
            ?></strong></div>
            <div><span>Runtime configuration</span><strong><?= $runtimeConfigured ? 'ACTIVE / NOT ACTIVATED' : 'NOT ACTIVE' ?></strong></div>
            <div><span>Post-promotion verification</span><strong><?= $runtimeVerified ? 'VERIFIED / NOT ACTIVATED' : e($postPromotionVerificationCode) ?></strong></div>
            <div><span>Installation completion</span><strong><?= $installationComplete ? 'COMPLETE / NOT ACTIVATED' : e($completionCode) ?></strong></div>
            <div><span>Migration</span><strong>NOT EXECUTED</strong></div>
            <div><span>Technical Preview</span><strong>NOT AUTHORIZED</strong></div>
            <div><span>Production</span><strong>NOT AUTHORIZED</strong></div>
        </div>
    </section>

    <footer>Author by Lab | zefry</footer>
</main>
</body>
</html>

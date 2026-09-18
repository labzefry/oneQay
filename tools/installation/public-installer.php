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

$installer = new \App\Infrastructure\Installation\PrebootInstallationConfiguration($sharedRoot, $releaseId, time());
$state = [
    'state' => 'UNAVAILABLE',
    'can_prepare' => false,
    'activation_handoff_ready' => false,
    'activation_authorized' => false,
];
$result = null;
$errorCode = null;

try {
    $state = $installer->inspect();

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

        $result = $installer->prepare($_POST);
        $state = $installer->inspect();
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
        'PENDING_CONFIGURATION_VERIFIED' => 'Database compatibility is verified and the pending runtime configuration is sealed to this exact governed release. Activation remains separately authorized and locked.',
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
        .boundary { margin-top: 18px; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
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

        <?php if ($prepared): ?>
            <div class="notice success">
                Configuration was verified, sealed to the exact governed release, and written to the private pending runtime boundary. Activation remains locked.
            </div>
        <?php elseif ($handoffReady && $stateCode === 'PENDING_CONFIGURATION_VERIFIED'): ?>
            <div class="notice success">
                Activation-readiness handoff is sealed and tamper-evident. A separate operational authority is still required before any runtime activation.
            </div>
        <?php elseif ($errorCode !== null): ?>
            <div class="notice error">
                Request denied safely. Code: <strong><?= e($errorCode) ?></strong>
            </div>
        <?php endif; ?>

        <?php if ($canPrepare): ?>
            <form method="post" autocomplete="off">
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

        <div class="boundary" aria-label="Operational safety boundaries">
            <div><span>Activation handoff</span><strong><?= $handoffReady ? 'SEALED / NOT AUTHORIZED' : 'NOT READY' ?></strong></div>
            <div><span>Migration</span><strong>NOT EXECUTED</strong></div>
            <div><span>Technical Preview</span><strong>NOT AUTHORIZED</strong></div>
            <div><span>Production</span><strong>NOT AUTHORIZED</strong></div>
        </div>
    </section>

    <footer>Author by Lab | zefry</footer>
</main>
</body>
</html>

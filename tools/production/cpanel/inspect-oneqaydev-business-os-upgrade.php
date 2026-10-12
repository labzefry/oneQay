<?php

declare(strict_types=1);

// oneQay host source-binding inspection, read-only and fail closed.
// Author by Lab | zefry. Does NOT deploy, read private credentials or call HTTP.
final class OneQayDevBusinessOsHostPreflight
{
    public const CURRENT_SOURCE = 'e93b7fef42cb36840b7fca6b79435c6188a13cd5';
    public const TARGET_SOURCE = 'd15f2fc3f9aa9320670657d03b044e132bf812a2';
    private const CURRENT_RELEASE = 'production-e93b7fef42cb';
    private const TARGET_RELEASE = 'production-d15f2fc3f9aa';
    private const REPORT_RELATIVE = 'oneqaydev-language-update-e93b7fef42cb/update-result.json';
    private const ENTRY = 'oneqay-business-e93b7fef42cb-ui.php';

    public function __construct(private readonly string $privateRoot, private readonly string $publicRoot) {}

    /** @return array<string, mixed> */
    public function inspect(): array
    {
        $checks = [
            'private_root_directory' => $this->realDirectory($this->privateRoot),
            'public_root_directory' => $this->realDirectory($this->publicRoot),
        ];
        $report = null;
        $meta = null;
        if ($checks['private_root_directory']) {
            $report = $this->readObject($this->privateRoot.'/'.self::REPORT_RELATIVE, 32768);
            $meta = $this->readObject($this->privateRoot.'/releases/'.self::CURRENT_RELEASE.'/RELEASE.json', 32768);
        }

        $checks['host_report_present'] = $report !== null;
        $checks['previous_host_update_report'] = $report !== null
            && ($report['state'] ?? null) === 'BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING'
            && ($report['source_commit'] ?? null) === self::CURRENT_SOURCE
            && ($report['release_id'] ?? null) === self::CURRENT_RELEASE
            && ($report['business_traffic'] ?? null) === 'ON_PRESERVED';
        $checks['previous_release_metadata'] = $meta !== null
            && ($meta['source_commit'] ?? null) === self::CURRENT_SOURCE
            && ($meta['release_id'] ?? null) === self::CURRENT_RELEASE;

        $entry = $this->publicRoot.'/'.self::ENTRY;
        $routing = $this->publicRoot.'/.htaccess';
        $checks['public_entry_integrity'] = $report !== null
            && $this->hashMatches($entry, $report['bridge_sha256'] ?? null, 250000);
        $checks['public_routing_integrity'] = $report !== null
            && $this->hashMatches($routing, $report['routing_sha256'] ?? null, 65536);

        $routingText = $this->readFile($routing, 65536);
        $checks['routing_targets_current_release'] = $routingText !== null
            && str_contains($routingText, self::ENTRY);
        $checks['candidate_release_not_preexisting'] = ! file_exists(
            $this->privateRoot.'/releases/'.self::TARGET_RELEASE
        ) && ! is_link($this->privateRoot.'/releases/'.self::TARGET_RELEASE);

        // Do not trust a report to prove access control, financial accuracy, live
        // HTTPS, a successful merchant login or successful transactions.
        $eligible = ! in_array(false, $checks, true);
        return [
            'schema' => 'oneqay.business-os-host-readonly-preflight.v1',
            'attribution' => 'Lab | zefry',
            'target' => 'oneqaydev.n07.my.id',
            'previous_source_commit' => self::CURRENT_SOURCE,
            'candidate_source_commit' => self::TARGET_SOURCE,
            'state' => $eligible ? 'HOST_FILE_BINDINGS_CONSISTENT_PREDEPLOYMENT'
                : 'STOPPED_FAIL_CLOSED_HOST_STATE_UNQUALIFIED',
            'checks' => $checks,
            'database_mutated' => false,
            'merchant_mutated' => false,
            'production_traffic_mutated' => false,
            'staging_mutated' => false,
            'deployment_executed' => false,
            'live_https_verified' => false,
            'business_acceptance_verified' => false,
            'next_action' => $eligible
                ? 'BUILD_AND_QUALIFY_BACKEND_CAPABLE_EXACT_SOURCE_UPDATE_OPERATOR'
                : 'RECONCILE_HOST_WITH_OWNER_SUPPLIED_SANITIZED_EVIDENCE',
        ];
    }

    private function realDirectory(string $path): bool
    {
        return ! is_link($path) && is_dir($path) && realpath($path) === $path;
    }

    private function readFile(string $path, int $maxBytes): ?string
    {
        if (! is_file($path) || is_link($path) || ! is_readable($path)) {
            return null;
        }
        $size = @filesize($path);
        if (! is_int($size) || $size < 1 || $size > $maxBytes) {
            return null;
        }
        $value = @file_get_contents($path);
        return is_string($value) && strlen($value) === $size ? $value : null;
    }

    /** @return array<string, mixed>|null */
    private function readObject(string $path, int $maxBytes): ?array
    {
        $raw = $this->readFile($path, $maxBytes);
        if ($raw === null) {
            return null;
        }
        try {
            $value = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
            return is_array($value) && ! array_is_list($value) ? $value : null;
        } catch (JsonException) {
            return null;
        }
    }

    private function hashMatches(string $path, mixed $expected, int $maxBytes): bool
    {
        if (! is_string($expected) || preg_match('/\A[a-f0-9]{64}\z/D', $expected) !== 1) {
            return false;
        }
        $content = $this->readFile($path, $maxBytes);
        return $content !== null && hash_equals($expected, hash('sha256', $content));
    }
}

if (PHP_SAPI === 'cli' && realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    if ($argc !== 1) {
        fwrite(STDERR, "USAGE: php inspect-oneqaydev-business-os-upgrade.php\n");
        exit(64);
    }
    $probe = new OneQayDevBusinessOsHostPreflight(
        '/home/pekd7254/oneqay-production-test',
        '/home/pekd7254/public_html/oneqaydev.n07.my.id',
    );
    $result = $probe->inspect();
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
    exit($result['state'] === 'HOST_FILE_BINDINGS_CONSISTENT_PREDEPLOYMENT' ? 0 : 2);
}

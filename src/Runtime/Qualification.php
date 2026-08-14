<?php

declare(strict_types=1);

// Author by Lab | zefry

namespace OneQay\Runtime\Qualification;

enum EvidenceStatus: string
{
    case VERIFIED = 'VERIFIED';
    case PARTIAL = 'PARTIAL';
    case UNVERIFIED = 'UNVERIFIED';
    case NOT_SUPPLIED = 'NOT_SUPPLIED';
    case UNAVAILABLE = 'UNAVAILABLE';
}

enum QualificationOutcome: string
{
    case EVIDENCE_COMPLETE = 'EVIDENCE_COMPLETE';
    case BLOCKED = 'BLOCKED';
}

enum RelationalEngineFamily: string
{
    case MARIADB = 'MARIADB';
    case MYSQL = 'MYSQL';
    case POSTGRESQL = 'POSTGRESQL';
}

final class RuntimeQualificationCatalog
{
    /** @var list<string> */
    public const RUNTIME_REQUIREMENTS = [
        'PHP_RUNTIME',
        'PHP_CLI',
        'WEB_SERVER_REQUEST_RUNTIME',
        'SAFE_DOCUMENT_ROOT',
        'URL_REWRITE',
        'BACKGROUND_EXECUTION',
        'QUEUE_EXECUTION',
        'SCHEDULER_CRON',
        'FILESYSTEM_STORAGE',
        'ENVIRONMENT_SECRETS',
        'TLS_HTTPS',
        'DATABASE_CONNECTIVITY',
        'BACKUP_RESTORE',
        'OBSERVABILITY_LOGGING',
        'RESOURCE_LIMITS',
        'DEPLOYMENT_RECOVERY',
        'ROLLBACK',
        'SECURITY_BOUNDARY',
        'PREVIEW_ISOLATION',
        'OUTBOUND_DNS_HTTPS',
    ];

    /** @var list<string> */
    public const ENGINE_PROFILE_REQUIREMENTS = [
        'APPLICATION_CONNECTIVITY',
        'LEAST_PRIVILEGE',
        'CONNECTION_LIMIT_VISIBILITY',
        'TRANSACTION_SEMANTICS',
        'TENANT_ISOLATION',
        'BACKUP_EXPORT',
        'RESTORE_VERIFIED',
        'MIGRATION_BOUNDARY',
        'PORTABILITY_CONTRACT',
    ];

    public static function runtimeRequirementExists(string $identifier): bool
    {
        return in_array($identifier, self::RUNTIME_REQUIREMENTS, true);
    }

    public static function engineRequirementExists(string $identifier): bool
    {
        return in_array($identifier, self::ENGINE_PROFILE_REQUIREMENTS, true);
    }
}

final readonly class EvidenceItem
{
    public function __construct(
        public EvidenceStatus $status,
        public string $reference = '',
    ) {
        if ($this->reference !== '' && preg_match('/^[A-Za-z0-9._:\/#@+\-]{1,200}$/', $this->reference) !== 1) {
            throw new \InvalidArgumentException('Evidence reference must be a sanitized repository or evidence identifier.');
        }

        if ($this->status === EvidenceStatus::VERIFIED && $this->reference === '') {
            throw new \InvalidArgumentException('Verified evidence requires a sanitized evidence reference.');
        }
    }
}

final readonly class SanitizedRuntimeEvidence
{
    /**
     * @param array<string, EvidenceItem> $capabilities
     * @param array<string, EvidenceItem> $engineChecks
     */
    public function __construct(
        public string $targetId,
        public string $observedAt,
        public RelationalEngineFamily $engineFamily,
        public string $engineVersion,
        public array $capabilities,
        public array $engineChecks,
    ) {
        if (preg_match('/^[a-z0-9][a-z0-9._-]{2,63}$/', $this->targetId) !== 1) {
            throw new \InvalidArgumentException('Target identifier must be sanitized and non-secret.');
        }

        try {
            new \DateTimeImmutable($this->observedAt);
        } catch (\Throwable $exception) {
            throw new \InvalidArgumentException('Observed timestamp is invalid.', 0, $exception);
        }

        if (preg_match('/^[0-9A-Za-z][0-9A-Za-z._+\-]{0,31}$/', $this->engineVersion) !== 1) {
            throw new \InvalidArgumentException('Engine version is invalid.');
        }

        foreach ($this->capabilities as $identifier => $item) {
            if (!RuntimeQualificationCatalog::runtimeRequirementExists($identifier) || !$item instanceof EvidenceItem) {
                throw new \InvalidArgumentException('Unknown runtime capability evidence.');
            }
        }

        foreach ($this->engineChecks as $identifier => $item) {
            if (!RuntimeQualificationCatalog::engineRequirementExists($identifier) || !$item instanceof EvidenceItem) {
                throw new \InvalidArgumentException('Unknown engine-profile evidence.');
            }
        }
    }
}

final class SanitizedRuntimeEvidenceParser
{
    /** @param array<string, mixed> $payload */
    public function parse(array $payload): SanitizedRuntimeEvidence
    {
        $allowed = ['target_id', 'observed_at', 'engine', 'capabilities', 'engine_checks'];
        $extra = array_diff(array_keys($payload), $allowed);
        if ($extra !== []) {
            throw new \InvalidArgumentException('Unexpected evidence fields are prohibited.');
        }

        foreach ($allowed as $required) {
            if (!array_key_exists($required, $payload)) {
                throw new \InvalidArgumentException(sprintf('Missing required evidence field: %s.', $required));
            }
        }

        if (!is_array($payload['engine']) || array_diff(array_keys($payload['engine']), ['family', 'version']) !== []) {
            throw new \InvalidArgumentException('Engine evidence must contain only family and version.');
        }

        if (!isset($payload['engine']['family'], $payload['engine']['version'])
            || !is_string($payload['engine']['family'])
            || !is_string($payload['engine']['version'])) {
            throw new \InvalidArgumentException('Engine evidence is incomplete.');
        }

        if (!is_string($payload['target_id']) || !is_string($payload['observed_at'])
            || !is_array($payload['capabilities']) || !is_array($payload['engine_checks'])) {
            throw new \InvalidArgumentException('Evidence payload types are invalid.');
        }

        $family = RelationalEngineFamily::tryFrom($payload['engine']['family']);
        if ($family === null) {
            throw new \InvalidArgumentException('Relational engine family is not authorized by DEC-005R.');
        }

        return new SanitizedRuntimeEvidence(
            $payload['target_id'],
            $payload['observed_at'],
            $family,
            $payload['engine']['version'],
            $this->parseItems($payload['capabilities'], true),
            $this->parseItems($payload['engine_checks'], false),
        );
    }

    /**
     * @param array<string, mixed> $items
     * @return array<string, EvidenceItem>
     */
    private function parseItems(array $items, bool $runtime): array
    {
        $parsed = [];
        foreach ($items as $identifier => $item) {
            if (!is_string($identifier) || !is_array($item)
                || array_diff(array_keys($item), ['status', 'reference']) !== []) {
                throw new \InvalidArgumentException('Evidence item schema is invalid.');
            }

            if ($runtime ? !RuntimeQualificationCatalog::runtimeRequirementExists($identifier)
                : !RuntimeQualificationCatalog::engineRequirementExists($identifier)) {
                throw new \InvalidArgumentException(sprintf('Unknown evidence identifier: %s.', $identifier));
            }

            if (!isset($item['status']) || !is_string($item['status'])) {
                throw new \InvalidArgumentException('Evidence item status is required.');
            }

            $status = EvidenceStatus::tryFrom($item['status']);
            if ($status === null) {
                throw new \InvalidArgumentException('Evidence item status is invalid.');
            }

            $reference = $item['reference'] ?? '';
            if (!is_string($reference)) {
                throw new \InvalidArgumentException('Evidence reference must be a string.');
            }

            $parsed[$identifier] = new EvidenceItem($status, $reference);
        }

        ksort($parsed);
        return $parsed;
    }
}

final readonly class RuntimeQualificationReport
{
    /**
     * @param list<string> $verified
     * @param list<string> $blocking
     */
    public function __construct(
        public QualificationOutcome $outcome,
        public string $targetId,
        public string $observedAt,
        public RelationalEngineFamily $engineFamily,
        public string $engineVersion,
        public array $verified,
        public array $blocking,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'schema' => 'oneqay.m7_5.runtime-qualification.v1',
            'outcome' => $this->outcome->value,
            'target_id' => $this->targetId,
            'observed_at' => $this->observedAt,
            'engine_profile' => [
                'family' => $this->engineFamily->value,
                'version' => $this->engineVersion,
            ],
            'verified' => $this->verified,
            'blocking' => $this->blocking,
            'lifecycle_authority_created' => false,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
}

final class RuntimeQualificationEvaluator
{
    public function evaluate(SanitizedRuntimeEvidence $evidence): RuntimeQualificationReport
    {
        $verified = [];
        $blocking = [];

        foreach (RuntimeQualificationCatalog::RUNTIME_REQUIREMENTS as $identifier) {
            $item = $evidence->capabilities[$identifier] ?? null;
            if ($item?->status === EvidenceStatus::VERIFIED) {
                $verified[] = 'RUNTIME:' . $identifier;
            } else {
                $blocking[] = 'RUNTIME:' . $identifier . ':' . ($item?->status->value ?? EvidenceStatus::NOT_SUPPLIED->value);
            }
        }

        foreach (RuntimeQualificationCatalog::ENGINE_PROFILE_REQUIREMENTS as $identifier) {
            $item = $evidence->engineChecks[$identifier] ?? null;
            if ($item?->status === EvidenceStatus::VERIFIED) {
                $verified[] = 'ENGINE:' . $identifier;
            } else {
                $blocking[] = 'ENGINE:' . $identifier . ':' . ($item?->status->value ?? EvidenceStatus::NOT_SUPPLIED->value);
            }
        }

        sort($verified);
        sort($blocking);

        return new RuntimeQualificationReport(
            $blocking === [] ? QualificationOutcome::EVIDENCE_COMPLETE : QualificationOutcome::BLOCKED,
            $evidence->targetId,
            $evidence->observedAt,
            $evidence->engineFamily,
            $evidence->engineVersion,
            $verified,
            $blocking,
        );
    }
}

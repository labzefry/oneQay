<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
final readonly class SystemUpdateReleaseIdentity
{
    public function __construct(
        private string $releaseId,
        private string $sourceCommit,
        private string $artifactSha256,
    ) {
        if (preg_match('/\Am75-preview-[0-9a-f]{12}\z/', $releaseId) !== 1) {
            throw new SystemUpdateControlPlaneViolation('invalid_release_identity');
        }

        if (preg_match('/\A[0-9a-f]{40}\z/', $sourceCommit) !== 1) {
            throw new SystemUpdateControlPlaneViolation('invalid_release_identity');
        }

        if (! hash_equals(substr($sourceCommit, 0, 12), substr($releaseId, -12))) {
            throw new SystemUpdateControlPlaneViolation('release_source_mismatch');
        }

        if (preg_match('/\A[0-9a-f]{64}\z/', $artifactSha256) !== 1) {
            throw new SystemUpdateControlPlaneViolation('invalid_artifact_digest');
        }
    }

    public function releaseId(): string
    {
        return $this->releaseId;
    }

    public function sourceCommit(): string
    {
        return $this->sourceCommit;
    }

    public function artifactSha256(): string
    {
        return $this->artifactSha256;
    }

    /** @return array{release_id:string,source_commit:string,artifact_sha256:string} */
    public function toSafeArray(): array
    {
        return [
            'release_id' => $this->releaseId,
            'source_commit' => $this->sourceCommit,
            'artifact_sha256' => $this->artifactSha256,
        ];
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->releaseId, $other->releaseId)
            && hash_equals($this->sourceCommit, $other->sourceCommit)
            && hash_equals($this->artifactSha256, $other->artifactSha256);
    }
}

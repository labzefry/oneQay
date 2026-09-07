<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class FinalShiftCloseRuntimeBindingManifestMaterializationRequest
{
    public function __construct(
        public string $operationId,
        public string $expectedSelectionFingerprintSha256,
    ) {
        if (preg_match('/\A[a-z0-9][a-z0-9._:-]{15,127}\z/D', $operationId) !== 1) {
            throw new InvalidArgumentException('Runtime binding materialization operation ID is invalid.');
        }

        if (preg_match('/\A[0-9a-f]{64}\z/D', $expectedSelectionFingerprintSha256) !== 1) {
            throw new InvalidArgumentException('Runtime binding expected selection fingerprint is invalid.');
        }
    }
}

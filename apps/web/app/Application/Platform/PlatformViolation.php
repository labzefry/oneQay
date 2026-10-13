<?php
declare(strict_types=1);
namespace App\Application\Platform;
// Author by Lab | zefry
final class PlatformViolation extends \RuntimeException
{
    public function __construct(public readonly string $reason, public readonly int $status = 422) { parent::__construct($reason); }
}

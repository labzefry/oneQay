<?php
// Author by Lab | zefry
declare(strict_types=1);
namespace App\Infrastructure\Pos;
final class DesktopSyncViolation extends \InvalidArgumentException {
 public function __construct(public readonly string $safeCode){parent::__construct($safeCode);}
}

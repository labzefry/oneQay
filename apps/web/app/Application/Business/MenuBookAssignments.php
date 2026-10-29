<?php
// Author by Lab | zefry. Domain-facing menu assignment boundary.
declare(strict_types=1);
namespace App\Application\Business;
interface MenuBookAssignments {
 public function installed():bool;
 public function categories():array;
 public function assign(string $operation,array $input,string $correlation):array;
}

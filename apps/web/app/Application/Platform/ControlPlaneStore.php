<?php
declare(strict_types=1);
namespace App\Application\Platform;
// Author by Lab | zefry
interface ControlPlaneStore {
 public function overview():array;
 public function detail(array $business):array;
 public function preview(string $id):array;
 public function execute(string $action,array $input,?array $business,string $actor):array;
}

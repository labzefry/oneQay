<?php

namespace App\Application\Persistence;

// Author by Lab | zefry
interface PersistenceTransaction
{
    public function run(callable $operation): mixed;
}

<?php
// Author by Lab | zefry. Isolated database; no runtime boot or production configuration.
declare(strict_types=1);
require __DIR__.'/../vendor/autoload.php';
use App\Application\Identity\InitialPasswordEnrollmentViolation as Enrollment;
use App\Application\Persistence\DurablePersistenceViolation as Persistence;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;
use Illuminate\Database\SQLiteConnection;

try {
    $db = new SQLiteConnection(new PDO('sqlite::memory:'));
    $db->statement('CREATE TABLE rollback_probe (id INTEGER PRIMARY KEY, label TEXT NOT NULL)');
    $tx = new LaravelPersistenceTransaction($db, true, 'ci');
    $n = 0;
    $assert = static function (bool $ok) use (&$n): void {
        if (!$ok) throw new RuntimeException('ENROLLMENT_TRANSACTION_ASSERTION_FAILED');
        ++$n;
    };
    foreach ([Enrollment::ACTIVE_ENROLLMENT_EXISTS, Enrollment::ENROLLMENT_CONFLICT, Enrollment::AUTHORIZATION_DENIED, Enrollment::INVALID_ENROLLMENT] as $code) {
        $expected = new Enrollment($code, 'Synthetic enrollment rejection');
        try {
            $tx->run(static function () use ($db, $expected): void {
                $db->table('rollback_probe')->insert(['id'=>1, 'label'=>'must-roll-back']);
                throw $expected;
            });
            $assert(false);
        } catch (Enrollment $actual) {
            $assert($actual === $expected);
        }
        $assert($db->table('rollback_probe')->count() === 0);
        $assert($db->transactionLevel() === 0);
    }
    try {
        $tx->run(static function () use ($db): void {
            $db->table('rollback_probe')->insert(['id'=>1, 'label'=>'must-roll-back']);
            throw new RuntimeException('synthetic-private-storage-detail');
        });
        $assert(false);
    } catch (Persistence $actual) {
        $assert($actual->errorCode === Persistence::TRANSACTION_FAILURE);
        $assert(!str_contains($actual->getMessage(), 'synthetic-private-storage-detail'));
        $assert($actual->getPrevious() === null);
    }
    $assert($db->table('rollback_probe')->count() === 0);
    foreach ([[false,'ci',Persistence::PERSISTENCE_DISABLED], [true,'unknown',Persistence::RUNTIME_DENIED]] as [$enabled,$runtime,$code]) {
        $called = false;
        try {
            (new LaravelPersistenceTransaction($db, $enabled, $runtime))->run(static function () use (&$called): void {$called=true;});
            $assert(false);
        } catch (Persistence $actual) {
            $assert($actual->errorCode === $code);
        }
        $assert(!$called);
    }
    $assert($tx->run(static function () use ($db): string {
        $db->table('rollback_probe')->insert(['id'=>2, 'label'=>'committed']);
        return 'ok';
    }) === 'ok');
    $assert($db->table('rollback_probe')->count() === 1);
    echo "ENROLLMENT_TRANSACTION_PASS assertions=$n\n";
} catch (Throwable $error) {
    fwrite(STDERR, "ENROLLMENT_TRANSACTION_FAILED\n");
    exit(1);
}

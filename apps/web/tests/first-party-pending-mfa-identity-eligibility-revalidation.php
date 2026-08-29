<?php

declare(strict_types=1);

use App\Application\Identity\FirstPartyIdentityEligibilityVerifier;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
use App\Delivery\Http\Identity\PrivilegedTotpMfaController;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app['config']->set('oneqay.session_control.enabled', true);
$app['config']->set('oneqay.session_control.idle_ttl_seconds', 7200);

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint45 pending-MFA eligibility revalidation regression failed: '.$message);
    }
};

$source = file_get_contents(__DIR__.'/../app/Delivery/Http/Identity/PrivilegedTotpMfaController.php');
$assert(is_string($source), 'controller source unavailable');
$assert(str_contains($source, 'FirstPartyIdentityEligibilityVerifier'), 'eligibility verifier is not composed');
$assert(substr_count($source, '$this->pendingContext($request,') === 3, 'all three pending MFA advancement operations must use the guarded pending context');
$assert(str_contains($source, '! $this->identityEligibility->isEligible($tenantId, $identityId)'), 'exact pending tenant+identity eligibility guard missing');
$assert(str_contains($source, '$session->invalidate();') && str_contains($source, '$session->regenerateToken();'), 'ineligible pending state is not destroyed safely');
$assert(strpos($source, '! $this->identityEligibility->isEligible($tenantId, $identityId)') < strpos($source, '$this->enterOrganizationalContext->enter('), 'eligibility must be checked before organizational-context advancement');

$eligibility = new class implements FirstPartyIdentityEligibilityVerifier {
    public bool $eligible = false;
    public int $calls = 0;

    public function isEligible(TenantId $tenantId, PlatformIdentityId $identityId): bool
    {
        ++$this->calls;
        if ($tenantId->value() !== 'tenant-alpha' || $identityId->value() !== 'admin-alpha') {
            return false;
        }
        return $this->eligible;
    }
};

$reflection = new ReflectionClass(PrivilegedTotpMfaController::class);
/** @var PrivilegedTotpMfaController $controller */
$controller = $reflection->newInstanceWithoutConstructor();
$eligibilityProperty = $reflection->getProperty('identityEligibility');
$eligibilityProperty->setValue($controller, $eligibility);
$pendingContext = $reflection->getMethod('pendingContext');

$makePendingRequest = static function (string $state): Request {
    $request = Request::create('/auth/mfa/totp/test', 'POST');
    $session = new Store('oneqay-s45-'.bin2hex(random_bytes(4)), new ArraySessionHandler(120));
    $session->start();
    $session->put(FirstPartySessionKeys::PENDING_IDENTITY, 'admin-alpha');
    $session->put(FirstPartySessionKeys::PENDING_TENANT, 'tenant-alpha');
    $session->put(FirstPartySessionKeys::PENDING_ORGANIZATION, 'organization-alpha');
    $session->put(FirstPartySessionKeys::PENDING_MFA_STATE, $state);
    $request->setLaravelSession($session);
    return $request;
};

foreach ([FirstPartySessionKeys::MFA_ENROLLMENT_REQUIRED, FirstPartySessionKeys::MFA_CHALLENGE_REQUIRED] as $state) {
    $request = $makePendingRequest($state);
    $oldToken = $request->session()->token();
    try {
        $pendingContext->invoke($controller, $request, $state);
        $assert(false, 'ineligible pending state was accepted');
    } catch (InvalidArgumentException $exception) {
        $assert(str_contains($exception->getMessage(), 'not currently eligible'), 'ineligible state failed with unexpected disposition');
    }

    foreach (FirstPartySessionKeys::pending() as $key) {
        $assert($request->session()->get($key) === null, 'ineligible pending state retained '.$key);
    }
    foreach (FirstPartySessionKeys::all() as $key) {
        $assert($request->session()->get($key) === null, 'ineligible pending state created full authority '.$key);
    }
    $assert($request->session()->get(FirstPartySessionKeys::SESSION_AUTHORITY_ID) === null, 'ineligible pending state created session authority');
    $assert(! hash_equals($oldToken, $request->session()->token()), 'ineligible pending state did not regenerate CSRF token');

    // Reactivation cannot resurrect the invalidated pending flow. Fresh primary authentication is required.
    $eligibility->eligible = true;
    $callsBeforeRetry = $eligibility->calls;
    try {
        $pendingContext->invoke($controller, $request, $state);
        $assert(false, 'reactivation resumed an invalidated pending flow');
    } catch (InvalidArgumentException) {
        // Expected: pending state was destroyed before reactivation.
    }
    $assert($eligibility->calls === $callsBeforeRetry, 'stale invalidated flow reached eligibility verification after reactivation');
    $eligibility->eligible = false;
}

$assert($eligibility->calls === 2, 'eligibility verifier call count changed');

$migrations = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($migrations);
$expected = [];
for ($index = 1; $index <= 15; ++$index) {
    $prefix = sprintf('0000_00_00_%06d_', $index);
    $matches = array_values(array_filter($migrations, static fn (string $file): bool => str_starts_with($file, $prefix)));
    $assert(count($matches) === 1, 'migration #'.$index.' must exist exactly once');
    $expected[] = $matches[0];
}
$assert($migrations === $expected, 'canonical migrations must remain exactly #1-#15');
$assert(! array_filter($migrations, static fn (string $file): bool => str_contains($file, '000016')), 'migration #16 must not exist');

echo "Sprint45 pending-MFA identity eligibility revalidation regression passed.\n";

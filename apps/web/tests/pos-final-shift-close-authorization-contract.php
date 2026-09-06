<?php

declare(strict_types=1);

use App\Application\Authorization\PosPermission;
use App\Application\Pos\FinalShiftCloseAuthorizationPolicy;
use App\Application\Pos\PosTransactionViolation;

// Author by Lab | zefry
require __DIR__.'/../vendor/autoload.php';

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException($message);
    }
};

$expectViolation = static function (callable $operation, string $message) use ($assert): void {
    try {
        $operation();
    } catch (PosTransactionViolation) {
        return;
    }

    $assert(false, $message);
};

$assert(PosPermission::SHIFT_CLOSE === 'pos.shift.close', 'Sprint94 dedicated Final Shift Close permission identifier changed.');
$assert(PosPermission::closeShift()->value() === 'pos.shift.close', 'Sprint94 Final Shift Close permission factory changed.');

$policy = new FinalShiftCloseAuthorizationPolicy();
$closer = 'identity-closer-001';
$opener = 'identity-opener-001';
$explanationAuthor = 'identity-explainer-001';
$reviewer = 'identity-reviewer-001';

$policy->requireAuthorizedActors($closer, $opener, false);
$policy->requireAuthorizedActors($closer, $opener, true, $explanationAuthor, $reviewer);

$expectViolation(
    static fn () => $policy->requireAuthorizedActors($closer, $closer, false),
    'Sprint94 must deny closer equal to opener.',
);
$expectViolation(
    static fn () => $policy->requireAuthorizedActors($closer, $opener, true),
    'Sprint94 must deny nonzero variance without explanation/review actors.',
);
$expectViolation(
    static fn () => $policy->requireAuthorizedActors($closer, $opener, true, $closer, $reviewer),
    'Sprint94 must deny closer equal to variance explanation author.',
);
$expectViolation(
    static fn () => $policy->requireAuthorizedActors($closer, $opener, true, $explanationAuthor, $closer),
    'Sprint94 must deny closer equal to variance reviewer.',
);
$expectViolation(
    static fn () => $policy->requireAuthorizedActors('bad', $opener, false),
    'Sprint94 must fail closed on malformed closer identity.',
);
$expectViolation(
    static fn () => $policy->requireAuthorizedActors($closer, 'bad', false),
    'Sprint94 must fail closed on malformed opener identity.',
);
$expectViolation(
    static fn () => $policy->requireAuthorizedActors($closer, $opener, true, 'bad', $reviewer),
    'Sprint94 must fail closed on malformed explanation-author identity.',
);
$expectViolation(
    static fn () => $policy->requireAuthorizedActors($closer, $opener, true, $explanationAuthor, 'bad'),
    'Sprint94 must fail closed on malformed reviewer identity.',
);

fwrite(STDOUT, "Sprint94 Final Shift Close authorization contract regression passed.\n");

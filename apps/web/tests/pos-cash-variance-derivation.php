<?php

declare(strict_types=1);

use App\Application\Pos\CashVarianceResult;
use App\Application\Pos\DeriveCashVariance;
use App\Application\Pos\ExpectedCashResult;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ShiftClosingCashResult;
use App\Domain\Pos\Money;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Sprint64 cash variance regression failed: '.$case);
    }
};

$expectViolation = static function (callable $call, string $case) use ($assert): void {
    try {
        $call();
        $assert(false, $case.' accepted');
    } catch (PosTransactionViolation) {
    }
};

$make = static function (
    array $expectedOverride = [],
    array $closingOverride = [],
): array {
    $expected = array_merge([
        'tenant' => 'tenant-alpha',
        'organization' => 'organization-alpha',
        'outlet' => 'outlet-alpha',
        'shift' => 'shift-alpha',
        'opening' => 'opening-alpha',
        'closing' => 'closing-alpha',
        'cutoff' => 200,
        'atomic' => 1000,
        'currency' => 'IDR',
        'scale' => 0,
    ], $expectedOverride);

    $closing = array_merge([
        'tenant' => 'tenant-alpha',
        'outlet' => 'outlet-alpha',
        'shift' => 'shift-alpha',
        'opening' => 'opening-alpha',
        'evidence' => 'closing-alpha',
        'recorded' => 200,
        'atomic' => 1000,
        'currency' => 'IDR',
        'scale' => 0,
    ], $closingOverride);

    return [
        new ExpectedCashResult(
            $expected['tenant'],
            $expected['organization'],
            $expected['outlet'],
            $expected['shift'],
            $expected['opening'],
            $expected['closing'],
            $expected['cutoff'],
            Money::fromAtomicUnits($expected['atomic'], $expected['currency'], $expected['scale']),
        ),
        new ShiftClosingCashResult(
            $closing['evidence'],
            $closing['opening'],
            $closing['shift'],
            'closing-operation-alpha',
            $closing['tenant'],
            $closing['outlet'],
            'device-alpha',
            Money::fromAtomicUnits($closing['atomic'], $closing['currency'], $closing['scale']),
            'OPERATOR_OBSERVED_CLOSING_CASH',
            'correlation-closing-alpha',
            $closing['recorded'],
        ),
    ];
};

$derive = new DeriveCashVariance();
$method = new ReflectionMethod(DeriveCashVariance::class, 'derive');
$params = $method->getParameters();
$assert(count($params) === 2, 'derive parameter count');
$assert(($params[0]->getType()?->__toString() ?? '') === ExpectedCashResult::class, 'expected input type');
$assert(($params[1]->getType()?->__toString() ?? '') === ShiftClosingCashResult::class, 'closing input type');
$assert(($method->getReturnType()?->__toString() ?? '') === CashVarianceResult::class, 'result type');
$assert((new ReflectionClass(CashVarianceResult::class))->isReadOnly(), 'result is not readonly');

[$expected, $closing] = $make();
$match = $derive->derive($expected, $closing);
$assert($match->tenantId() === 'tenant-alpha', 'tenant output');
$assert($match->organizationId() === 'organization-alpha', 'organization output');
$assert($match->outletId() === 'outlet-alpha', 'outlet output');
$assert($match->shiftId() === 'shift-alpha', 'shift output');
$assert($match->openingCashEvidenceId() === 'opening-alpha', 'opening evidence output');
$assert($match->closingCashEvidenceId() === 'closing-alpha', 'closing evidence output');
$assert($match->cutoffAtUnix() === 200, 'cutoff output');
$assert($match->expectedCashAtomic() === 1000, 'expected atomic output');
$assert($match->observedClosingAtomic() === 1000, 'observed atomic output');
$assert($match->varianceAtomic() === 0, 'match variance');
$assert($match->direction() === CashVarianceResult::DIRECTION_MATCH, 'match direction');
$assert($match->currency() === 'IDR' && $match->currencyScale() === 0, 'money identity output');

[$expectedOver, $closingOver] = $make([], ['atomic' => 1250]);
$over = $derive->derive($expectedOver, $closingOver);
$assert($over->varianceAtomic() === 250, 'over variance');
$assert($over->direction() === CashVarianceResult::DIRECTION_OVER, 'over direction');

[$expectedShort, $closingShort] = $make([], ['atomic' => 750]);
$short = $derive->derive($expectedShort, $closingShort);
$assert($short->varianceAtomic() === -250, 'short variance');
$assert($short->direction() === CashVarianceResult::DIRECTION_SHORT, 'short direction');

[$expectedMaxOver, $closingMaxOver] = $make(['atomic' => 0], ['atomic' => PHP_INT_MAX]);
$maxOver = $derive->derive($expectedMaxOver, $closingMaxOver);
$assert($maxOver->varianceAtomic() === PHP_INT_MAX, 'positive integer boundary');
$assert($maxOver->direction() === CashVarianceResult::DIRECTION_OVER, 'positive boundary direction');

[$expectedMaxShort, $closingMaxShort] = $make(['atomic' => PHP_INT_MAX], ['atomic' => 0]);
$maxShort = $derive->derive($expectedMaxShort, $closingMaxShort);
$assert($maxShort->varianceAtomic() === -PHP_INT_MAX, 'negative integer boundary');
$assert($maxShort->direction() === CashVarianceResult::DIRECTION_SHORT, 'negative boundary direction');

foreach ([
    'tenant' => [['tenant' => 'tenant-beta'], []],
    'outlet' => [['outlet' => 'outlet-beta'], []],
    'shift' => [['shift' => 'shift-beta'], []],
    'opening evidence' => [['opening' => 'opening-beta'], []],
    'closing evidence' => [['closing' => 'closing-beta'], []],
    'cutoff' => [['cutoff' => 201], []],
] as $case => [$expectedOverride, $closingOverride]) {
    [$badExpected, $badClosing] = $make($expectedOverride, $closingOverride);
    $expectViolation(
        static fn () => $derive->derive($badExpected, $badClosing),
        $case.' mismatch',
    );
}

[$currencyExpected, $currencyClosing] = $make([], ['currency' => 'USD']);
$expectViolation(
    static fn () => $derive->derive($currencyExpected, $currencyClosing),
    'currency mismatch',
);

[$scaleExpected, $scaleClosing] = $make([], ['scale' => 2]);
$expectViolation(
    static fn () => $derive->derive($scaleExpected, $scaleClosing),
    'scale mismatch',
);

foreach ([
    'tenant' => ['tenant' => ''],
    'organization' => ['organization' => ''],
    'outlet' => ['outlet' => ''],
    'shift' => ['shift' => ''],
    'opening evidence' => ['opening' => ''],
    'closing evidence' => ['closing' => ''],
] as $case => $override) {
    $closingOverride = [];
    if ($case === 'tenant') {
        $closingOverride['tenant'] = '';
    } elseif ($case === 'outlet') {
        $closingOverride['outlet'] = '';
    } elseif ($case === 'shift') {
        $closingOverride['shift'] = '';
    } elseif ($case === 'opening evidence') {
        $closingOverride['opening'] = '';
    } elseif ($case === 'closing evidence') {
        $closingOverride['evidence'] = '';
    }

    [$badExpected, $badClosing] = $make($override, $closingOverride);
    $expectViolation(
        static fn () => $derive->derive($badExpected, $badClosing),
        'blank '.$case,
    );
}

[$zeroTimeExpected, $zeroTimeClosing] = $make(['cutoff' => 0], ['recorded' => 0]);
$expectViolation(
    static fn () => $derive->derive($zeroTimeExpected, $zeroTimeClosing),
    'non-positive cutoff',
);

$repeat = $derive->derive($expected, $closing);
$assert(
    [
        $repeat->tenantId(),
        $repeat->organizationId(),
        $repeat->outletId(),
        $repeat->shiftId(),
        $repeat->openingCashEvidenceId(),
        $repeat->closingCashEvidenceId(),
        $repeat->cutoffAtUnix(),
        $repeat->expectedCashAtomic(),
        $repeat->observedClosingAtomic(),
        $repeat->varianceAtomic(),
        $repeat->direction(),
        $repeat->currency(),
        $repeat->currencyScale(),
    ] === [
        $match->tenantId(),
        $match->organizationId(),
        $match->outletId(),
        $match->shiftId(),
        $match->openingCashEvidenceId(),
        $match->closingCashEvidenceId(),
        $match->cutoffAtUnix(),
        $match->expectedCashAtomic(),
        $match->observedClosingAtomic(),
        $match->varianceAtomic(),
        $match->direction(),
        $match->currency(),
        $match->currencyScale(),
    ],
    'deterministic repeat',
);

$assert($expected->expectedCash()->atomicUnits() === 1000, 'expected input mutated');
$assert($closing->closingCash()->atomicUnits() === 1000, 'closing input mutated');
$assert($closing->shiftId() === 'shift-alpha', 'closing shift mutated');

foreach (['tolerance', 'explanation', 'approval', 'close', 'transition'] as $forbiddenMethod) {
    $assert(! method_exists(CashVarianceResult::class, $forbiddenMethod), 'forbidden result method '.$forbiddenMethod);
}

$source = (string) file_get_contents(__DIR__.'/../app/Application/Pos/DeriveCashVariance.php');
foreach (['Illuminate\\', 'Repository', 'DB::', '->table(', '->insert(', '->update(', '->delete('] as $forbiddenSource) {
    $assert(! str_contains($source, $forbiddenSource), 'database/infrastructure token '.$forbiddenSource);
}

echo "Sprint64 JRN-010 cash variance derivation regression passed.\n";

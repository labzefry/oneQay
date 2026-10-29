<?php
// Author by Lab | zefry. Pure integer arithmetic; integration and persistence are separate.
declare(strict_types=1);
namespace App\Application\BusinessLoyalty;
use InvalidArgumentException;

final class LoyaltyMath
{
    private const MAX_AMOUNT = 1000000000000;
    private const MAX_POINTS = 1000000000;
    private static function bounded(int $value, int $max): void
    {
        if ($value < 0 || $value > $max) throw new InvalidArgumentException('LOYALTY_VALUE_OUT_OF_RANGE');
    }
    /** Percentage is basis points (10000 = 100%); fractional atomic units round down. */
    public static function percentageDiscount(int $subtotal, int $basisPoints, int $cap): int
    {
        self::bounded($subtotal, self::MAX_AMOUNT);
        self::bounded($basisPoints, 10000);
        self::bounded($cap, self::MAX_AMOUNT);
        return min($cap, intdiv($subtotal * $basisPoints, 10000));
    }
    /** Whole points earned per completed spending unit; the remainder carries no points. */
    public static function earnedPoints(int $eligibleAmount, int $spendUnit, int $pointsPerUnit): int
    {
        self::bounded($eligibleAmount, self::MAX_AMOUNT);
        self::bounded($spendUnit, self::MAX_AMOUNT);
        self::bounded($pointsPerUnit, self::MAX_POINTS);
        if ($spendUnit === 0) throw new InvalidArgumentException('LOYALTY_SPEND_UNIT_REQUIRED');
        $units = intdiv($eligibleAmount, $spendUnit);
        if ($pointsPerUnit !== 0 && $units > intdiv(self::MAX_POINTS, $pointsPerUnit)) throw new InvalidArgumentException('LOYALTY_POINTS_OUT_OF_RANGE');
        return $units * $pointsPerUnit;
    }
    /**
     * Percentage earning: one point per atomicPerPoint atomic currency units of
     * the percentage-eligible amount. Rounds down once, after multiplication.
     */
    public static function earnedPercentagePoints(int $eligibleAmount, int $basisPoints, int $atomicPerPoint): int
    {
        self::bounded($eligibleAmount, self::MAX_AMOUNT);
        self::bounded($basisPoints, 10000);
        self::bounded($atomicPerPoint, self::MAX_AMOUNT);
        if ($atomicPerPoint === 0) throw new InvalidArgumentException('LOYALTY_POINT_VALUE_REQUIRED');
        $points = intdiv($eligibleAmount * $basisPoints, 10000 * $atomicPerPoint);
        if ($points > self::MAX_POINTS) throw new InvalidArgumentException('LOYALTY_POINTS_OUT_OF_RANGE');
        return $points;
    }
    /** A redemption must be fully affordable and cannot exceed the outstanding price. */
    public static function redeemDiscount(int $points, int $balance, int $pointsPerUnit, int $atomicPerUnit, int $outstanding): int
    {
        self::bounded($points, self::MAX_POINTS);
        self::bounded($balance, self::MAX_POINTS);
        self::bounded($pointsPerUnit, self::MAX_POINTS);
        self::bounded($atomicPerUnit, self::MAX_AMOUNT);
        self::bounded($outstanding, self::MAX_AMOUNT);
        if ($points === 0 || $pointsPerUnit === 0 || $atomicPerUnit === 0 || $points > $balance || $points % $pointsPerUnit !== 0) throw new InvalidArgumentException('LOYALTY_REDEMPTION_INVALID');
        $units = intdiv($points, $pointsPerUnit);
        if ($units > intdiv($outstanding, $atomicPerUnit)) throw new InvalidArgumentException('LOYALTY_REDEMPTION_EXCEEDS_TOTAL');
        return $units * $atomicPerUnit;
    }
}

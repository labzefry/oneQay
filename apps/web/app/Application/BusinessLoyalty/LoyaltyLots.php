<?php
// Author by Lab | zefry. Pure policy; callers must lock and persist the resulting allocation atomically.
declare(strict_types=1);
namespace App\Application\BusinessLoyalty;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final class LoyaltyLots
{
    private const MAX_POINTS = 1000000000;
    private const MAX_TIMESTAMP = 253402300799;

    public static function expiresAt(int $earnedAt, int $validDays): int
    {
        if ($earnedAt < 1 || $validDays < 1 || $validDays > 36500 || $earnedAt > self::MAX_TIMESTAMP - $validDays * 86400) {
            throw new InvalidArgumentException('LOYALTY_EXPIRY_INVALID');
        }
        return $earnedAt + $validDays * 86400;
    }

    /** @return list<array{id:string,earned:int,remaining:int,earned_at:int,expires_at:int}> */
    private static function validate(array $lots, string $tenant, string $member, int $now): array
    {
        if ($tenant === '' || $member === '' || $now < 1 || $now > self::MAX_TIMESTAMP || !array_is_list($lots) || count($lots) > 10000) {
            throw new InvalidArgumentException('LOYALTY_LOTS_INVALID');
        }
        $seen = []; $result = []; $total = 0;
        foreach ($lots as $lot) {
            if (!is_array($lot) || ($lot['tenant_id'] ?? null) !== $tenant || ($lot['member_id'] ?? null) !== $member) {
                throw new InvalidArgumentException('LOYALTY_LOT_SCOPE_MISMATCH');
            }
            $id = $lot['id'] ?? null;
            if (!is_string($id) || $id === '' || strlen($id) > 128 || isset($seen[$id])) {
                throw new InvalidArgumentException('LOYALTY_LOT_ID_INVALID');
            }
            foreach (['earned','remaining','earned_at','expires_at'] as $field) {
                if (!isset($lot[$field]) || !is_int($lot[$field])) throw new InvalidArgumentException('LOYALTY_LOT_VALUE_INVALID');
            }
            if ($lot['earned'] < 0 || $lot['earned'] > self::MAX_POINTS || $lot['remaining'] < 0 || $lot['remaining'] > $lot['earned'] || $lot['earned_at'] < 1 || $lot['earned_at'] > $now || $lot['expires_at'] <= $lot['earned_at'] || $lot['expires_at'] > self::MAX_TIMESTAMP) {
                throw new InvalidArgumentException('LOYALTY_LOT_VALUE_INVALID');
            }
            $total += $lot['earned'];
            if ($total > self::MAX_POINTS) throw new InvalidArgumentException('LOYALTY_POINTS_OUT_OF_RANGE');
            $seen[$id] = true;
            $result[] = ['id'=>$id,'earned'=>$lot['earned'],'remaining'=>$lot['remaining'],'earned_at'=>$lot['earned_at'],'expires_at'=>$lot['expires_at']];
        }
        return $result;
    }

    /** Earliest expiry first. No mutation, transfers, pooling, or promotion stacking. */
    public static function allocate(array $lots, string $tenant, string $member, int $now, int $points, bool $hasPromotion): array
    {
        $valid = self::validate($lots, $tenant, $member, $now);
        if ($hasPromotion) throw new InvalidArgumentException('LOYALTY_PROMOTION_COMBINATION_DENIED');
        if ($points < 1 || $points > self::MAX_POINTS) throw new InvalidArgumentException('LOYALTY_REDEMPTION_INVALID');
        usort($valid, static fn(array $a,array $b): int => ($a['expires_at'] <=> $b['expires_at']) ?: strcmp($a['id'],$b['id']));
        $needed = $points; $allocation = [];
        foreach ($valid as $lot) {
            if ($lot['expires_at'] <= $now || $lot['remaining'] === 0) continue;
            $take = min($needed, $lot['remaining']);
            $allocation[] = ['lot_id'=>$lot['id'],'points'=>$take];
            $needed -= $take;
            if ($needed === 0) return $allocation;
        }
        throw new InvalidArgumentException('LOYALTY_INSUFFICIENT_POINTS');
    }

    /** Current-month reminders use business-local calendar days; expiry is an exact instant. */
    public static function summary(array $lots, string $tenant, string $member, int $now, DateTimeZone $timezone): array
    {
        $valid = self::validate($lots, $tenant, $member, $now);
        $today = (new DateTimeImmutable('@'.$now))->setTimezone($timezone);
        $month = $today->format('Y-m'); $balance = 0; $earned = 0; $expiring = 0; $reminders = [];
        foreach ($valid as $lot) {
            $earnedDate = (new DateTimeImmutable('@'.$lot['earned_at']))->setTimezone($timezone);
            if ($earnedDate->format('Y-m') === $month) $earned += $lot['earned'];
            if ($lot['expires_at'] <= $now || $lot['remaining'] === 0) continue;
            $balance += $lot['remaining'];
            $expiry = (new DateTimeImmutable('@'.$lot['expires_at']))->setTimezone($timezone);
            if ($expiry->format('Y-m') !== $month) continue;
            $expiring += $lot['remaining'];
            $days = (int)$today->setTime(0,0)->diff($expiry->setTime(0,0))->format('%r%a');
            if (in_array($days,[20,10,5],true)) {
                $reminders[] = ['lot_id'=>$lot['id'],'days'=>$days,'points'=>$lot['remaining'],'expires_at'=>$lot['expires_at']];
            }
        }
        usort($reminders,static fn(array $a,array $b): int => ($a['expires_at'] <=> $b['expires_at']) ?: strcmp($a['lot_id'],$b['lot_id']));
        return ['month'=>$month,'balance'=>$balance,'earned_this_month'=>$earned,'expiring_this_month'=>$expiring,'reminders'=>$reminders];
    }
}

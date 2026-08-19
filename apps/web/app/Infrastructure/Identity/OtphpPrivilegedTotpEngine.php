<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Identity\PrivilegedTotpEngine;
use App\Application\Identity\PrivilegedTotpMfaViolation;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use DateTimeImmutable;
use OTPHP\TOTP;
use Psr\Clock\ClockInterface;
use Throwable;

// Author by Lab | zefry
final readonly class OtphpPrivilegedTotpEngine implements PrivilegedTotpEngine
{
    private const PERIOD_SECONDS = 30;
    private const DIGITS = 6;
    private const DIGEST = 'sha1';
    private const SECRET_BYTES = 20;
    private const ISSUER = 'oneQay';

    public function generateSecret(): string
    {
        try {
            $totp = TOTP::create(
                secret: null,
                period: self::PERIOD_SECONDS,
                digest: self::DIGEST,
                digits: self::DIGITS,
                epoch: 0,
                clock: $this->clock(),
                secretSize: self::SECRET_BYTES,
            );
            $secret = $totp->getSecret();
        } catch (Throwable) {
            $this->storageFailure();
        }

        if (preg_match('/\A[A-Z2-7]{32}\z/D', $secret) !== 1) {
            $this->storageFailure();
        }

        return $secret;
    }

    public function provisioningUri(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        #[\SensitiveParameter] string $secret,
    ): string {
        $this->assertSecret($secret);

        try {
            $totp = $this->totp($secret)
                ->withIssuer(self::ISSUER)
                ->withLabel($tenantId->value().'/'.$identityId->value());
            $uri = $totp->getProvisioningUri();
        } catch (Throwable) {
            $this->storageFailure();
        }

        if (! is_string($uri) || ! str_starts_with($uri, 'otpauth://totp/')) {
            $this->storageFailure();
        }

        return $uri;
    }

    public function matchTimeStep(
        #[\SensitiveParameter] string $secret,
        #[\SensitiveParameter] string $code,
        int $nowUnix,
    ): ?int {
        $this->assertSecret($secret);

        if (preg_match('/\A[0-9]{6}\z/D', $code) !== 1 || $nowUnix < 0) {
            return null;
        }

        try {
            $totp = $this->totp($secret);
            $current = intdiv($nowUnix, self::PERIOD_SECONDS);
            $candidates = [$current];

            if ($current > 0) {
                $candidates[] = $current - 1;
            }
            $candidates[] = $current + 1;

            foreach ($candidates as $step) {
                $timestamp = $step * self::PERIOD_SECONDS;
                if ($totp->verify($code, $timestamp, null)) {
                    return $step;
                }
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private function totp(#[\SensitiveParameter] string $secret): TOTP
    {
        return TOTP::create(
            secret: $secret,
            period: self::PERIOD_SECONDS,
            digest: self::DIGEST,
            digits: self::DIGITS,
            epoch: 0,
            clock: $this->clock(),
        );
    }

    private function clock(): ClockInterface
    {
        return new class implements ClockInterface {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable('@0');
            }
        };
    }

    private function assertSecret(#[\SensitiveParameter] string $secret): void
    {
        if (preg_match('/\A[A-Z2-7]{32}\z/D', $secret) !== 1) {
            $this->storageFailure();
        }
    }

    private function storageFailure(): never
    {
        throw new PrivilegedTotpMfaViolation(
            PrivilegedTotpMfaViolation::STORAGE_FAILURE,
            'Privileged TOTP provider operation failed.',
        );
    }
}

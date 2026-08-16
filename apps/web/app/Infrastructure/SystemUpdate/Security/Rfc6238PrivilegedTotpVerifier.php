<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\Security;

use App\Application\SystemUpdate\Security\PrivilegedTotpSecretProvider;
use App\Application\SystemUpdate\Security\PrivilegedTotpVerifier;
use App\Application\SystemUpdate\Security\PrivilegedUpdateSecurityPolicy;
use App\Domain\Identity\PlatformIdentityId;

// Author by Lab | zefry
final readonly class Rfc6238PrivilegedTotpVerifier implements PrivilegedTotpVerifier
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function __construct(private PrivilegedTotpSecretProvider $secrets)
    {
    }

    public function verify(PlatformIdentityId $identityId, string $code, int $unixTime): bool
    {
        if (
            $unixTime <= 0
            || preg_match('/\A[0-9]{'.PrivilegedUpdateSecurityPolicy::TOTP_DIGITS.'}\z/', $code) !== 1
        ) {
            return false;
        }

        $encodedSecret = $this->secrets->base32SecretFor($identityId);
        if ($encodedSecret === null) {
            return false;
        }

        $secret = $this->decodeBase32($encodedSecret);
        if ($secret === null || $secret === '') {
            return false;
        }

        $counter = intdiv($unixTime, PrivilegedUpdateSecurityPolicy::TOTP_PERIOD_SECONDS);
        $matched = false;

        for (
            $offset = -PrivilegedUpdateSecurityPolicy::TOTP_WINDOW_STEPS;
            $offset <= PrivilegedUpdateSecurityPolicy::TOTP_WINDOW_STEPS;
            $offset++
        ) {
            $candidateCounter = $counter + $offset;
            if ($candidateCounter < 0) {
                continue;
            }

            $expected = $this->codeForCounter($secret, $candidateCounter);
            $matched = hash_equals($expected, $code) || $matched;
        }

        return $matched;
    }

    private function codeForCounter(string $secret, int $counter): string
    {
        $high = intdiv($counter, 4_294_967_296);
        $low = $counter % 4_294_967_296;
        $counterBytes = pack('N2', $high, $low);
        $digest = hash_hmac('sha1', $counterBytes, $secret, true);
        $offset = ord($digest[19]) & 0x0f;

        $binary = ((ord($digest[$offset]) & 0x7f) << 24)
            | ((ord($digest[$offset + 1]) & 0xff) << 16)
            | ((ord($digest[$offset + 2]) & 0xff) << 8)
            | (ord($digest[$offset + 3]) & 0xff);

        $modulus = 10 ** PrivilegedUpdateSecurityPolicy::TOTP_DIGITS;

        return str_pad(
            (string) ($binary % $modulus),
            PrivilegedUpdateSecurityPolicy::TOTP_DIGITS,
            '0',
            STR_PAD_LEFT,
        );
    }

    private function decodeBase32(string $encoded): ?string
    {
        $normalized = strtoupper(trim($encoded));
        if ($normalized === '' || preg_match('/\A[A-Z2-7]+=*\z/', $normalized) !== 1) {
            return null;
        }

        $normalized = rtrim($normalized, '=');
        if ($normalized === '') {
            return null;
        }

        $buffer = 0;
        $bits = 0;
        $decoded = '';

        foreach (str_split($normalized) as $character) {
            $value = strpos(self::BASE32_ALPHABET, $character);
            if ($value === false) {
                return null;
            }

            $buffer = ($buffer << 5) | $value;
            $bits += 5;

            while ($bits >= 8) {
                $bits -= 8;
                $decoded .= chr(($buffer >> $bits) & 0xff);
                $buffer &= (1 << $bits) - 1;
            }
        }

        return $decoded;
    }
}

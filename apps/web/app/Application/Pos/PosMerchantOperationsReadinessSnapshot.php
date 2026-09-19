<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosMerchantOperationsReadinessSnapshot
{
    public const STATE_GUIDANCE_UNAVAILABLE = 'guidance_unavailable';
    public const STATE_SETUP_REQUIRED = 'setup_required';
    public const STATE_SHIFT_REQUIRED = 'shift_required';
    public const STATE_CASHIER_READY = 'cashier_ready';
    public const STATE_REVIEW_AVAILABLE = 'review_available';
    public const STATE_ROUTES_ONLY = 'routes_only';

    /** @var list<string> */
    private const STATES = [
        self::STATE_GUIDANCE_UNAVAILABLE,
        self::STATE_SETUP_REQUIRED,
        self::STATE_SHIFT_REQUIRED,
        self::STATE_CASHIER_READY,
        self::STATE_REVIEW_AVAILABLE,
        self::STATE_ROUTES_ONLY,
    ];

    /** @var list<string> */
    private const RECOMMENDABLE_KEYS = [
        'catalog_inventory',
        'shift_start',
        'cashier',
        'sales_summary',
    ];

    public function __construct(
        private string $state,
        private ?string $recommendedKey,
        private ?int $catalogItemCount,
        private ?int $sellableItemCount,
        private ?bool $shiftActive,
        private ?bool $openingCashReady,
    ) {
        if (! in_array($this->state, self::STATES, true)) {
            throw new InvalidArgumentException('Merchant operations readiness state is invalid.');
        }

        if ($this->recommendedKey !== null
            && ! in_array($this->recommendedKey, self::RECOMMENDABLE_KEYS, true)) {
            throw new InvalidArgumentException('Merchant operations readiness recommendation is invalid.');
        }

        foreach ([$this->catalogItemCount, $this->sellableItemCount] as $count) {
            if ($count !== null && $count < 0) {
                throw new InvalidArgumentException('Merchant operations readiness count is invalid.');
            }
        }

        if ($this->state === self::STATE_GUIDANCE_UNAVAILABLE && $this->recommendedKey !== null) {
            throw new InvalidArgumentException('Unavailable guidance cannot carry a recommendation.');
        }

        if ($this->state === self::STATE_SETUP_REQUIRED && $this->recommendedKey !== 'catalog_inventory') {
            throw new InvalidArgumentException('Setup guidance must recommend catalog inventory.');
        }

        if ($this->state === self::STATE_SHIFT_REQUIRED && $this->recommendedKey !== 'shift_start') {
            throw new InvalidArgumentException('Shift guidance must recommend shift start.');
        }

        if ($this->state === self::STATE_CASHIER_READY && $this->recommendedKey !== 'cashier') {
            throw new InvalidArgumentException('Cashier-ready guidance must recommend cashier.');
        }

        if ($this->state === self::STATE_REVIEW_AVAILABLE && $this->recommendedKey !== 'sales_summary') {
            throw new InvalidArgumentException('Review guidance must recommend sales summary.');
        }
    }

    public function state(): string { return $this->state; }
    public function recommendedKey(): ?string { return $this->recommendedKey; }
    public function catalogItemCount(): ?int { return $this->catalogItemCount; }
    public function sellableItemCount(): ?int { return $this->sellableItemCount; }
    public function shiftActive(): ?bool { return $this->shiftActive; }
    public function openingCashReady(): ?bool { return $this->openingCashReady; }
}

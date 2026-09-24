<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Pos\FinalShiftCloseDurableStagingDeliveryGate;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use JsonException;

// Author by Lab | zefry
final class FinalShiftCloseDurableStagingDeliveryServiceProvider extends ServiceProvider
{
    private const RELEASE_METADATA_MAX_BYTES = 32768;

    public function boot(): void
    {
        if (! $this->deliveryEnabled()) {
            return;
        }

        Route::middleware([
            'web',
            'session.active',
            'throttle:5,1',
            'throttle:50,60',
            RequirePosSessionContextMiddleware::class,
        ])->group(base_path('routes/pos-final-shift-close.php'));
    }

    private function deliveryEnabled(): bool
    {
        $runtimeClass = (string) config('oneqay.runtime_class', '');
        if (! DurableStagingMerchantCoreBridge::armedFor($runtimeClass)) {
            return false;
        }

        $release = $this->releaseMetadata();
        if ($release === null) {
            return false;
        }

        return (new FinalShiftCloseDurableStagingDeliveryGate())->allows(
            $release,
            $runtimeClass,
            (string) env('ONEQAY_RUNNING_SOURCE_COMMIT', ''),
            (string) env('ONEQAY_RUNNING_ARTIFACT_SHA256', ''),
            (bool) config('database.oneqay_persistence_enabled', false),
            (bool) config('oneqay.session_control.enabled', false),
            (bool) config('oneqay.pos_sale_completion.enabled', false),
            filter_var(env('ONEQAY_POS_SHIFT_CLOSE_ENABLED', false), FILTER_VALIDATE_BOOL),
        );
    }

    /** @return array<string, mixed>|null */
    private function releaseMetadata(): ?array
    {
        $path = dirname(base_path(), 2).DIRECTORY_SEPARATOR.'RELEASE.json';

        if (is_link($path) || ! is_file($path) || ! is_readable($path)) {
            return null;
        }

        $size = filesize($path);
        if (! is_int($size) || $size < 2 || $size > self::RELEASE_METADATA_MAX_BYTES) {
            return null;
        }

        $raw = file_get_contents($path);
        if (! is_string($raw) || strlen($raw) !== $size) {
            return null;
        }

        try {
            $decoded = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (! is_array($decoded) || array_is_list($decoded)) {
            return null;
        }

        return $decoded;
    }
}

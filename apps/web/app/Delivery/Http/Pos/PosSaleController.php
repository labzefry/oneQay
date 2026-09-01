<?php

declare(strict_types=1);

// Sprint47 JRN-004 JRN-006 compatibility preservation anchor.

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\CompleteSale;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\SaleCommand;
use App\Delivery\Http\SafeErrorEnvelope;
use App\Domain\Pos\Cart;
use App\Domain\Pos\CartLine;
use App\Domain\Pos\Money;
use App\Domain\Pos\ProductId;
use App\Domain\Pos\TenderCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use ValueError;

// Author by Lab | zefry
final class PosSaleController
{
    private const ALLOWED_FIELDS = [
        'operation_id',
        'lines',
        'tender_category',
        'tendered_atomic_units',
        'currency',
        'currency_scale',
    ];

    public function __construct(private readonly CompleteSale $sales) {}

    public function __invoke(Request $request): JsonResponse
    {
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            $payload = $request->except('_token');
            $this->assertClosedPayload($payload);

            $operationId = $payload['operation_id'];
            $rawLines = $payload['lines'];
            $tenderCategory = $payload['tender_category'];
            $atomicUnits = $payload['tendered_atomic_units'];
            $currency = $payload['currency'];
            $scale = $payload['currency_scale'];

            if (! is_string($operationId)
                || ! is_array($rawLines)
                || ! is_string($tenderCategory)
                || ! is_int($atomicUnits)
                || ! is_string($currency)
                || ! is_int($scale)) {
                throw new InvalidArgumentException('POS sale request is invalid.');
            }

            $lines = [];
            foreach ($rawLines as $line) {
                if (! is_array($line)) {
                    throw new InvalidArgumentException('POS sale line is invalid.');
                }
                $keys = array_keys($line);
                sort($keys);
                if ($keys !== ['product_id', 'quantity']
                    || ! is_string($line['product_id'])
                    || ! is_int($line['quantity'])) {
                    throw new InvalidArgumentException('POS sale line is invalid.');
                }
                $lines[] = new CartLine(ProductId::fromString($line['product_id']), $line['quantity']);
            }

            $receipt = $this->sales->complete(new SaleCommand(
                $operationId,
                Cart::fromLines($lines),
                TenderCategory::from($tenderCategory),
                Money::fromAtomicUnits($atomicUnits, $currency, $scale),
                $correlationId,
            ));

            return response()->json([
                'status' => 'completed',
                'sale_id' => $receipt->saleId(),
                'operation_id' => $receipt->operationId(),
                'tenant_id' => $receipt->tenantId(),
                'outlet_id' => $receipt->outletId(),
                'total' => [
                    'atomic_units' => $receipt->total()->atomicUnits(),
                    'currency' => $receipt->total()->currency(),
                    'scale' => $receipt->total()->scale(),
                ],
                'tender_category' => $receipt->tenderCategory()->value,
                'evidence_mode' => $receipt->evidenceMode(),
                'change' => [
                    'atomic_units' => $receipt->changeAmount()->atomicUnits(),
                    'currency' => $receipt->changeAmount()->currency(),
                    'scale' => $receipt->changeAmount()->scale(),
                ],
                'correlation_id' => $receipt->correlationId(),
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (DurableAuthorizationViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (InvalidArgumentException|ValueError|PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SALE_REJECTED', $correlationId),
                422,
                ['Cache-Control' => 'no-store, private'],
            );
        }
    }

    private function assertClosedPayload(array $payload): void
    {
        $keys = array_keys($payload);
        sort($keys);
        $allowed = self::ALLOWED_FIELDS;
        sort($allowed);
        if ($keys !== $allowed) {
            throw new InvalidArgumentException('POS sale request is invalid.');
        }
    }
}

// Sprint48 JRN-005 Sprint46 compatibility preservation anchor.

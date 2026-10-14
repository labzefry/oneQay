<?php

declare(strict_types=1);

namespace App\Delivery\Http\Business;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Business\BusinessInput;
use App\Application\Business\BusinessManagement;
use App\Application\Business\BusinessViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Inertia\Inertia;

// Author by Lab | zefry
final readonly class BusinessWorkspaceController
{
    public function __construct(private BusinessManagement $business) {}

    public function show(Request $request, string $module): mixed
    {
        return $this->respond($request, function () use ($request, $module): mixed {
            $data = $this->business->workspace($module, BusinessInput::integer($request->query('page', '1'), 1, 10000), BusinessInput::text($request->query('q', ''), 120, true), $request->query('from'), $request->query('to'));
            $data['correlation_id'] = $request->attributes->get('oneqay.correlation_id', 'correlation-missing');
            return Inertia::render('Business/Workspace', $data);
        });
    }

    public function command(Request $request, string $action): mixed
    {
        return $this->respond($request, function () use ($request, $action): mixed {
            if (strlen($request->getContent()) > 100000) throw new BusinessViolation('REQUEST_TOO_LARGE', 413);
            $input = $request->except('_token');
            BusinessInput::fields($input, ['operation_id', 'data']);
            if (!is_array($input['data']) || !is_string($input['operation_id'])) throw new BusinessViolation('INVALID_FIELDS');
            $result = $this->business->execute($action, $input['operation_id'], $input['data'], (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing'));
            return response()->json(['status' => 'saved', 'record' => $result]);
        });
    }

    public function options(Request $request, string $resource): mixed
    {
        return $this->respond($request, fn (): mixed => response()->json($this->business->options($resource, BusinessInput::text($request->query('q', ''), 120, true), BusinessInput::integer($request->query('page', '1'), 1, 10000))));
    }

    public function export(Request $request, string $module): mixed
    {
        if (in_array($request->query('format'), ['pdf','xlsx'], true)) return app(MerchandisingController::class)->download($request, $module);
        return $this->respond($request, function () use ($request, $module): mixed {
            // Export precisely the filtered page, with an explicit bound and formula neutralization.
            $workspace = $this->business->workspace($module, BusinessInput::integer($request->query('page', '1'), 1, 10000), BusinessInput::text($request->query('q', ''), 120, true), $request->query('from'), $request->query('to'));
            $rows = $workspace['records']['data'];
            $headers = match ($module) {
                'suppliers', 'customers' => ['id', 'code', 'name', 'email', 'phone', 'active', 'marketing_consent'],
                'purchases' => ['id', 'reference', 'supplier_id', 'status', 'currency', 'scale', 'total_atomic'],
                'finance' => ['id', 'kind', 'reference', 'partner_id', 'issue_date', 'due_date', 'currency', 'scale', 'total_atomic', 'paid_atomic', 'status'],
                'people' => ['id', 'code', 'name', 'department', 'job_title', 'start_date', 'active'],
                default => ['id', 'action', 'resource_id', 'created_by', 'created_at_unix', 'correlation_id'],
            };
            return response()->streamDownload(static function () use ($rows, $headers): void {
                $out = fopen('php://output', 'wb'); fwrite($out, "\xEF\xBB\xBF"); fputcsv($out, $headers, ',', '"', '');
                foreach ($rows as $row) {
                    $cells = [];
                    foreach ($headers as $header) {
                        $cell = (string) ($row[$header] ?? '');
                        if (preg_match('/\A[\s]*[=+@-]|\A[\t\r\n]/u', $cell)) $cell = "'".$cell;
                        $cells[] = $cell;
                    }
                    fputcsv($out, $cells, ',', '"', '');
                }
                fclose($out);
            }, 'oneQay-'.$module.'-page-'.$workspace['records']['page'].'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        });
    }

    private function respond(Request $request, callable $work): mixed
    {
        try {
            $response = $work();
            if (method_exists($response, 'toResponse')) $response = $response->toResponse($request);
            $response->headers->set('Cache-Control', 'no-store, private');
            return $response;
        } catch (DurableAuthorizationViolation) {
            return $this->error($request, 'BUSINESS_PERMISSION_DENIED', 403);
        } catch (BusinessViolation $error) {
            return $this->error($request, $error->reason, $error->status);
        } catch (PosTransactionViolation) {
            return $this->error($request, 'STOCK_RECEIPT_REJECTED', 409);
        } catch (QueryException $error) {
            $state = (string) ($error->errorInfo[0] ?? '');
            return $this->error($request, str_starts_with($state, '23') ? 'DUPLICATE_OR_INVALID_REFERENCE' : 'BUSINESS_STORAGE_UNAVAILABLE', str_starts_with($state, '23') ? 409 : 503);
        }
    }

    private function error(Request $request, string $code, int $status): mixed
    {
        return response()->json(SafeErrorEnvelope::make($code, (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing')), $status, ['Cache-Control' => 'no-store, private']);
    }
}

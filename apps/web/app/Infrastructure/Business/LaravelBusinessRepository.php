<?php

declare(strict_types=1);

namespace App\Infrastructure\Business;

use App\Application\Business\BusinessInput;
use App\Application\Business\BusinessRepository;
use App\Application\Business\BusinessViolation;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\InventoryReplenishmentCommand;
use App\Application\Pos\InventoryReplenishmentRepository;
use App\Application\Pos\PosExecutionContext;
use App\Domain\Pos\ProductId;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

// Author by Lab | zefry. SQL and engine details remain in Infrastructure.
final readonly class LaravelBusinessRepository implements BusinessRepository
{
    private const RESOURCES = ['partners', 'purchases', 'receipts', 'invoices', 'settlements', 'journals', 'interactions', 'employees', 'attendance', 'leave', 'events'];
    private const JSON_FIELDS = ['lines'];
    private const FILTERS = ['kind', 'active', 'status', 'supplier_id', 'purchase_id', 'partner_id', 'receipt_id', 'invoice_id', 'customer_id', 'employee_id', 'work_date'];

    public function __construct(private Connection $db, private InventoryReplenishmentRepository $stock) {}

    private function query(VerifiedOrganizationalContext $scope, string $resource): Builder
    {
        if (!in_array($resource, self::RESOURCES, true) || $scope->outletId() === null) throw new BusinessViolation('INVALID_RESOURCE');
        return $this->db->table('oneqay_business_'.$resource)->where('tenant_id', $scope->tenantId()->value())->where('organization_id', $scope->organizationId()->value())->where('outlet_id', $scope->outletId()->value());
    }

    public function once(VerifiedOrganizationalContext $scope, string $operationId, string $action, array $payload, string $correlation, callable $work): array
    {
        if (preg_match('/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/', $operationId) !== 1) throw new BusinessViolation('INVALID_OPERATION');
        if (strlen($correlation) > 128) throw new BusinessViolation('INVALID_CORRELATION');
        $fingerprint = BusinessInput::fingerprint([$scope->identityId()->value(), $scope->organizationId()->value(), $scope->outletId()?->value(), $scope->deviceId()?->value(), $action, $payload]);
        return $this->db->transaction(function () use ($scope, $operationId, $action, $correlation, $fingerprint, $work): array {
            // The unique insert serializes concurrent commands before any domain write.
            $this->db->table('oneqay_business_operations')->insertOrIgnore(['tenant_id' => $scope->tenantId()->value(), 'operation_id' => $operationId, 'fingerprint' => $fingerprint, 'result' => null, 'created_at_unix' => time()]);
            $operations = $this->db->table('oneqay_business_operations')->where('tenant_id', $scope->tenantId()->value())->where('operation_id', $operationId);
            $existing = (clone $operations)->lockForUpdate()->first();
            if ($existing === null || !hash_equals((string) $existing->fingerprint, $fingerprint)) throw new BusinessViolation('OPERATION_CONFLICT', 409);
            if ($existing->result !== null) return json_decode($existing->result, true, 64, JSON_THROW_ON_ERROR);
            $result = $work();
            $this->create($scope, 'events', ['action' => $action, 'resource_id' => $result['id'] ?? '', 'operation_id' => $operationId, 'correlation_id' => $correlation, 'payload_fingerprint' => $fingerprint]);
            $operations->update(['result' => json_encode($result, JSON_THROW_ON_ERROR)]);
            return $result;
        }, 3);
    }

    public function find(VerifiedOrganizationalContext $scope, string $resource, string $id, bool $lock = false): array
    {
        $query = $this->query($scope, $resource)->where('id', $id);
        if ($lock) $query->lockForUpdate();
        $row = $query->first();
        if ($row === null) throw new BusinessViolation('RECORD_NOT_FOUND', 404);
        return $this->hydrate($row);
    }

    public function create(VerifiedOrganizationalContext $scope, string $resource, array $data): array
    {
        $id = bin2hex(random_bytes(16)); $now = time();
        $this->query($scope, $resource)->insert(array_merge($this->encode($data), ['tenant_id' => $scope->tenantId()->value(), 'organization_id' => $scope->organizationId()->value(), 'outlet_id' => $scope->outletId()?->value(), 'id' => $id, 'created_by' => $scope->identityId()->value(), 'version' => 1, 'created_at_unix' => $now, 'updated_at_unix' => $now]));
        return $this->find($scope, $resource, $id);
    }

    public function update(VerifiedOrganizationalContext $scope, string $resource, string $id, int $version, array $data): array
    {
        if (in_array($resource, ['events', 'journals', 'receipts', 'settlements', 'interactions'], true)) throw new BusinessViolation('IMMUTABLE_RECORD', 409);
        foreach (['tenant_id', 'organization_id', 'outlet_id', 'id', 'created_by', 'created_at_unix'] as $key) if (array_key_exists($key, $data)) throw new BusinessViolation('IMMUTABLE_SCOPE');
        $count = $this->query($scope, $resource)->where('id', $id)->where('version', $version)->update(array_merge($this->encode($data), ['version' => $version + 1, 'updated_at_unix' => time()]));
        if ($count !== 1) throw new BusinessViolation('RECORD_CHANGED', 409);
        return $this->find($scope, $resource, $id);
    }

    public function listing(VerifiedOrganizationalContext $scope, string $resource, array $filters = [], int $page = 1): array
    {
        if ($page < 1 || $page > 10000) throw new BusinessViolation('INVALID_PAGE');
        $query = $this->query($scope, $resource);
        foreach ($filters as $key => $value) {
            if ($key === 'q') {
                $column = match ($resource) { 'partners', 'employees' => 'name', 'purchases', 'receipts', 'invoices', 'settlements' => 'reference', 'journals' => 'description', 'events' => 'action', default => null };
                if ($column !== null) $query->where($column, 'like', '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], BusinessInput::text($value, 120, true)).'%');
                continue;
            }
            if (!in_array($key, self::FILTERS, true)) throw new BusinessViolation('INVALID_FILTER');
            $query->where($key, $value);
        }
        $rows = $query->orderByDesc('created_at_unix')->orderByDesc('id')->offset(($page - 1) * 50)->limit(51)->get()->all();
        $hasMore = count($rows) > 50;
        return ['data' => array_map($this->hydrate(...), array_slice($rows, 0, 50)), 'page' => $page, 'has_more' => $hasMore];
    }

    public function catalogOptions(VerifiedOrganizationalContext $scope, string $search = '', int $page = 1): array
    {
        if ($page < 1 || $page > 10000) throw new BusinessViolation('INVALID_PAGE');
        $term = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], BusinessInput::text($search, 120, true)).'%';
        $rows = $this->db->table('oneqay_pos_sale_catalog_items as c')->join('oneqay_pos_inventory_baselines as b', function ($join): void {
            $join->on('b.tenant_id', '=', 'c.tenant_id')->on('b.outlet_id', '=', 'c.outlet_id')->on('b.product_id', '=', 'c.product_id');
        })->where('c.tenant_id', $scope->tenantId()->value())->where('b.organization_id', $scope->organizationId()->value())
            ->where('c.outlet_id', $scope->outletId()?->value())->where('c.active', true)
            ->where(fn ($query) => $query->where('c.display_name', 'like', $term)->orWhere('c.product_id', 'like', $term))
            ->orderBy('c.display_name')->orderBy('c.product_id')->offset(($page - 1) * 50)->limit(51)
            ->get(['c.product_id', 'c.display_name', 'c.currency', 'c.currency_scale'])->all();
        return ['data' => array_map(static fn ($row): array => (array) $row, array_slice($rows, 0, 50)), 'page' => $page, 'has_more' => count($rows) > 50];
    }

    public function catalog(VerifiedOrganizationalContext $scope, string $productId): array
    {
        $item = $this->db->table('oneqay_pos_sale_catalog_items as c')->join('oneqay_pos_inventory_baselines as b', function ($j): void {
            $j->on('b.tenant_id', '=', 'c.tenant_id')->on('b.outlet_id', '=', 'c.outlet_id')->on('b.product_id', '=', 'c.product_id');
        })->where('c.tenant_id', $scope->tenantId()->value())->where('b.organization_id', $scope->organizationId()->value())->where('c.outlet_id', $scope->outletId()?->value())->where('c.product_id', $productId)->where('c.active', true)->first(['c.*']);
        if ($item === null) throw new BusinessViolation('PRODUCT_NOT_READY');
        return (array) $item;
    }

    public function receiveStock(VerifiedOrganizationalContext $scope, string $operationId, string $productId, int $quantity, string $correlation): void
    {
        $this->stock->replenish(PosExecutionContext::fromVerified($scope), new InventoryReplenishmentCommand($operationId, ProductId::fromString($productId), $quantity), $correlation, time());
    }

    public function journalExists(VerifiedOrganizationalContext $scope, string $source): bool
    {
        return $this->query($scope, 'journals')->where('source', $source)->exists();
    }

    public function leaveOverlaps(VerifiedOrganizationalContext $scope, string $employee, string $from, string $to): bool
    {
        return $this->query($scope, 'leave')->where('employee_id', $employee)->whereIn('status', ['PENDING', 'APPROVED'])->where('start_date', '<=', $to)->where('end_date', '>=', $from)->exists();
    }

    public function balance(VerifiedOrganizationalContext $scope, string $from, string $to): array
    {
        $balances = [];
        foreach ($this->query($scope, 'journals')->whereBetween('posting_date', [$from, $to])->orderBy('id')->cursor() as $row) {
            foreach (json_decode($row->lines, true, 64, JSON_THROW_ON_ERROR) as $line) {
                $key = $row->currency.':'.$row->scale.':'.$line['account'];
                $balances[$key] ??= ['account' => $line['account'], 'currency' => $row->currency, 'scale' => (int) $row->scale, 'debit_atomic' => 0, 'credit_atomic' => 0];
                $balances[$key]['debit_atomic'] = BusinessInput::add($balances[$key]['debit_atomic'], BusinessInput::integer($line['debit_atomic']));
                $balances[$key]['credit_atomic'] = BusinessInput::add($balances[$key]['credit_atomic'], BusinessInput::integer($line['credit_atomic']));
            }
        }
        ksort($balances);
        foreach ($balances as &$row) {
            $row['debit_atomic'] = (string) $row['debit_atomic']; $row['credit_atomic'] = (string) $row['credit_atomic'];
        }
        return array_values($balances);
    }

    private function encode(array $data): array
    {
        foreach (self::JSON_FIELDS as $field) if (isset($data[$field])) $data[$field] = json_encode($data[$field], JSON_THROW_ON_ERROR);
        return $data;
    }

    private function hydrate(object $row): array
    {
        $data = (array) $row;
        foreach (self::JSON_FIELDS as $field) if (isset($data[$field])) $data[$field] = json_decode($data[$field], true, 64, JSON_THROW_ON_ERROR);
        foreach ($data as $key => &$value) {
            if (str_ends_with($key, '_atomic')) $value = (string) $value;
            elseif (in_array($key, ['version', 'scale', 'created_at_unix', 'updated_at_unix', 'consent_at', 'clock_in', 'clock_out'], true) && $value !== null) $value = (int) $value;
            elseif (in_array($key, ['active', 'marketing_consent'], true)) $value = (bool) $value;
        }
        return $data;
    }
}

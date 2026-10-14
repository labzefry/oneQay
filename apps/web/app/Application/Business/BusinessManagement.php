<?php

declare(strict_types=1);

namespace App\Application\Business;

// Author by Lab | zefry. Authorization precedes both replay lookup and data reads.
final readonly class BusinessManagement
{
    public function __construct(private BusinessRepository $records, private BusinessAccess $access, private PartnerManagement $partners, private ProcurementManagement $purchases, private FinancialManagement $finance, private WorkforceManagement $people) {}

    public function execute(string $action, string $operation, array $input, string $correlation): array
    {
        $permission = BusinessAccess::ACTIONS[$action] ?? throw new BusinessViolation('UNKNOWN_ACTION', 404);
        $scope = $this->access->require($permission);
        if ($action === 'purchase.receive') $this->access->require('pos.inventory.replenish');
        return $this->records->once($scope, $operation, $action, $input, $correlation, fn (): array => match ($action) {
            'supplier.save' => $this->partners->save($scope, 'SUPPLIER', $input),
            'customer.save' => $this->partners->save($scope, 'CUSTOMER', $input),
            'customer.interact' => $this->partners->interaction($scope, $input),
            'purchase.create' => $this->purchases->create($scope, $input),
            'purchase.submit', 'purchase.approve', 'purchase.reject', 'purchase.cancel' => $this->purchases->transition($scope, $action, $input),
            'purchase.receive' => $this->purchases->receive($scope, $input, $operation, $correlation),
            'invoice.create' => $this->finance->invoice($scope, $input),
            'invoice.settle' => $this->finance->settle($scope, $input),
            'journal.post' => $this->finance->manual($scope, $input, $operation),
            'journal.reverse' => $this->finance->reverse($scope, $input),
            'employee.save' => $this->people->save($scope, $input),
            'attendance.record' => $this->people->attendance($scope, $input),
            'leave.request' => $this->people->leave($scope, $input),
            'leave.approve' => $this->people->decide($scope, $input, true),
            'leave.reject' => $this->people->decide($scope, $input, false),
        });
    }

    public function workspace(string $module, int $page = 1, string $search = '', ?string $from = null, ?string $to = null): array
    {
        if (in_array($module, ['access','products','settings','reports'], true)) throw new BusinessViolation('UNKNOWN_MODULE', 404);
        $definition = BusinessAccess::MODULES[$module] ?? throw new BusinessViolation('UNKNOWN_MODULE', 404);
        $scope = $this->access->require($definition[1]);
        $resource = match ($module) { 'suppliers', 'customers' => 'partners', 'finance' => 'invoices', 'people' => 'employees', 'audit' => 'events', default => 'purchases' };
        $filters = ['q' => $search];
        if ($module === 'suppliers' || $module === 'customers') $filters['kind'] = $module === 'suppliers' ? 'SUPPLIER' : 'CUSTOMER';
        $result = ['module' => $module, 'title' => $definition[0], 'scope' => ['tenant_id' => $scope->tenantId()->value(), 'organization_id' => $scope->organizationId()->value(), 'outlet_id' => $scope->outletId()?->value(), 'device_id' => $scope->deviceId()?->value()], 'records' => $this->records->listing($scope, $resource, $filters, $page), 'actions' => $this->access->actions(), 'modules' => $this->access->modules(), 'search' => $search];
        if ($module === 'purchases') {
            $result['suppliers'] = $this->options('suppliers')['data'];
            $result['products'] = $this->options('products');
            $result['receipts'] = $this->records->listing($scope, 'receipts', [], $page);
        } elseif ($module === 'customers') {
            $result['interactions'] = $this->records->listing($scope, 'interactions', [], $page);
        } elseif ($module === 'finance') {
            $start = BusinessInput::date($from ?? gmdate('Y-01-01')); $end = BusinessInput::date($to ?? gmdate('Y-m-d'));
            if ($start > $end || (new \DateTimeImmutable($start))->diff(new \DateTimeImmutable($end))->days > 3660) throw new BusinessViolation('INVALID_REPORT_PERIOD');
            $result['from'] = $start; $result['to'] = $end;
            $result['balances'] = $this->records->balance($scope, $start, $end);
            $result['journals'] = $this->records->listing($scope, 'journals', [], $page);
            $result['receipts'] = $this->records->listing($scope, 'receipts', [], $page);
            $result['partners'] = $this->options('partners')['data'];
            $result['accounts'] = FinancialManagement::ACCOUNTS;
        } elseif ($module === 'people') {
            $result['attendance'] = $this->records->listing($scope, 'attendance', [], $page);
            $result['leave'] = $this->records->listing($scope, 'leave', [], $page);
        }
        return $result;
    }

    public function options(string $kind, string $q = '', int $page = 1): array
    {
        $permission = match ($kind) { 'suppliers', 'products' => 'business.purchases.view', 'partners' => 'business.finance.view', default => throw new BusinessViolation('UNKNOWN_RESOURCE', 404) };
        $scope = $this->access->require($permission);
        if ($kind === 'products') return $this->records->catalogOptions($scope, $q, $page);
        $filters = ['active' => true, 'q' => $q];
        if ($kind === 'suppliers') $filters['kind'] = 'SUPPLIER';
        $result = $this->records->listing($scope, 'partners', $filters, $page);
        // Purchase/finance users need identification, not unrestricted CRM contact data.
        $result['data'] = array_map(static fn (array $row): array => array_intersect_key($row, array_flip(['id', 'code', 'name', 'kind'])), $result['data']);
        return $result;
    }
}

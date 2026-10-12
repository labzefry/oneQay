<?php

declare(strict_types=1);

namespace App\Application\Business;

use App\Application\Organization\VerifiedOrganizationalContext;

// Author by Lab | zefry
final readonly class ProcurementManagement
{
    public function __construct(private BusinessRepository $records, private FinancialManagement $finance) {}

    public function create(VerifiedOrganizationalContext $scope, array $input): array
    {
        BusinessInput::fields($input, ['supplier_id', 'reference', 'currency', 'scale', 'lines', 'note']);
        $supplier = $this->records->find($scope, 'partners', BusinessInput::id($input['supplier_id']), true);
        if ($supplier['kind'] !== 'SUPPLIER' || !$supplier['active']) throw new BusinessViolation('SUPPLIER_NOT_ACTIVE');
        $lines = []; $total = 0; $currency = BusinessInput::currency($input['currency']); $scale = BusinessInput::integer($input['scale'], 0, 6);
        foreach (BusinessInput::rows($input['lines']) as $line) {
            BusinessInput::fields($line, ['product_id', 'quantity', 'unit_cost_atomic']);
            $product = BusinessInput::id($line['product_id']);
            if (isset($lines[$product])) throw new BusinessViolation('DUPLICATE_PRODUCT');
            $catalog = $this->records->catalog($scope, $product);
            if ($catalog['currency'] !== $currency || (int) $catalog['currency_scale'] !== $scale) throw new BusinessViolation('CURRENCY_MISMATCH');
            $qty = BusinessInput::integer($line['quantity'], 1, 1000000); $cost = BusinessInput::integer($line['unit_cost_atomic'], 1);
            $total = BusinessInput::add($total, BusinessInput::multiply($cost, $qty));
            $lines[$product] = ['product_id' => $product, 'name' => (string) $catalog['display_name'], 'quantity' => $qty, 'received' => 0, 'unit_cost_atomic' => (string) $cost];
        }
        ksort($lines);
        return $this->records->create($scope, 'purchases', ['supplier_id' => $supplier['id'], 'reference' => BusinessInput::text($input['reference'], 80), 'status' => 'DRAFT', 'currency' => $currency, 'scale' => $scale, 'total_atomic' => $total, 'lines' => array_values($lines), 'note' => BusinessInput::text($input['note'], 2000, true), 'approved_by' => null]);
    }

    public function transition(VerifiedOrganizationalContext $scope, string $action, array $input): array
    {
        BusinessInput::fields($input, ['id', 'version']);
        $purchase = $this->records->find($scope, 'purchases', BusinessInput::id($input['id']), true);
        [$allowed, $status] = match ($action) {
            'purchase.submit' => [['DRAFT'], 'SUBMITTED'],
            'purchase.approve' => [['SUBMITTED'], 'APPROVED'],
            'purchase.reject' => [['SUBMITTED'], 'REJECTED'],
            'purchase.cancel' => [['DRAFT', 'SUBMITTED', 'APPROVED'], 'CANCELLED'],
            default => throw new BusinessViolation('INVALID_ACTION'),
        };
        if (!in_array($purchase['status'], $allowed, true)) throw new BusinessViolation('INVALID_PURCHASE_STATE', 409);
        $data = ['status' => $status];
        if ($status === 'APPROVED' || $status === 'REJECTED') $data['approved_by'] = $scope->identityId()->value();
        return $this->records->update($scope, 'purchases', $purchase['id'], BusinessInput::integer($input['version'], 1), $data);
    }

    public function receive(VerifiedOrganizationalContext $scope, array $input, string $operation, string $correlation): array
    {
        BusinessInput::fields($input, ['id', 'version', 'reference', 'received_date', 'lines']);
        $purchase = $this->records->find($scope, 'purchases', BusinessInput::id($input['id']), true);
        if (!in_array($purchase['status'], ['APPROVED', 'PARTIAL'], true)) throw new BusinessViolation('PURCHASE_NOT_APPROVED', 409);
        $requested = []; $receiptLines = []; $total = 0;
        foreach (BusinessInput::rows($input['lines']) as $line) {
            BusinessInput::fields($line, ['product_id', 'quantity']);
            $id = BusinessInput::id($line['product_id']);
            if (isset($requested[$id])) throw new BusinessViolation('DUPLICATE_PRODUCT');
            $requested[$id] = BusinessInput::integer($line['quantity'], 1, 1000000);
        }
        $lines = $purchase['lines']; $allReceived = true;
        foreach ($lines as &$line) {
            $id = $line['product_id'];
            if (isset($requested[$id])) {
                $quantity = $requested[$id]; unset($requested[$id]);
                if ($quantity > $line['quantity'] - $line['received']) throw new BusinessViolation('QUANTITY_EXCEEDS_ORDER');
                $this->records->receiveStock($scope, 'purchase:'.hash('sha256', $operation.'|'.$id), $id, $quantity, $correlation);
                $line['received'] += $quantity;
                $total = BusinessInput::add($total, BusinessInput::multiply(BusinessInput::integer($line['unit_cost_atomic']), $quantity));
                $receiptLines[] = ['product_id' => $id, 'name' => $line['name'], 'quantity' => $quantity, 'unit_cost_atomic' => $line['unit_cost_atomic']];
            }
            if ($line['received'] !== $line['quantity']) $allReceived = false;
        }
        unset($line);
        if ($requested !== [] || $receiptLines === []) throw new BusinessViolation('PRODUCT_NOT_IN_ORDER');
        $this->records->update($scope, 'purchases', $purchase['id'], BusinessInput::integer($input['version'], 1), ['lines' => $lines, 'status' => $allReceived ? 'RECEIVED' : 'PARTIAL']);
        $receipt = $this->records->create($scope, 'receipts', ['purchase_id' => $purchase['id'], 'reference' => BusinessInput::text($input['reference'], 80), 'lines' => $receiptLines, 'total_atomic' => $total, 'currency' => $purchase['currency'], 'scale' => $purchase['scale']]);
        $this->finance->pair($scope, 'receipt:'.$receipt['id'], BusinessInput::date($input['received_date']), 'Goods received '.$receipt['reference'], $purchase['currency'], $purchase['scale'], $total, '1100', '2100');
        return $receipt;
    }
}

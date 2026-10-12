<?php

declare(strict_types=1);

namespace App\Application\Business;

use App\Application\Organization\VerifiedOrganizationalContext;

// Author by Lab | zefry. Exact integer arithmetic, immutable balanced journals.
final readonly class FinancialManagement
{
    public const ACCOUNTS = [
        '1000' => ['Cash', 'ASSET'], '1010' => ['Bank', 'ASSET'], '1100' => ['Inventory', 'ASSET'],
        '1200' => ['Accounts receivable', 'ASSET'], '2000' => ['Accounts payable', 'LIABILITY'],
        '2100' => ['Goods received, not invoiced', 'LIABILITY'], '3000' => ['Owner equity', 'EQUITY'],
        '4000' => ['Revenue', 'REVENUE'], '5000' => ['Cost of goods sold', 'EXPENSE'],
        '6000' => ['Operating expenses', 'EXPENSE'], '6100' => ['Payroll expense', 'EXPENSE'],
    ];

    public function __construct(private BusinessRepository $records) {}

    public function post(VerifiedOrganizationalContext $scope, string $source, string $date, string $description, string $currency, int $scale, array $lines, ?string $reversal = null): array
    {
        $debit = 0; $credit = 0; $normalized = [];
        foreach (BusinessInput::rows($lines) as $line) {
            BusinessInput::fields($line, ['account', 'debit_atomic', 'credit_atomic']);
            $account = BusinessInput::id($line['account']);
            if (!isset(self::ACCOUNTS[$account])) throw new BusinessViolation('UNKNOWN_ACCOUNT');
            $d = BusinessInput::integer($line['debit_atomic']); $c = BusinessInput::integer($line['credit_atomic']);
            if (($d > 0) === ($c > 0)) throw new BusinessViolation('INVALID_JOURNAL_LINE');
            $debit = BusinessInput::add($debit, $d); $credit = BusinessInput::add($credit, $c);
            $normalized[] = ['account' => $account, 'debit_atomic' => (string) $d, 'credit_atomic' => (string) $c];
        }
        if ($debit === 0 || $debit !== $credit) throw new BusinessViolation('UNBALANCED_JOURNAL');
        if ($this->records->journalExists($scope, $source)) throw new BusinessViolation('ALREADY_POSTED', 409);
        return $this->records->create($scope, 'journals', ['source' => $source, 'posting_date' => BusinessInput::date($date), 'description' => BusinessInput::text($description, 240), 'currency' => BusinessInput::currency($currency), 'scale' => BusinessInput::integer($scale, 0, 6), 'total_atomic' => $debit, 'lines' => $normalized, 'reversal_of' => $reversal]);
    }

    public function pair(VerifiedOrganizationalContext $scope, string $source, string $date, string $description, string $currency, int $scale, int $amount, string $debit, string $credit): array
    {
        return $this->post($scope, $source, $date, $description, $currency, $scale, [['account' => $debit, 'debit_atomic' => $amount, 'credit_atomic' => 0], ['account' => $credit, 'debit_atomic' => 0, 'credit_atomic' => $amount]]);
    }

    public function manual(VerifiedOrganizationalContext $scope, array $input, string $operation): array
    {
        BusinessInput::fields($input, ['posting_date', 'description', 'currency', 'scale', 'lines']);
        return $this->post($scope, 'manual:'.hash('sha256', $operation), BusinessInput::date($input['posting_date']), BusinessInput::text($input['description'], 240), BusinessInput::currency($input['currency']), BusinessInput::integer($input['scale'], 0, 6), BusinessInput::rows($input['lines']));
    }

    public function reverse(VerifiedOrganizationalContext $scope, array $input): array
    {
        BusinessInput::fields($input, ['id', 'posting_date', 'reason']);
        $journal = $this->records->find($scope, 'journals', BusinessInput::id($input['id']), true);
        // Operational journals reverse through their domain document to preserve AP/AR and stock.
        if (!str_starts_with($journal['source'], 'manual:')) throw new BusinessViolation('DOMAIN_REVERSAL_REQUIRED');
        $date = BusinessInput::date($input['posting_date']);
        if ($date < $journal['posting_date']) throw new BusinessViolation('INVALID_POSTING_DATE');
        $lines = array_map(static fn (array $l): array => ['account' => $l['account'], 'debit_atomic' => $l['credit_atomic'], 'credit_atomic' => $l['debit_atomic']], $journal['lines']);
        return $this->post($scope, 'reversal:'.$journal['id'], $date, BusinessInput::text($input['reason'], 240), $journal['currency'], $journal['scale'], $lines, $journal['id']);
    }

    public function invoice(VerifiedOrganizationalContext $scope, array $input): array
    {
        BusinessInput::fields($input, ['kind', 'partner_id', 'receipt_id', 'reference', 'issue_date', 'due_date', 'currency', 'scale', 'total_atomic', 'description']);
        $kind = $input['kind'];
        if (!in_array($kind, ['AP', 'AR'], true)) throw new BusinessViolation('INVALID_INVOICE_KIND');
        $party = $this->records->find($scope, 'partners', BusinessInput::id($input['partner_id']), true);
        if (!$party['active'] || $party['kind'] !== ($kind === 'AP' ? 'SUPPLIER' : 'CUSTOMER')) throw new BusinessViolation('PARTNER_NOT_ACTIVE');
        $amount = BusinessInput::integer($input['total_atomic'], 1); $currency = BusinessInput::currency($input['currency']); $scale = BusinessInput::integer($input['scale'], 0, 6);
        $receiptId = null;
        if ($kind === 'AP') {
            $receipt = $this->records->find($scope, 'receipts', BusinessInput::id($input['receipt_id']), true);
            $purchase = $this->records->find($scope, 'purchases', $receipt['purchase_id']);
            if ($purchase['supplier_id'] !== $party['id'] || (string) $amount !== $receipt['total_atomic'] || $currency !== $receipt['currency'] || $scale !== $receipt['scale']) throw new BusinessViolation('INVOICE_RECEIPT_MISMATCH');
            $receiptId = $receipt['id'];
        } elseif ($input['receipt_id'] !== '') throw new BusinessViolation('INVALID_RECEIPT');
        $issued = BusinessInput::date($input['issue_date']); $due = BusinessInput::date($input['due_date']);
        if ($due < $issued) throw new BusinessViolation('INVALID_DUE_DATE');
        $invoice = $this->records->create($scope, 'invoices', ['kind' => $kind, 'partner_id' => $party['id'], 'receipt_id' => $receiptId, 'reference' => BusinessInput::text($input['reference'], 80), 'issue_date' => $issued, 'due_date' => $due, 'currency' => $currency, 'scale' => $scale, 'total_atomic' => $amount, 'paid_atomic' => 0, 'status' => 'OPEN', 'description' => BusinessInput::text($input['description'], 2000)]);
        $this->pair($scope, 'invoice:'.$invoice['id'], $issued, 'Invoice '.$invoice['reference'], $currency, $scale, $amount, $kind === 'AP' ? '2100' : '1200', $kind === 'AP' ? '2000' : '4000');
        return $invoice;
    }

    public function settle(VerifiedOrganizationalContext $scope, array $input): array
    {
        BusinessInput::fields($input, ['id', 'version', 'amount_atomic', 'paid_date', 'reference', 'cash_account']);
        $invoice = $this->records->find($scope, 'invoices', BusinessInput::id($input['id']), true);
        $amount = BusinessInput::integer($input['amount_atomic'], 1); $paid = BusinessInput::integer($invoice['paid_atomic']);
        if ($invoice['status'] === 'PAID' || $amount > BusinessInput::integer($invoice['total_atomic']) - $paid) throw new BusinessViolation('PAYMENT_EXCEEDS_BALANCE');
        $date = BusinessInput::date($input['paid_date']);
        if ($date < $invoice['issue_date']) throw new BusinessViolation('INVALID_POSTING_DATE');
        $account = BusinessInput::id($input['cash_account']);
        if (!in_array($account, ['1000', '1010'], true)) throw new BusinessViolation('INVALID_CASH_ACCOUNT');
        $settlement = $this->records->create($scope, 'settlements', ['invoice_id' => $invoice['id'], 'amount_atomic' => $amount, 'paid_date' => $date, 'reference' => BusinessInput::text($input['reference'], 120), 'cash_account' => $account]);
        $this->pair($scope, 'settlement:'.$settlement['id'], $date, 'Settlement '.$invoice['reference'], $invoice['currency'], $invoice['scale'], $amount, $invoice['kind'] === 'AP' ? '2000' : $account, $invoice['kind'] === 'AP' ? $account : '1200');
        $newPaid = BusinessInput::add($paid, $amount);
        return $this->records->update($scope, 'invoices', $invoice['id'], BusinessInput::integer($input['version'], 1), ['paid_atomic' => $newPaid, 'status' => (string) $newPaid === $invoice['total_atomic'] ? 'PAID' : 'PARTIAL']);
    }
}

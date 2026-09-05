from pathlib import Path

void_path = Path('apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php')
refund_path = Path('apps/web/app/Infrastructure/Pos/LaravelSaleCashRefundRepository.php')

void_text = void_path.read_text()
refund_text = refund_path.read_text()

void_old = """            if ($sale === null
                || ! is_string($sale->organization_id)
                || ! hash_equals($context->organizationId(), $sale->organization_id)
                || ! is_string($sale->outlet_id)
                || ! hash_equals($context->outletId(), $sale->outlet_id)) {
                throw new PosTransactionViolation();
            }

            $alreadyVoided = $this->connection->table('oneqay_pos_sale_voids')
"""
void_new = """            if ($sale === null
                || ! is_string($sale->organization_id)
                || ! hash_equals($context->organizationId(), $sale->organization_id)
                || ! is_string($sale->outlet_id)
                || ! hash_equals($context->outletId(), $sale->outlet_id)
                || ! is_string($sale->shift_id)
                || trim($sale->shift_id) === '') {
                throw new PosTransactionViolation();
            }

            $activeShift = $this->connection->table('oneqay_pos_shifts')
                ->where('tenant_id', $context->tenantId())
                ->where('shift_id', $sale->shift_id)
                ->where('organization_id', $sale->organization_id)
                ->where('outlet_id', $sale->outlet_id)
                ->where('active_slot', 1)
                ->lockForUpdate()
                ->first();

            if ($activeShift === null) {
                throw new PosTransactionViolation();
            }

            $alreadyVoided = $this->connection->table('oneqay_pos_sale_voids')
"""

refund_old = """            if ($sale === null
                || ! is_string($sale->organization_id)
                || ! hash_equals($context->organizationId(), $sale->organization_id)
                || ! is_string($sale->outlet_id)
                || ! hash_equals($context->outletId(), $sale->outlet_id)
                || ! is_string($sale->tender_category)
                || $sale->tender_category !== TenderCategory::CASH->value) {
                throw new PosTransactionViolation();
            }

            $void = $this->connection->table('oneqay_pos_sale_voids')
"""
refund_new = """            if ($sale === null
                || ! is_string($sale->organization_id)
                || ! hash_equals($context->organizationId(), $sale->organization_id)
                || ! is_string($sale->outlet_id)
                || ! hash_equals($context->outletId(), $sale->outlet_id)
                || ! is_string($sale->shift_id)
                || trim($sale->shift_id) === ''
                || ! is_string($sale->tender_category)
                || $sale->tender_category !== TenderCategory::CASH->value) {
                throw new PosTransactionViolation();
            }

            $activeShift = $this->connection->table('oneqay_pos_shifts')
                ->where('tenant_id', $context->tenantId())
                ->where('shift_id', $sale->shift_id)
                ->where('organization_id', $sale->organization_id)
                ->where('outlet_id', $sale->outlet_id)
                ->where('active_slot', 1)
                ->lockForUpdate()
                ->first();

            if ($activeShift === null) {
                throw new PosTransactionViolation();
            }

            $void = $this->connection->table('oneqay_pos_sale_voids')
"""

if void_new in void_text and refund_new in refund_text:
    print('Sprint90 hardening already materialized.')
    raise SystemExit(0)

if void_text.count(void_old) != 1:
    raise SystemExit('Void repository exact insertion anchor mismatch.')
if refund_text.count(refund_old) != 1:
    raise SystemExit('Refund repository exact insertion anchor mismatch.')

void_path.write_text(void_text.replace(void_old, void_new, 1))
refund_path.write_text(refund_text.replace(refund_old, refund_new, 1))

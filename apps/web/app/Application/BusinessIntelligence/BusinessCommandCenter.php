<?php

declare(strict_types=1);

namespace App\Application\BusinessIntelligence;

use App\Application\Pos\PosOperationalSalesSummary;

// Author by Lab | zefry.
// Projected from canonical tenant/outlet-scoped, permission-checked POS read models.
// No AI claims, extrapolation, financial mutation or cross-tenant aggregation.
final class BusinessCommandCenter
{
    public static function reporting(?PosOperationalSalesSummary $summary, string $status): array
    {
        if ($status !== 'available' || $summary === null) {
            return [
                'status' => $status === 'restricted' ? 'restricted' : 'unavailable',
                'counters' => null,
                'gross_totals' => [],
                'recent_sales' => [],
            ];
        }

        return [
            'status' => 'available',
            'counters' => [
                'completed_sales' => $summary->completedSales(),
                'voided_sales' => $summary->voidedSales(),
                'cash_refunded_sales' => $summary->cashRefundedSales(),
            ],
            'gross_totals' => $summary->grossTotals(),
            'recent_sales' => $summary->recentSales(),
        ];
    }

    /**
     * A transparent business capability directory, never a permissions grant.
     * Only destinations already authorized AND delivered by the existing controller
     * become clickable. Future products must have real code before activation.
     *
     * @param list<array{key:string,title:string,description:string,url:string,category:string}> $destinations
     * @return list<array{key:string,title:string,description:string,status:string,url:?string,group:string}>
     */
    public static function capabilities(array $destinations): array
    {
        $authorized = [];
        foreach ($destinations as $destination) {
            if (isset($destination['key'], $destination['url'])
                && is_string($destination['key'])
                && is_string($destination['url'])
                && str_starts_with($destination['url'], '/pos/')) {
                $authorized[$destination['key']] = $destination['url'];
            }
        }

        $definitions = [
            ['commerce', 'Commerce & POS', 'Sales, receipts and cashier operations', 'Operations', ['cashier']],
            ['inventory', 'Inventory Management', 'Catalog, stock receiving and accountability', 'Operations', ['inventory_accountability', 'inventory_replenishment', 'catalog_inventory']],
            ['cash_control', 'Cash & Shift Control', 'Shift preparation, reconciliation and closing', 'Operations', ['cash_variance_reconciliation', 'shift_close', 'shift_start']],
            ['reports', 'Business Reports', 'Scoped operational sales and product performance', 'Intelligence', ['sales_summary', 'product_sales_performance']],
            ['procurement', 'Procurement & Suppliers', 'Purchase orders, supplier lifecycle and receiving', 'Management', ['business_purchases', 'business_suppliers']],
            ['crm', 'CRM & Customer Intelligence', 'Customer records, consent and interactions', 'Management', ['business_customers']],
            ['accounting', 'Finance & Accounting', 'Balanced journals, payables and receivables', 'Management', ['business_finance']],
            ['people', 'People & HR', 'Employees, attendance and leave approvals', 'Management', ['business_people']],
            ['ai', 'AI Business Intelligence', 'Forecasts, recommendations and governed automation', 'Intelligence', []],
            ['ecosystem', 'Integrations & Marketplace', 'Public APIs, partners and extensions', 'Platform', []],
        ];

        $result = [];
        foreach ($definitions as [$key, $title, $description, $group, $candidateKeys]) {
            $url = null;
            foreach ($candidateKeys as $candidateKey) {
                if (isset($authorized[$candidateKey])) {
                    $url = $authorized[$candidateKey];
                    break;
                }
            }
            $result[] = [
                'key' => $key,
                'title' => $title,
                'description' => $description,
                'group' => $group,
                'status' => $url === null ? 'not_available' : 'available',
                'url' => $url,
            ];
        }

        return $result;
    }
}

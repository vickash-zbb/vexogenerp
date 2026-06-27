<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Invoice
{
    public static function all(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        return Database::fetchAll(
            "SELECT i.*, c.company_name as client_name, p.name as project_name
             FROM invoices i JOIN clients c ON c.id = i.client_id
             LEFT JOIN projects p ON p.id = i.project_id
             ORDER BY i.invoice_date DESC LIMIT {$perPage} OFFSET {$offset}"
        );
    }

    public static function find(int $id): ?array
    {
        $invoice = Database::fetch(
            'SELECT i.*, c.company_name, c.contact_person, c.address as client_address, c.city as client_city,
                    c.state as client_state, c.pincode as client_pincode, c.gst_number as client_gst, c.email, c.phone,
                    p.project_code, p.name as project_name, p.category as service_type, p.expected_delivery,
                    p.status as project_status, e.name as account_manager,
                    COALESCE((SELECT SUM(py.amount) FROM payments py WHERE py.invoice_id = i.id AND py.payment_stage = "advance"), 0) as advance_paid
             FROM invoices i
             JOIN clients c ON c.id = i.client_id
             LEFT JOIN projects p ON p.id = i.project_id
             LEFT JOIN employees e ON e.id = p.assigned_employee_id
             WHERE i.id = ?',
            [$id]
        );
        if ($invoice) {
            if (!empty($invoice['billing_address'])) {
                $invoice['client_address'] = $invoice['billing_address'];
            }
            $invoice['items'] = Database::fetchAll(
                'SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY position',
                [$id]
            );
        }
        return $invoice;
    }

    public static function create(array $data, array $items): int
    {
        Database::beginTransaction();
        try {
            $id = Database::insert('invoices', $data);
            foreach ($items as $i => $item) {
                $item['invoice_id'] = $id;
                $item['position'] = $i;
                Database::insert('invoice_items', $item);
            }
            Database::commit();
            return $id;
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public static function calculateTotals(array $items, float $gstRate, float $discountPercent): array
    {
        $subtotal = 0;
        foreach ($items as &$item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $rate = (float) ($item['rate'] ?? 0);
            $item['amount'] = $qty * $rate;
            $subtotal += $item['amount'];
        }
        unset($item);
        $discount = $subtotal * ($discountPercent / 100);
        $taxable = $subtotal - $discount;
        $gst = $taxable * ($gstRate / 100);
        $total = $taxable + $gst;
        return compact('subtotal', 'discount', 'gst', 'total', 'items');
    }
}

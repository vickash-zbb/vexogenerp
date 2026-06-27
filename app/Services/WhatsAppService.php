<?php

declare(strict_types=1);

namespace App\Services;

class WhatsAppService
{
    public static function link(string $phone, string $message): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '91' . substr($phone, 1);
        } elseif (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }
        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
    }

    public static function invoiceMessage(array $invoice, array $settings): string
    {
        $company = $settings['company_name'] ?? 'Vexogen';
        $url = url('documents/invoice/' . $invoice['id'] . '/pdf');
        return "Hello from {$company}!\n\n"
            . "Invoice: {$invoice['invoice_number']}\n"
            . "Amount: " . format_money((float) $invoice['total_amount']) . "\n"
            . "Due: " . format_date($invoice['due_date']) . "\n\n"
            . "View & Download Invoice PDF here:\n{$url}\n\n"
            . "Please let us know if you have any questions.";
    }

    public static function quotationMessage(array $quote, array $settings): string
    {
        $company = $settings['company_name'] ?? 'Vexogen';
        $url = url('documents/quotation/' . $quote['id'] . '/pdf');
        return "Hello from {$company}!\n\n"
            . "Quotation: {$quote['quote_number']}\n"
            . "Amount: " . format_money((float) $quote['total_amount']) . "\n"
            . "Valid until: " . format_date($quote['valid_until']) . "\n\n"
            . "View & Download Quotation PDF here:\n{$url}\n\n"
            . "Reply to accept or discuss further.";
    }
}

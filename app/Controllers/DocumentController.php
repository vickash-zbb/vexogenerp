<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Services\MailService;
use App\Services\PdfService;
use App\Services\WhatsAppService;

class DocumentController extends Controller
{
    public function invoicePdf(string $id): void
    {
        $invoice = Invoice::find((int) $id);
        if (!$invoice) {
            http_response_code(404);
            die('Invoice not found');
        }
        $settings = PdfService::companySettings();
        PdfService::render('invoice', compact('invoice', 'settings'), $invoice['invoice_number'] . '.pdf');
    }

    public function quotationPdf(string $id): void
    {
        $quote = Quotation::find((int) $id);
        if (!$quote) {
            http_response_code(404);
            die('Quotation not found');
        }
        $settings = PdfService::companySettings();
        PdfService::render('quotation', compact('quote', 'settings'), $quote['quote_number'] . '.pdf');
    }

    public function emailInvoice(string $id): void
    {
        $invoice = Invoice::find((int) $id);
        if (!$invoice) {
            $this->json(['success' => false, 'message' => 'Invoice not found'], 404);
        }
        $email = $this->input('email') ?: ($invoice['email'] ?? null);
        if (!$email) {
            $this->json(['success' => false, 'message' => 'Client email is required.'], 422);
        }
        $settings = PdfService::companySettings();
        $tmp = $this->generateTempPdf('invoice', compact('invoice', 'settings'), $invoice['invoice_number']);
        $sent = MailService::send(
            $email,
            'Invoice ' . $invoice['invoice_number'] . ' from ' . ($settings['company_name'] ?? 'Vexogen'),
            MailService::invoiceEmailBody($invoice, $settings),
            $tmp,
            $invoice['invoice_number'] . '.pdf'
        );
        @unlink($tmp);
        if ($sent) {
            $this->json(['success' => true, 'message' => 'Invoice emailed to ' . $email]);
        }
        $this->json(['success' => false, 'message' => 'Failed to send email. Check SMTP settings.'], 500);
    }

    public function whatsappInvoice(string $id): void
    {
        $invoice = Invoice::find((int) $id);
        if (!$invoice) {
            $this->json(['success' => false, 'message' => 'Invoice not found'], 404);
        }
        $phone = $this->input('phone');
        if (!$phone) {
            $client = \App\Models\Client::find((int) $invoice['client_id']);
            $phone = $client['phone'] ?? '';
        }
        if (!$phone) {
            $this->json(['success' => false, 'message' => 'Client phone number required.'], 422);
        }
        $settings = PdfService::companySettings();
        $msg = WhatsAppService::invoiceMessage($invoice, $settings);
        $link = WhatsAppService::link($phone, $msg);
        $this->json(['success' => true, 'url' => $link]);
    }

    public function emailQuotation(string $id): void
    {
        $quote = Quotation::find((int) $id);
        if (!$quote) {
            $this->json(['success' => false, 'message' => 'Quotation not found'], 404);
        }
        $client = \App\Models\Client::find((int) $quote['client_id']);
        $email = $this->input('email') ?: ($client['email'] ?? null);
        if (!$email) {
            $this->json(['success' => false, 'message' => 'Client email is required.'], 422);
        }
        $settings = PdfService::companySettings();
        $tmp = $this->generateTempPdf('quotation', ['quote' => $quote, 'settings' => $settings], $quote['quote_number']);
        $sent = MailService::send(
            $email,
            'Quotation ' . $quote['quote_number'] . ' from ' . ($settings['company_name'] ?? 'Vexogen'),
            MailService::quotationEmailBody($quote, $settings),
            $tmp,
            $quote['quote_number'] . '.pdf'
        );
        @unlink($tmp);
        if ($sent) {
            $this->json(['success' => true, 'message' => 'Quotation emailed to ' . $email]);
        }
        $this->json(['success' => false, 'message' => 'Failed to send email. Check SMTP settings.'], 500);
    }

    public function whatsappQuotation(string $id): void
    {
        $quote = Quotation::find((int) $id);
        if (!$quote) {
            $this->json(['success' => false, 'message' => 'Quotation not found'], 404);
        }
        $client = \App\Models\Client::find((int) $quote['client_id']);
        $phone = $this->input('phone') ?: ($client['phone'] ?? '');
        if (!$phone) {
            $this->json(['success' => false, 'message' => 'Client phone required.'], 422);
        }
        $settings = PdfService::companySettings();
        $link = WhatsAppService::link($phone, WhatsAppService::quotationMessage($quote, $settings));
        $this->json(['success' => true, 'url' => $link]);
    }

    private function generateTempPdf(string $view, array $data, string $basename): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require APP_PATH . '/Views/documents/' . $view . '.php';
        $html = ob_get_clean();

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $tmp = sys_get_temp_dir() . '/' . $basename . '_' . uniqid() . '.pdf';
        file_put_contents($tmp, $dompdf->output());
        return $tmp;
    }
}

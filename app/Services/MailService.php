<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PHPMailer\PHPMailer\Exception as MailerException;
use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    public static function send(string $to, string $subject, string $body, ?string $attachmentPath = null, ?string $attachmentName = null): bool
    {
        if (!load_composer()) {
            error_log('Mail error: Composer vendor folder is missing. Run composer install and upload vendor/.');
            return false;
        }

        $settings = Database::fetch('SELECT * FROM company_settings LIMIT 1') ?: [];
        $mailConfig = require CONFIG_PATH . '/mail.php';

        $mail = new PHPMailer(true);
        try {
            $host = $settings['smtp_host'] ?? $mailConfig['host'];
            if ($host) {
                $mail->isSMTP();
                $mail->Host = $host;
                $mail->SMTPAuth = !empty($settings['smtp_user']);
                $mail->Username = $settings['smtp_user'] ?? $mailConfig['username'];
                $mail->Password = $settings['smtp_pass'] ?? $mailConfig['password'];
                $mail->SMTPSecure = $settings['smtp_encryption'] ?? $mailConfig['encryption'];
                $mail->Port = (int) ($settings['smtp_port'] ?? $mailConfig['port']);
            } else {
                $mail->isMail();
            }

            $fromEmail = $settings['email'] ?? $mailConfig['from_email'];
            $fromName = $settings['company_name'] ?? $mailConfig['from_name'];
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);

            if ($attachmentPath && is_file($attachmentPath)) {
                $mail->addAttachment($attachmentPath, $attachmentName ?? basename($attachmentPath));
            }

            return $mail->send();
        } catch (MailerException $e) {
            error_log('Mail error: ' . $e->getMessage());
            return false;
        }
    }

    public static function invoiceEmailBody(array $invoice, array $settings): string
    {
        $company = e($settings['company_name'] ?? 'Vexogen');
        $number = e($invoice['invoice_number']);
        $total = format_money((float) $invoice['total_amount']);
        $due = format_date($invoice['due_date']);
        return "<p>Dear {$invoice['contact_person']},</p>
            <p>Please find attached invoice <strong>{$number}</strong> from {$company}.</p>
            <p><strong>Total:</strong> {$total}<br><strong>Due Date:</strong> {$due}</p>
            <p>Thank you for your business.</p>
            <p>— {$company}</p>";
    }

    public static function quotationEmailBody(array $quote, array $settings): string
    {
        $company = e($settings['company_name'] ?? 'Vexogen');
        $number = e($quote['quote_number']);
        $total = format_money((float) $quote['total_amount']);
        return "<p>Dear Client,</p>
            <p>Please find our quotation <strong>{$number}</strong> from {$company}.</p>
            <p><strong>Total:</strong> {$total}</p>
            <p>We look forward to working with you.</p>
            <p>— {$company}</p>";
    }
}

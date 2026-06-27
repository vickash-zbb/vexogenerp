<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class PdfService
{
    public static function render(string $view, array $data, string $filename): void
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require APP_PATH . '/Views/documents/' . $view . '.php';
        $html = ob_get_clean();

        $printInjection = '
        <div id="print-header-actions" style="background:#f3f4f6; padding:10px 20px; border-bottom:1px solid #d1d5db; text-align:center; font-family:sans-serif; margin-bottom: 20px;">
            <button onclick="window.print()" style="background:#3b82f6; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-weight:bold; cursor:pointer; font-size:13px; margin-right: 10px;">🖨️ Print / Save as PDF</button>
            <button onclick="window.close()" style="background:#e5e7eb; color:#374151; border:1px solid #d1d5db; padding:8px 16px; border-radius:6px; cursor:pointer; font-size:13px;">❌ Close Window</button>
        </div>
        <style>
            @media print {
                #print-header-actions { display: none !important; }
            }
        </style>
        <script>
            window.onload = function() {
                window.print();
            };
        </script>
        ';

        $html = str_replace('<body>', '<body>' . $printInjection, $html);
        echo $html;
        exit;
    }

    public static function companySettings(): array
    {
        return Database::fetch('SELECT * FROM company_settings LIMIT 1') ?: [];
    }
}

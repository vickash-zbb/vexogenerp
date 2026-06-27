<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    public static function render(string $view, array $data, string $filename): void
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require APP_PATH . '/Views/documents/' . $view . '.php';
        $html = ob_get_clean();
        if (!extension_loaded('gd')) {
            $html = (string) preg_replace('/<img\b[^>]*>/i', '', $html);
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    public static function companySettings(): array
    {
        return Database::fetch('SELECT * FROM company_settings LIMIT 1') ?: [];
    }
}

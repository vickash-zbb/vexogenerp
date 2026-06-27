<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Client;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function index(): void
    {
        $preview = null;
        if ($this->input('preview')) {
            $preview = Invoice::find((int) $this->input('preview'));
        } elseif ($invoices = Invoice::all()) {
            $preview = Invoice::find((int) $invoices[0]['id']);
        }
        $settings = Database::fetch('SELECT * FROM company_settings LIMIT 1');
        $this->view('invoices/index', [
            'title' => 'Invoices',
            'page' => 'invoices',
            'invoices' => Invoice::all(),
            'preview' => $preview,
            'settings' => $settings,
            'clients' => Client::dropdown(),
        ]);
    }
}

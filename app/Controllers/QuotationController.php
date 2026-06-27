<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Client;
use App\Models\Quotation;

class QuotationController extends Controller
{
    public function index(): void
    {
        $preview = $this->input('preview') ? Quotation::find((int) $this->input('preview')) : null;
        $settings = \App\Core\Database::fetch('SELECT * FROM company_settings LIMIT 1');
        $this->view('quotations/index', [
            'title' => 'Quotations',
            'page' => 'quotations',
            'quotations' => Quotation::all(),
            'clients' => Client::dropdown(),
            'preview' => $preview,
            'settings' => $settings,
        ]);
    }
}

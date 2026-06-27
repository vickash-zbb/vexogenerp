<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;

class PaymentController extends Controller
{
    public function index(): void
    {
        $filters = array_filter([
            'client_id' => $this->input('client_id'),
            'invoice_id' => $this->input('invoice_id'),
            'project_id' => $this->input('project_id'),
            'method' => $this->input('method'),
            'stage' => $this->input('stage'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
            'search' => $this->input('q'),
        ], fn($value) => $value !== null && $value !== '');
        $this->view('payments/index', [
            'title' => 'Payments',
            'page' => 'payments',
            'payments' => Payment::all($filters, 1, 200),
            'stats' => Payment::stats(),
            'invoices' => Invoice::all(1, 100),
            'clients' => Client::dropdown(),
            'projects' => Project::all([], 1, 200),
            'filters' => $filters,
        ]);
    }
}

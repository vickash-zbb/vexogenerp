<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Client;
use App\Models\Communication;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Project;

class ClientController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int) $this->input('page', 1));
        $filters = [
            'status' => $this->input('status'),
            'industry' => $this->input('industry'),
            'search' => $this->input('q'),
        ];
        $this->view('clients/index', [
            'title' => 'Clients',
            'page' => 'clients',
            'clients' => Client::all(array_filter($filters), $page),
            'total' => Client::count(array_filter($filters)),
            'currentPage' => $page,
            'filters' => $filters,
        ]);
    }

    public function show(string $id): void
    {
        $client = Client::find((int) $id);
        if (!$client) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Not Found']);
            return;
        }
        $projects = Project::all(['client_id' => (int) $id]);
        $payments = Payment::all(['client_id' => (int) $id]);
        $communications = Communication::forClient((int) $id);
        $this->view('clients/show', [
            'title' => $client['company_name'],
            'page' => 'clients',
            'client' => $client,
            'projects' => $projects,
            'payments' => $payments,
            'communications' => $communications,
        ]);
    }
}

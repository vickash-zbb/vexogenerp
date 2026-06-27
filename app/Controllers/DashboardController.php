<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Dashboard;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index(): void
    {
        $filters = [
            'month' => $this->input('month', date('Y-m')),
            'status' => $this->input('status'),
            'priority' => $this->input('priority')
        ];

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'page' => 'dashboard',
            'filters' => $filters,
            'stats' => Dashboard::stats($filters),
            'chartData' => Dashboard::chartData(),
            'statusChart' => Dashboard::projectStatusChart($filters),
            'recentProjects' => Project::recent(5, $filters),
            'pendingTasks' => Task::pending(5, $filters),
            'recentPayments' => Payment::recent(5, $filters),
        ]);
    }
}

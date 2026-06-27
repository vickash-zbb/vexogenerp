<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;

class TaskController extends Controller
{
    public function index(): void
    {
        $filters = ['status' => $this->input('status')];
        $this->view('tasks/index', [
            'title' => 'Tasks',
            'page' => 'tasks',
            'tasks' => Task::all(array_filter($filters)),
            'employees' => Employee::dropdown(),
            'projects' => Project::all([], 1, 100),
        ]);
    }
}

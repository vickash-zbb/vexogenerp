<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index(): void
    {
        $this->view('employees/index', [
            'title' => 'Employees',
            'page' => 'employees',
            'employees' => Employee::all(),
        ]);
    }
}

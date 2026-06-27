<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Expense;

class ExpenseController extends Controller
{
    public function index(): void
    {
        $month = $this->input('month', date('Y-m'));
        $category = $this->input('category');
        $filters = array_filter(['month' => $month, 'category' => $category]);
        $byCategory = Expense::byCategory($month);
        $catTotals = ['salary' => 0, 'office_rent' => 0, 'software' => 0, 'other' => 0];
        foreach ($byCategory as $row) {
            if ($row['category'] === 'salary') {
                $catTotals['salary'] = (float) $row['total'];
            } elseif ($row['category'] === 'office_rent') {
                $catTotals['office_rent'] = (float) $row['total'];
            } elseif ($row['category'] === 'software') {
                $catTotals['software'] = (float) $row['total'];
            } else {
                $catTotals['other'] += (float) $row['total'];
            }
        }
        $this->view('expenses/index', [
            'title' => 'Expenses',
            'page' => 'expenses',
            'expenses' => Expense::all($filters),
            'monthTotal' => Expense::monthTotal($month),
            'catTotals' => $catTotals,
            'month' => $month,
        ]);
    }
}

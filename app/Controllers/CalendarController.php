<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class CalendarController extends Controller
{
    public function index(): void
    {
        $year = (int) $this->input('year', date('Y'));
        $month = (int) $this->input('month', date('n'));
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));
        $events = Database::fetchAll(
            'SELECT * FROM calendar_events WHERE event_date BETWEEN ? AND ? ORDER BY event_date',
            [$start, $end]
        );
        $this->view('calendar/index', [
            'title' => 'Calendar',
            'page' => 'calendar',
            'events' => $events,
            'year' => $year,
            'month' => $month,
        ]);
    }
}

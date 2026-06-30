<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class LandingController extends Controller
{
    public function index(): void
    {
        $this->view('landing/index', [
            'title' => 'Vexogen Agency ERP & CRM',
            'isLoggedIn' => Auth::check(),
        ], null);
    }
}

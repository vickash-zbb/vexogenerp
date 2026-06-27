<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect('');
        }
        $this->view('auth/login', ['title' => 'Sign In'], null);
    }

    public function login(): void
    {
        CSRF::verifyRequest();
        $email = trim((string) $this->input('email'));
        $password = (string) $this->input('password');
        if (Auth::attempt($email, $password)) {
            redirect('');
        }
        $_SESSION['_old'] = ['email' => $email];
        flash('error', 'Invalid email or password.');
        redirect('login');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('login');
    }
}

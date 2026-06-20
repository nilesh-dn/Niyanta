<?php
namespace Niyanta\Controllers;

use Niyanta\Core\Auth;
use Niyanta\Core\Csrf;
use Niyanta\Core\Flash;
use Niyanta\Core\Log;
use Niyanta\Core\View;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }
        View::render('auth.login', [], 'auth');
    }

    public function login(): void
    {
        Csrf::check();
        $email = trim((string) request('email', ''));
        $password = (string) request('password', '');

        $_SESSION['_old'] = ['email' => $email];

        if (Auth::attempt($email, $password)) {
            unset($_SESSION['_old']);
            Log::record('auth.login', 'Successful sign-in');
            redirect('/dashboard');
        }

        Flash::error('Invalid email or password.');
        redirect('/login');
    }

    public function logout(): void
    {
        Log::record('auth.logout', 'Signed out');
        Auth::logout();
        Flash::success('You have been signed out.');
        redirect('/login');
    }
}

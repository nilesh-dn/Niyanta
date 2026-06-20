<?php
namespace Niyanta\Controllers;

use Niyanta\Core\Auth;
use Niyanta\Core\Database;
use Niyanta\Core\Flash;
use Niyanta\Core\Theme;
use Niyanta\Core\View;

class ProfileController
{
    public function show(): void
    {
        View::render('profile.show', ['user' => Auth::user()], 'app');
    }

    public function update(): void
    {
        $name = trim((string) request('name', ''));
        if ($name === '') {
            Flash::error('Name cannot be empty.');
            redirect('/profile');
        }
        Database::query('UPDATE users SET name = ? WHERE id = ?', [$name, Auth::id()]);

        $password = (string) request('password', '');
        if ($password !== '') {
            if (strlen($password) < 8) {
                Flash::error('Password must be at least 8 characters.');
                redirect('/profile');
            }
            Database::query(
                'UPDATE users SET password_hash = ? WHERE id = ?',
                [password_hash($password, PASSWORD_DEFAULT), Auth::id()]
            );
        }
        Flash::success('Profile updated.');
        redirect('/profile');
    }

    /** Persist the dark/light theme preference (also called via fetch). */
    public function theme(): void
    {
        Theme::set((string) request('theme', 'light'));
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch') {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            return;
        }
        redirect('/profile');
    }
}

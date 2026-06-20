<?php
namespace Niyanta\Controllers;

use Niyanta\Core\Dashboard;
use Niyanta\Core\View;

class DashboardController
{
    public function index(): void
    {
        View::render('dashboard.index', [
            'widgets' => Dashboard::visible(),
        ], 'app');
    }
}

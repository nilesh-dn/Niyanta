<?php
namespace Niyanta\Controllers;

use Niyanta\Core\Branding;
use Niyanta\Core\Flash;
use Niyanta\Core\Log;
use Niyanta\Core\View;

class SettingsController
{
    public function index(): void
    {
        View::render('settings.index', [
            'company'             => Branding::companyName(),
            'managerViewSalary'   => (bool) Branding::setting('manager_can_view_salary', false),
        ], 'app');
    }

    public function update(): void
    {
        Branding::set('manager_can_view_salary', request('manager_can_view_salary') ? true : false);
        Log::record('settings.update', 'Updated general settings');
        Flash::success('Settings saved.');
        redirect('/settings');
    }

    public function logs(): void
    {
        View::render('settings.logs', [
            'logs' => Log::recent(),
        ], 'app');
    }
}

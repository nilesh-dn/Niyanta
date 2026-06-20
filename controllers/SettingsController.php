<?php
namespace Niyanta\Controllers;

use Niyanta\Core\Branding;
use Niyanta\Core\Flash;
use Niyanta\Core\Log;
use Niyanta\Core\Permissions;
use Niyanta\Core\View;

class SettingsController
{
    public function index(): void
    {
        // Users without general-settings access land on the first section they can use.
        if (!Permissions::can('manage_settings')) {
            $fallbacks = [
                'manage_branding' => '/settings/branding',
                'manage_palettes' => '/settings/branding',
                'manage_plugins'  => '/settings/plugins',
                'view_logs'       => '/settings/logs',
            ];
            foreach ($fallbacks as $perm => $route) {
                if (Permissions::can($perm)) {
                    redirect($route);
                }
            }
            Permissions::require('manage_settings'); // none matched -> 403
        }

        View::render('settings.index', [
            'title'             => 'Settings',
            'company'           => Branding::companyName(),
            'managerViewSalary' => (bool) Branding::setting('manager_can_view_salary', false),
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
            'title' => 'System Logs',
            'logs'  => Log::recent(),
        ], 'app');
    }
}

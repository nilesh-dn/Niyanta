<?php
/**
 * Employee Management — plugin entry point.
 *
 * Returns the register() closure that the core PluginManager runs on every
 * request while the plugin is active. It wires routes, the sidebar menu,
 * permissions and dashboard widgets into the core with no core modifications.
 */

use Niyanta\Core\Router;
use Niyanta\Core\Menu;
use Niyanta\Core\Permissions;
use Niyanta\Core\Dashboard;
use Niyanta\Core\Database;

require_once __DIR__ . '/src/EmployeeController.php';

use EmpMgmt\EmployeeController;

return function (): void {

    // --- Permissions (idempotent; attaches to core roles) ---
    Permissions::register('employees_view',   'View employees',   ['manager']);
    Permissions::register('employees_add',    'Add employees',    ['manager']);
    Permissions::register('employees_edit',   'Edit employees',   ['manager']);
    Permissions::register('employees_delete', 'Delete employees', []); // super admin only by default

    // --- Routes ---
    Router::get('/employees',                 [EmployeeController::class, 'index'],       ['permission' => 'employees_view']);
    Router::get('/employees/create',          [EmployeeController::class, 'create'],      ['permission' => 'employees_add']);
    Router::post('/employees/store',          [EmployeeController::class, 'store'],       ['permission' => 'employees_add']);
    Router::get('/employees/profile',         [EmployeeController::class, 'profile'],     ['permission' => 'employees_view']);
    Router::get('/employees/edit',            [EmployeeController::class, 'edit'],        ['permission' => 'employees_edit']);
    Router::post('/employees/update',         [EmployeeController::class, 'update'],      ['permission' => 'employees_edit']);
    Router::post('/employees/delete',         [EmployeeController::class, 'destroy'],     ['permission' => 'employees_delete']);
    Router::get('/employees/export',          [EmployeeController::class, 'exportCsv'],   ['permission' => 'employees_view']);
    Router::get('/employees/import',          [EmployeeController::class, 'importForm'],  ['permission' => 'employees_add']);
    Router::post('/employees/import',         [EmployeeController::class, 'importCsv'],   ['permission' => 'employees_add']);
    Router::post('/employees/document/delete', [EmployeeController::class, 'deleteDocument'], ['permission' => 'employees_edit']);

    // --- Sidebar menu ---
    Menu::add([
        'label' => 'Employees', 'route' => '/employees', 'icon' => 'bi-people',
        'permission' => 'employees_view', 'order' => 10,
    ]);

    // --- Dashboard widgets (override core placeholders that share a key) ---
    Dashboard::add([
        'key' => 'total_employees', 'title' => 'Total Employees', 'icon' => 'bi-people',
        'order' => 10, 'permission' => 'employees_view',
        'render' => static function (): string {
            $total = (int) Database::scalar('SELECT COUNT(*) FROM employees');
            $active = (int) Database::scalar("SELECT COUNT(*) FROM employees WHERE status = 'active'");
            return '<span>' . $total . '</span>'
                . '<div class="text-muted small">' . $active . ' active</div>';
        },
    ]);

    Dashboard::add([
        'key' => 'employees_new_joinees', 'title' => 'New Joinees', 'icon' => 'bi-person-plus',
        'order' => 15, 'permission' => 'employees_view',
        'render' => static function (): string {
            $count = (int) Database::scalar(
                'SELECT COUNT(*) FROM employees WHERE date_of_joining >= (CURDATE() - INTERVAL 30 DAY)'
            );
            return '<span>' . $count . '</span>'
                . '<div class="text-muted small">joined in last 30 days</div>';
        },
    ]);

    Dashboard::add([
        'key' => 'upcoming_birthdays', 'title' => 'Upcoming Birthdays', 'icon' => 'bi-gift',
        'order' => 50, 'permission' => 'employees_view',
        'render' => static function (): string {
            // Next 30 days, ignoring year (birthday recurrence).
            $rows = Database::all(
                "SELECT full_name, date_of_birth,
                        DATE_FORMAT(date_of_birth, '%m-%d') AS md
                 FROM employees
                 WHERE date_of_birth IS NOT NULL"
            );
            $today = new DateTimeImmutable('today');
            $upcoming = [];
            foreach ($rows as $r) {
                [$m, $d] = array_pad(explode('-', $r['md']), 2, '01');
                $next = DateTimeImmutable::createFromFormat('Y-m-d', $today->format('Y') . "-{$m}-{$d}");
                if (!$next) { continue; }
                if ($next < $today) { $next = $next->modify('+1 year'); }
                $days = (int) $today->diff($next)->format('%a');
                if ($days <= 30) {
                    $upcoming[] = ['name' => $r['full_name'], 'date' => $next, 'days' => $days];
                }
            }
            usort($upcoming, static fn($a, $b) => $a['days'] <=> $b['days']);
            if (!$upcoming) {
                return '<span>0</span><div class="text-muted small">none in next 30 days</div>';
            }
            $list = '';
            foreach (array_slice($upcoming, 0, 4) as $u) {
                $list .= '<div class="d-flex justify-content-between small">'
                    . '<span>' . e($u['name']) . '</span>'
                    . '<span class="text-muted">' . e($u['date']->format('d M')) . '</span></div>';
            }
            return '<span>' . count($upcoming) . '</span>'
                . '<div class="mt-1">' . $list . '</div>';
        },
    ]);
};

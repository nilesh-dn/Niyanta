<?php
/**
 * Salary & Performance Management — plugin entry point.
 *
 * Returns the register() closure run by the core PluginManager on every request
 * while active. Wires permissions, routes, menu items and dashboard widgets
 * without any core modification. Fully self-contained (owns its own tables).
 */

use Niyanta\Core\Router;
use Niyanta\Core\Menu;
use Niyanta\Core\Permissions;
use Niyanta\Core\Dashboard;
use Niyanta\Core\Database;

require_once __DIR__ . '/src/Calc.php';
require_once __DIR__ . '/src/Pdf.php';
require_once __DIR__ . '/src/Mailer.php';
require_once __DIR__ . '/src/AppolyeClient.php';
require_once __DIR__ . '/src/SalaryController.php';
require_once __DIR__ . '/src/PerformanceController.php';

use SalaryPerf\SalaryController;
use SalaryPerf\PerformanceController;
use SalaryPerf\Calc;

return function (): void {

    // --- Permissions ---
    Permissions::register('salary_view',      'View salaries and slips', ['manager']);
    Permissions::register('salary_edit',      'Edit salary profiles and attendance', []);
    Permissions::register('salary_process',   'Process and pay salary slips', []);
    Permissions::register('performance_view', 'View performance reports', ['manager']);

    // --- Salary routes ---
    Router::get('/salary',                    [SalaryController::class, 'profiles'],       ['permission' => 'salary_view']);
    Router::get('/salary/profile/create',     [SalaryController::class, 'profileForm'],    ['permission' => 'salary_edit']);
    Router::post('/salary/profile/store',     [SalaryController::class, 'storeProfile'],   ['permission' => 'salary_edit']);
    Router::get('/salary/profile/edit',       [SalaryController::class, 'profileForm'],    ['permission' => 'salary_edit']);
    Router::post('/salary/profile/update',    [SalaryController::class, 'updateProfile'],  ['permission' => 'salary_edit']);
    Router::post('/salary/profile/delete',    [SalaryController::class, 'deleteProfile'],  ['permission' => 'salary_edit']);

    Router::get('/salary/attendance',         [SalaryController::class, 'attendanceForm'], ['permission' => 'salary_edit']);
    Router::post('/salary/attendance/save',   [SalaryController::class, 'saveAttendance'], ['permission' => 'salary_edit']);
    Router::post('/salary/attendance/sync',   [SalaryController::class, 'syncAttendance'], ['permission' => 'salary_edit']);

    Router::get('/salary/slips',              [SalaryController::class, 'slips'],          ['permission' => 'salary_view']);
    Router::get('/salary/slip/create',        [SalaryController::class, 'slipForm'],       ['permission' => 'salary_process']);
    Router::post('/salary/slip/generate',     [SalaryController::class, 'generateSlip'],   ['permission' => 'salary_process']);
    Router::get('/salary/slip/view',          [SalaryController::class, 'slipView'],       ['permission' => 'salary_view']);
    Router::get('/salary/slip/pdf',           [SalaryController::class, 'slipPdf'],        ['permission' => 'salary_view']);
    Router::post('/salary/slip/email',        [SalaryController::class, 'slipEmail'],      ['permission' => 'salary_process']);
    Router::post('/salary/slip/status',       [SalaryController::class, 'slipStatus'],     ['permission' => 'salary_process']);

    Router::get('/salary/integration',        [SalaryController::class, 'integration'],    ['permission' => 'salary_edit']);
    Router::post('/salary/integration/save',  [SalaryController::class, 'saveIntegration'],['permission' => 'salary_edit']);
    Router::post('/salary/integration/sync',  [SalaryController::class, 'integrationSync'],['permission' => 'salary_edit']);

    // --- Performance routes ---
    Router::get('/salary/performance',           [PerformanceController::class, 'index'],     ['permission' => 'performance_view']);
    Router::post('/salary/performance/calculate', [PerformanceController::class, 'calculate'], ['permission' => 'salary_edit']);

    // --- Menu ---
    Menu::add(['label' => 'Salary', 'route' => '/salary', 'icon' => 'bi-cash-stack', 'permission' => 'salary_view', 'order' => 20]);
    Menu::add(['label' => 'Performance', 'route' => '/salary/performance', 'icon' => 'bi-graph-up-arrow', 'permission' => 'performance_view', 'order' => 21]);

    // --- Dashboard widgets ---
    $month = Calc::currentMonth();

    Dashboard::add([
        'key' => 'salary_pending', 'title' => 'Salary Pending', 'icon' => 'bi-hourglass-split',
        'order' => 40, 'permission' => 'salary_view',
        'render' => static function () {
            $count = (int) Database::scalar("SELECT COUNT(*) FROM salary_slips WHERE status = 'pending'");
            $sum = (float) Database::scalar("SELECT COALESCE(SUM(net_pay),0) FROM salary_slips WHERE status = 'pending'");
            return '<span>' . $count . '</span><div class="text-muted small">' . Calc::money($sum) . ' outstanding</div>';
        },
    ]);

    Dashboard::add([
        'key' => 'salary_paid', 'title' => 'Salary Paid', 'icon' => 'bi-check2-circle',
        'order' => 41, 'permission' => 'salary_view',
        'render' => static function () use ($month) {
            $sum = (float) Database::scalar("SELECT COALESCE(SUM(net_pay),0) FROM salary_slips WHERE status = 'paid' AND period_month = ?", [$month]);
            return '<span>' . Calc::money($sum) . '</span><div class="text-muted small">paid in ' . Calc::monthLabel($month) . '</div>';
        },
    ]);

    Dashboard::add([
        'key' => 'monthly_payroll', 'title' => 'Monthly Payroll', 'icon' => 'bi-cash-stack',
        'order' => 42, 'permission' => 'salary_view',
        'render' => static function () use ($month) {
            $sum = (float) Database::scalar('SELECT COALESCE(SUM(net_pay),0) FROM salary_slips WHERE period_month = ?', [$month]);
            $n = (int) Database::scalar('SELECT COUNT(*) FROM salary_slips WHERE period_month = ?', [$month]);
            return '<span>' . Calc::money($sum) . '</span><div class="text-muted small">' . $n . ' slip(s), ' . Calc::monthLabel($month) . '</div>';
        },
    ]);

    Dashboard::add([
        'key' => 'top_performers', 'title' => 'Top Performers', 'icon' => 'bi-trophy',
        'order' => 43, 'permission' => 'performance_view',
        'render' => static function () use ($month) {
            $rows = Database::all(
                'SELECT p.employee_name, r.grade FROM performance_reviews r
                 JOIN salary_profiles p ON p.id = r.profile_id
                 WHERE r.period_month = ?
                 ORDER BY FIELD(r.grade, "A+","A","B+","B","C"), r.attendance_pct DESC LIMIT 4',
                [$month]
            );
            if (!$rows) {
                return '<div class="text-muted small">No performance data yet</div>';
            }
            $out = '';
            foreach ($rows as $r) {
                $out .= '<div class="d-flex justify-content-between small">'
                    . '<span>' . e($r['employee_name']) . '</span>'
                    . '<span class="badge text-bg-primary">' . e($r['grade']) . '</span></div>';
            }
            return '<div class="mt-1">' . $out . '</div>';
        },
    ]);
};

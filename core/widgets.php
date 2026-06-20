<?php
/**
 * Core dashboard widgets. These are intentionally placeholders: the real data
 * comes from feature plugins (Employees, Attendance, Salary, Leave, Notices).
 * Until a plugin populates them, they render an empty state. Plugins replace a
 * widget by registering one with the same key, or via the 'dashboard_widgets'
 * filter.
 */

use Niyanta\Core\Dashboard;

$placeholder = static function (string $hint): string {
    return '<div class="text-muted small d-flex align-items-center gap-2">'
        . '<i class="bi bi-puzzle"></i><span>' . e($hint) . '</span></div>';
};

Dashboard::add(['key' => 'total_employees', 'title' => 'Total Employees', 'icon' => 'bi-people', 'order' => 10,
    'render' => fn() => $placeholder('Install the Employees plugin')]);
Dashboard::add(['key' => 'present_today', 'title' => 'Present Today', 'icon' => 'bi-check2-circle', 'order' => 20,
    'render' => fn() => $placeholder('Install the Attendance plugin')]);
Dashboard::add(['key' => 'on_leave', 'title' => 'On Leave', 'icon' => 'bi-airplane', 'order' => 30,
    'render' => fn() => $placeholder('Install the Leave plugin')]);
Dashboard::add(['key' => 'pending_salaries', 'title' => 'Pending Salaries', 'icon' => 'bi-cash-stack', 'order' => 40,
    'render' => fn() => $placeholder('Install the Payroll plugin')]);
Dashboard::add(['key' => 'upcoming_birthdays', 'title' => 'Upcoming Birthdays', 'icon' => 'bi-gift', 'order' => 50,
    'render' => fn() => $placeholder('Install the Employees plugin')]);
Dashboard::add(['key' => 'recent_notices', 'title' => 'Recent Notices', 'icon' => 'bi-megaphone', 'order' => 60,
    'render' => fn() => $placeholder('Install the Notices plugin')]);

<?php
namespace SalaryPerf;

use Niyanta\Core\Database;
use Niyanta\Core\Flash;
use Niyanta\Core\Log;
use Niyanta\Core\View;

/**
 * Performance metrics and grading, derived from stored attendance.
 */
class PerformanceController
{
    public function index(): void
    {
        $month = $this->month();
        $rows = Database::all(
            'SELECT r.*, p.employee_name FROM performance_reviews r
             JOIN salary_profiles p ON p.id = r.profile_id
             WHERE r.period_month = ?
             ORDER BY FIELD(r.grade, "A+","A","B+","B","C"), r.attendance_pct DESC',
            [$month]
        );
        View::render('performance', [
            'title'    => 'Performance',
            'rows'     => $rows,
            'month'    => $month,
            'hasData'  => (int) Database::scalar('SELECT COUNT(*) FROM salary_attendance WHERE period_month = ?', [$month]) > 0,
        ], 'app');
    }

    /** Compute performance for every profile that has attendance in the month. */
    public function calculate(): void
    {
        $month = $this->month((string) request('period_month', ''));
        $attendance = Database::all(
            'SELECT a.*, p.employee_name FROM salary_attendance a
             JOIN salary_profiles p ON p.id = a.profile_id
             WHERE a.period_month = ?',
            [$month]
        );
        if (!$attendance) {
            Flash::error('No attendance recorded for ' . Calc::monthLabel($month) . '. Enter or sync hours first.');
            redirect('/salary/performance?month=' . $month);
        }
        $count = 0;
        foreach ($attendance as $a) {
            $pct = Calc::attendancePct((float) $a['worked_hours'], (float) $a['expected_hours']);
            $score = Calc::performanceScore($pct, (float) $a['productive_hours'], (float) $a['worked_hours']);
            $grade = Calc::grade($score);
            Database::query(
                'INSERT INTO performance_reviews
                    (profile_id, period_month, attendance_pct, productive_hours, overtime_hours, leave_hours, grade)
                 VALUES (?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    attendance_pct = VALUES(attendance_pct), productive_hours = VALUES(productive_hours),
                    overtime_hours = VALUES(overtime_hours), leave_hours = VALUES(leave_hours), grade = VALUES(grade)',
                [$a['profile_id'], $month, $pct, $a['productive_hours'], $a['overtime_hours'], $a['leave_hours'], $grade]
            );
            $count++;
        }
        Log::record('performance.calculate', "Computed {$count} reviews for {$month}");
        Flash::success("Performance computed for {$count} employee(s) for " . Calc::monthLabel($month) . '.');
        redirect('/salary/performance?month=' . $month);
    }

    private function month(string $value = ''): string
    {
        $value = $value !== '' ? $value : (string) request('month', '');
        return preg_match('/^\d{4}-\d{2}$/', $value) ? $value : Calc::currentMonth();
    }
}

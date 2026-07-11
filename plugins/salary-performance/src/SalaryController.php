<?php
namespace SalaryPerf;

use Niyanta\Core\Database;
use Niyanta\Core\Branding;
use Niyanta\Core\Flash;
use Niyanta\Core\Log;
use Niyanta\Core\View;

/**
 * Salary, attendance and Apploye integration. Record ids travel as ?id= query
 * parameters (the core Router matches exact paths).
 */
class SalaryController
{
    public const STATUSES = ['pending' => 'Pending', 'processed' => 'Processed', 'paid' => 'Paid'];

    // ------------------------------------------------------------- profiles ---

    public function profiles(): void
    {
        $rows = Database::all(
            'SELECT p.*, (SELECT COUNT(*) FROM salary_slips s WHERE s.profile_id = p.id) AS slip_count
             FROM salary_profiles p ORDER BY p.employee_name ASC'
        );
        View::render('profiles', [
            'title'    => 'Salary',
            'rows'     => $rows,
            'currency' => $this->currency(),
        ], 'app');
    }

    public function profileForm(): void
    {
        $id = (int) request('id', 0);
        $profile = $id ? $this->findProfile($id) : null;
        View::render('profile_form', [
            'title'     => $profile ? 'Edit Salary Profile' : 'Add Salary Profile',
            'profile'   => $profile,
            'employees' => $this->employeeOptions(),
            'action'    => url($profile ? '/salary/profile/update?id=' . $id : '/salary/profile/store'),
        ], 'app');
    }

    public function storeProfile(): void
    {
        $data = $this->collectProfile();
        if ($data['employee_name'] === '') {
            Flash::error('Employee name is required.');
            redirect('/salary/profile/create');
        }
        $id = Database::insert(
            'INSERT INTO salary_profiles (employee_id, employee_name, employee_email, apploye_ref, hourly_rate, monthly_salary, overtime_rate)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            array_values($data)
        );
        Log::record('salary.profile.add', 'Profile #' . $id . ' ' . $data['employee_name']);
        Flash::success('Salary profile saved.');
        redirect('/salary');
    }

    public function updateProfile(): void
    {
        $id = (int) request('id', 0);
        $this->findProfile($id);
        $data = $this->collectProfile();
        $set = implode(', ', array_map(static fn($c) => "{$c} = ?", array_keys($data)));
        Database::query("UPDATE salary_profiles SET {$set} WHERE id = ?", array_merge(array_values($data), [$id]));
        Log::record('salary.profile.edit', 'Profile #' . $id);
        Flash::success('Salary profile updated.');
        redirect('/salary');
    }

    public function deleteProfile(): void
    {
        $id = (int) request('id', 0);
        Database::query('DELETE FROM salary_profiles WHERE id = ?', [$id]);
        Log::record('salary.profile.delete', 'Profile #' . $id);
        Flash::success('Salary profile removed.');
        redirect('/salary');
    }

    // ----------------------------------------------------------- attendance ---

    public function attendanceForm(): void
    {
        $profile = $this->findProfile((int) request('id', 0));
        $month = $this->month();
        $att = $this->attendance((int) $profile['id'], $month);
        View::render('attendance_form', [
            'title'      => 'Attendance — ' . $profile['employee_name'],
            'profile'    => $profile,
            'month'      => $month,
            'attendance' => $att,
            'appolye'    => AppolyeClient::fromSettings()->isConfigured(),
        ], 'app');
    }

    public function saveAttendance(): void
    {
        $profile = $this->findProfile((int) request('profile_id', 0));
        $month = $this->month((string) request('period_month', ''));
        $this->upsertAttendance((int) $profile['id'], $month, [
            'worked_hours'     => (float) request('worked_hours', 0),
            'productive_hours' => (float) request('productive_hours', 0),
            'overtime_hours'   => (float) request('overtime_hours', 0),
            'leave_hours'      => (float) request('leave_hours', 0),
            'expected_hours'   => (float) request('expected_hours', 176),
            'source'           => 'manual',
        ]);
        Flash::success('Attendance saved for ' . Calc::monthLabel($month) . '.');
        redirect('/salary/attendance?id=' . $profile['id'] . '&month=' . $month);
    }

    /** Sync one profile's attendance from Apploye for the selected month. */
    public function syncAttendance(): void
    {
        $profile = $this->findProfile((int) request('profile_id', 0));
        $month = $this->month((string) request('period_month', ''));
        $client = AppolyeClient::fromSettings();
        if (!$client->isConfigured()) {
            Flash::error('Apploye integration is not configured. Enter hours manually or set it up under Integration.');
            redirect('/salary/attendance?id=' . $profile['id'] . '&month=' . $month);
        }
        $ref = $profile['apploye_ref'] ?: (string) $profile['employee_id'];
        $hours = $client->getMonthlyHours($ref, $month);
        if ($hours === null) {
            Flash::error('Could not fetch hours from Apploye for this member/month.');
            redirect('/salary/attendance?id=' . $profile['id'] . '&month=' . $month);
        }
        $existing = $this->attendance((int) $profile['id'], $month);
        $this->upsertAttendance((int) $profile['id'], $month, [
            'worked_hours'     => $hours['worked'],
            'productive_hours' => $hours['productive'],
            'overtime_hours'   => $hours['overtime'],
            'leave_hours'      => $hours['leave'],
            'expected_hours'   => (float) ($existing['expected_hours'] ?? 176),
            'source'           => 'appolye',
        ]);
        Log::record('salary.attendance.sync', 'Profile #' . $profile['id'] . ' ' . $month);
        Flash::success('Synced attendance from Apploye for ' . Calc::monthLabel($month) . '.');
        redirect('/salary/attendance?id=' . $profile['id'] . '&month=' . $month);
    }

    // ---------------------------------------------------------------- slips ---

    public function slips(): void
    {
        $status = (string) request('status', '');
        $month = (string) request('month', '');
        $where = [];
        $params = [];
        if (isset(self::STATUSES[$status])) {
            $where[] = 's.status = ?';
            $params[] = $status;
        }
        if (preg_match('/^\d{4}-\d{2}$/', $month)) {
            $where[] = 's.period_month = ?';
            $params[] = $month;
        }
        $clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $rows = Database::all(
            "SELECT s.*, p.employee_name FROM salary_slips s
             JOIN salary_profiles p ON p.id = s.profile_id
             {$clause} ORDER BY s.period_month DESC, p.employee_name ASC",
            $params
        );
        View::render('slips', [
            'title'    => 'Salary Slips',
            'rows'     => $rows,
            'statuses' => self::STATUSES,
            'filters'  => ['status' => $status, 'month' => $month],
            'currency' => $this->currency(),
        ], 'app');
    }

    public function slipForm(): void
    {
        $month = $this->month();
        View::render('slip_form', [
            'title'    => 'Generate Salary Slip',
            'profiles' => Database::all('SELECT id, employee_name FROM salary_profiles ORDER BY employee_name ASC'),
            'month'    => $month,
            'action'   => url('/salary/slip/generate'),
        ], 'app');
    }

    /** Create or recompute a slip for a profile + month, pulling attendance. */
    public function generateSlip(): void
    {
        $profile = $this->findProfile((int) request('profile_id', 0));
        $month = $this->month((string) request('period_month', ''));
        $att = $this->attendance((int) $profile['id'], $month);

        $hours = $att ? (float) $att['worked_hours'] : (float) request('hours_worked', 0);
        $otHours = $att ? (float) $att['overtime_hours'] : (float) request('overtime_hours', 0);
        $bonuses = (float) request('bonuses', 0);
        $deductions = (float) request('deductions', 0);
        $hourlyRate = (float) $profile['hourly_rate'];
        $otRate = (float) $profile['overtime_rate'];

        $calc = Calc::salary($hours, $hourlyRate, $otHours, $otRate, $bonuses, $deductions);

        $existing = Database::first('SELECT id FROM salary_slips WHERE profile_id = ? AND period_month = ?', [$profile['id'], $month]);
        $fields = [
            'hours_worked'   => $hours,
            'overtime_hours' => $otHours,
            'hourly_rate'    => $hourlyRate,
            'overtime_rate'  => $otRate,
            'base_pay'       => $calc['base_pay'],
            'overtime_pay'   => $calc['overtime_pay'],
            'bonuses'        => $bonuses,
            'deductions'     => $deductions,
            'gross_pay'      => $calc['gross_pay'],
            'net_pay'        => $calc['net_pay'],
            'notes'          => (string) request('notes', ''),
        ];
        if ($existing) {
            $set = implode(', ', array_map(static fn($c) => "{$c} = ?", array_keys($fields)));
            Database::query("UPDATE salary_slips SET {$set} WHERE id = ?", array_merge(array_values($fields), [$existing['id']]));
            $slipId = (int) $existing['id'];
        } else {
            $cols = array_merge(['profile_id', 'period_month'], array_keys($fields));
            $vals = array_merge([$profile['id'], $month], array_values($fields));
            $ph = implode(', ', array_fill(0, count($cols), '?'));
            $slipId = Database::insert('INSERT INTO salary_slips (' . implode(', ', $cols) . ") VALUES ({$ph})", $vals);
        }
        Log::record('salary.slip.generate', 'Slip #' . $slipId . ' ' . $month);
        Flash::success('Salary slip generated for ' . Calc::monthLabel($month) . '.');
        redirect('/salary/slip/view?id=' . $slipId);
    }

    public function slipView(): void
    {
        $slip = $this->findSlip((int) request('id', 0));
        View::render('slip', [
            'title'    => 'Salary Slip',
            'slip'     => $slip,
            'statuses' => self::STATUSES,
            'currency' => $this->currency(),
            'company'  => Branding::companyName(),
        ], 'app');
    }

    public function slipPdf(): void
    {
        $slip = $this->findSlip((int) request('id', 0));
        $pdf = $this->buildSlipPdf($slip);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="salary-slip-' . $slip['period_month'] . '-' . $slip['profile_id'] . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    public function slipEmail(): void
    {
        $slip = $this->findSlip((int) request('id', 0));
        $to = trim((string) request('email', '')) ?: (string) $slip['employee_email'];
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Flash::error('A valid recipient email is required.');
            redirect('/salary/slip/view?id=' . $slip['id']);
        }
        $pdf = $this->buildSlipPdf($slip);
        $subject = 'Salary Slip — ' . Calc::monthLabel($slip['period_month']);
        $body = "Dear {$slip['employee_name']},\n\nPlease find attached your salary slip for "
            . Calc::monthLabel($slip['period_month']) . ".\n\nRegards,\n" . Branding::companyName();
        $ok = Mailer::sendSlip($to, $subject, $body, $pdf, 'salary-slip-' . $slip['period_month'] . '.pdf');
        if ($ok) {
            Log::record('salary.slip.email', 'Slip #' . $slip['id'] . ' to ' . $to);
            Flash::success('Salary slip emailed to ' . $to . '.');
        } else {
            Flash::error('Could not send email (mail delivery may be unavailable on this server).');
        }
        redirect('/salary/slip/view?id=' . $slip['id']);
    }

    public function slipStatus(): void
    {
        $slip = $this->findSlip((int) request('id', 0));
        $status = (string) request('status', '');
        if (!isset(self::STATUSES[$status])) {
            Flash::error('Invalid status.');
            redirect('/salary/slip/view?id=' . $slip['id']);
        }
        Database::query('UPDATE salary_slips SET status = ? WHERE id = ?', [$status, $slip['id']]);
        Log::record('salary.slip.status', 'Slip #' . $slip['id'] . ' -> ' . $status);
        Flash::success('Slip marked as ' . self::STATUSES[$status] . '.');
        redirect('/salary/slip/view?id=' . $slip['id']);
    }

    // ---------------------------------------------------------- integration ---

    public function integration(): void
    {
        View::render('integration', [
            'title'    => 'Apploye Integration',
            'baseUrl'  => (string) Branding::setting('appolye_base_url', 'https://api.apploye.com'),
            'token'    => (string) Branding::setting('appolye_api_token', ''),
            'enabled'  => (bool) Branding::setting('appolye_enabled', false),
            'currency' => $this->currency(),
        ], 'app');
    }

    public function saveIntegration(): void
    {
        Branding::set('appolye_base_url', rtrim(trim((string) request('base_url', '')), '/'));
        Branding::set('appolye_api_token', trim((string) request('api_token', '')));
        Branding::set('appolye_enabled', request('enabled') ? true : false);
        Branding::set('salary_currency', strtoupper(trim((string) request('currency', 'INR'))) ?: 'INR');
        Branding::set('mail_from', trim((string) request('mail_from', '')));
        Log::record('salary.integration.save', 'Updated Apploye settings');
        Flash::success('Integration settings saved.');
        redirect('/salary/integration');
    }

    /** Sync attendance for every profile for the current month. */
    public function integrationSync(): void
    {
        $client = AppolyeClient::fromSettings();
        if (!$client->isConfigured()) {
            Flash::error('Configure and enable the Apploye integration first.');
            redirect('/salary/integration');
        }
        $month = $this->month((string) request('period_month', ''));
        $profiles = Database::all('SELECT * FROM salary_profiles');
        $synced = 0;
        foreach ($profiles as $p) {
            $ref = $p['apploye_ref'] ?: (string) $p['employee_id'];
            if ($ref === '') {
                continue;
            }
            $hours = $client->getMonthlyHours($ref, $month);
            if ($hours === null) {
                continue;
            }
            $existing = $this->attendance((int) $p['id'], $month);
            $this->upsertAttendance((int) $p['id'], $month, [
                'worked_hours'     => $hours['worked'],
                'productive_hours' => $hours['productive'],
                'overtime_hours'   => $hours['overtime'],
                'leave_hours'      => $hours['leave'],
                'expected_hours'   => (float) ($existing['expected_hours'] ?? 176),
                'source'           => 'appolye',
            ]);
            $synced++;
        }
        Log::record('salary.integration.sync', "Synced {$synced} profiles for {$month}");
        Flash::success("Synced {$synced} profile(s) from Apploye for " . Calc::monthLabel($month) . '.');
        redirect('/salary/integration');
    }

    // ------------------------------------------------------------ internals ---

    private function findProfile(int $id): array
    {
        $p = $id ? Database::first('SELECT * FROM salary_profiles WHERE id = ?', [$id]) : null;
        if (!$p) {
            $this->notFound();
        }
        return $p;
    }

    private function findSlip(int $id): array
    {
        $s = $id ? Database::first(
            'SELECT s.*, p.employee_name, p.employee_email FROM salary_slips s
             JOIN salary_profiles p ON p.id = s.profile_id WHERE s.id = ?',
            [$id]
        ) : null;
        if (!$s) {
            $this->notFound();
        }
        return $s;
    }

    private function attendance(int $profileId, string $month): ?array
    {
        return Database::first('SELECT * FROM salary_attendance WHERE profile_id = ? AND period_month = ?', [$profileId, $month]);
    }

    private function upsertAttendance(int $profileId, string $month, array $d): void
    {
        Database::query(
            'INSERT INTO salary_attendance
                (profile_id, period_month, worked_hours, productive_hours, overtime_hours, leave_hours, expected_hours, source)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                worked_hours = VALUES(worked_hours), productive_hours = VALUES(productive_hours),
                overtime_hours = VALUES(overtime_hours), leave_hours = VALUES(leave_hours),
                expected_hours = VALUES(expected_hours), source = VALUES(source)',
            [$profileId, $month, $d['worked_hours'], $d['productive_hours'], $d['overtime_hours'], $d['leave_hours'], $d['expected_hours'], $d['source']]
        );
    }

    private function collectProfile(): array
    {
        $mgr = request('employee_id');
        return [
            'employee_id'    => ($mgr !== null && $mgr !== '') ? (int) $mgr : null,
            'employee_name'  => trim((string) request('employee_name', '')),
            'employee_email' => trim((string) request('employee_email', '')),
            'apploye_ref'    => trim((string) request('apploye_ref', '')),
            'hourly_rate'    => (float) request('hourly_rate', 0),
            'monthly_salary' => (float) request('monthly_salary', 0),
            'overtime_rate'  => (float) request('overtime_rate', 0),
        ];
    }

    /** Employees from the Employee Management plugin, if that module is installed. */
    private function employeeOptions(): array
    {
        try {
            $exists = Database::scalar("SHOW TABLES LIKE 'employees'");
            if (!$exists) {
                return [];
            }
            return Database::all('SELECT id, full_name, email FROM employees ORDER BY full_name ASC');
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function currency(): string
    {
        return (string) Branding::setting('salary_currency', 'INR');
    }

    /** Resolve a YYYY-MM month from an explicit value, ?month, or the current month. */
    private function month(string $value = ''): string
    {
        $value = $value !== '' ? $value : (string) request('month', '');
        return preg_match('/^\d{4}-\d{2}$/', $value) ? $value : Calc::currentMonth();
    }

    private function buildSlipPdf(array $slip): string
    {
        $cur = $this->currency();
        $company = Branding::companyName();
        $pdf = new Pdf();

        $pdf->rectFill(0, 0, 595.28, 90, 0.043, 0.121, 0.301);
        $pdf->text(40, 40, 20, $company, true);
        $pdf->text(40, 64, 11, 'Salary Slip — ' . Calc::monthLabel($slip['period_month']));
        // header text in white would need color state; keep default for body.

        $y = 130;
        $pdf->text(40, $y, 12, 'Employee: ' . $slip['employee_name'], true);
        $pdf->textRight(555, $y, 11, 'Status: ' . ucfirst($slip['status']));
        $y += 18;
        $pdf->text(40, $y, 10, 'Period: ' . Calc::monthLabel($slip['period_month']));
        $pdf->line(40, $y + 12, 555, $y + 12);

        $y += 40;
        $rowsData = [
            ['Hours Worked', number_format((float) $slip['hours_worked'], 2)],
            ['Hourly Rate (' . $cur . ')', Calc::money((float) $slip['hourly_rate'])],
            ['Base Pay (' . $cur . ')', Calc::money((float) $slip['base_pay'])],
            ['Overtime Hours', number_format((float) $slip['overtime_hours'], 2)],
            ['Overtime Rate (' . $cur . ')', Calc::money((float) $slip['overtime_rate'])],
            ['Overtime Pay (' . $cur . ')', Calc::money((float) $slip['overtime_pay'])],
            ['Bonuses (' . $cur . ')', Calc::money((float) $slip['bonuses'])],
            ['Deductions (' . $cur . ')', '- ' . Calc::money((float) $slip['deductions'])],
            ['Gross Pay (' . $cur . ')', Calc::money((float) $slip['gross_pay'])],
        ];
        foreach ($rowsData as $r) {
            $pdf->text(40, $y, 11, $r[0]);
            $pdf->textRight(555, $y, 11, $r[1]);
            $y += 22;
        }
        $pdf->line(40, $y, 555, $y);
        $y += 24;
        $pdf->text(40, $y, 13, 'Net Pay (' . $cur . ')', true);
        $pdf->textRight(555, $y, 13, Calc::money((float) $slip['net_pay']), true);

        if (!empty($slip['notes'])) {
            $y += 40;
            $pdf->text(40, $y, 10, 'Notes: ' . $slip['notes']);
        }
        $pdf->text(40, 800, 8, 'Generated by Niyanta on ' . date('d M Y'));
        return $pdf->output();
    }

    private function notFound(): void
    {
        http_response_code(404);
        View::render('errors/404', ['path' => '/salary'], 'app');
        exit;
    }
}

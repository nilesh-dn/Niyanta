<?php
namespace EmpMgmt;

use Niyanta\Core\Database;
use Niyanta\Core\Flash;
use Niyanta\Core\Log;
use Niyanta\Core\View;

/**
 * Employee Management controller. Loaded by the plugin entry (index.php); the
 * plugin's views/ directory is auto-registered as a View path, so render()
 * targets templates that live beside this plugin.
 *
 * The core Router matches exact paths (no path params), so record ids travel as
 * ?id= query parameters.
 */
class EmployeeController
{
    public const TYPES = [
        'full_time' => 'Full Time',
        'part_time' => 'Part Time',
        'contract'  => 'Contract',
        'intern'    => 'Intern',
    ];

    public const STATUSES = [
        'active'   => 'Active',
        'inactive' => 'Inactive',
        'resigned' => 'Resigned',
    ];

    /** Single-file document inputs -> doc_type. */
    public const DOC_TYPES = [
        'offer_letter' => 'Offer Letter',
        'nda'          => 'NDA',
        'id_proof'     => 'ID Proof',
        'resume'       => 'Resume',
        'other'        => 'Other',
    ];

    private const IMAGE_MIMES = [
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp',
    ];

    private const DOC_MIMES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg', 'image/png' => 'png',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];

    /** CSV column order for import/export. */
    private const CSV_COLUMNS = [
        'emp_code', 'full_name', 'email', 'phone', 'emergency_contact',
        'designation', 'department', 'date_of_joining', 'employment_type',
        'status', 'address', 'date_of_birth', 'blood_group', 'aadhaar', 'pan',
    ];

    // ---------------------------------------------------------------- list ---

    public function index(): void
    {
        [$where, $params] = $this->filters();
        $perPage = 15;
        $page = max(1, (int) request('page', 1));
        $total = (int) Database::scalar("SELECT COUNT(*) FROM employees {$where}", $params);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);
        $offset = ($page - 1) * $perPage;

        $rows = Database::all(
            "SELECT * FROM employees {$where} ORDER BY full_name ASC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        View::render('list', [
            'title'       => 'Employees',
            'rows'        => $rows,
            'total'       => $total,
            'page'        => $page,
            'pages'       => $pages,
            'departments' => $this->departments(),
            'filters'     => [
                'q'          => (string) request('q', ''),
                'department' => (string) request('department', ''),
                'status'     => (string) request('status', ''),
                'type'       => (string) request('type', ''),
            ],
            'types'       => self::TYPES,
            'statuses'    => self::STATUSES,
        ], 'app');
    }

    /** Build a WHERE clause from search + filter inputs. */
    private function filters(): array
    {
        $clauses = [];
        $params = [];

        $q = trim((string) request('q', ''));
        if ($q !== '') {
            $clauses[] = '(full_name LIKE ? OR email LIKE ? OR emp_code LIKE ? OR phone LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like, $like);
        }
        foreach (['department' => 'department', 'status' => 'status', 'type' => 'employment_type'] as $input => $col) {
            $val = trim((string) request($input, ''));
            if ($val !== '') {
                $clauses[] = "{$col} = ?";
                $params[] = $val;
            }
        }
        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';
        return [$where, $params];
    }

    private function departments(): array
    {
        return array_column(
            Database::all("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL AND department <> '' ORDER BY department"),
            'department'
        );
    }

    private function managers(?int $excludeId = null): array
    {
        $rows = Database::all('SELECT id, full_name, emp_code FROM employees ORDER BY full_name ASC');
        if ($excludeId) {
            $rows = array_values(array_filter($rows, static fn($r) => (int) $r['id'] !== $excludeId));
        }
        return $rows;
    }

    // --------------------------------------------------------- create/edit ---

    public function create(): void
    {
        View::render('form', [
            'title'     => 'Add Employee',
            'employee'  => null,
            'documents' => [],
            'managers'  => $this->managers(),
            'types'     => self::TYPES,
            'statuses'  => self::STATUSES,
            'docTypes'  => self::DOC_TYPES,
            'action'    => url('/employees/store'),
        ], 'app');
    }

    public function edit(): void
    {
        $employee = $this->find((int) request('id', 0));
        View::render('form', [
            'title'     => 'Edit Employee',
            'employee'  => $employee,
            'documents' => $this->documentsFor((int) $employee['id']),
            'managers'  => $this->managers((int) $employee['id']),
            'types'     => self::TYPES,
            'statuses'  => self::STATUSES,
            'docTypes'  => self::DOC_TYPES,
            'action'    => url('/employees/update?id=' . (int) $employee['id']),
        ], 'app');
    }

    public function store(): void
    {
        $data = $this->collect();
        if (!$this->validate($data, null)) {
            redirect('/employees/create');
        }
        try {
            $id = $this->insertEmployee($data);
            $this->handlePhoto($id);
            $this->handleDocuments($id);
            Log::record('employees.add', 'Employee #' . $id . ' ' . $data['full_name']);
            Flash::success('Employee added.');
            redirect('/employees/profile?id=' . $id);
        } catch (\Throwable $e) {
            Flash::error('Could not add employee: ' . $e->getMessage());
            redirect('/employees/create');
        }
    }

    public function update(): void
    {
        $employee = $this->find((int) request('id', 0));
        $id = (int) $employee['id'];
        $data = $this->collect();
        if (!$this->validate($data, $id)) {
            redirect('/employees/edit?id=' . $id);
        }
        try {
            $set = implode(', ', array_map(static fn($c) => "{$c} = ?", array_keys($data)));
            Database::query(
                "UPDATE employees SET {$set} WHERE id = ?",
                array_merge(array_values($data), [$id])
            );
            $this->handlePhoto($id);
            $this->handleDocuments($id);
            Log::record('employees.edit', 'Employee #' . $id . ' ' . $data['full_name']);
            Flash::success('Employee updated.');
            redirect('/employees/profile?id=' . $id);
        } catch (\Throwable $e) {
            Flash::error('Could not update employee: ' . $e->getMessage());
            redirect('/employees/edit?id=' . $id);
        }
    }

    public function destroy(): void
    {
        $employee = $this->find((int) request('id', 0));
        $id = (int) $employee['id'];
        // Remove stored files (documents cascade in DB; delete their files first).
        foreach ($this->documentsFor($id) as $doc) {
            $this->deleteFile('uploads/employees/docs/' . $doc['file_path']);
        }
        if (!empty($employee['photo'])) {
            $this->deleteFile('uploads/employees/photos/' . $employee['photo']);
        }
        Database::query('DELETE FROM employees WHERE id = ?', [$id]);
        Log::record('employees.delete', 'Employee #' . $id . ' ' . $employee['full_name']);
        Flash::success('Employee deleted.');
        redirect('/employees');
    }

    // -------------------------------------------------------------- profile ---

    public function profile(): void
    {
        $employee = $this->find((int) request('id', 0));
        $manager = null;
        if (!empty($employee['reporting_manager_id'])) {
            $manager = Database::first('SELECT id, full_name, emp_code FROM employees WHERE id = ?', [(int) $employee['reporting_manager_id']]);
        }
        View::render('profile', [
            'title'     => $employee['full_name'],
            'employee'  => $employee,
            'manager'   => $manager,
            'documents' => $this->documentsFor((int) $employee['id']),
            'types'     => self::TYPES,
            'statuses'  => self::STATUSES,
            'docTypes'  => self::DOC_TYPES,
        ], 'app');
    }

    public function deleteDocument(): void
    {
        $docId = (int) request('doc_id', 0);
        $doc = Database::first('SELECT * FROM employee_documents WHERE id = ?', [$docId]);
        if ($doc) {
            $this->deleteFile('uploads/employees/docs/' . $doc['file_path']);
            Database::query('DELETE FROM employee_documents WHERE id = ?', [$docId]);
            Flash::success('Document removed.');
            redirect('/employees/profile?id=' . (int) $doc['employee_id']);
        }
        redirect('/employees');
    }

    // ------------------------------------------------------------- CSV I/O ---

    public function exportCsv(): void
    {
        [$where, $params] = $this->filters();
        $rows = Database::all("SELECT * FROM employees {$where} ORDER BY id ASC", $params);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="employees-' . date('Ymd-His') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, self::CSV_COLUMNS);
        foreach ($rows as $row) {
            $line = [];
            foreach (self::CSV_COLUMNS as $col) {
                $line[] = $row[$col] ?? '';
            }
            fputcsv($out, $line);
        }
        fclose($out);
        exit;
    }

    public function importForm(): void
    {
        View::render('import', [
            'title'   => 'Bulk Import Employees',
            'columns' => self::CSV_COLUMNS,
        ], 'app');
    }

    public function importCsv(): void
    {
        $file = $_FILES['csv'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Flash::error('Please choose a CSV file to import.');
            redirect('/employees/import');
        }
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            Flash::error('Could not read the uploaded file.');
            redirect('/employees/import');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            Flash::error('The CSV appears to be empty.');
            redirect('/employees/import');
        }
        $header = array_map(static fn($h) => strtolower(trim((string) $h)), $header);

        $imported = 0;
        $skipped = 0;
        while (($cells = fgetcsv($handle)) !== false) {
            if (count(array_filter($cells, static fn($c) => trim((string) $c) !== '')) === 0) {
                continue; // blank line
            }
            $assoc = [];
            foreach ($header as $i => $key) {
                $assoc[$key] = isset($cells[$i]) ? trim((string) $cells[$i]) : '';
            }
            $data = $this->mapCsvRow($assoc);
            if ($data['full_name'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)
                || Database::first('SELECT id FROM employees WHERE email = ?', [$data['email']])) {
                $skipped++;
                continue;
            }
            $this->insertEmployee($data);
            $imported++;
        }
        fclose($handle);
        Log::record('employees.import', "Imported {$imported}, skipped {$skipped}");
        Flash::success("Import complete: {$imported} added, {$skipped} skipped.");
        redirect('/employees');
    }

    private function mapCsvRow(array $r): array
    {
        $type = strtolower(str_replace([' ', '-'], '_', $r['employment_type'] ?? ''));
        $status = strtolower($r['status'] ?? '');
        return [
            'full_name'         => $r['full_name'] ?? '',
            'email'             => $r['email'] ?? '',
            'phone'             => $r['phone'] ?? '',
            'emergency_contact' => $r['emergency_contact'] ?? '',
            'designation'       => $r['designation'] ?? '',
            'department'        => $r['department'] ?? '',
            'reporting_manager_id' => null,
            'date_of_joining'   => $this->date($r['date_of_joining'] ?? ''),
            'employment_type'   => isset(self::TYPES[$type]) ? $type : 'full_time',
            'status'            => isset(self::STATUSES[$status]) ? $status : 'active',
            'address'           => $r['address'] ?? '',
            'date_of_birth'     => $this->date($r['date_of_birth'] ?? ''),
            'blood_group'       => $r['blood_group'] ?? '',
            'aadhaar'           => $r['aadhaar'] ?? '',
            'pan'               => strtoupper($r['pan'] ?? ''),
        ];
    }

    // ---------------------------------------------------------- internals ---

    private function find(int $id): array
    {
        $employee = $id ? Database::first('SELECT * FROM employees WHERE id = ?', [$id]) : null;
        if (!$employee) {
            http_response_code(404);
            View::render('errors/404', ['path' => '/employees'], 'app');
            exit;
        }
        return $employee;
    }

    private function documentsFor(int $employeeId): array
    {
        return Database::all('SELECT * FROM employee_documents WHERE employee_id = ? ORDER BY id ASC', [$employeeId]);
    }

    /** Collect and normalise employee fields from the request. */
    private function collect(): array
    {
        $t = static fn(string $k): string => trim((string) request($k, ''));
        $mgr = request('reporting_manager_id');
        return [
            'full_name'         => $t('full_name'),
            'email'             => $t('email'),
            'phone'             => $t('phone'),
            'emergency_contact' => $t('emergency_contact'),
            'designation'       => $t('designation'),
            'department'        => $t('department'),
            'reporting_manager_id' => ($mgr !== null && $mgr !== '') ? (int) $mgr : null,
            'date_of_joining'   => $this->date($t('date_of_joining')),
            'employment_type'   => isset(self::TYPES[$t('employment_type')]) ? $t('employment_type') : 'full_time',
            'status'            => isset(self::STATUSES[$t('status')]) ? $t('status') : 'active',
            'address'           => $t('address'),
            'date_of_birth'     => $this->date($t('date_of_birth')),
            'blood_group'       => $t('blood_group'),
            'aadhaar'           => $t('aadhaar'),
            'pan'               => strtoupper($t('pan')),
        ];
    }

    private function validate(array $data, ?int $excludeId): bool
    {
        if ($data['full_name'] === '') {
            Flash::error('Full name is required.');
            return false;
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Flash::error('A valid email address is required.');
            return false;
        }
        $dupe = Database::first(
            'SELECT id FROM employees WHERE email = ? AND id <> ?',
            [$data['email'], $excludeId ?? 0]
        );
        if ($dupe) {
            Flash::error('Another employee already uses that email address.');
            return false;
        }
        return true;
    }

    private function insertEmployee(array $data): int
    {
        $cols = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($cols), '?'));
        $id = Database::insert(
            'INSERT INTO employees (' . implode(', ', $cols) . ') VALUES (' . $placeholders . ')',
            array_values($data)
        );
        Database::query(
            'UPDATE employees SET emp_code = ? WHERE id = ?',
            ['EMP' . str_pad((string) $id, 4, '0', STR_PAD_LEFT), $id]
        );
        return $id;
    }

    private function handlePhoto(int $employeeId): void
    {
        $stored = $this->storeUpload($_FILES['photo'] ?? [], 'photos', self::IMAGE_MIMES);
        if ($stored) {
            // Replace any previous photo file.
            $old = Database::scalar('SELECT photo FROM employees WHERE id = ?', [$employeeId]);
            if ($old) {
                $this->deleteFile('uploads/employees/photos/' . $old);
            }
            Database::query('UPDATE employees SET photo = ? WHERE id = ?', [$stored['stored'], $employeeId]);
        }
    }

    private function handleDocuments(int $employeeId): void
    {
        foreach (['offer_letter', 'nda', 'id_proof', 'resume'] as $type) {
            $stored = $this->storeUpload($_FILES[$type] ?? [], 'docs', self::DOC_MIMES);
            if ($stored) {
                $this->insertDocument($employeeId, $type, $stored);
            }
        }
        foreach ($this->normalizeMulti($_FILES['other_documents'] ?? []) as $file) {
            $stored = $this->storeUpload($file, 'docs', self::DOC_MIMES);
            if ($stored) {
                $this->insertDocument($employeeId, 'other', $stored);
            }
        }
    }

    private function insertDocument(int $employeeId, string $type, array $stored): void
    {
        Database::query(
            'INSERT INTO employee_documents (employee_id, doc_type, file_path, original_name) VALUES (?, ?, ?, ?)',
            [$employeeId, $type, $stored['stored'], $stored['original']]
        );
    }

    /** Validate and move an uploaded file. Returns ['stored','original'] or null. */
    private function storeUpload(array $file, string $subdir, array $allowed): ?array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
            return null;
        }
        $mime = @mime_content_type($file['tmp_name']) ?: '';
        if (!isset($allowed[$mime])) {
            throw new \RuntimeException('Unsupported file type (' . ($mime ?: 'unknown') . ').');
        }
        $dir = base_path('uploads/employees/' . $subdir);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('Upload directory is not writable.');
        }
        $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            throw new \RuntimeException('Could not save the uploaded file.');
        }
        return ['stored' => $name, 'original' => (string) ($file['name'] ?? $name)];
    }

    /** Reindex a multi-file $_FILES entry into a list of single-file arrays. */
    private function normalizeMulti(array $field): array
    {
        if (!isset($field['name'])) {
            return [];
        }
        if (!is_array($field['name'])) {
            return ($field['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK ? [$field] : [];
        }
        $out = [];
        foreach ($field['name'] as $i => $name) {
            if (($field['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $out[] = [
                    'name'     => $name,
                    'tmp_name' => $field['tmp_name'][$i],
                    'error'    => $field['error'][$i],
                    'size'     => $field['size'][$i] ?? 0,
                ];
            }
        }
        return $out;
    }

    private function deleteFile(string $relative): void
    {
        $path = base_path($relative);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /** Normalise a date-ish string to Y-m-d, or null. */
    private function date(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $ts = strtotime($value);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}

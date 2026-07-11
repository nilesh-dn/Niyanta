<?php
/** @var array $employee @var ?array $manager @var array $documents
 *  @var array $types @var array $statuses @var array $docTypes */
$statusClass = ['active' => 'success', 'inactive' => 'secondary', 'resigned' => 'danger'];
$photoUrl = !empty($employee['photo']) ? asset('uploads/employees/photos/' . $employee['photo']) : null;
$fmt = static fn(?string $d): string => $d ? date('d M Y', strtotime($d)) : '—';
$row = static function (string $label, ?string $value): string {
    return '<div class="col-sm-6"><div class="prof-label">' . e($label) . '</div>'
        . '<div class="prof-value">' . ($value !== null && $value !== '' ? e($value) : '—') . '</div></div>';
};
?>
<?= \Niyanta\Core\View::partial('_styles') ?>
<div class="page-header">
    <div>
        <h1><?= e($employee['full_name']) ?></h1>
        <p class="page-sub"><code><?= e($employee['emp_code']) ?></code> ·
            <span class="badge text-bg-<?= $statusClass[$employee['status']] ?? 'secondary' ?>"><?= e($statuses[$employee['status']] ?? $employee['status']) ?></span>
        </p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(url('/employees')) ?>" class="btn btn-link">Back</a>
        <?php if (can('employees_edit')): ?>
            <a href="<?= e(url('/employees/edit?id=' . $employee['id'])) ?>" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        <?php endif; ?>
        <?php if (can('employees_delete')): ?>
            <form method="post" action="<?= e(url('/employees/delete')) ?>" onsubmit="return confirm('Delete this employee?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $employee['id'] ?>">
                <button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash me-1"></i>Delete</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <?php if ($photoUrl): ?>
                    <img src="<?= e($photoUrl) ?>" class="emp-photo mb-3" alt="">
                <?php else: ?>
                    <div class="emp-photo-placeholder mb-3"><i class="bi bi-person"></i></div>
                <?php endif; ?>
                <h2 class="h5 mb-0"><?= e($employee['full_name']) ?></h2>
                <div class="text-muted"><?= e($employee['designation'] ?: '—') ?></div>
                <div class="text-muted small mb-3"><?= e($employee['department'] ?: '') ?></div>
                <div class="d-grid gap-1 text-start small">
                    <div><i class="bi bi-envelope me-2"></i><?= e($employee['email']) ?></div>
                    <div><i class="bi bi-telephone me-2"></i><?= e($employee['phone'] ?: '—') ?></div>
                    <div><i class="bi bi-diagram-3 me-2"></i><?= $manager ? e($manager['full_name']) : 'No reporting manager' ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h6 mb-3">Employment</h2>
                <div class="row g-3">
                    <?= $row('Employment Type', $types[$employee['employment_type']] ?? $employee['employment_type']) ?>
                    <?= $row('Status', $statuses[$employee['status']] ?? $employee['status']) ?>
                    <?= $row('Date of Joining', $fmt($employee['date_of_joining'])) ?>
                    <?= $row('Reporting Manager', $manager['full_name'] ?? '') ?>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h6 mb-3">Personal</h2>
                <div class="row g-3">
                    <?= $row('Date of Birth', $fmt($employee['date_of_birth'])) ?>
                    <?= $row('Blood Group', $employee['blood_group']) ?>
                    <?= $row('Emergency Contact', $employee['emergency_contact']) ?>
                    <?= $row('Address', $employee['address']) ?>
                    <?= $row('Aadhaar Number', $employee['aadhaar']) ?>
                    <?= $row('PAN Number', $employee['pan']) ?>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3">Documents</h2>
                <?php if (empty($documents)): ?>
                    <p class="text-muted mb-0">No documents uploaded.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($documents as $doc): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-file-earmark-text me-2"></i><?= e($docTypes[$doc['doc_type']] ?? 'Document') ?>
                                    <span class="text-muted small">— <?= e($doc['original_name']) ?></span>
                                </span>
                                <a href="<?= e(asset('uploads/employees/docs/' . $doc['file_path'])) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-download me-1"></i>Download</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

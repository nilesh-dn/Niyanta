<?php
/** @var ?array $employee @var array $documents @var array $managers
 *  @var array $types @var array $statuses @var array $docTypes @var string $action */
$v = static fn(string $k): string => e((string) ($employee[$k] ?? ''));
$isEdit = $employee !== null;
$photoUrl = $isEdit && !empty($employee['photo']) ? asset('uploads/employees/photos/' . $employee['photo']) : null;
?>
<?= \Niyanta\Core\View::partial('_styles') ?>
<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Edit Employee' : 'Add Employee' ?></h1>
        <p class="page-sub"><?= $isEdit ? e($employee['emp_code'] . ' · ' . $employee['full_name']) : 'Create a new employee record.' ?></p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(url($isEdit ? '/employees/profile?id=' . $employee['id'] : '/employees')) ?>" class="btn btn-link">Cancel</a>
    </div>
</div>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <!-- Personal -->
        <div class="col-12 col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-3">Personal details</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" value="<?= $v('full_name') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" value="<?= $v('email') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?= $v('phone') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Emergency Contact</label>
                            <input type="text" class="form-control" name="emergency_contact" value="<?= $v('emergency_contact') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" value="<?= $v('date_of_birth') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Blood Group</label>
                            <input type="text" class="form-control" name="blood_group" value="<?= $v('blood_group') ?>" placeholder="O+">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"><?= $v('address') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-3">Employment</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Designation</label>
                            <input type="text" class="form-control" name="designation" value="<?= $v('designation') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" name="department" value="<?= $v('department') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reporting Manager</label>
                            <select class="form-select" name="reporting_manager_id">
                                <option value="">— None —</option>
                                <?php foreach ($managers as $m): ?>
                                    <option value="<?= (int) $m['id'] ?>" <?= (int) ($employee['reporting_manager_id'] ?? 0) === (int) $m['id'] ? 'selected' : '' ?>>
                                        <?= e($m['full_name']) ?><?= $m['emp_code'] ? ' (' . e($m['emp_code']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Joining</label>
                            <input type="date" class="form-control" name="date_of_joining" value="<?= $v('date_of_joining') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employment Type</label>
                            <select class="form-select" name="employment_type">
                                <?php foreach ($types as $k => $label): ?>
                                    <option value="<?= e($k) ?>" <?= ($employee['employment_type'] ?? 'full_time') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach ($statuses as $k => $label): ?>
                                    <option value="<?= e($k) ?>" <?= ($employee['status'] ?? 'active') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Identity -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-3">Identity</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Aadhaar Number</label>
                            <input type="text" class="form-control" name="aadhaar" value="<?= $v('aadhaar') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">PAN Number</label>
                            <input type="text" class="form-control text-uppercase" name="pan" value="<?= $v('pan') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo + Documents -->
        <div class="col-12 col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <h2 class="h6 mb-3 text-start">Photo</h2>
                    <?php if ($photoUrl): ?>
                        <img src="<?= e($photoUrl) ?>" class="emp-photo mb-3" alt="Employee photo">
                    <?php else: ?>
                        <div class="emp-photo-placeholder mb-3"><i class="bi bi-person"></i></div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="photo" accept="image/png,image/jpeg,image/webp">
                    <p class="text-muted small mt-2 mb-0">PNG, JPG or WEBP.</p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-3">Documents</h2>
                    <div class="mb-2">
                        <label class="form-label small">Offer Letter</label>
                        <input type="file" class="form-control form-control-sm" name="offer_letter">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">NDA</label>
                        <input type="file" class="form-control form-control-sm" name="nda">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">ID Proof</label>
                        <input type="file" class="form-control form-control-sm" name="id_proof">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Resume</label>
                        <input type="file" class="form-control form-control-sm" name="resume">
                    </div>
                    <div class="mb-1">
                        <label class="form-label small">Other Documents</label>
                        <input type="file" class="form-control form-control-sm" name="other_documents[]" multiple>
                    </div>
                    <p class="text-muted small mb-0">PDF, DOC/DOCX, PNG or JPG.</p>

                    <?php if ($isEdit && !empty($documents)): ?>
                        <hr>
                        <div class="small fw-semibold mb-2">Uploaded</div>
                        <?php foreach ($documents as $doc): ?>
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <a href="<?= e(asset('uploads/employees/docs/' . $doc['file_path'])) ?>" target="_blank" class="text-truncate me-2">
                                    <i class="bi bi-file-earmark me-1"></i><?= e($docTypes[$doc['doc_type']] ?? 'Document') ?>
                                </a>
                                <button form="doc-del-<?= (int) $doc['id'] ?>" class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-x"></i></button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-5">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-1"></i><?= $isEdit ? 'Save changes' : 'Create employee' ?></button>
        <a href="<?= e(url($isEdit ? '/employees/profile?id=' . $employee['id'] : '/employees')) ?>" class="btn btn-link">Cancel</a>
    </div>
</form>

<?php if ($isEdit && !empty($documents)): ?>
    <?php foreach ($documents as $doc): ?>
        <form id="doc-del-<?= (int) $doc['id'] ?>" method="post" action="<?= e(url('/employees/document/delete')) ?>" onsubmit="return confirm('Remove this document?')" class="d-none">
            <?= csrf_field() ?>
            <input type="hidden" name="doc_id" value="<?= (int) $doc['id'] ?>">
        </form>
    <?php endforeach; ?>
<?php endif; ?>

<?php
/** @var array $rows @var int $total @var int $page @var int $pages
 *  @var array $departments @var array $filters @var array $types @var array $statuses */
$statusClass = ['active' => 'success', 'inactive' => 'secondary', 'resigned' => 'danger'];
$qs = array_filter($filters, static fn($v) => $v !== '');
?>
<?= \Niyanta\Core\View::partial('_styles') ?>
<div class="page-header">
    <div>
        <h1>Employees</h1>
        <p class="page-sub"><?= (int) $total ?> employee<?= $total === 1 ? '' : 's' ?> on record.</p>
    </div>
    <div class="page-header-actions">
        <?php if (can('employees_add')): ?>
            <a href="<?= e(url('/employees/import')) ?>" class="btn btn-outline-primary"><i class="bi bi-upload me-1"></i>Import</a>
            <a href="<?= e(url('/employees/create')) ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Employee</a>
        <?php endif; ?>
        <a href="<?= e(url('/employees/export') . ($qs ? '?' . http_build_query($qs) : '')) ?>" class="btn btn-outline-primary"><i class="bi bi-download me-1"></i>Export</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= e(url('/employees')) ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md">
                <label class="form-label small">Search</label>
                <input type="text" class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Name, email, code, phone">
            </div>
            <div class="col-6 col-md-auto">
                <label class="form-label small">Department</label>
                <select class="form-select" name="department">
                    <option value="">All</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= e($d) ?>" <?= $filters['department'] === $d ? 'selected' : '' ?>><?= e($d) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-auto">
                <label class="form-label small">Status</label>
                <select class="form-select" name="status">
                    <option value="">All</option>
                    <?php foreach ($statuses as $k => $label): ?>
                        <option value="<?= e($k) ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-auto">
                <label class="form-label small">Type</label>
                <select class="form-select" name="type">
                    <option value="">All</option>
                    <?php foreach ($types as $k => $label): ?>
                        <option value="<?= e($k) ?>" <?= $filters['type'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-auto">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Apply</button>
                <a class="btn btn-link" href="<?= e(url('/employees')) ?>">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($rows)): ?>
            <p class="text-muted mb-0">No employees found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th><th>Code</th><th>Designation</th>
                            <th>Department</th><th>Type</th><th>Status</th><th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($r['photo'])): ?>
                                        <img src="<?= e(asset('uploads/employees/photos/' . $r['photo'])) ?>" class="emp-avatar" alt="">
                                    <?php else: ?>
                                        <span class="avatar" style="width:38px;height:38px;"><?= e(mb_substr($r['full_name'], 0, 1)) ?></span>
                                    <?php endif; ?>
                                    <div>
                                        <a class="fw-semibold" href="<?= e(url('/employees/profile?id=' . $r['id'])) ?>"><?= e($r['full_name']) ?></a>
                                        <div class="small text-muted"><?= e($r['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><code><?= e($r['emp_code']) ?></code></td>
                            <td><?= e($r['designation']) ?></td>
                            <td><?= e($r['department']) ?></td>
                            <td><?= e($types[$r['employment_type']] ?? $r['employment_type']) ?></td>
                            <td><span class="badge text-bg-<?= $statusClass[$r['status']] ?? 'secondary' ?>"><?= e($statuses[$r['status']] ?? $r['status']) ?></span></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= e(url('/employees/profile?id=' . $r['id'])) ?>" title="View"><i class="bi bi-eye"></i></a>
                                    <?php if (can('employees_edit')): ?>
                                        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('/employees/edit?id=' . $r['id'])) ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <?php endif; ?>
                                    <?php if (can('employees_delete')): ?>
                                        <form method="post" action="<?= e(url('/employees/delete')) ?>" onsubmit="return confirm('Delete <?= e($r['full_name']) ?>?')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pages > 1): ?>
                <nav class="d-flex justify-content-between align-items-center mt-3">
                    <span class="text-muted small">Page <?= (int) $page ?> of <?= (int) $pages ?></span>
                    <div class="btn-group">
                        <?php
                        $mk = static function (int $p) use ($qs) {
                            $qs['page'] = $p;
                            return url('/employees') . '?' . http_build_query($qs);
                        };
                        ?>
                        <a class="btn btn-sm btn-outline-primary <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= e($mk(max(1, $page - 1))) ?>">Previous</a>
                        <a class="btn btn-sm btn-outline-primary <?= $page >= $pages ? 'disabled' : '' ?>" href="<?= e($mk(min($pages, $page + 1))) ?>">Next</a>
                    </div>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

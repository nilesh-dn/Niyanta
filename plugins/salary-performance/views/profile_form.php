<?php
/** @var ?array $profile @var array $employees @var string $action */
$v = static fn(string $k): string => e((string) ($profile[$k] ?? ''));
?>
<div class="page-header">
    <div>
        <h1><?= $profile ? 'Edit Salary Profile' : 'Add Salary Profile' ?></h1>
        <p class="page-sub">Set the pay rates used to calculate salary slips.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(url('/salary')) ?>" class="btn btn-link">Cancel</a>
    </div>
</div>

<form method="post" action="<?= e($action) ?>">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h2 class="h6 mb-3">Employee</h2>
                    <div class="row g-3">
                        <?php if (!empty($employees)): ?>
                            <div class="col-12">
                                <label class="form-label">Link employee <span class="text-muted">(optional)</span></label>
                                <select class="form-select" name="employee_id" id="empSelect"
                                        data-map='<?= e(json_encode(array_column($employees, null, 'id'))) ?>'>
                                    <option value="">— Not linked —</option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?= (int) $emp['id'] ?>" <?= (int) ($profile['employee_id'] ?? 0) === (int) $emp['id'] ? 'selected' : '' ?>>
                                            <?= e($emp['full_name']) ?> (<?= e($emp['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Links to the Employee Management module if installed.</div>
                            </div>
                        <?php else: ?>
                            <div class="col-md-6">
                                <label class="form-label">Employee ID <span class="text-muted">(optional)</span></label>
                                <input type="number" class="form-control" name="employee_id" value="<?= $v('employee_id') ?>">
                            </div>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="employee_name" id="empName" value="<?= $v('employee_name') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="employee_email" id="empEmail" value="<?= $v('employee_email') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apploye member ref <span class="text-muted">(optional)</span></label>
                            <input type="text" class="form-control" name="apploye_ref" value="<?= $v('apploye_ref') ?>" placeholder="Apploye member/user id">
                            <div class="form-text">Used when syncing hours from Apploye.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h2 class="h6 mb-3">Pay rates</h2>
                    <div class="mb-3">
                        <label class="form-label">Hourly Rate</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="hourly_rate" value="<?= e((string) ($profile['hourly_rate'] ?? '0')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monthly Salary</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="monthly_salary" value="<?= e((string) ($profile['monthly_salary'] ?? '0')) ?>">
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Overtime Rate (per hour)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="overtime_rate" value="<?= e((string) ($profile['overtime_rate'] ?? '0')) ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2 mt-4 mb-5">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-1"></i>Save profile</button>
        <a href="<?= e(url('/salary')) ?>" class="btn btn-link">Cancel</a>
    </div>
</form>

<?php if (!empty($employees)): ?>
<script>
document.getElementById('empSelect')?.addEventListener('change', function () {
    var map = {}; try { map = JSON.parse(this.getAttribute('data-map') || '{}'); } catch (e) {}
    var emp = map[this.value];
    if (emp) {
        document.getElementById('empName').value = emp.full_name || '';
        document.getElementById('empEmail').value = emp.email || '';
    }
});
</script>
<?php endif; ?>

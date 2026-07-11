<?php
/** @var array $profiles @var string $month @var string $action */
$selected = (int) request('profile_id', 0);
?>
<div class="page-header">
    <div>
        <h1>Generate Salary Slip</h1>
        <p class="page-sub">Salary = hours worked × hourly rate, plus overtime, bonuses and deductions.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(url('/salary/slips')) ?>" class="btn btn-link">Cancel</a>
    </div>
</div>

<form method="post" action="<?= e($action) ?>">
    <?= csrf_field() ?>
    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Employee <span class="text-danger">*</span></label>
                    <select class="form-select" name="profile_id" required>
                        <option value="">— Select —</option>
                        <?php foreach ($profiles as $p): ?>
                            <option value="<?= (int) $p['id'] ?>" <?= $selected === (int) $p['id'] ? 'selected' : '' ?>><?= e($p['employee_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Month</label>
                    <input type="month" class="form-control" name="period_month" value="<?= e($month) ?>">
                    <div class="form-text">Worked &amp; overtime hours are taken from recorded attendance for this month when available.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hours worked <span class="text-muted">(fallback)</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" name="hours_worked" value="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Overtime hours <span class="text-muted">(fallback)</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" name="overtime_hours" value="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bonuses</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="bonuses" value="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Deductions</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="deductions" value="0">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <input type="text" class="form-control" name="notes" placeholder="Optional note shown on the slip">
                </div>
            </div>
            <button class="btn btn-primary mt-3" type="submit"><i class="bi bi-receipt me-1"></i>Generate slip</button>
        </div>
    </div>
</form>

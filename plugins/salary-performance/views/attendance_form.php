<?php
/** @var array $profile @var string $month @var ?array $attendance @var bool $appolye */
$a = $attendance ?? [];
$val = static fn(string $k, $d = '') => e((string) ($a[$k] ?? $d));
?>
<div class="page-header">
    <div>
        <h1>Attendance</h1>
        <p class="page-sub"><?= e($profile['employee_name']) ?> — hours for the selected month.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(url('/salary')) ?>" class="btn btn-link">Back</a>
    </div>
</div>

<form method="get" action="<?= e(url('/salary/attendance')) ?>" class="mb-4 d-flex gap-2 align-items-end">
    <input type="hidden" name="id" value="<?= (int) $profile['id'] ?>">
    <div>
        <label class="form-label small">Month</label>
        <input type="month" class="form-control" name="month" value="<?= e($month) ?>">
    </div>
    <button class="btn btn-outline-primary" type="submit">Go</button>
</form>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3">Hours — <?= e(\SalaryPerf\Calc::monthLabel($month)) ?>
                    <?php if (!empty($a['source'])): ?>
                        <span class="badge text-bg-secondary ms-1"><?= e($a['source']) ?></span>
                    <?php endif; ?>
                </h2>
                <form method="post" action="<?= e(url('/salary/attendance/save')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="profile_id" value="<?= (int) $profile['id'] ?>">
                    <input type="hidden" name="period_month" value="<?= e($month) ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Worked Hours</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="worked_hours" value="<?= $val('worked_hours', '0') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Productive Hours</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="productive_hours" value="<?= $val('productive_hours', '0') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Overtime Hours</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="overtime_hours" value="<?= $val('overtime_hours', '0') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Leave Hours</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="leave_hours" value="<?= $val('leave_hours', '0') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Expected Hours</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="expected_hours" value="<?= $val('expected_hours', '176') ?>">
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit"><i class="bi bi-check2 me-1"></i>Save hours</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3">Apploye sync</h2>
                <?php if ($appolye): ?>
                    <p class="text-muted small">Fetch daily/weekly/monthly hours from Apploye for this member and month.</p>
                    <form method="post" action="<?= e(url('/salary/attendance/sync')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="profile_id" value="<?= (int) $profile['id'] ?>">
                        <input type="hidden" name="period_month" value="<?= e($month) ?>">
                        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-arrow-repeat me-1"></i>Sync from Apploye</button>
                    </form>
                    <?php if (empty($profile['apploye_ref']) && empty($profile['employee_id'])): ?>
                        <p class="text-danger small mt-2 mb-0">Set an Apploye member ref on the profile first.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted small mb-2">Apploye integration is not enabled.</p>
                    <a href="<?= e(url('/salary/integration')) ?>" class="btn btn-outline-primary btn-sm">Configure integration</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

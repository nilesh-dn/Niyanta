<?php
/** @var array $slip @var array $statuses @var string $currency @var string $company */
$m = static fn($v) => number_format((float) $v, 2);
?>
<?= \Niyanta\Core\View::partial('_styles') ?>
<div class="page-header">
    <div>
        <h1>Salary Slip</h1>
        <p class="page-sub"><?= e($slip['employee_name']) ?> — <?= e(\SalaryPerf\Calc::monthLabel($slip['period_month'])) ?></p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(url('/salary/slips')) ?>" class="btn btn-link">Back</a>
        <a href="<?= e(url('/salary/slip/pdf?id=' . $slip['id'])) ?>" class="btn btn-primary"><i class="bi bi-file-earmark-pdf me-1"></i>Download PDF</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="h5 mb-0"><?= e($company) ?></div>
                        <div class="text-muted small">Salary slip · <?= e(\SalaryPerf\Calc::monthLabel($slip['period_month'])) ?></div>
                    </div>
                    <span class="badge text-bg-<?= salperf_status_class($slip['status']) ?>"><?= e($statuses[$slip['status']] ?? $slip['status']) ?></span>
                </div>
                <div class="mb-3">
                    <div class="fw-semibold"><?= e($slip['employee_name']) ?></div>
                    <?php if (!empty($slip['employee_email'])): ?><div class="small text-muted"><?= e($slip['employee_email']) ?></div><?php endif; ?>
                </div>
                <div class="slip-box">
                    <div class="slip-row"><span>Hours Worked</span><span><?= $m($slip['hours_worked']) ?></span></div>
                    <div class="slip-row"><span>Hourly Rate (<?= e($currency) ?>)</span><span><?= $m($slip['hourly_rate']) ?></span></div>
                    <div class="slip-row"><span>Base Pay (<?= e($currency) ?>)</span><span><?= $m($slip['base_pay']) ?></span></div>
                    <div class="slip-row"><span>Overtime Hours × Rate</span><span><?= $m($slip['overtime_hours']) ?> × <?= $m($slip['overtime_rate']) ?></span></div>
                    <div class="slip-row"><span>Overtime Pay (<?= e($currency) ?>)</span><span><?= $m($slip['overtime_pay']) ?></span></div>
                    <div class="slip-row"><span>Bonuses (<?= e($currency) ?>)</span><span><?= $m($slip['bonuses']) ?></span></div>
                    <div class="slip-row"><span>Deductions (<?= e($currency) ?>)</span><span>- <?= $m($slip['deductions']) ?></span></div>
                    <div class="slip-row"><span>Gross Pay (<?= e($currency) ?>)</span><span><?= $m($slip['gross_pay']) ?></span></div>
                    <div class="slip-row slip-total"><span>Net Pay (<?= e($currency) ?>)</span><span><?= $m($slip['net_pay']) ?></span></div>
                </div>
                <?php if (!empty($slip['notes'])): ?>
                    <p class="text-muted small mt-3 mb-0">Notes: <?= e($slip['notes']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <?php if (can('salary_process')): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-3">Status</h2>
                    <form method="post" action="<?= e(url('/salary/slip/status')) ?>" class="d-flex gap-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $slip['id'] ?>">
                        <select class="form-select" name="status">
                            <?php foreach ($statuses as $k => $label): ?>
                                <option value="<?= e($k) ?>" <?= $slip['status'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary" type="submit">Update</button>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h2 class="h6 mb-3">Email slip</h2>
                    <form method="post" action="<?= e(url('/salary/slip/email')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $slip['id'] ?>">
                        <div class="input-group">
                            <input type="email" class="form-control" name="email" value="<?= e($slip['employee_email'] ?? '') ?>" placeholder="recipient@example.com">
                            <button class="btn btn-outline-primary" type="submit"><i class="bi bi-envelope me-1"></i>Send</button>
                        </div>
                        <div class="form-text">Sends the PDF slip as an attachment.</div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

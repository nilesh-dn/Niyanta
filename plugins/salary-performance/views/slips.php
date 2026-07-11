<?php /** @var array $rows @var array $statuses @var array $filters @var string $currency */ ?>
<?= \Niyanta\Core\View::partial('_styles') ?>
<div class="page-header">
    <div>
        <h1>Salary Slips</h1>
        <p class="page-sub">Generated payroll slips and their status.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(url('/salary')) ?>" class="btn btn-link">Profiles</a>
        <?php if (can('salary_process')): ?>
            <a href="<?= e(url('/salary/slip/create')) ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Generate Slip</a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= e(url('/salary/slips')) ?>" class="row g-2 align-items-end">
            <div class="col-6 col-md-auto">
                <label class="form-label small">Month</label>
                <input type="month" class="form-control" name="month" value="<?= e($filters['month']) ?>">
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
            <div class="col-auto">
                <button class="btn btn-primary" type="submit">Filter</button>
                <a class="btn btn-link" href="<?= e(url('/salary/slips')) ?>">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($rows)): ?>
            <p class="text-muted mb-0">No salary slips found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th><th>Period</th>
                            <th class="text-end">Gross (<?= e($currency) ?>)</th>
                            <th class="text-end">Net (<?= e($currency) ?>)</th>
                            <th>Status</th><th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($r['employee_name']) ?></td>
                            <td><?= e(\SalaryPerf\Calc::monthLabel($r['period_month'])) ?></td>
                            <td class="text-end"><?= number_format((float) $r['gross_pay'], 2) ?></td>
                            <td class="text-end"><?= number_format((float) $r['net_pay'], 2) ?></td>
                            <td><span class="badge text-bg-<?= salperf_status_class($r['status']) ?>"><?= e($statuses[$r['status']] ?? $r['status']) ?></span></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= e(url('/salary/slip/view?id=' . $r['id'])) ?>" title="View"><i class="bi bi-eye"></i></a>
                                    <a class="btn btn-sm btn-outline-primary" href="<?= e(url('/salary/slip/pdf?id=' . $r['id'])) ?>" title="PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

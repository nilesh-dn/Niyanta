<?php /** @var array $rows @var string $month @var bool $hasData */ ?>
<?= \Niyanta\Core\View::partial('_styles') ?>
<div class="page-header">
    <div>
        <h1>Performance</h1>
        <p class="page-sub">Grades derived from attendance and productive hours.</p>
    </div>
    <div class="page-header-actions">
        <form method="get" action="<?= e(url('/salary/performance')) ?>" class="d-flex gap-2 align-items-end">
            <input type="month" class="form-control" name="month" value="<?= e($month) ?>">
            <button class="btn btn-outline-primary" type="submit">Go</button>
        </form>
        <?php if (can('salary_edit')): ?>
            <form method="post" action="<?= e(url('/salary/performance/calculate')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="period_month" value="<?= e($month) ?>">
                <button class="btn btn-primary" type="submit"><i class="bi bi-calculator me-1"></i>Calculate</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($rows)): ?>
            <p class="text-muted mb-1">No performance data for <?= e(\SalaryPerf\Calc::monthLabel($month)) ?>.</p>
            <?php if ($hasData): ?>
                <p class="text-muted small mb-0">Attendance exists for this month — click <strong>Calculate</strong> to grade it.</p>
            <?php else: ?>
                <p class="text-muted small mb-0">Record or sync attendance first, then calculate.</p>
            <?php endif; ?>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th><th>Attendance %</th>
                            <th class="text-end">Productive</th><th class="text-end">Overtime</th>
                            <th class="text-end">Leave</th><th class="text-center">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($r['employee_name']) ?></td>
                            <td style="min-width:160px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="meter flex-grow-1"><span style="width:<?= (float) $r['attendance_pct'] ?>%"></span></div>
                                    <span class="small text-muted"><?= number_format((float) $r['attendance_pct'], 1) ?>%</span>
                                </div>
                            </td>
                            <td class="text-end"><?= number_format((float) $r['productive_hours'], 2) ?> h</td>
                            <td class="text-end"><?= number_format((float) $r['overtime_hours'], 2) ?> h</td>
                            <td class="text-end"><?= number_format((float) $r['leave_hours'], 2) ?> h</td>
                            <td class="text-center"><span class="grade-badge <?= salperf_grade_class($r['grade']) ?>"><?= e($r['grade']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php /** @var array $rows @var string $currency */ ?>
<?= \Niyanta\Core\View::partial('_styles') ?>
<div class="page-header">
    <div>
        <h1>Salary</h1>
        <p class="page-sub">Hourly rates, monthly salary and overtime per employee.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(url('/salary/slips')) ?>" class="btn btn-outline-primary"><i class="bi bi-receipt me-1"></i>Slips</a>
        <?php if (can('salary_edit')): ?>
            <a href="<?= e(url('/salary/integration')) ?>" class="btn btn-outline-primary"><i class="bi bi-plug me-1"></i>Integration</a>
            <a href="<?= e(url('/salary/profile/create')) ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Profile</a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($rows)): ?>
            <p class="text-muted mb-0">No salary profiles yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th class="text-end">Hourly (<?= e($currency) ?>)</th>
                            <th class="text-end">Monthly (<?= e($currency) ?>)</th>
                            <th class="text-end">Overtime (<?= e($currency) ?>)</th>
                            <th class="text-end">Slips</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($r['employee_name']) ?></div>
                                <?php if (!empty($r['employee_email'])): ?><div class="small text-muted"><?= e($r['employee_email']) ?></div><?php endif; ?>
                            </td>
                            <td class="text-end"><?= number_format((float) $r['hourly_rate'], 2) ?></td>
                            <td class="text-end"><?= number_format((float) $r['monthly_salary'], 2) ?></td>
                            <td class="text-end"><?= number_format((float) $r['overtime_rate'], 2) ?></td>
                            <td class="text-end"><?= (int) $r['slip_count'] ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <?php if (can('salary_edit')): ?>
                                        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('/salary/attendance?id=' . $r['id'])) ?>" title="Attendance"><i class="bi bi-clock-history"></i></a>
                                        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('/salary/profile/edit?id=' . $r['id'])) ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <?php endif; ?>
                                    <?php if (can('salary_process')): ?>
                                        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('/salary/slip/create?profile_id=' . $r['id'])) ?>" title="Generate slip"><i class="bi bi-receipt"></i></a>
                                    <?php endif; ?>
                                    <?php if (can('salary_edit')): ?>
                                        <form method="post" action="<?= e(url('/salary/profile/delete')) ?>" onsubmit="return confirm('Delete this profile and its slips?')">
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
        <?php endif; ?>
    </div>
</div>

<?php /** @var array $columns */ ?>
<div class="page-header">
    <div>
        <h1>Bulk Import Employees</h1>
        <p class="page-sub">Upload a CSV to add many employees at once.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(url('/employees')) ?>" class="btn btn-link">Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3">Upload CSV</h2>
                <form method="post" action="<?= e(url('/employees/import')) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <input type="file" class="form-control" name="csv" accept=".csv,text/csv" required>
                    </div>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-upload me-1"></i>Import</button>
                    <a href="<?= e(url('/employees/export')) ?>" class="btn btn-outline-primary"><i class="bi bi-download me-1"></i>Download template</a>
                </form>
                <p class="text-muted small mt-3 mb-0">
                    Rows are matched by column header. <code>full_name</code> and a valid, unique
                    <code>email</code> are required; rows failing that are skipped. Dates accept common
                    formats (e.g. <code>2024-01-31</code>). The <code>emp_code</code> column is ignored on
                    import — codes are generated automatically.
                </p>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3">Expected columns</h2>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($columns as $c): ?>
                        <code class="border rounded px-2 py-1"><?= e($c) ?></code>
                    <?php endforeach; ?>
                </div>
                <p class="text-muted small mt-3 mb-0">
                    <strong>employment_type</strong>: full_time, part_time, contract, intern.<br>
                    <strong>status</strong>: active, inactive, resigned.
                </p>
            </div>
        </div>
    </div>
</div>

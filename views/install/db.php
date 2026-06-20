<?php /** @var array $data */ ?>
<h2 class="h4 mb-3">Database connection</h2>
<p class="text-muted">Enter the credentials for an existing MySQL / MariaDB database. The tables will be created for you.</p>

<form method="post" action="?step=2">
    <div class="mb-3">
        <label class="form-label" for="db_host">Database host</label>
        <input type="text" class="form-control" id="db_host" name="db_host" value="<?= e($data['db_host'] ?? 'localhost') ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="db_name">Database name</label>
        <input type="text" class="form-control" id="db_name" name="db_name" value="<?= e($data['db_name'] ?? '') ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="db_user">Database username</label>
        <input type="text" class="form-control" id="db_user" name="db_user" value="<?= e($data['db_user'] ?? '') ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="db_pass">Database password</label>
        <input type="password" class="form-control" id="db_pass" name="db_pass" value="<?= e($data['db_pass'] ?? '') ?>">
    </div>
    <button class="btn btn-primary w-100" type="submit">Test &amp; continue <i class="bi bi-arrow-right ms-1"></i></button>
</form>

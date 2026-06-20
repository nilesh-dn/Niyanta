<?php /** @var array $data */ ?>
<h2 class="h4 mb-3">Company &amp; administrator</h2>
<p class="text-muted">Create your company profile and the first Super Admin account.</p>

<form method="post" action="?step=3">
    <div class="mb-3">
        <label class="form-label" for="company_name">Company name</label>
        <input type="text" class="form-control" id="company_name" name="company_name" value="<?= e($data['company_name'] ?? 'ABC Pvt Ltd.') ?>" required>
    </div>
    <hr>
    <div class="mb-3">
        <label class="form-label" for="admin_name">Admin name</label>
        <input type="text" class="form-control" id="admin_name" name="admin_name" value="<?= e($data['admin_name'] ?? '') ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="admin_email">Admin email</label>
        <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?= e($data['admin_email'] ?? '') ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="admin_password">Admin password <span class="text-muted">(min 8 characters)</span></label>
        <input type="password" class="form-control" id="admin_password" name="admin_password" minlength="8" required>
    </div>
    <button class="btn btn-primary w-100" type="submit">Install Niyanta <i class="bi bi-check2 ms-1"></i></button>
</form>

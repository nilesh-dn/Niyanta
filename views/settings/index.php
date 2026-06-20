<?php /** @var string $company @var bool $managerViewSalary */ ?>
<h1 class="h3 mb-4">Settings</h1>

<div class="card">
    <div class="card-body">
        <h2 class="h6 mb-3">General</h2>
        <form method="post" action="<?= e(url('/settings')) ?>">
            <?= csrf_field() ?>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="managerSalary"
                       name="manager_can_view_salary" value="1" <?= $managerViewSalary ? 'checked' : '' ?>>
                <label class="form-check-label" for="managerSalary">
                    Allow managers to view salary information
                </label>
            </div>
            <button class="btn btn-primary" type="submit">Save settings</button>
        </form>
    </div>
</div>

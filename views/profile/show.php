<?php
/** @var array $user */
use Niyanta\Core\Theme;
$theme = Theme::current();
?>
<h1 class="h3 mb-4">My Profile</h1>

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3">Account details</h2>
                <form method="post" action="<?= e(url('/profile')) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="name">Full name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= e($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?= e($user['role_name']) ?>" disabled>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label" for="password">New password <span class="text-muted">(leave blank to keep)</span></label>
                        <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                    </div>
                    <button class="btn btn-primary" type="submit">Save changes</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3">Appearance</h2>
                <form method="post" action="<?= e(url('/theme')) ?>">
                    <?= csrf_field() ?>
                    <div class="btn-group" role="group" aria-label="Theme">
                        <input type="radio" class="btn-check" name="theme" id="themeLight" value="light" <?= $theme === 'light' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-primary" for="themeLight"><i class="bi bi-sun me-1"></i>Light</label>
                        <input type="radio" class="btn-check" name="theme" id="themeDark" value="dark" <?= $theme === 'dark' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-primary" for="themeDark"><i class="bi bi-moon-stars me-1"></i>Dark</label>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-outline-secondary btn-sm" type="submit">Save preference</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

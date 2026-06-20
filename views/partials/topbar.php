<?php
use Niyanta\Core\Auth;
use Niyanta\Core\Theme;

$user = Auth::user();
$theme = Theme::current();
?>
<header class="app-topbar">
    <button class="btn btn-link sidebar-toggle d-lg-none" id="sidebarToggle" aria-label="Toggle menu">
        <i class="bi bi-list"></i>
    </button>
    <div class="ms-auto d-flex align-items-center gap-2">
        <!-- Dark/light theme toggle (top-right) -->
        <button class="btn btn-icon" id="themeToggle" type="button"
                data-theme="<?= e($theme) ?>"
                data-theme-url="<?= e(url('/theme')) ?>"
                data-csrf="<?= e(\Niyanta\Core\Csrf::token()) ?>"
                title="Toggle dark / light mode" aria-label="Toggle theme">
            <i class="bi <?= $theme === 'dark' ? 'bi-sun' : 'bi-moon-stars' ?>"></i>
        </button>

        <div class="dropdown">
            <button class="btn btn-link dropdown-toggle user-menu" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar"><?= e(mb_substr($user['name'] ?? '?', 0, 1)) ?></span>
                <span class="d-none d-sm-inline ms-2">
                    <?= e($user['name'] ?? '') ?>
                    <small class="d-block text-muted"><?= e($user['role_name'] ?? '') ?></small>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= e(url('/profile')) ?>"><i class="bi bi-person me-2"></i>My Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="post" action="<?= e(url('/logout')) ?>" class="px-1">
                        <?= csrf_field() ?>
                        <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Sign out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

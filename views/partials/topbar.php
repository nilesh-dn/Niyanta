<?php
use Niyanta\Core\Auth;
use Niyanta\Core\Theme;
use Niyanta\Core\Menu;
use Niyanta\Core\Router;

$user = Auth::user();
$theme = Theme::current();

// Resolve a page title: explicit override, else the matching menu label,
// else a tidied last path segment.
$pageTitle = $title ?? null;
if ($pageTitle === null) {
    $current = Router::currentPath();
    foreach (Menu::visible() as $item) {
        $route = rtrim($item['route'], '/') ?: '/';
        if ($current === $route || ($route !== '/' && str_starts_with($current, $route . '/'))) {
            $pageTitle = $item['label'];
            break;
        }
    }
    if ($pageTitle === null) {
        $seg = trim((string) strrchr('/' . trim($current, '/'), '/'), '/');
        $pageTitle = $seg === '' ? 'Dashboard' : ucwords(str_replace(['-', '_'], ' ', $seg));
    }
}
?>
<header class="app-topbar">
    <button class="btn btn-link sidebar-toggle d-lg-none p-0" id="sidebarToggle" aria-label="Toggle menu">
        <i class="bi bi-list"></i>
    </button>
    <h1 class="topbar-title"><?= e($pageTitle) ?></h1>

    <div class="topbar-actions">
        <!-- Dark/light theme toggle (top-right) -->
        <button class="btn-icon" id="themeToggle" type="button"
                data-theme="<?= e($theme) ?>"
                data-theme-url="<?= e(url('/theme')) ?>"
                data-csrf="<?= e(\Niyanta\Core\Csrf::token()) ?>"
                title="Toggle dark / light mode" aria-label="Toggle theme">
            <i class="bi <?= $theme === 'dark' ? 'bi-sun' : 'bi-moon-stars' ?>"></i>
        </button>

        <div class="dropdown">
            <button class="btn user-menu" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar"><?= e(mb_substr($user['name'] ?? '?', 0, 1)) ?></span>
                <span class="d-none d-sm-block text-start lh-sm">
                    <span class="fw-semibold d-block"><?= e($user['name'] ?? '') ?></span>
                    <small class="text-muted"><?= e($user['role_name'] ?? '') ?></small>
                </span>
                <i class="bi bi-chevron-down small d-none d-sm-inline ms-1"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= e(url('/profile')) ?>"><i class="bi bi-person me-2"></i>My Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="post" action="<?= e(url('/logout')) ?>">
                        <?= csrf_field() ?>
                        <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Sign out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

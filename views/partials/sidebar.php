<?php
use Niyanta\Core\Menu;
use Niyanta\Core\Branding;
use Niyanta\Core\Router;

$current = Router::currentPath();
$company = Branding::companyName();
$logo = Branding::logoUrl();
?>
<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-brand">
        <?php if ($logo): ?>
            <img src="<?= e($logo) ?>" alt="<?= e($company) ?>" class="sidebar-logo">
        <?php else: ?>
            <span class="brand-badge"><?= e(mb_substr($company, 0, 1)) ?></span>
        <?php endif; ?>
        <span class="sidebar-company text-truncate">
            <?= e($company) ?>
            <small>Niyanta</small>
        </span>
    </div>
    <div class="sidebar-section">Menu</div>
    <nav class="sidebar-nav">
        <?php foreach (Menu::visible() as $item): ?>
            <?php
            $route = rtrim($item['route'], '/') ?: '/';
            $active = ($current === $route)
                || ($route !== '/' && str_starts_with($current, $route . '/'));
            ?>
            <a class="sidebar-link<?= $active ? ' active' : '' ?>" href="<?= e(url($item['route'])) ?>">
                <i class="bi <?= e($item['icon']) ?>"></i>
                <span><?= e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>

<?php
use Niyanta\Core\Router;

$current = Router::currentPath();
$tabs = [
    ['label' => 'General',     'icon' => 'bi-sliders',       'route' => '/settings',          'perm' => 'manage_settings'],
    ['label' => 'Branding',    'icon' => 'bi-palette',       'route' => '/settings/branding', 'perm' => 'manage_branding'],
    ['label' => 'Plugins',     'icon' => 'bi-plug',          'route' => '/settings/plugins',  'perm' => 'manage_plugins'],
    ['label' => 'System Logs', 'icon' => 'bi-list-columns',  'route' => '/settings/logs',     'perm' => 'view_logs'],
];
?>
<nav class="settings-nav" aria-label="Settings sections">
    <?php foreach ($tabs as $tab): ?>
        <?php if (!can($tab['perm'])) { continue; } ?>
        <?php $active = ($current === $tab['route']); ?>
        <a class="<?= $active ? 'active' : '' ?>" href="<?= e(url($tab['route'])) ?>">
            <i class="bi <?= e($tab['icon']) ?>"></i><span><?= e($tab['label']) ?></span>
        </a>
    <?php endforeach; ?>
</nav>

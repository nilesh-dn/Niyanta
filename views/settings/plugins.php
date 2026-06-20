<?php /** @var array $plugins */ ?>
<h1 class="h3 mb-4">Plugins</h1>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h6 mb-3">Upload a plugin</h2>
        <form method="post" action="<?= e(url('/settings/plugins/upload')) ?>" enctype="multipart/form-data" class="row g-2 align-items-center">
            <?= csrf_field() ?>
            <div class="col-12 col-sm">
                <input type="file" class="form-control" name="plugin" accept=".zip" required>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" type="submit"><i class="bi bi-upload me-1"></i>Upload ZIP</button>
            </div>
        </form>
        <p class="text-muted small mt-2 mb-0">The archive must contain a single folder with a <code>plugin.json</code>.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h2 class="h6 mb-3">Installed &amp; available plugins</h2>
        <?php if (empty($plugins)): ?>
            <p class="text-muted mb-0">No plugins found in <code>/plugins</code>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr><th>Plugin</th><th>Version</th><th>Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($plugins as $p): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($p['name']) ?></div>
                                <div class="small text-muted"><?= e($p['description']) ?></div>
                                <div class="small text-muted">by <?= e($p['author']) ?> · <code><?= e($p['slug']) ?></code></div>
                            </td>
                            <td><?= e($p['version']) ?></td>
                            <td>
                                <?php if ($p['active']): ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php elseif ($p['installed']): ?>
                                    <span class="badge text-bg-secondary">Inactive</span>
                                <?php else: ?>
                                    <span class="badge text-bg-light text-dark border">Not installed</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <?php if (!$p['installed']): ?>
                                        <?= plugin_action_button('/settings/plugins/install', $p['slug'], 'Install', 'btn-primary') ?>
                                    <?php elseif ($p['active']): ?>
                                        <?= plugin_action_button('/settings/plugins/deactivate', $p['slug'], 'Deactivate', 'btn-outline-secondary') ?>
                                    <?php else: ?>
                                        <?= plugin_action_button('/settings/plugins/activate', $p['slug'], 'Activate', 'btn-success') ?>
                                    <?php endif; ?>
                                    <?= plugin_action_button('/settings/plugins/remove', $p['slug'], 'Remove', 'btn-outline-danger', 'Remove this plugin and its files?') ?>
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

<?php
function plugin_action_button(string $route, string $slug, string $label, string $class, ?string $confirm = null): string
{
    $onsubmit = $confirm ? ' onsubmit="return confirm(\'' . e($confirm) . '\')"' : '';
    return '<form method="post" action="' . e(url($route)) . '"' . $onsubmit . '>'
        . csrf_field()
        . '<input type="hidden" name="slug" value="' . e($slug) . '">'
        . '<button class="btn btn-sm ' . e($class) . '" type="submit">' . e($label) . '</button>'
        . '</form>';
}

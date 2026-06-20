<?php /** @var array $logs */ ?>
<div class="page-header">
    <div>
        <h1>System Logs</h1>
        <p class="page-sub">Recent activity across the workspace.</p>
    </div>
</div>
<?= \Niyanta\Core\View::partial('partials.settings-nav') ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <p class="text-muted mb-0">No activity recorded yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr><th>When</th><th>User</th><th>Action</th><th>Context</th><th>IP</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-nowrap"><?= e($log['created_at']) ?></td>
                            <td><?= e($log['user_name'] ?? 'System') ?></td>
                            <td><code><?= e($log['action']) ?></code></td>
                            <td><?= e($log['context']) ?></td>
                            <td class="text-muted small"><?= e($log['ip']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

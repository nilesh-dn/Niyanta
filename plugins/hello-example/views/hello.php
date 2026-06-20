<?php /** @var array $messages */ ?>
<h1 class="h3 mb-4"><i class="bi bi-emoji-smile me-2"></i>Hello Example</h1>

<div class="card">
    <div class="card-body">
        <p class="text-muted">This page is served entirely by the <code>hello-example</code> plugin —
            its route, menu item, permission and this view all live under <code>/plugins/hello-example</code>.</p>
        <ul class="list-group">
            <?php foreach ($messages as $m): ?>
                <li class="list-group-item d-flex justify-content-between">
                    <span><?= e($m['message']) ?></span>
                    <span class="text-muted small"><?= e($m['created_at']) ?></span>
                </li>
            <?php endforeach; ?>
            <?php if (empty($messages)): ?>
                <li class="list-group-item text-muted">No messages yet.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php /** @var array $widgets */ ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1">Dashboard</h1>
        <p class="text-muted mb-0">Welcome back, <?= e(auth()['name'] ?? '') ?>.</p>
    </div>
</div>

<div class="row g-3">
    <?php foreach ($widgets as $widget): ?>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card widget-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h2 class="h6 text-muted mb-0"><?= e($widget['title']) ?></h2>
                        <span class="widget-icon"><i class="bi <?= e($widget['icon']) ?>"></i></span>
                    </div>
                    <div class="widget-body">
                        <?= ($widget['render'])() ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (empty($widgets)): ?>
    <div class="text-muted">No widgets available.</div>
<?php endif; ?>

<?php /** @var array $widgets */ ?>
<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p class="page-sub">Welcome back, <?= e(auth()['name'] ?? '') ?>.</p>
    </div>
</div>

<div class="row g-4">
    <?php foreach ($widgets as $widget): ?>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card widget-card h-100">
                <div class="card-body">
                    <div class="widget-head">
                        <h2 class="widget-title"><?= e($widget['title']) ?></h2>
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

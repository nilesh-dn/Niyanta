<?php
/** @var string $content */
use Niyanta\Core\Branding;
use Niyanta\Core\Theme;
use Niyanta\Core\Flash;
use Niyanta\Core\View;

$theme = Theme::current();
$company = Branding::companyName();
$pageTitle = $title ?? null;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= e($theme) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($company) ?> — Niyanta</title>
    <link href="<?= asset('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('assets/css/app.css') ?>" rel="stylesheet">
    <style><?= Branding::cssVariables() ?></style>
</head>
<body>
<div class="app-shell">
    <?= View::partial('partials.sidebar') ?>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <div class="app-main">
        <?= View::partial('partials.topbar', ['title' => $pageTitle]) ?>
        <main class="app-content">
            <div class="container-inner">
                <?php foreach (Flash::pull() as $flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                        <?= e($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endforeach; ?>
                <?= $content ?>
            </div>
        </main>
    </div>
</div>
<script src="<?= asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>

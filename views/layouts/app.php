<?php
/** @var string $content */
use Niyanta\Core\Branding;
use Niyanta\Core\Theme;
use Niyanta\Core\Flash;

$theme = Theme::current();
$company = Branding::companyName();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= e($theme) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($company) ?> — Niyanta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('assets/css/app.css') ?>" rel="stylesheet">
    <style><?= Branding::cssVariables() ?></style>
</head>
<body>
<div class="app-shell">
    <?= \Niyanta\Core\View::partial('partials.sidebar') ?>
    <div class="app-main">
        <?= \Niyanta\Core\View::partial('partials.topbar') ?>
        <main class="app-content container-fluid py-4">
            <?php foreach (Flash::pull() as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
            <?= $content ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>

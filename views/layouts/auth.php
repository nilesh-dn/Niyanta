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
    <title>Sign in — <?= e($company) ?></title>
    <link href="<?= asset('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('assets/css/app.css') ?>" rel="stylesheet">
    <style><?= Branding::cssVariables() ?></style>
</head>
<body class="auth-body">
<div class="auth-wrap">
    <div class="card auth-card shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <?php if ($logo = Branding::logoUrl()): ?>
                    <img src="<?= e($logo) ?>" alt="<?= e($company) ?>" class="auth-logo mb-2">
                <?php else: ?>
                    <div class="brand-badge mb-2"><?= e(mb_substr($company, 0, 1)) ?></div>
                <?php endif; ?>
                <h1 class="h4 mb-0"><?= e($company) ?></h1>
                <p class="text-muted small">Powered by Niyanta</p>
            </div>
            <?php foreach (Flash::pull() as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>
            <?= $content ?>
        </div>
    </div>
</div>
<script src="<?= asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>

<?php /** @var string $content @var int $step */ ?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install Niyanta</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background: #0B1F4D; min-height: 100vh; }
        .install-wrap { max-width: 620px; margin: 0 auto; padding: 2.5rem 1rem; }
        .install-card { border: none; border-radius: 14px; }
        .steps { display: flex; gap: .5rem; margin-bottom: 1.5rem; }
        .steps .dot { flex: 1; height: 6px; border-radius: 3px; background: rgba(255,255,255,.25); }
        .steps .dot.done { background: #FFD84D; }
        .install-brand { color: #fff; text-align: center; margin-bottom: 1.25rem; }
    </style>
</head>
<body>
<div class="install-wrap">
    <div class="install-brand">
        <h1 class="h3 mb-1">Niyanta</h1>
        <p class="mb-0 opacity-75">Employee Management System — Installation</p>
    </div>
    <div class="steps">
        <?php for ($i = 1; $i <= 4; $i++): ?>
            <span class="dot <?= $i <= ($step ?? 1) ? 'done' : '' ?>"></span>
        <?php endfor; ?>
    </div>
    <div class="card install-card shadow">
        <div class="card-body p-4 p-md-5">
            <?php foreach ($errors ?? [] as $err): ?>
                <div class="alert alert-danger"><?= e($err) ?></div>
            <?php endforeach; ?>
            <?= $content ?>
        </div>
    </div>
</div>
</body>
</html>

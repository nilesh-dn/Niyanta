<?php /** @var array $checks @var bool $ok */ ?>
<h2 class="h4 mb-3">Welcome</h2>
<p class="text-muted">This wizard will set up your database, create the first administrator account and generate your <code>config.php</code>.</p>

<ul class="list-group mb-4">
    <?php foreach ($checks as $label => $pass): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= e($label) ?>
            <?php if ($pass): ?>
                <span class="badge text-bg-success"><i class="bi bi-check-lg"></i> OK</span>
            <?php else: ?>
                <span class="badge text-bg-danger"><i class="bi bi-x-lg"></i> Missing</span>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>

<form method="post" action="?step=1">
    <button class="btn btn-primary w-100" type="submit" <?= $ok ? '' : 'disabled' ?>>
        Get started <i class="bi bi-arrow-right ms-1"></i>
    </button>
    <?php if (!$ok): ?>
        <p class="text-danger small mt-2 mb-0">Please resolve the missing requirements before continuing.</p>
    <?php endif; ?>
</form>

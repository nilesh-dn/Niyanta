<div class="text-center py-5">
    <i class="bi bi-compass display-3 text-muted"></i>
    <h1 class="h3 mt-3">Page not found</h1>
    <p class="text-muted">We couldn't find <code><?= e($path ?? '') ?></code>.</p>
    <a href="<?= e(url('/')) ?>" class="btn btn-primary">Go home</a>
</div>

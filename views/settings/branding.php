<?php
/** @var string $company @var ?string $logo @var array $palettes @var array $active */
?>
<h1 class="h3 mb-4">Branding</h1>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3">Company details</h2>
                <form method="post" action="<?= e(url('/settings/branding')) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="company_name">Company name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="<?= e($company) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="logo">Square logo</label>
                        <?php if ($logo): ?>
                            <div class="mb-2"><img src="<?= e($logo) ?>" alt="Logo" class="logo-preview"></div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                    </div>
                    <button class="btn btn-primary" type="submit">Save</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3">Colour palettes</h2>
                <div class="row g-3 mb-4">
                    <?php foreach ($palettes as $p): ?>
                        <div class="col-12 col-md-6">
                            <div class="palette-card <?= (int) $p['is_active'] === 1 ? 'is-active' : '' ?>">
                                <div class="palette-swatches">
                                    <span style="background:<?= e($p['primary']) ?>"></span>
                                    <span style="background:<?= e($p['secondary']) ?>"></span>
                                    <span style="background:<?= e($p['accent']) ?>"></span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <strong><?= e($p['name']) ?></strong>
                                    <div class="d-flex gap-1">
                                        <?php if ((int) $p['is_active'] === 1): ?>
                                            <span class="badge text-bg-primary">Active</span>
                                        <?php else: ?>
                                            <form method="post" action="<?= e(url('/settings/palettes/activate')) ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                                <button class="btn btn-sm btn-outline-primary" type="submit">Use</button>
                                            </form>
                                            <form method="post" action="<?= e(url('/settings/palettes/delete')) ?>" onsubmit="return confirm('Delete this palette?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h3 class="h6 mb-3">Create a custom palette</h3>
                <form method="post" action="<?= e(url('/settings/palettes')) ?>" class="row g-2 align-items-end">
                    <?= csrf_field() ?>
                    <div class="col-12 col-sm">
                        <label class="form-label small">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="My palette" required>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small">Primary</label>
                        <input type="color" class="form-control form-control-color" name="primary" value="#0B1F4D">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small">Secondary</label>
                        <input type="color" class="form-control form-control-color" name="secondary" value="#6EC1FF">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small">Accent</label>
                        <input type="color" class="form-control form-control-color" name="accent" value="#FFD84D">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" type="submit">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

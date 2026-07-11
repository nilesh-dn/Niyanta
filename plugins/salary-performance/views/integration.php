<?php /** @var string $baseUrl @var string $token @var bool $enabled @var string $currency */ ?>
<div class="page-header">
    <div>
        <h1>Apploye Integration</h1>
        <p class="page-sub">Connect the Apploye time-tracking API to pull attendance hours.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(url('/salary')) ?>" class="btn btn-link">Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3">Connection</h2>
                <form method="post" action="<?= e(url('/salary/integration/save')) ?>">
                    <?= csrf_field() ?>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="enabled" name="enabled" value="1" <?= $enabled ? 'checked' : '' ?>>
                        <label class="form-check-label" for="enabled">Enable Apploye integration</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Base URL</label>
                        <input type="url" class="form-control" name="base_url" value="<?= e($baseUrl) ?>" placeholder="https://api.apploye.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Token</label>
                        <input type="text" class="form-control" name="api_token" value="<?= e($token) ?>" placeholder="Bearer token" autocomplete="off">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Currency label</label>
                            <input type="text" class="form-control" name="currency" value="<?= e($currency) ?>" maxlength="8">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Salary-slip "From" email</label>
                            <input type="email" class="form-control" name="mail_from" value="<?= e((string) setting('mail_from', '')) ?>" placeholder="payroll@company.com">
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit"><i class="bi bi-check2 me-1"></i>Save settings</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h6 mb-3">Sync all</h2>
                <p class="text-muted small">Pull this month's hours from Apploye for every salary profile that has a member reference.</p>
                <form method="post" action="<?= e(url('/salary/integration/sync')) ?>" class="d-flex gap-2 align-items-end">
                    <?= csrf_field() ?>
                    <div>
                        <label class="form-label small">Month</label>
                        <input type="month" class="form-control" name="period_month" value="<?= e(\SalaryPerf\Calc::currentMonth()) ?>">
                    </div>
                    <button class="btn btn-outline-primary" type="submit" <?= $enabled ? '' : 'disabled' ?>><i class="bi bi-arrow-repeat me-1"></i>Sync</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-2">About</h2>
                <p class="text-muted small mb-0">
                    This integration layer targets the
                    <a href="https://apploye-com.s3.amazonaws.com/time-tracking-api/index.html" target="_blank" rel="noopener">Apploye time-tracking API</a>.
                    Endpoint paths and field mappings are centralised in <code>AppolyeClient</code> and can be adjusted to your API
                    version. Without the integration, attendance can always be entered manually.
                </p>
            </div>
        </div>
    </div>
</div>

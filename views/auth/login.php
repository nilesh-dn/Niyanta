<form method="post" action="<?= e(url('/login')) ?>">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label" for="email">Email address</label>
        <input type="email" class="form-control" id="email" name="email"
               value="<?= e(old('email')) ?>" required autofocus autocomplete="username">
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <input type="password" class="form-control" id="password" name="password"
               required autocomplete="current-password">
    </div>
    <button type="submit" class="btn btn-primary w-100">Sign in</button>
</form>

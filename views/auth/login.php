<section class="guest-card">
    <div class="page-header">
        <p class="eyebrow">Welcome Back</p>
        <h1>Login</h1>
        <p>Use your username or email and password to sign in.</p>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="/login">
        <div class="form-grid">
            <div class="field-group">
                <label class="field-label" for="identifier">Username or Email</label>
                <input id="identifier" name="identifier" type="text" required value="<?= htmlspecialchars((string) ($old['identifier'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($errors['identifier'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars((string) $errors['identifier'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>

            <div class="field-group">
                <label class="field-label" for="password">Password</label>
                <input id="password" name="password" type="password" required>
                <?php if (!empty($errors['password'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars((string) $errors['password'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button class="btn" type="submit">Login</button>
            </div>
        </div>
    </form>
</section>
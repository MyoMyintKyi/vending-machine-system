<main>
    <section class="card">
        <h1>Login</h1>
        <p>Use your username or email and password to sign in.</p>

        <?php if (!empty($flash)): ?>
            <div class="flash"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="/login">
            <label for="identifier">Username or Email</label>
            <input id="identifier" name="identifier" type="text" required value="<?= htmlspecialchars((string) ($old['identifier'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['identifier'])): ?>
                <div class="error"><?= htmlspecialchars((string) $errors['identifier'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
            <?php if (!empty($errors['password'])): ?>
                <div class="error"><?= htmlspecialchars((string) $errors['password'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <button type="submit">Login</button>
        </form>
    </section>
</main>
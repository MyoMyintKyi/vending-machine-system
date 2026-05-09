<main>
    <section class="card">
        <h1>Register</h1>
        <p>Create a standard user account for the vending machine system.</p>

        <?php if (!empty($errors['form'])): ?>
            <div class="error"><?= htmlspecialchars((string) $errors['form'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="/register">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" required value="<?= htmlspecialchars((string) ($old['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['username'])): ?>
                <div class="error"><?= htmlspecialchars((string) $errors['username'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <label for="email">Email</label>
            <input id="email" name="email" type="email" required value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['email'])): ?>
                <div class="error"><?= htmlspecialchars((string) $errors['email'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" minlength="8" required>
            <?php if (!empty($errors['password'])): ?>
                <div class="error"><?= htmlspecialchars((string) $errors['password'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required>
            <?php if (!empty($errors['password_confirmation'])): ?>
                <div class="error"><?= htmlspecialchars((string) $errors['password_confirmation'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <button type="submit">Register</button>
        </form>
    </section>
</main>
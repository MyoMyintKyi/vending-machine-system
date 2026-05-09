<main>
    <section class="card">
        <h1><?= htmlspecialchars((string) ($title ?? 'Dashboard'), ENT_QUOTES, 'UTF-8') ?></h1>

        <?php if (!empty($flash)): ?>
            <div class="flash"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <p>You are logged in.</p>
        <ul>
            <li>Username: <?= htmlspecialchars((string) ($username ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
            <li>Email: <?= htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
            <li>Role: <?= htmlspecialchars((string) ($role ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
        </ul>

        <?php if (($role ?? '') === 'Admin'): ?>
            <p><a href="/products">Go to product management</a></p>
        <?php elseif (($role ?? '') === 'User'): ?>
            <p><a href="/products">Browse products to purchase</a></p>
        <?php endif; ?>
    </section>
</main>
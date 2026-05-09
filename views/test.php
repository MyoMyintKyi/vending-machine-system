<main>
    <div class="card">
    <h1><?= htmlspecialchars($title ?? 'Test Route', ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars($message ?? '', ENT_QUOTES, 'UTF-8') ?></p>
    <ul>
        <li>Path: <?= htmlspecialchars($path ?? '', ENT_QUOTES, 'UTF-8') ?></li>
        <li>Method: <?= htmlspecialchars($method ?? '', ENT_QUOTES, 'UTF-8') ?></li>
        <?php if (!empty($username)): ?>
            <li>Username: <?= htmlspecialchars((string) $username, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endif; ?>
        <?php if (!empty($role)): ?>
            <li>Role: <?= htmlspecialchars((string) $role, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endif; ?>
    </ul>
    </div>
</main>
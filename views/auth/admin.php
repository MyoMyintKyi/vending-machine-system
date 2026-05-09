<main>
    <section class="card">
        <h1><?= htmlspecialchars((string) ($title ?? 'Admin Area'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p>This page is available only when the active session role is <strong>Admin</strong>.</p>
        <ul>
            <li>Username: <?= htmlspecialchars((string) ($username ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
            <li>Role: <?= htmlspecialchars((string) ($role ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
        </ul>
        <p><a href="/products">Open product management</a></p>
    </section>
</main>
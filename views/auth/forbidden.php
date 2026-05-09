<main>
    <section class="card">
        <h1><?= htmlspecialchars((string) ($title ?? 'Access Denied'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars((string) ($message ?? 'You are not allowed to access this page.'), ENT_QUOTES, 'UTF-8') ?></p>
        <ul>
            <li>Required role: <?= htmlspecialchars((string) ($requiredRole ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
            <li>Current role: <?= htmlspecialchars((string) ($currentRole ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
        </ul>
        <p><a href="/dashboard">Return to dashboard</a></p>
    </section>
</main>
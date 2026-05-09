<section class="page-header page-header-split">
    <div class="page-header-copy">
        <h1><?= htmlspecialchars((string) ($title ?? 'Dashboard'), ENT_QUOTES, 'UTF-8') ?></h1>
    </div>

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            <li><span class="breadcrumb-current">Dashboard</span></li>
        </ol>
    </nav>
</section>

<?php if (!empty($flash)): ?>
    <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<section class="metric-grid">
    <article class="metric-card">
        <p class="metric-label">Username</p>
        <p class="metric-value"><?= htmlspecialchars((string) ($username ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    </article>
    <article class="metric-card">
        <p class="metric-label">Email</p>
        <p class="metric-value"><?= htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    </article>
    <article class="metric-card">
        <p class="metric-label">Active Role</p>
        <p class="metric-value"><?= htmlspecialchars((string) ($role ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    </article>
</section>
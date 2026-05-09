<section class="page-header page-header-split">
    <div class="page-header-copy">
        <p class="eyebrow">Overview</p>
        <h1><?= htmlspecialchars((string) ($title ?? 'Dashboard'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p>Monitor your session details and jump directly into the most relevant workflows for your current role.</p>
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

<section class="split-grid">
    <article class="page-card">
        <div class="page-header">
            <p class="eyebrow">Session</p>
            <h1>Account Summary</h1>
            <p>Your authenticated session is active and ready for inventory operations.</p>
        </div>

        <dl class="info-list">
            <div>
                <dt>Username</dt>
                <dd><?= htmlspecialchars((string) ($username ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
            <div>
                <dt>Email</dt>
                <dd><?= htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
            <div>
                <dt>Role</dt>
                <dd><?= htmlspecialchars((string) ($role ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
        </dl>
    </article>

    <aside class="stack-grid">
        <article class="page-card">
            <p class="section-heading" style="color: var(--primary); margin-bottom: 0.8rem;">Quick Actions</p>
            <div class="quick-actions">
                <?php if (($role ?? '') === 'Admin'): ?>
                    <a class="btn" href="/products">Go to product management</a>
                    <a class="btn-secondary" href="/admin">Open admin area</a>
                <?php elseif (($role ?? '') === 'User'): ?>
                    <a class="btn" href="/products">Browse products to purchase</a>
                <?php endif; ?>
            </div>
        </article>
    </aside>
</section>
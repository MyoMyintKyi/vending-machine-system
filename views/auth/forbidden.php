<section class="page-header page-header-split">
    <div class="page-header-copy">
        <p class="eyebrow">Permission Check</p>
        <h1><?= htmlspecialchars((string) ($title ?? 'Access Denied'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars((string) ($message ?? 'You are not allowed to access this page.'), ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            <li><a href="/dashboard">Dashboard</a></li>
            <li><span class="breadcrumb-current">Access Denied</span></li>
        </ol>
    </nav>
</section>

<section class="split-grid">
    <article class="page-card">
        <div class="alert alert-warning">This screen is visible because your account is authenticated, but the requested action is restricted to another role.</div>

        <dl class="info-list">
            <div>
                <dt>Required Role</dt>
                <dd><?= htmlspecialchars((string) ($requiredRole ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
            <div>
                <dt>Current Role</dt>
                <dd><?= htmlspecialchars((string) ($currentRole ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
        </dl>
    </article>

    <article class="page-card">
        <p class="section-heading" style="color: var(--primary); margin-bottom: 0.8rem;">Next Step</p>
        <div class="quick-actions">
            <a class="btn-secondary" href="/dashboard">Return to dashboard</a>
        </div>
    </article>
</section>
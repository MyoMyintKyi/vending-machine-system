<section class="page-header page-header-split">
    <div class="page-header-copy">
        <p class="eyebrow">Administrative Access</p>
        <h1><?= htmlspecialchars((string) ($title ?? 'Admin Area'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p>This area is reserved for stock owners and operators with full management access.</p>
    </div>

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            <li><a href="/dashboard">Dashboard</a></li>
            <li><span class="breadcrumb-current">Admin</span></li>
        </ol>
    </nav>
</section>

<section class="split-grid">
    <article class="page-card">
        <div class="page-header">
            <p class="eyebrow">Authorization</p>
            <h1>Current Access</h1>
            <p>You are currently using an elevated session with access to product administration workflows.</p>
        </div>

        <dl class="info-list">
            <div>
                <dt>Username</dt>
                <dd><?= htmlspecialchars((string) ($username ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
            <div>
                <dt>Role</dt>
                <dd><?= htmlspecialchars((string) ($role ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
        </dl>
    </article>

    <article class="page-card">
        <p class="section-heading" style="color: var(--primary); margin-bottom: 0.8rem;">Actions</p>
        <div class="quick-actions">
            <a class="btn" href="/products">Open product management</a>
            <a class="btn-secondary" href="/dashboard">Return to dashboard</a>
        </div>
    </article>
</section>
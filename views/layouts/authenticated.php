<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vending Machine</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="page-shell">
<div class="auth-shell">
    <aside class="auth-sidebar">
        <div class="brand">
            <div class="brand-mark">VM</div>
            <div class="brand-copy">
                <h1>Vending Machine</h1>
                <p>Operations console</p>
            </div>
        </div>

        <div>
            <p class="section-heading">Workspace</p>
            <nav class="auth-nav">
                <a href="/dashboard">Dashboard</a>
                <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
                    <a href="/products">Manage Products</a>
                    <a href="/admin">Admin</a>
                <?php elseif (($_SESSION['role'] ?? '') === 'User'): ?>
                    <a href="/products">Browse Products</a>
                <?php endif; ?>
            </nav>
        </div>

        <div class="sidebar-footer">
            <p>Signed in as <?= htmlspecialchars((string) ($_SESSION['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) ($_SESSION['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)</p>
            <p class="muted">Session navigation adapts to your role.</p>
        </div>
    </aside>

    <div class="auth-main">
        <header class="topbar">
            <div>
                <p class="topbar-title">Operations Dashboard</p>
                <p class="topbar-subtitle">Manage inventory, purchases, and admin workflows from one place.</p>
            </div>
            <div class="topbar-actions">
                <span class="user-badge">Signed in as <?= htmlspecialchars((string) ($_SESSION['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) ($_SESSION['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)</span>
                <form action="/logout" method="post">
                    <button class="btn btn-logout" type="submit">Logout</button>
                </form>
            </div>
        </header>

        <main class="content-wrap">
            <?= $content ?>
        </main>
    </div>
</div>
</body>
</html>
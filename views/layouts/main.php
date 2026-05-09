<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vending Machine</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; margin: 2rem auto; max-width: 960px; line-height: 1.5; padding: 0 1rem; }
        nav { display: flex; gap: 1rem; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; }
        nav form { display: inline; margin: 0; }
        a { color: #0b57d0; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .card { border: 1px solid #d0d7de; border-radius: 12px; padding: 1rem 1.25rem; background: #fff; }
        .flash { background: #eef6ff; border: 1px solid #b6d4fe; color: #0a3d91; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .error { color: #b42318; margin-top: 0.25rem; }
        label { display: block; margin-top: 1rem; font-weight: 600; }
        input { width: 100%; max-width: 420px; padding: 0.65rem 0.75rem; margin-top: 0.35rem; border: 1px solid #cbd5e1; border-radius: 8px; }
        button { margin-top: 1rem; padding: 0.7rem 1rem; border: 0; border-radius: 8px; background: #111827; color: #fff; cursor: pointer; }
    </style>
</head>
<body>
<nav>
    <?php if (!empty($_SESSION['authenticated'])): ?>
        <a href="/dashboard">Dashboard</a>
        <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
            <a href="/products">Manage Products</a>
            <a href="/admin">Admin</a>
        <?php elseif (($_SESSION['role'] ?? '') === 'User'): ?>
            <a href="/products">Browse Products</a>
        <?php endif; ?>
        <span>Signed in as <?= htmlspecialchars((string) ($_SESSION['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) ($_SESSION['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)</span>
        <form action="/logout" method="post">
            <button type="submit">Logout</button>
        </form>
    <?php else: ?>
        <a href="/login">Login</a>
    <?php endif; ?>
</nav>
<?= $content ?>
</body>
</html>
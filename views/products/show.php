<section class="page-header page-header-split">
    <div class="page-header-copy">
        <h1><?= htmlspecialchars((string) ($product['name'] ?? 'Product'), ENT_QUOTES, 'UTF-8') ?></h1>
    </div>

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            <li><a href="/dashboard">Dashboard</a></li>
            <li><a href="/products">Products</a></li>
            <li><span class="breadcrumb-current"><?= htmlspecialchars((string) ($product['name'] ?? 'Product'), ENT_QUOTES, 'UTF-8') ?></span></li>
        </ol>
    </nav>
</section>

<?php if (!empty($flash)): ?>
    <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<section class="split-grid">
    <article class="page-card">
        <dl class="info-list">
            <div>
                <dt>Price</dt>
                <dd><?= htmlspecialchars(number_format((float) ($product['price'] ?? 0), 3, '.', ''), ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
            <div>
                <dt>Quantity Available</dt>
                <dd><?= htmlspecialchars((string) ($product['quantity_available'] ?? 0), ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
            <div>
                <dt>Created At</dt>
                <dd><?= htmlspecialchars((string) ($product['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
            <div>
                <dt>Updated At</dt>
                <dd><?= htmlspecialchars((string) ($product['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
        </dl>
    </article>

    <article class="page-card">
        <p class="section-heading" style="color: var(--primary); margin-bottom: 0.8rem;">Actions</p>
        <div class="quick-actions">
            <a class="btn btn-secondary" href="/products">Back to products</a>
            <a class="btn" href="/products/<?= (int) ($product['id'] ?? 0) ?>/purchase">Purchase this product</a>
            <?php if (($role ?? '') === 'Admin'): ?>
                <a class="btn" href="/products/<?= (int) ($product['id'] ?? 0) ?>/edit">Edit this product</a>
                <form action="/products/<?= (int) ($product['id'] ?? 0) ?>/delete" method="post">
                    <button class="btn btn-danger" type="submit">Delete product</button>
                </form>
            <?php endif; ?>
        </div>
    </article>
</section>
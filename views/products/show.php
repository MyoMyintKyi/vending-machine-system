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
        <div class="data-points">
            <div class="data-point">
                <strong>Product Name</strong>
                <span><?= htmlspecialchars((string) ($product['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="data-point">
                <strong>Price</strong>
                <span><?= htmlspecialchars((string) ($product['price'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="data-point">
                <strong>Price</strong>
                <span><?= htmlspecialchars((string) ($product['price'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="data-point">
                <strong>Quantity Available</strong>
                <span><?= htmlspecialchars((string) ($product['quantity_available'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="data-point">
                <strong>Created At</strong>
                <span><?= htmlspecialchars((string) ($product['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </article>

    <article class="page-card">
        <p class="section-heading" style="color: var(--primary); margin-bottom: 0.8rem;">Actions</p>
        <div class="quick-actions">
            <a class="btn btn-secondary" href="/products">Back to products</a>
            <?php if ((int) ($product['quantity_available'] ?? 0) > 0): ?>
                <a class="btn" href="/products/<?= (int) ($product['id'] ?? 0) ?>/purchase">Purchase this product</a>
            <?php else: ?>
                <span class="btn btn-secondary" aria-disabled="true">Out of stock</span>
            <?php endif; ?>
            <?php if (($role ?? '') === 'Admin'): ?>
                <a class="btn" href="/products/<?= (int) ($product['id'] ?? 0) ?>/edit">Edit this product</a>
                <form action="/products/<?= (int) ($product['id'] ?? 0) ?>/delete" method="post">
                    <button class="btn btn-danger" type="submit">Delete product</button>
                </form>
            <?php endif; ?>
        </div>
    </article>
</section>
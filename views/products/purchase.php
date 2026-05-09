<section class="page-header page-header-split">
    <div class="page-header-copy">
        <h1>Purchase <?= htmlspecialchars((string) ($product['name'] ?? 'Product'), ENT_QUOTES, 'UTF-8') ?></h1>
    </div>

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            <li><a href="/dashboard">Dashboard</a></li>
            <li><a href="/products">Products</a></li>
            <li><span class="breadcrumb-current">Purchase</span></li>
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
                <strong>Price</strong>
                <span><?= htmlspecialchars(number_format((float) ($product['price'] ?? 0), 3, '.', ''), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="data-point">
                <strong>Quantity available</strong>
                <span><?= htmlspecialchars((string) ($product['quantity_available'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </article>

    <article class="page-card">
        <?php if (!empty($errors['quantity'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars((string) $errors['quantity'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="/products/<?= (int) ($product['id'] ?? 0) ?>/purchase" class="form-grid">
            <div class="field-group">
                <label class="field-label" for="quantity">Quantity</label>
                <input id="quantity" name="quantity" type="number" min="1" step="1" max="<?= htmlspecialchars((string) ($product['quantity_available'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" required value="<?= htmlspecialchars((string) (($old['quantity'] ?? '1')), ENT_QUOTES, 'UTF-8') ?>">
                <p class="helper-text">Choose a quantity between 1 and the available stock count.</p>
            </div>

            <div class="form-actions">
                <button class="btn" type="submit">Purchase</button>
                <a class="btn-secondary" href="/products/<?= (int) ($product['id'] ?? 0) ?>">Back to product</a>
            </div>
        </form>
    </article>
</section>
<?php
use App\Support\ViewNumberFormatter;
use App\Support\CurrencyFormatter;

$catalogHref = '/catalog';
$purchasePath = product_purchase_path((int) ($product['id'] ?? 0), (string) ($product['name'] ?? 'product'));
?>

<section class="page-header page-header-split">
    <div class="page-header-copy">
        <h1>Purchase</h1>
    </div>

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            <li><a href="/dashboard">Dashboard</a></li>
            <li><a href="<?= htmlspecialchars($catalogHref, ENT_QUOTES, 'UTF-8') ?>">Catalog</a></li>
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
                <strong>Product Name</strong>
                <span><?= htmlspecialchars((string) ($product['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="data-point">
                <strong>Price</strong>
                <span><?= htmlspecialchars(CurrencyFormatter::formatUsd((string) ($product['price'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="data-point">
                <strong>Quantity available</strong>
                <span><?= htmlspecialchars(ViewNumberFormatter::format((string) ($product['quantity_available'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </article>

    <article class="page-card">
        <?php if (!empty($errors['quantity'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars((string) $errors['quantity'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ((int) ($product['quantity_available'] ?? 0) > 0): ?>
            <form method="post" action="<?= htmlspecialchars($purchasePath, ENT_QUOTES, 'UTF-8') ?>" class="form-grid">
                <div class="field-group">
                    <label class="field-label" for="quantity">Purchase Quantity</label>
                    <input id="quantity" name="quantity" type="number" min="1" step="1" max="<?= (int) ($product['quantity_available'] ?? 0) ?>" required value="<?= htmlspecialchars((string) (($old['quantity'] ?? '1')), ENT_QUOTES, 'UTF-8') ?>">
                    <p class="helper-text">Enter the quantity to deduct from available stock.</p>
                </div>

                <div class="form-actions">
                    <a class="btn btn-secondary" href="<?= htmlspecialchars($catalogHref, ENT_QUOTES, 'UTF-8') ?>">Back to product catalog</a>
                    <button class="btn" type="submit">Purchase</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">This product is out of stock and cannot be purchased right now.</div>
            <div class="form-actions">
                <a class="btn btn-secondary" href="<?= htmlspecialchars($catalogHref, ENT_QUOTES, 'UTF-8') ?>">Back to product catalog</a>
            </div>
        <?php endif; ?>
    </article>
</section>
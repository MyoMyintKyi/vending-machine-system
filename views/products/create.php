<section class="page-header page-header-split">
    <div class="page-header-copy">
        <h1>Create Product</h1>
    </div>

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            <li><a href="/dashboard">Dashboard</a></li>
            <li><a href="/products">Products</a></li>
            <li><span class="breadcrumb-current">Create</span></li>
        </ol>
    </nav>
</section>

<section class="page-card">
    <?php if (!empty($errors['form'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars((string) $errors['form'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="/products" class="form-grid">
        <div class="field-group">
            <label class="field-label" for="name">Name</label>
            <input id="name" name="name" type="text" required value="<?= htmlspecialchars((string) ($old['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['name'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars((string) $errors['name'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <div class="field-group">
                <label class="field-label" for="price">Price</label>
                <input id="price" name="price" type="number" min="0.001" step="0.001" required value="<?= htmlspecialchars((string) ($old['price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($errors['price'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars((string) $errors['price'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>

            <div class="field-group">
                <label class="field-label" for="quantity_available">Quantity Available</label>
                <input id="quantity_available" name="quantity_available" type="number" min="0" step="1" required value="<?= htmlspecialchars((string) ($old['quantity_available'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($errors['quantity_available'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars((string) $errors['quantity_available'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn" type="submit">Create Product</button>
            <a class="btn btn-secondary" href="/products">Cancel</a>
        </div>
    </form>
</section>
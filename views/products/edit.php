<main>
    <section class="card">
        <h1>Edit Product</h1>

        <?php if (!empty($errors['form'])): ?>
            <div class="error"><?= htmlspecialchars((string) $errors['form'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="/products/<?= (int) ($product['id'] ?? 0) ?>/update">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" required value="<?= htmlspecialchars((string) ($old['name'] ?? $product['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['name'])): ?>
                <div class="error"><?= htmlspecialchars((string) $errors['name'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <label for="price">Price</label>
            <input id="price" name="price" type="number" min="0.001" step="0.001" required value="<?= htmlspecialchars((string) ($old['price'] ?? $product['price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['price'])): ?>
                <div class="error"><?= htmlspecialchars((string) $errors['price'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <label for="quantity_available">Quantity Available</label>
            <input id="quantity_available" name="quantity_available" type="number" min="0" step="1" required value="<?= htmlspecialchars((string) ($old['quantity_available'] ?? $product['quantity_available'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['quantity_available'])): ?>
                <div class="error"><?= htmlspecialchars((string) $errors['quantity_available'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <button type="submit">Update Product</button>
        </form>
    </section>
</main>
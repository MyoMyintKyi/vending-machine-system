<main>
    <section class="card">
        <h1>Purchase <?= htmlspecialchars((string) ($product['name'] ?? 'Product'), ENT_QUOTES, 'UTF-8') ?></h1>

        <?php if (!empty($flash)): ?>
            <div class="flash"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <ul>
            <li>Price: <?= htmlspecialchars(number_format((float) ($product['price'] ?? 0), 3, '.', ''), ENT_QUOTES, 'UTF-8') ?></li>
            <li>Quantity available: <?= htmlspecialchars((string) ($product['quantity_available'] ?? 0), ENT_QUOTES, 'UTF-8') ?></li>
        </ul>

        <?php if (!empty($errors['quantity'])): ?>
            <div class="error"><?= htmlspecialchars((string) $errors['quantity'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="/products/<?= (int) ($product['id'] ?? 0) ?>/purchase">
            <label for="quantity">Quantity</label>
            <input id="quantity" name="quantity" type="number" min="1" step="1" max="<?= htmlspecialchars((string) ($product['quantity_available'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" required value="<?= htmlspecialchars((string) (($old['quantity'] ?? '1')), ENT_QUOTES, 'UTF-8') ?>">

            <button type="submit">Purchase</button>
        </form>

        <p><a href="/products/<?= (int) ($product['id'] ?? 0) ?>">Back to product</a></p>
    </section>
</main>
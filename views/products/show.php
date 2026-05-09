<main>
    <section class="card">
        <h1><?= htmlspecialchars((string) ($product['name'] ?? 'Product'), ENT_QUOTES, 'UTF-8') ?></h1>

        <?php if (!empty($flash)): ?>
            <div class="flash"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <ul>
            <li>Price: <?= htmlspecialchars(number_format((float) ($product['price'] ?? 0), 3, '.', ''), ENT_QUOTES, 'UTF-8') ?></li>
            <li>Quantity available: <?= htmlspecialchars((string) ($product['quantity_available'] ?? 0), ENT_QUOTES, 'UTF-8') ?></li>
            <li>Created at: <?= htmlspecialchars((string) ($product['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
            <li>Updated at: <?= htmlspecialchars((string) ($product['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
        </ul>

        <p><a href="/products">Back to products</a></p>
        <?php if (($role ?? '') === 'User'): ?>
            <p><a href="/products/<?= (int) ($product['id'] ?? 0) ?>/purchase">Purchase this product</a></p>
        <?php endif; ?>

        <?php if (($role ?? '') === 'Admin'): ?>
            <p><a href="/products/<?= (int) ($product['id'] ?? 0) ?>/edit">Edit this product</a></p>
            <form action="/products/<?= (int) ($product['id'] ?? 0) ?>/delete" method="post">
                <button type="submit">Delete product</button>
            </form>
        <?php endif; ?>
    </section>
</main>
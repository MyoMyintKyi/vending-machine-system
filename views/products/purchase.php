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

        <p>The full purchase workflow is scheduled for Phase 9. This route exists now so the controller and route contract are in place.</p>

        <form method="post" action="/products/<?= (int) ($product['id'] ?? 0) ?>/purchase">
            <button type="submit">Continue</button>
        </form>
    </section>
</main>
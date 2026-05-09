<main>
    <section class="card">
        <h1><?= htmlspecialchars((string) ($title ?? 'Products'), ENT_QUOTES, 'UTF-8') ?></h1>

        <?php if (!empty($flash)): ?>
            <div class="flash"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (($role ?? '') === 'Admin'): ?>
            <p><a href="/products/create">Create product</a></p>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <p>No products are available.</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th><a href="/products?sort=name&direction=<?= ($sort ?? 'name') === 'name' && ($direction ?? 'asc') === 'asc' ? 'desc' : 'asc' ?>">Name</a></th>
                    <th><a href="/products?sort=price&direction=<?= ($sort ?? 'name') === 'price' && ($direction ?? 'asc') === 'asc' ? 'desc' : 'asc' ?>">Price</a></th>
                    <th><a href="/products?sort=quantity_available&direction=<?= ($sort ?? 'name') === 'quantity_available' && ($direction ?? 'asc') === 'asc' ? 'desc' : 'asc' ?>">Quantity</a></th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(number_format((float) $product['price'], 3, '.', ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $product['quantity_available'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="/products/<?= (int) $product['id'] ?>">View</a>
                            <?php if (($role ?? '') === 'User'): ?>
                                <a href="/products/<?= (int) $product['id'] ?>/purchase">Purchase</a>
                            <?php endif; ?>
                            <?php if (($role ?? '') === 'Admin'): ?>
                                <a href="/products/<?= (int) $product['id'] ?>/edit">Edit</a>
                                <form action="/products/<?= (int) $product['id'] ?>/delete" method="post" style="display:inline">
                                    <button type="submit">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (($totalPages ?? 1) > 1): ?>
                <nav>
                    <?php if (!empty($hasPreviousPage)): ?>
                        <a href="/products?page=<?= (int) (($page ?? 1) - 1) ?>&sort=<?= urlencode((string) ($sort ?? 'name')) ?>&direction=<?= urlencode((string) ($direction ?? 'asc')) ?>">Previous</a>
                    <?php endif; ?>

                    <span>Page <?= (int) ($page ?? 1) ?> of <?= (int) ($totalPages ?? 1) ?></span>

                    <?php if (!empty($hasNextPage)): ?>
                        <a href="/products?page=<?= (int) (($page ?? 1) + 1) ?>&sort=<?= urlencode((string) ($sort ?? 'name')) ?>&direction=<?= urlencode((string) ($direction ?? 'asc')) ?>">Next</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>
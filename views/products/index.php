<?php
$currentPage = max(1, (int) ($page ?? 1));
$totalPageCount = max(1, (int) ($totalPages ?? 1));
$itemsPerPage = max(1, (int) ($perPage ?? 10));
$sortField = (string) ($sort ?? 'name');
$sortOrder = (string) ($direction ?? 'asc');
$products = is_array($products ?? null) ? $products : [];
$startingRowNumber = (($currentPage - 1) * $itemsPerPage) + 1;
$endingRowNumber = $startingRowNumber + max(0, count($products) - 1);
$pageItems = [];

if ($totalPageCount <= 10) {
    for ($pageNumber = 1; $pageNumber <= $totalPageCount; $pageNumber++) {
        $pageItems[] = [
            'type' => 'page',
            'number' => $pageNumber,
        ];
    }
} else {
    $pagesToShow = [1, 2, 3, $currentPage - 1, $currentPage, $currentPage + 1, $totalPageCount - 2, $totalPageCount - 1, $totalPageCount];
    $pagesToShow = array_values(array_unique(array_filter($pagesToShow, static fn (int $pageNumber): bool => $pageNumber >= 1 && $pageNumber <= $totalPageCount)));
    sort($pagesToShow);

    $previousPageNumber = null;

    foreach ($pagesToShow as $pageNumber) {
        if ($previousPageNumber !== null) {
            $gap = $pageNumber - $previousPageNumber;

            if ($gap === 2) {
                $pageItems[] = [
                    'type' => 'page',
                    'number' => $previousPageNumber + 1,
                ];
            } elseif ($gap > 2) {
                $pageItems[] = [
                    'type' => 'ellipsis',
                ];
            }
        }

        $pageItems[] = [
            'type' => 'page',
            'number' => $pageNumber,
        ];

        $previousPageNumber = $pageNumber;
    }
}
?>

<section class="page-header page-header-split">
    <div class="page-header-copy">
        <h1><?= htmlspecialchars((string) ($title ?? 'Products'), ENT_QUOTES, 'UTF-8') ?></h1>
    </div>

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            <li><a href="/dashboard">Dashboard</a></li>
            <li><span class="breadcrumb-current">Products</span></li>
        </ol>
    </nav>
</section>

<?php if (!empty($flash)): ?>
    <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<section class="table-card">
    <div class="table-card-header">
        <div></div>
        <div class="table-tools">
            <?php if (($role ?? '') === 'Admin'): ?>
                <a class="btn" href="/products/create">Create product</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($products === []): ?>
        <div class="empty-state">No products are available.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th class="row-number-heading">
                        <span class="not-sort-link">No.</span>
                    </th>
                    <th>
                        <a class="sort-link<?= $sortField === 'name' ? ' is-active' : '' ?>" href="/products?sort=name&direction=<?= $sortField === 'name' && $sortOrder === 'asc' ? 'desc' : 'asc' ?>">
                            <span>Name</span>
                            <span class="sort-direction"><?= $sortField === 'name' ? ($sortOrder === 'desc' ? '↓' : '↑') : '↓↑' ?></span>
                        </a>
                    </th>
                    <th>
                        <a class="sort-link<?= $sortField === 'price' ? ' is-active' : '' ?>" href="/products?sort=price&direction=<?= $sortField === 'price' && $sortOrder === 'asc' ? 'desc' : 'asc' ?>">
                            <span>Price</span>
                            <span class="sort-direction"><?= $sortField === 'price' ? ($sortOrder === 'desc' ? '↓' : '↑') : '↓↑' ?></span>
                        </a>
                    </th>
                    <th>
                        <a class="sort-link<?= $sortField === 'quantity_available' ? ' is-active' : '' ?>" href="/products?sort=quantity_available&direction=<?= $sortField === 'quantity_available' && $sortOrder === 'asc' ? 'desc' : 'asc' ?>">
                            <span>Quantity</span>
                            <span class="sort-direction"><?= $sortField === 'quantity_available' ? ($sortOrder === 'desc' ? '↓' : '↑') : '↓↑' ?></span>
                        </a>
                    </th>
                    <th class="action-column-heading">
                        <span class="not-sort-link">Actions</span>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $index => $product): ?>
                    <tr>
                        <td class="row-number-cell"><?= $startingRowNumber + $index ?></td>
                        <td>
                            <strong><?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </td>
                        <td><?= htmlspecialchars((string) $product['price'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <span class="status-pill<?= (int) $product['quantity_available'] === 0 ? ' is-warning' : '' ?>">
                                <?= htmlspecialchars((string) $product['quantity_available'], ENT_QUOTES, 'UTF-8') ?> in stock
                            </span>
                        </td>
                        <td class="action-column-cell">
                            <div class="inline-actions">
                                <a class="page-link" href="/products/<?= (int) $product['id'] ?>">View</a>
                                <?php if ((int) $product['quantity_available'] > 0): ?>
                                    <a class="page-link" href="/products/<?= (int) $product['id'] ?>/purchase">Purchase</a>
                                <?php else: ?>
                                    <span class="page-link is-disabled" aria-disabled="true">Out of stock</span>
                                <?php endif; ?>
                                <?php if (($role ?? '') === 'Admin'): ?>
                                    <a class="page-link" href="/products/<?= (int) $product['id'] ?>/edit">Edit</a>
                                    <form action="/products/<?= (int) $product['id'] ?>/delete" method="post">
                                        <button class="btn btn-danger" type="submit">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPageCount > 1): ?>
            <div class="pagination">
                <span class="pagination-summary">Showing <?= $startingRowNumber ?> - <?= $endingRowNumber ?> | Page <?= $currentPage ?> of <?= $totalPageCount ?></span>
                <nav class="pagination-nav" aria-label="Products pagination">
                    <?php if (!empty($hasPreviousPage)): ?>
                        <a class="pagination-link" href="/products?page=<?= $currentPage - 1 ?>&sort=<?= urlencode($sortField) ?>&direction=<?= urlencode($sortOrder) ?>">Previous</a>
                    <?php else: ?>
                        <span class="pagination-link is-disabled">Previous</span>
                    <?php endif; ?>

                    <?php foreach ($pageItems as $pageItem): ?>
                        <?php if ($pageItem['type'] === 'ellipsis'): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php else: ?>
                            <a class="pagination-link<?= $pageItem['number'] === $currentPage ? ' is-active' : '' ?>" href="/products?page=<?= $pageItem['number'] ?>&sort=<?= urlencode($sortField) ?>&direction=<?= urlencode($sortOrder) ?>"><?= $pageItem['number'] ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (!empty($hasNextPage)): ?>
                        <a class="pagination-link" href="/products?page=<?= $currentPage + 1 ?>&sort=<?= urlencode($sortField) ?>&direction=<?= urlencode($sortOrder) ?>">Next</a>
                    <?php else: ?>
                        <span class="pagination-link is-disabled">Next</span>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
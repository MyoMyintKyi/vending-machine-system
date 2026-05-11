<?php
use App\Support\CurrencyFormatter;
use App\Support\ViewNumberFormatter;

$products = is_array($products ?? null) ? $products : [];
$filters = is_array($filters ?? null) ? $filters : [];
$activeName = (string) ($filters['name'] ?? '');
$activeSort = (string) ($sort ?? 'name_asc');
$hasActiveFilters = $activeName !== '';
$currentPage = max(1, (int) ($page ?? 1));
$totalPageCount = max(1, (int) ($totalPages ?? 1));
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

$queryBase = [
    'name' => $activeName,
    'sort' => $activeSort,
];
?>

<section class="page-header page-header-split">
    <div class="page-header-copy">
        <h1><?= htmlspecialchars((string) ($title ?? 'Product Catalog'), ENT_QUOTES, 'UTF-8') ?></h1>
    </div>

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            <li><a href="/dashboard">Dashboard</a></li>
            <li><span class="breadcrumb-current">Catalog</span></li>
        </ol>
    </nav>
</section>

<?php if (!empty($flash)): ?>
    <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<section class="page-card" style="margin-bottom: 1rem;">
    <form method="get" action="/catalog" class="transaction-filter-form">
        <div class="field-group">
            <label class="field-label" for="name">Product name</label>
            <input id="name" name="name" type="text" value="<?= htmlspecialchars($activeName, ENT_QUOTES, 'UTF-8') ?>" placeholder="Filter by product name">
        </div>

        <div class="field-group">
            <label class="field-label" for="sort">Sort by</label>
            <select id="sort" name="sort">
                <option value="name_asc"<?= $activeSort === 'name_asc' ? ' selected' : '' ?>>Name A-Z</option>
                <option value="name_desc"<?= $activeSort === 'name_desc' ? ' selected' : '' ?>>Name Z-A</option>
                <option value="price_asc"<?= $activeSort === 'price_asc' ? ' selected' : '' ?>>Price low to high</option>
                <option value="price_desc"<?= $activeSort === 'price_desc' ? ' selected' : '' ?>>Price high to low</option>
            </select>
        </div>

        <div class="form-actions">
            <button class="btn" type="submit">Apply</button>
            <a class="btn btn-secondary" href="/catalog">Reset</a>
        </div>
    </form>
</section>

<?php if ($products === []): ?>
    <section class="page-card">
        <div class="empty-state"><?= $hasActiveFilters ? 'No products matched the current name filter.' : 'No products are available in the catalog right now.' ?></div>
    </section>
<?php else: ?>
    <section class="product-catalog-grid" aria-label="Product catalog">
        <?php foreach ($products as $product): ?>
            <?php $isInStock = (int) ($product['quantity_available'] ?? 0) > 0; ?>
            <article class="page-card product-catalog-card">
                <div class="product-catalog-card-header">
                    <div>
                        <p class="helper-text">Product #<?= htmlspecialchars(ViewNumberFormatter::format((string) ($product['id'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></p>
                        <h2><?= htmlspecialchars((string) ($product['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                </div>

                <div class="product-catalog-price"><?= htmlspecialchars(CurrencyFormatter::formatUsd((string) ($product['price'] ?? '0')), ENT_QUOTES, 'UTF-8') ?></div>
                    
                <!-- <div class="product-catalog-meta-item">
                    <strong>Stock</strong>
                    <span><?= htmlspecialchars(ViewNumberFormatter::format((string) ($product['quantity_available'] ?? 0)), ENT_QUOTES, 'UTF-8') ?> units</span>
                </div> -->
                <div class="product-catalog-actions">
                    <?php if ($isInStock): ?>
                        <a class="btn" href="<?= htmlspecialchars(product_purchase_path((int) $product['id'], (string) $product['name']), ENT_QUOTES, 'UTF-8') ?>">Buy now</a>
                    <?php else: ?>
                        <span class="btn btn-secondary" aria-disabled="true">Out of stock</span>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <?php if ($totalPageCount > 1): ?>
        <nav class="product-catalog-pagination" aria-label="Catalog pagination">
            <?php if (!empty($hasPreviousPage)): ?>
                <a class="pagination-link" href="/catalog?<?= htmlspecialchars(http_build_query(array_merge($queryBase, ['page' => $currentPage - 1])), ENT_QUOTES, 'UTF-8') ?>">Previous</a>
            <?php else: ?>
                <span class="pagination-link is-disabled">Previous</span>
            <?php endif; ?>

            <?php foreach ($pageItems as $pageItem): ?>
                <?php if ($pageItem['type'] === 'ellipsis'): ?>
                    <span class="pagination-ellipsis">...</span>
                <?php else: ?>
                    <a class="pagination-link<?= $pageItem['number'] === $currentPage ? ' is-active' : '' ?>" href="/catalog?<?= htmlspecialchars(http_build_query(array_merge($queryBase, ['page' => $pageItem['number']])), ENT_QUOTES, 'UTF-8') ?>"><?= $pageItem['number'] ?></a>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (!empty($hasNextPage)): ?>
                <a class="pagination-link" href="/catalog?<?= htmlspecialchars(http_build_query(array_merge($queryBase, ['page' => $currentPage + 1])), ENT_QUOTES, 'UTF-8') ?>">Next</a>
            <?php else: ?>
                <span class="pagination-link is-disabled">Next</span>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
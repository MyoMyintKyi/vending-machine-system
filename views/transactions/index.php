<?php
$currentPage = max(1, (int) ($page ?? 1));
$totalPageCount = max(1, (int) ($totalPages ?? 1));
$itemsPerPage = max(1, (int) ($perPage ?? 10));
$filters = is_array($filters ?? null) ? $filters : [];
$transactions = is_array($transactions ?? null) ? $transactions : [];
$activeTransactionType = (string) ($filters['transaction_type'] ?? '');
$activeUsername = (string) ($filters['username'] ?? '');
$activeProductName = (string) ($filters['product_name'] ?? '');
$hasActiveFilters = $activeTransactionType !== '' || $activeUsername !== '' || $activeProductName !== '';
$startingRowNumber = (($currentPage - 1) * $itemsPerPage) + 1;
$endingRowNumber = $startingRowNumber + max(0, count($transactions) - 1);
$pageItems = [];
$queryBase = [
    'transaction_type' => $activeTransactionType,
    'username' => $activeUsername,
    'product_name' => $activeProductName,
];

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
        <h1><?= htmlspecialchars((string) ($title ?? 'Transactions'), ENT_QUOTES, 'UTF-8') ?> </h1>
    </div>

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            <li><a href="/dashboard">Dashboard</a></li>
            <li><span class="breadcrumb-current">Transactions</span></li>
        </ol>
    </nav>
</section>

<?php if (!empty($flash)): ?>
    <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<section class="metric-grid">
    <article class="metric-card">
        <p class="metric-label">Total Transactions</p>
        <p class="metric-value"><?= htmlspecialchars((string) ($metrics['total_transactions'] ?? 0), ENT_QUOTES, 'UTF-8') ?></p>
    </article>
    <article class="metric-card">
        <p class="metric-label">Units Purchased</p>
        <p class="metric-value"><?= htmlspecialchars((string) ($metrics['total_quantity'] ?? 0), ENT_QUOTES, 'UTF-8') ?></p>
    </article>
    <article class="metric-card">
        <p class="metric-label">Total Revenue</p>
        <p class="metric-value"><?= htmlspecialchars((string) ($metrics['total_revenue'] ?? '0.000'), ENT_QUOTES, 'UTF-8') ?></p>
    </article>
    <article class="metric-card">
        <p class="metric-label">Unique Buyers</p>
        <p class="metric-value"><?= htmlspecialchars((string) ($metrics['unique_users'] ?? 0), ENT_QUOTES, 'UTF-8') ?></p>
    </article>
</section>

<section class="page-card">
    <div class="table-card-header">
        <form method="get" action="/transactions" class="transaction-filter-form">
            <div class="field-group">
                <label class="field-label" for="transaction_type">Transaction Type</label>
                <select id="transaction_type" name="transaction_type">
                    <option value="">All types</option>
                    <option value="PURCHASE"<?= $activeTransactionType === 'PURCHASE' ? ' selected' : '' ?>>PURCHASE</option>
                </select>
            </div>
            <div class="field-group">
                <label class="field-label" for="product_name">Product</label>
                <input id="product_name" name="product_name" type="text" value="<?= htmlspecialchars($activeProductName, ENT_QUOTES, 'UTF-8') ?>" placeholder="Filter by product">
            </div>
            <div class="field-group">
                <label class="field-label" for="username">Username</label>
                <input id="username" name="username" type="text" value="<?= htmlspecialchars($activeUsername, ENT_QUOTES, 'UTF-8') ?>" placeholder="Filter by username">
            </div>

            <div class="form-actions">
                <button class="btn" type="submit">Apply filters</button>
                <a class="btn btn-secondary" href="/transactions">Reset</a>
            </div>
        </form>
    </div>

    <?php if ($transactions === []): ?>
        <div class="alert alert-info"><?= $hasActiveFilters ? 'No transactions matched the current filters.' : 'No transactions have been recorded yet.' ?></div>
    <?php else: ?>
        <div class="table-shell">
            <table>
                <thead>
                <tr>
                    <th>No.</th>
                    <th>Transaction Date</th>
                    <th>User</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                    <th>Type</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($transactions as $index => $transaction): ?>
                    <tr>
                        <td><?= $startingRowNumber + $index ?></td>
                        <td><?= htmlspecialchars((string) ($transaction['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($transaction['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($transaction['product_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($transaction['quantity'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($transaction['unit_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($transaction['total_amount'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="status-pill"><?= htmlspecialchars((string) ($transaction['transaction_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPageCount > 1): ?>
            <div class="pagination">
                <span class="pagination-summary">Showing <?= $startingRowNumber ?> - <?= $endingRowNumber ?> | Page <?= $currentPage ?> of <?= $totalPageCount ?></span>
                <nav class="pagination-nav" aria-label="Transactions pagination">
                    <?php if (!empty($hasPreviousPage)): ?>
                        <a class="pagination-link" href="/transactions?<?= htmlspecialchars(http_build_query(array_merge($queryBase, ['page' => $currentPage - 1])), ENT_QUOTES, 'UTF-8') ?>">Previous</a>
                    <?php else: ?>
                        <span class="pagination-link is-disabled">Previous</span>
                    <?php endif; ?>

                    <?php foreach ($pageItems as $pageItem): ?>
                        <?php if ($pageItem['type'] === 'ellipsis'): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php else: ?>
                            <a class="pagination-link<?= $pageItem['number'] === $currentPage ? ' is-active' : '' ?>" href="/transactions?<?= htmlspecialchars(http_build_query(array_merge($queryBase, ['page' => $pageItem['number']])), ENT_QUOTES, 'UTF-8') ?>"><?= $pageItem['number'] ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (!empty($hasNextPage)): ?>
                        <a class="pagination-link" href="/transactions?<?= htmlspecialchars(http_build_query(array_merge($queryBase, ['page' => $currentPage + 1])), ENT_QUOTES, 'UTF-8') ?>">Next</a>
                    <?php else: ?>
                        <span class="pagination-link is-disabled">Next</span>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
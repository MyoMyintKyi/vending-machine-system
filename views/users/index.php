<?php
$currentPage = max(1, (int) ($page ?? 1));
$totalPageCount = max(1, (int) ($totalPages ?? 1));
$itemsPerPage = max(1, (int) ($perPage ?? 10));
$filters = is_array($filters ?? null) ? $filters : [];
$users = is_array($users ?? null) ? $users : [];
$sortField = (string) ($sort ?? 'created_at');
$sortOrder = (string) ($direction ?? 'desc');
$activeUsername = (string) ($filters['username'] ?? '');
$activeEmail = (string) ($filters['email'] ?? '');
$activeRole = (string) ($filters['role'] ?? '');
$hasActiveFilters = $activeUsername !== '' || $activeEmail !== '' || $activeRole !== '';
$startingRowNumber = (($currentPage - 1) * $itemsPerPage) + 1;
$endingRowNumber = $startingRowNumber + max(0, count($users) - 1);
$pageItems = [];
$queryBase = [
    'username' => $activeUsername,
    'email' => $activeEmail,
    'role' => $activeRole,
    'sort' => $sortField,
    'direction' => $sortOrder,
];

$toggleDirection = static function (string $field) use ($sortField, $sortOrder): string {
    return $sortField === $field && $sortOrder === 'asc' ? 'desc' : 'asc';
};

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
        <h1><?= htmlspecialchars((string) ($title ?? 'Users'), ENT_QUOTES, 'UTF-8') ?></h1>
    </div>

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            <li><a href="/dashboard">Dashboard</a></li>
            <li><span class="breadcrumb-current">Users</span></li>
        </ol>
    </nav>
</section>

<?php if (!empty($flash)): ?>
    <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<section class="metric-grid">
    <article class="metric-card">
        <p class="metric-label">Total Users</p>
        <p class="metric-value"><?= htmlspecialchars(\App\Support\ViewNumberFormatter::format((string) ($metrics['total_users'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></p>
    </article>
    <article class="metric-card">
        <p class="metric-label">Admins</p>
        <p class="metric-value"><?= htmlspecialchars(\App\Support\ViewNumberFormatter::format((string) ($metrics['total_admins'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></p>
    </article>
    <article class="metric-card">
        <p class="metric-label">Users</p>
        <p class="metric-value"><?= htmlspecialchars(\App\Support\ViewNumberFormatter::format((string) ($metrics['total_standard_users'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></p>
    </article>
</section>

<section class="page-card">
    <div class="table-card-header">
        <form method="get" action="/users" class="transaction-filter-form">
            <div class="field-group">
                <label class="field-label" for="role">Role</label>
                <select id="role" name="role">
                    <option value="">All roles</option>
                    <option value="Admin"<?= $activeRole === 'Admin' ? ' selected' : '' ?>>Admin</option>
                    <option value="User"<?= $activeRole === 'User' ? ' selected' : '' ?>>User</option>
                </select>
            </div>
            <div class="field-group">
                <label class="field-label" for="username">Username</label>
                <input id="username" name="username" type="text" value="<?= htmlspecialchars($activeUsername, ENT_QUOTES, 'UTF-8') ?>" placeholder="Filter by username">
            </div>
            <div class="field-group">
                <label class="field-label" for="email">Email</label>
                <input id="email" name="email" type="text" value="<?= htmlspecialchars($activeEmail, ENT_QUOTES, 'UTF-8') ?>" placeholder="Filter by email">
            </div>
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sortField, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="direction" value="<?= htmlspecialchars($sortOrder, ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-actions">
                <button class="btn" type="submit">Apply filters</button>
                <a class="btn btn-secondary" href="/users">Reset</a>
            </div>
        </form>
    </div>

    <?php if ($users === []): ?>
        <div class="alert alert-info"><?= $hasActiveFilters ? 'No users matched the current filters.' : 'No users are available yet.' ?></div>
    <?php else: ?>
        <div class="table-shell">
            <table>
                <thead>
                <tr>
                    <th>No.</th>
                    <th>
                        <a class="sort-link<?= $sortField === 'username' ? ' is-active' : '' ?>" href="/users?<?= htmlspecialchars(http_build_query(array_merge($filters, ['sort' => 'username', 'direction' => $toggleDirection('username')])), ENT_QUOTES, 'UTF-8') ?>">
                            <span>Username</span>
                            <span class="sort-direction"><?= $sortField === 'username' ? ($sortOrder === 'desc' ? '↓' : '↑') : '↓↑' ?></span>
                        </a>
                    </th>
                    <th>
                        <a class="sort-link<?= $sortField === 'email' ? ' is-active' : '' ?>" href="/users?<?= htmlspecialchars(http_build_query(array_merge($filters, ['sort' => 'email', 'direction' => $toggleDirection('email')])), ENT_QUOTES, 'UTF-8') ?>">
                            <span>Email</span>
                            <span class="sort-direction"><?= $sortField === 'email' ? ($sortOrder === 'desc' ? '↓' : '↑') : '↓↑' ?></span>
                        </a>
                    </th>
                    <th>
                        <a class="sort-link<?= $sortField === 'role' ? ' is-active' : '' ?>" href="/users?<?= htmlspecialchars(http_build_query(array_merge($filters, ['sort' => 'role', 'direction' => $toggleDirection('role')])), ENT_QUOTES, 'UTF-8') ?>">
                            <span>Role</span>
                            <span class="sort-direction"><?= $sortField === 'role' ? ($sortOrder === 'desc' ? '↓' : '↑') : '↓↑' ?></span>
                        </a>
                    </th>
                    <th>
                        <a class="sort-link<?= $sortField === 'created_at' ? ' is-active' : '' ?>" href="/users?<?= htmlspecialchars(http_build_query(array_merge($filters, ['sort' => 'created_at', 'direction' => $toggleDirection('created_at')])), ENT_QUOTES, 'UTF-8') ?>">
                            <span>Joined</span>
                            <span class="sort-direction"><?= $sortField === 'created_at' ? ($sortOrder === 'desc' ? '↓' : '↑') : '↓↑' ?></span>
                        </a>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $index => $user): ?>
                    <tr>
                        <td><?= htmlspecialchars(\App\Support\ViewNumberFormatter::format($startingRowNumber + $index), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($user['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="status-pill<?= ($user['role'] ?? '') === 'Admin' ? '' : ' is-warning' ?>"><?= htmlspecialchars((string) ($user['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= htmlspecialchars((string) ($user['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPageCount > 1): ?>
            <div class="pagination">
                <span class="pagination-summary">Showing <?= htmlspecialchars(\App\Support\ViewNumberFormatter::format($startingRowNumber), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars(\App\Support\ViewNumberFormatter::format($endingRowNumber), ENT_QUOTES, 'UTF-8') ?> | Page <?= htmlspecialchars(\App\Support\ViewNumberFormatter::format($currentPage), ENT_QUOTES, 'UTF-8') ?> of <?= htmlspecialchars(\App\Support\ViewNumberFormatter::format($totalPageCount), ENT_QUOTES, 'UTF-8') ?></span>
                <nav class="pagination-nav" aria-label="Users pagination">
                    <?php if (!empty($hasPreviousPage)): ?>
                        <a class="pagination-link" href="/users?<?= htmlspecialchars(http_build_query(array_merge($queryBase, ['page' => $currentPage - 1])), ENT_QUOTES, 'UTF-8') ?>">Previous</a>
                    <?php else: ?>
                        <span class="pagination-link is-disabled">Previous</span>
                    <?php endif; ?>

                    <?php foreach ($pageItems as $pageItem): ?>
                        <?php if ($pageItem['type'] === 'ellipsis'): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php else: ?>
                            <a class="pagination-link<?= $pageItem['number'] === $currentPage ? ' is-active' : '' ?>" href="/users?<?= htmlspecialchars(http_build_query(array_merge($queryBase, ['page' => $pageItem['number']])), ENT_QUOTES, 'UTF-8') ?>"><?= $pageItem['number'] ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (!empty($hasNextPage)): ?>
                        <a class="pagination-link" href="/users?<?= htmlspecialchars(http_build_query(array_merge($queryBase, ['page' => $currentPage + 1])), ENT_QUOTES, 'UTF-8') ?>">Next</a>
                    <?php else: ?>
                        <span class="pagination-link is-disabled">Next</span>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
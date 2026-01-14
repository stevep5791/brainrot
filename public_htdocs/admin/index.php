<?php
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../config/database.php';

$auth = Auth::getInstance();
$auth->requireAdmin();

$pdo = DatabaseConfig::getConnection();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Search/filter
$search = trim($_GET['search'] ?? '');
$categoryFilter = $_GET['category'] ?? '';

// Build query
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "s.content_text LIKE ?";
    $params[] = "%$search%";
}

if ($categoryFilter) {
    $where[] = "JSON_CONTAINS(s.categories, ?)";
    $params[] = json_encode($categoryFilter);
}

$whereClause = implode(' AND ', $where);

// Get total count
$countSql = "SELECT COUNT(*) FROM submissions s WHERE $whereClause";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalCount = $stmt->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

// Get submissions (anonymous - no user join needed)
$sql = "SELECT s.*
        FROM submissions s
        WHERE $whereClause
        ORDER BY s.submission_timestamp DESC
        LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$submissions = $stmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories WHERE active = 1 ORDER BY category_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Brain Rot Submissions</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body class="admin-page">
    <div class="container">
        <div class="admin-wrapper">
            <header class="admin-header">
                <h1>Brain Rot Submissions</h1>
                <nav class="admin-nav">
                    <span>Logged in as <strong><?= htmlspecialchars($auth->getUser()['username']) ?></strong></span>
                    <a href="/">Public Site</a>
                    <a href="/auth/logout.php">Logout</a>
                </nav>
            </header>

            <main class="admin-main">
                <form method="GET" class="filters">
                    <input type="text" name="search" placeholder="Search content..."
                           value="<?= htmlspecialchars($search) ?>">
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['category_key']) ?>"
                                    <?= $categoryFilter === $cat['category_key'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <?php if ($search || $categoryFilter): ?>
                        <a href="/admin/" class="btn-clear">Clear</a>
                    <?php endif; ?>
                </form>

                <div class="stats">
                    <div class="stat-item">
                        <strong><?= number_format($totalCount) ?></strong> total submissions
                    </div>
                    <div class="stat-item">
                        Page <strong><?= $page ?></strong> of <strong><?= max(1, $totalPages) ?></strong>
                    </div>
                </div>

                <?php if (empty($submissions)): ?>
                    <div class="no-results">
                        <h2>No submissions found</h2>
                        <p>No brain rot content has been submitted yet, or your filters returned no results.</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Content</th>
                                <th>Media</th>
                                <th>Categories</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $sub): ?>
                                <tr>
                                    <td class="cell-id"><?= $sub['id'] ?></td>
                                    <td class="cell-type"><?= ucfirst($sub['submission_type']) ?></td>
                                    <td class="truncate">
                                        <?= htmlspecialchars(substr($sub['content_text'] ?? '(no text)', 0, 100)) ?>
                                    </td>
                                    <td>
                                        <?php if ($sub['image_filename']): ?>
                                            <span class="has-media">📷</span>
                                        <?php else: ?>
                                            <span style="color: var(--text-light);">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cats = json_decode($sub['categories'], true) ?? [];
                                        foreach (array_slice($cats, 0, 2) as $cat):
                                        ?>
                                            <span class="category-tag"><?= htmlspecialchars($cat) ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($cats) > 2): ?>
                                            <span class="category-tag">+<?= count($cats) - 2 ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="cell-date"><?= date('M j, Y g:ia', strtotime($sub['submission_timestamp'])) ?></td>
                                    <td>
                                        <a href="/admin/view.php?id=<?= $sub['id'] ?>" class="btn btn-primary btn-sm">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($categoryFilter) ?>">Prev</a>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($categoryFilter) ?>"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($categoryFilter) ?>">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>
</html>

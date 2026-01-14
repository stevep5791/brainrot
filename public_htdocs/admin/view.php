<?php
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../config/database.php';

$auth = Auth::getInstance();
$auth->requireAdmin();

$pdo = DatabaseConfig::getConnection();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /admin/');
    exit;
}

// Get submission (anonymous - no user join)
$stmt = $pdo->prepare("SELECT * FROM submissions WHERE id = ?");
$stmt->execute([$id]);
$submission = $stmt->fetch();

if (!$submission) {
    header('Location: /admin/?error=not_found');
    exit;
}

$categories = json_decode($submission['categories'], true) ?? [];

// Get category names
$categoryNames = [];
if (!empty($categories)) {
    $placeholders = implode(',', array_fill(0, count($categories), '?'));
    $stmt = $pdo->prepare("SELECT category_key, category_name FROM categories WHERE category_key IN ($placeholders)");
    $stmt->execute($categories);
    foreach ($stmt->fetchAll() as $cat) {
        $categoryNames[$cat['category_key']] = $cat['category_name'];
    }
}

// Handle delete (hard delete since no active column)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    // Delete associated file if exists
    if ($submission['image_filename']) {
        $filePath = __DIR__ . '/../uploads/' . $submission['image_filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM submissions WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: /admin/?deleted=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submission #<?= $id ?> - Admin</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body class="admin-page">
    <div class="container">
        <div class="admin-wrapper">
            <header class="admin-header">
                <h1>Submission #<?= $id ?></h1>
                <nav class="admin-nav">
                    <a href="/admin/">Back to List</a>
                    <a href="/">Public Site</a>
                    <a href="/auth/logout.php">Logout</a>
                </nav>
            </header>

            <main class="admin-main">
                <div class="meta-info">
                    <span><strong>Submitted:</strong> <?= date('F j, Y \a\t g:i A', strtotime($submission['submission_timestamp'])) ?></span>
                    <span><strong>Type:</strong> <?= ucfirst($submission['submission_type']) ?></span>
                    <span><strong>Status:</strong> Anonymous submission</span>
                </div>

                <div class="detail-card">
                    <?php if ($submission['content_text']): ?>
                        <div class="detail-row">
                            <div class="detail-label">Text Content</div>
                            <div class="detail-value">
                                <div class="content-text"><?= htmlspecialchars($submission['content_text']) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($submission['image_filename']): ?>
                        <div class="detail-row">
                            <div class="detail-label">Image</div>
                            <div class="detail-value">
                                <div class="media-preview">
                                    <img src="/uploads/<?= htmlspecialchars($submission['image_filename']) ?>"
                                         alt="Submitted image">
                                </div>
                                <div class="media-info">
                                    <?= htmlspecialchars($submission['image_filename']) ?>
                                    <?php if ($submission['image_mime_type']): ?>
                                        (<?= htmlspecialchars($submission['image_mime_type']) ?>)
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="detail-row">
                        <div class="detail-label">Categories</div>
                        <div class="detail-value">
                            <?php if (empty($categories)): ?>
                                <em style="color: var(--text-light);">No categories selected</em>
                            <?php else: ?>
                                <?php foreach ($categories as $cat): ?>
                                    <span class="category-tag">
                                        <?= htmlspecialchars($categoryNames[$cat] ?? $cat) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <a href="/admin/" class="btn btn-primary">Back to List</a>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this submission? This cannot be undone.');">
                        <button type="submit" name="delete" value="1" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

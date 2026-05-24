<?php
require_once __DIR__ . '/connection.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Handle delete (soft delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    // Verify CSRF token before performing delete
    if (!verify_csrf($_POST['_csrf'] ?? '')) {
        http_response_code(400);
        die('Invalid request.');
    }
    $id = (int) $_POST['delete_id'];
    $stmt = $conn->prepare('UPDATE news SET is_deleted = 1 WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: showNews.php');
    exit();
}

// Pagination settings
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$totalRes = $conn->query('SELECT COUNT(*) AS cnt FROM news WHERE is_deleted = 0');
$totalRow = $totalRes->fetch_assoc();
$total = (int)$totalRow['cnt'];
$totalPages = (int) ceil($total / $perPage);

$stmt = $conn->prepare('SELECT id, title, details, image, category_name, user_name FROM news WHERE is_deleted = 0 ORDER BY id ASC LIMIT ? OFFSET ?');
$stmt->bind_param('ii', $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>All News</title>
    <link rel="stylesheet" href="lginCSS.css">
    <link rel="stylesheet" href="assets.css">
    <style>
        .news-table { width: 90%; margin: 24px auto; border-collapse: collapse; }
        .news-table th, .news-table td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        .news-img { width: 120px; height: auto; object-fit: cover; border-radius: 6px; }
        .actions form { display:inline; }
        .actions button { background:#c0392b; color:#fff; border:none; padding:8px 10px; border-radius:6px; cursor:pointer }
        .actions a { background:#2980b9; color:#fff; padding:8px 10px; border-radius:6px; text-decoration:none }
        .pagination { text-align:center; margin:12px 0; }
        .pagination a { padding:6px 10px; margin:0 4px; text-decoration:none; border-radius:4px; background:#eee; color:#333 }
    </style>
</head>
<body>
    <h1 style="text-align:center; margin-top:18px;">All News</h1>

    <?php if ($result && $result->num_rows > 0): ?>
        <table class="news-table" role="table">
            <thead>
                <tr><th>ID</th><th>Title</th><th>Details</th><th>Image</th><th>Category</th><th>Author</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= e($row['id']) ?></td>
                    <td><?= e($row['title']) ?></td>
                    <td><?= e(mb_strimwidth($row['details'], 0, 200, '...')) ?></td>
                    <td>
                        <?php if (!empty($row['image']) && file_exists(__DIR__ . '/uploads/' . $row['image'])): ?>
                            <img src="uploads/<?= e($row['image']) ?>" alt="<?= e($row['title']) ?>" class="news-img" loading="lazy">
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?= e($row['category_name']) ?></td>
                    <td><?= e($row['user_name']) ?></td>
                    <td class="actions">
                        <form method="post" onsubmit="return confirm('Delete this news?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="delete_id" value="<?= e($row['id']) ?>">
                           <button type="submit">Delete</button>
                        </form>

                        <a href="UpdateForm.php?id=<?= e($row['id']) ?>">Update</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="?page=<?= $p ?>" <?= $p === $page ? 'style="font-weight:bold;background:#ddd"' : '' ?>><?= $p ?></a>
            <?php endfor; ?>
        </div>

    <?php else: ?>
        <p style="text-align:center">No news found.</p>
    <?php endif; ?>

</body>
</html>



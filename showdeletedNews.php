<?php
require_once __DIR__ . '/connection.php';

if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit();
}

// Handle hard delete of a news item (remove DB row and associated image)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
  if (!verify_csrf($_POST['_csrf'] ?? '')) {
    http_response_code(400);
    die('Invalid request.');
  }
  $delId = (int) $_POST['delete_id'];
  // fetch image filename (if any) to remove from uploads
  $s = $conn->prepare('SELECT image FROM news WHERE id = ? LIMIT 1');
  $s->bind_param('i', $delId);
  $s->execute();
  $r = $s->get_result();
  $imageName = null;
  if ($r && $r->num_rows > 0) {
    $row = $r->fetch_assoc();
    $imageName = $row['image'];
  }
  $s->close();
  // delete DB row
  $d = $conn->prepare('DELETE FROM news WHERE id = ?');
  $d->bind_param('i', $delId);
  $d->execute();
  $d->close();
  // remove image file if exists
  if (!empty($imageName)) {
    $path = __DIR__ . '/uploads/' . $imageName;
    if (is_file($path)) {
      @unlink($path);
    }
  }
  header('Location: showdeletedNews.php');
  exit();
}

$res = $conn->query('SELECT id, title, details, image, category_name, user_name FROM news WHERE is_deleted = 1 ORDER BY id DESC');
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>الأخبار المحذوفة</title>
  <link rel="stylesheet" href="lginCSS.css">
  <link rel="stylesheet" href="assets.css">
</head>
<body>
  <header style="background:linear-gradient(90deg,#2563eb,#06b6d4);color:#fff;padding:16px 0;">
    <div class="container" style="max-width:1100px;margin:0 auto;padding:0 20px;display:flex;justify-content:space-between;align-items:center">
      <h2 style="margin:0">الأخبار المحذوفة</h2>
      <div style="color:#fff">مرحباً، <?= e($_SESSION['user']) ?></div>
    </div>
  </header>
  <div class="container" style="max-width:1000px;margin:24px auto;padding:0 20px;">
    <h1 style="text-align:right">الأخبار المحذوفة</h1>
    <?php if ($res && $res->num_rows > 0): ?>
    <table class="news-table" style="width:100%; margin:auto">
      <thead><tr><th>ID</th><th>Title</th><th>Details</th><th>Image</th><th>Category</th><th>Author</th><th style="width:120px">إجراءات</th></tr></thead>
            <tbody>
            <?php while($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= e($row['id']) ?></td>
                    <td><?= e($row['title']) ?></td>
                    <td><?= e(mb_strimwidth($row['details'],0,200,'...')) ?></td>
                    <td><?= !empty($row['image']) ? '<img src="uploads/'.e($row['image']).'" width="120" alt="">' : '—' ?></td>
                    <td><?= e($row['category_name']) ?></td>
                    <td><?= e($row['user_name']) ?></td>
          <td>
            <form method="post" style="display:inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الخبر نهائياً؟');">
              <?= csrf_field() ?>
              <input type="hidden" name="delete_id" value="<?= e($row['id']) ?>">
              <button type="submit" class="action-btn btn-delete">حذف نهائي</button>
            </form>
          </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align:right">لا توجد أخبار محذوفة.</p>
    <?php endif; ?>
  </div>
</body>
</html>

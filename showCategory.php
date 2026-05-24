<?php
require_once __DIR__ . '/connection.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Handle delete action (hard delete). Use POST to avoid CSRF in a simple way.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
  if (!verify_csrf($_POST['_csrf'] ?? '')) {
    http_response_code(400);
    die('Invalid request.');
  }
  $deleteId = (int) $_POST['delete_id'];
  $dstmt = $conn->prepare('DELETE FROM category WHERE id = ?');
  $dstmt->bind_param('i', $deleteId);
  $dstmt->execute();
  $dstmt->close();
  header('Location: showCategory.php');
  exit();
}

$res = $conn->query('SELECT id, name, created_at FROM category ORDER BY id ASC');
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>الفئات</title>
  <link rel="stylesheet" href="lginCSS.css">
  <link rel="stylesheet" href="assets.css">
    <style>
      .news-table th, .news-table td{text-align:right}
      /* Category list/table styles */
      .cat-table{width:100%;border-collapse:collapse;margin-top:18px;background:var(--card);box-shadow:0 6px 18px rgba(13,38,59,0.04);border-radius:10px;overflow:hidden}
      .cat-table thead{background:linear-gradient(90deg,#f3f6fb,#eef3f8)}
      .cat-table th, .cat-table td{padding:12px;border-bottom:1px solid #f1f5f9}
      .cat-table tbody tr:hover{background:#fbfdff}
      .cat-name{font-weight:700}
      .cat-meta{color:#8892a6;font-size:0.92rem}

  /* Unified action buttons: consistent size, no hover animation (overridden in assets.css) */
  .action-btn{display:inline-block;padding:8px 12px;border-radius:8px;color:#fff;text-decoration:none;border:none;cursor:pointer;font-weight:600;line-height:1;font-size:14px}
  .btn-edit{background:#2563eb}
  .btn-delete{background:#ef4444}

      @media (max-width:640px){
        .cat-table th:nth-child(3), .cat-table td:nth-child(3){display:none}
      }
    </style>
</head>
<body>
  <header style="background:linear-gradient(90deg,#2563eb,#06b6d4);color:#fff;padding:16px 0;">
    <div class="container" style="max-width:1100px;margin:0 auto;padding:0 20px;display:flex;justify-content:space-between;align-items:center">
      <h2 style="margin:0">الفئات</h2>
      <div style="color:#fff">مرحباً، <?= e($_SESSION['user']) ?></div>
    </div>
  </header>
  <div class="container" style="max-width:1000px;margin:24px auto;padding:0 20px;">
  <h1 style="text-align:right">جميع الفئات</h1>
  <?php if ($res && $res->num_rows > 0): ?>
    <table class="cat-table" aria-label="قائمة الفئات">
      <thead>
        <tr>
          <th style="width:72px">id</th>
          <th>الاسم</th>
          <th>أنشئت</th>
          <th style="width:220px">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $res->fetch_assoc()): ?>
          <tr>
            <td><?= e($row['id']) ?></td>
            <td class="cat-name"><?= e($row['name']) ?></td>
            <td class="cat-meta"><?= e($row['created_at']) ?></td>
            <td>
              <a class="action-btn btn-edit" href="addCategory.php?id=<?= e($row['id']) ?>">تعديل</a>
              <form method="post" style="display:inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفئة؟');">
                <?= csrf_field() ?>
                <input type="hidden" name="delete_id" value="<?= e($row['id']) ?>">
                <button type="submit" class="action-btn btn-delete">حذف</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p style="text-align:right">لا توجد فئات.</p>
  <?php endif; ?>
  </div>
</body>
</html>


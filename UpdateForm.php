<?php
require_once __DIR__ . '/connection.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: showNews.php');
    exit();
}

$stmt = $conn->prepare('SELECT id, title, category_name, details, image FROM news WHERE id = ? AND is_deleted = 0');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_assoc();
$stmt->close();

if (!$news) {
    header('Location: showNews.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['_csrf'] ?? '')) {
        http_response_code(400);
        $error = 'Invalid request.';
    }
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $details = trim($_POST['details'] ?? '');

    // handle image
    $image = $news['image'];
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if (in_array($ext, $allowed)) {
            $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $target = __DIR__ . '/uploads/' . $newName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                // optionally delete old image
                if (!empty($image) && file_exists(__DIR__ . '/uploads/' . $image)) {
                    @unlink(__DIR__ . '/uploads/' . $image);
                }
                $image = $newName;
            }
        }
    }

    $upd = $conn->prepare('UPDATE news SET title = ?, category_name = ?, details = ?, image = ? WHERE id = ?');
    $upd->bind_param('ssssi', $title, $category, $details, $image, $id);
    if ($upd->execute()) {
        $upd->close();
        header('Location: showNews.php');
        exit();
    } else {
        $error = 'Update failed: ' . $conn->error;
    }
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <title>تعديل الخبر</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link rel="stylesheet" href="lginCSS.css">
    <link rel="stylesheet" href="assets.css">
    <style>img{max-width:100%;height:auto}</style>
</head>
<body>
  <header style="background:linear-gradient(90deg,#2563eb,#06b6d4);color:#fff;padding:16px 0;">
    <div class="container" style="max-width:1100px;margin:0 auto;padding:0 20px;display:flex;justify-content:space-between;align-items:center">
      <h2 style="margin:0">تعديل الخبر</h2>
      <div style="color:#fff">مرحباً، <?= e($_SESSION['user']) ?></div>
    </div>
  </header>
  <div class="container" style="max-width:900px;margin:20px auto;padding:0 20px;">
    <?php if (!empty($error ?? '')): ?>
        <div style="color:#b71c1c;margin-bottom:12px"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" style="background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(13,38,59,0.06);text-align:right">
        <label>العنوان</label><br>
        <input type="text" name="title" value="<?= e($news['title']) ?>" required><br><br>

        <label>التصنيف</label><br>
        <input type="text" name="category" value="<?= e($news['category_name']) ?>" required><br><br>

        <label>التفاصيل</label><br>
        <textarea name="details" rows="6" cols="60" required><?= e($news['details']) ?></textarea><br><br>

        <label>الصورة الحالية</label><br>
        <?php if (!empty($news['image']) && file_exists(__DIR__ . '/uploads/' . $news['image'])): ?>
            <img src="uploads/<?= e($news['image']) ?>" width="180" alt="<?= e($news['title']) ?>" loading="lazy">
        <?php else: ?>
            <div>—</div>
        <?php endif; ?>
        <br><br>

        <label>صورة جديدة (اختياري)</label><br>
        <input type="file" name="image"><br><br>

    <?= csrf_field() ?>
    <button type="submit">حفظ</button>
    </form>
  </div>
</body>
</html>



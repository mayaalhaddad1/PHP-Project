<?php
require_once __DIR__ . '/connection.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf($_POST['_csrf'] ?? '')) {
    http_response_code(400);
    $errors[] = 'Invalid request.';
  }
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $details = trim($_POST['details'] ?? '');
    $user = $_SESSION['user'];

    if ($title === '') $errors[] = 'Title is required.';
    if ($category === '') $errors[] = 'Category is required.';
    if ($details === '') $errors[] = 'Details are required.';

    // Handle upload
    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Invalid image format.';
        } else {
            $imageName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $target = __DIR__ . '/uploads/' . $imageName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $errors[] = 'Uploading image failed.';
                $imageName = null;
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('INSERT INTO news (title, category_name, details, image, user_name) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $title, $category, $details, $imageName, $user);
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: dashboard.php');
            exit();
        } else {
            $errors[] = 'Database error: ' . $conn->error;
        }
    }
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>إضافة خبر</title>
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <link rel="stylesheet" href="lginCSS.css">
  <link rel="stylesheet" href="assets.css">
</head>
<body>
  <header style="background:linear-gradient(90deg,#2563eb,#06b6d4);color:#fff;padding:18px 0;">
    <div class="container" style="max-width:1100px;margin:0 auto;padding:0 20px;display:flex;justify-content:space-between;align-items:center">
      <h2 style="margin:0">إضافة خبر</h2>
      <div style="color:#fff">مرحباً، <?= e($_SESSION['user']) ?></div>
    </div>
  </header>

  <div class="container" style="max-width:900px;margin:26px auto;padding:0 20px;">
    <?php if (!empty($errors)): ?>
      <div style="color:#b71c1c;margin-bottom:12px"><?= e(implode('<br>', $errors)) ?></div>
    <?php endif; ?>
  <form action="" method="post" enctype="multipart/form-data" style="background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(13,38,59,0.06)">
        <label>العنوان</label><br>
        <input type="text" name="title" placeholder="عنوان الخبر" required><br><br>
        <label>التصنيف</label><br>
        <input type="text" name="category" placeholder="التصنيف" required><br><br>
        <label>التفاصيل</label><br>
        <textarea name="details" rows="6" cols="50" required></textarea><br><br>
        <label>صورة (اختياري)</label><br>
        <input type="file" name="image"><br><br>
    <?= csrf_field() ?>
    <button type="submit">إضافة</button>
    </form>
  </div>
</body>
</html>




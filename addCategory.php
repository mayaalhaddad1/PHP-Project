<?php
require_once __DIR__ . '/connection.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Support editing when id is present
$editing = false;
$category = '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id > 0) {
  $editing = true;
  $s = $conn->prepare('SELECT id, name FROM category WHERE id = ? LIMIT 1');
  $s->bind_param('i', $id);
  $s->execute();
  $r = $s->get_result();
  if ($r && $r->num_rows > 0) {
    $row = $r->fetch_assoc();
    $category = $row['name'];
  } else {
    header('Location: showCategory.php');
    exit();
  }
  $s->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $categoryname = trim($_POST['categoryname'] ?? '');
  if ($categoryname !== '') {
    if (!empty($_POST['id'])) {
      $upId = (int) $_POST['id'];
      $ust = $conn->prepare('UPDATE category SET name = ? WHERE id = ?');
      $ust->bind_param('si', $categoryname, $upId);
      $ust->execute();
      $ust->close();
      header('Location: showCategory.php');
      exit();
    } else {
      $stmt = $conn->prepare('INSERT INTO category (name) VALUES (?)');
      $stmt->bind_param('s', $categoryname);
      $stmt->execute();
      $stmt->close();
      // After adding a category go to dashboard as requested
      header('Location: dashboard.php');
      exit();
    }
  } else {
    $error = 'Category name is required.';
  }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <title>إضافة فئة</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
       <link rel="stylesheet" href="lginCSS.css">
</head>
<body>
  <header style="background:linear-gradient(90deg,#2563eb,#06b6d4);color:#fff;padding:18px 0;">
    <div class="container" style="max-width:1100px;margin:0 auto;padding:0 20px;display:flex;justify-content:space-between;align-items:center">
      <h2 style="margin:0">إضافة فئة</h2>
      <div style="color:#fff">مرحباً، <?= e($_SESSION['user']) ?></div>
    </div>
  </header>

  <div class="container" style="max-width:760px;margin:40px auto;padding:0 20px;">
    <?php if (!empty($error ?? '')): ?>
      <div style="color:#b71c1c;margin-bottom:12px"><?= e($error) ?></div>
    <?php endif; ?>
  <form action="" method="post" style="background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(13,38,59,0.06);text-align:right">
    <?php if ($editing): ?>
      <input type="hidden" name="id" value="<?= e($id) ?>">
    <?php endif; ?>
    <label>اسم الفئة</label><br>
    <input type="text" name="categoryname" placeholder="مثال: تقنية" required value="<?= e($category) ?>"><br><br>
    <button type="submit"><?= $editing ? 'تحديث' : 'إضافة' ?></button>
  </form>
  </div>
</body>
</html>
<?php
require_once __DIR__ . '/connection.php';

// Combined login/register: if email exists -> attempt login; else register.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        // Check if user already exists
        $stmt = $conn->prepare('SELECT id, name, pass FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            // user exists -> verify password
            $row = $res->fetch_assoc();
            if (password_verify($password, $row['pass'])) {
                // login
                $_SESSION['user'] = $row['name'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Incorrect password for this email.';
            }
        } else {
            // register new user (name required for registration)
            if ($name === '') {
                $error = 'Name is required for registration.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $conn->prepare('INSERT INTO users (name, email, pass) VALUES (?, ?, ?)');
                $ins->bind_param('sss', $name, $email, $hash);
                if ($ins->execute()) {
                    $_SESSION['user'] = $name;
                    header('Location: dashboard.php');
                    exit();
                } else {
                    // handle duplicate email just in case of race
                    if ($conn->errno === 1062) {
                        $error = 'This email is already registered.';
                    } else {
                        $error = 'Registration failed.';
                    }
                }
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link rel="stylesheet" href="lginCSS.css">
        <link rel="stylesheet" href="assets.css">
    <title>تسجيل / تسجيل دخول</title>
    <style>body{direction:rtl;text-align:right}</style>
</head>
<body>
    <h2>تسجيل / تسجيل دخول</h2>
    <?php if (!empty($error ?? '')): ?>
        <div style="color:#b71c1c" class="container"><?= e($error) ?></div>
    <?php endif; ?>

    <form class="auth fade-in" action="" method="post">
        <label> الاسم (للتسجيل فقط)</label><br>
        <input type="text" name="name" placeholder="الاسم" value="<?= e($name ?? '') ?>">
        <br><br>
        <label> البريد الإلكتروني </label><br>
        <input type="email" name="email" placeholder="Enter your email" required value="<?= e($email ?? '') ?>">
        <br><br>
        <label> كلمة المرور </label><br>
        <input type="password" name="password" placeholder="Enter your password" required>
        <br><br>
        <?= csrf_field() ?>
        <button type="submit"> تسجيل </button>
    </form>
</body>
</html>

<?php
require 'config.php';
require 'includes/functions.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
        $error = 'Vui lòng nhập đủ thông tin. Mật khẩu cần ít nhất 6 ký tự.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users(full_name,email,password) VALUES(?,?,?)");
            $stmt->execute([
                $name,
                $email,
                password_hash($pass, PASSWORD_DEFAULT)
            ]);

            $id = (int)$pdo->lastInsertId();

            foreach ($pdo->query("SELECT id FROM categories WHERE user_id IS NULL") as $c) {
                $copy = $pdo->prepare("INSERT INTO categories(user_id,name,type) SELECT ?,name,type FROM categories WHERE id=?");
                $copy->execute([$id, $c['id']]);
            }

            $_SESSION['user'] = [
                'id' => $id,
                'full_name' => $name,
                'email' => $email,
                'role' => 'user'
            ];

            redirect('index.php');

        } catch (PDOException $e) {
            $error = 'Email này đã được sử dụng.';
        }
    }
}
?>

<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Đăng ký | Me & Money</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="auth-page">

    <div class="auth-card">

        <div class="auth-brand">
            <img src="assets/logo qltc.jpg" alt="Logo Me & Money" class="brand-icon">
            Me & Money
        </div>

        <h1>Tạo tài khoản</h1>

        <p>Money is everything !</p>

        <?php if ($error): ?>
            <div class="alert error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <div class="field">
                <label>Họ và tên</label>
                <input name="full_name" required>
            </div>

            <div class="field">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="field">
                <label>Mật khẩu (ít nhất 6 ký tự)</label>
                <input type="password" name="password" minlength="6" required>
            </div>

            <button class="btn">Đăng ký</button>

        </form>

        <div class="auth-foot">
            Đã có tài khoản?
            <a class="blue" href="login.php">Đăng nhập</a>
        </div>

    </div>

</body>
</html>
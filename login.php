<?php
require 'config.php';
require 'includes/functions.php';

if(is_logged_in()) redirect('index.php');

$error='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();

    $email=trim($_POST['email']??'');
    $password=$_POST['password']??'';

    $stmt=$pdo->prepare("SELECT * FROM users WHERE email=? AND status=1 LIMIT 1");
    $stmt->execute([$email]);
    $u=$stmt->fetch();

    if($u && password_verify($password,$u['password'])){
        session_regenerate_id(true);

        $_SESSION['user']=[
            'id'=>$u['id'],
            'full_name'=>$u['full_name'],
            'email'=>$u['email'],
            'role'=>$u['role']
        ];

        redirect('index.php');
    }

    $error='Email hoặc mật khẩu không chính xác.';
}
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>Đăng nhập |Me & Money </title>

    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="auth-page">

    <div class="auth-card">

        <div class="auth-brand">
            <img src="assets/logo qltc.jpg" alt="Logo Me & Money" class="brand-icon">
            Me & Money
        </div>

        <h1>Money is everything !</h1>

        <?php if($error): ?>
            <div class="alert error">
                <?=e($error)?>
            </div>
        <?php endif; ?>

        <form method="post">

            <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">

            <div class="field">
                <label>Email</label>
                <input type="email" name="email" required placeholder="name@example.com">
            </div>

            <div class="field">
                <label>Mật khẩu</label>
                <input type="password" name="password" required placeholder="Nhập mật khẩu">
            </div>

            <button class="btn" type="submit">Đăng nhập</button>

        </form>

        <div class="auth-foot">
            Chưa có tài khoản?
            <a href="register.php" class="blue">Đăng ký ngay</a>
        </div>

    </div>

</body>
</html>

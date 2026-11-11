<?php
$pageTitle = $pageTitle ?? 'Quản lý thu chi';
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | Me & Money</title>
<link rel="stylesheet" href="assets/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="app-shell">
<aside class="sidebar">
  <a class="brand" href="index.php">
    <img src="assets/logo qltc.jpg" alt="Logo Me & Money" class="brand-icon">
    <span>Me & Money</span></a>
<div class="side-caption">TỔNG QUAN</div>
<a href="index.php" class="nav-link"><i data-lucide="house"></i><span>Trang chủ</span></a>
<div class="side-caption">QUẢN LÝ TÀI CHÍNH</div>
<a href="transactions.php?type=income" class="nav-link"><i data-lucide="circle-arrow-up"></i><span>Khoản thu</span></a>
<a href="transactions.php?type=expense" class="nav-link"><i data-lucide="circle-arrow-down"></i><span>Khoản chi</span></a>
<a href="categories.php" class="nav-link"><i data-lucide="tags"></i><span>Danh mục</span></a>
<a href="budgets.php" class="nav-link"><i data-lucide="wallet"></i><span>Ngân sách</span></a>
<a href="reports.php" class="nav-link"><i data-lucide="chart-column"></i><span>Báo cáo</span></a>
  <?php if (is_admin()): ?>
  <div class="side-caption">QUẢN TRỊ</div>
  <a href="users.php" class="nav-link"><i data-lucide="user-round"></i><span>Người dùng</span></a>
  <?php endif; ?>
  <div class="sidebar-bottom">
    <div class="user-mini"><div class="avatar"><?= e(mb_substr($_SESSION['user']['full_name'],0,1)) ?></div>
      <div><strong><?= e($_SESSION['user']['full_name']) ?></strong><small><?= is_admin()?'Quản trị viên':'Tài khoản cá nhân' ?></small></div>
    </div>
    <a href="logout.php" class="logout-link">↪ Đăng xuất</a>
  </div>
</aside>
<main class="main-area">
<header class="topbar"><div><span class="eyebrow">KHÔNG GIAN CÁ NHÂN</span><h1><?= e($pageTitle) ?></h1></div><div class="top-date"><?= date('d/m/Y') ?></div></header>
<?php if ($flash): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>

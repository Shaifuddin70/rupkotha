<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php'; // checks if admin is logged in

// Load company settings
$settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
$companyName = $settings['company_name'] ?? 'Your Store';
$companyPhone = $settings['phone'] ?? '+8801791912323';
$companyLogo = !empty($settings['logo']) ? 'admin/assets/uploads/' . $settings['logo'] : 'assets/images/logo.jpg';

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars($companyName) ?></title>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
<!-- Topbar -->
<div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <div>Welcome to <?= htmlspecialchars($companyName) ?></div>
        <div>
            <?php if (!empty($settings['facebook'])): ?><a href="<?= $settings['facebook'] ?>" target="_blank"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
            <?php if (!empty($settings['instagram'])): ?><a href="<?= $settings['instagram'] ?>" target="_blank"><i class="fab fa-instagram"></i></a><?php endif; ?>
            <?php if (!empty($settings['twitter'])): ?><a href="<?= $settings['twitter'] ?>" target="_blank"><i class="fab fa-twitter"></i></a><?php endif; ?>
            <a href="login"><i class="fas fa-user"></i> Login</a>
        </div>
    </div>
</div>

<!-- Main Header -->
<div class="main-header">
    <div class="container d-flex align-items-center justify-content-between flex-wrap">
        <div class="logo">
            <a href="index"><img src="<?= $companyLogo ?>" alt="Logo" style="max-height: 60px;"></a>
        </div>

        <div class="search-box">
            <form action="search">
                <input type="text" name="q" placeholder="Search for products">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <div class="cart-info">
            <div><strong>24/7 Support:</strong> <?= htmlspecialchars($companyPhone) ?></div>
            <div>
                <i class="fas fa-shopping-cart"></i>
                <strong><?= isset($_SESSION['cart_total']) ? $_SESSION['cart_total'] : '0.00' ?>৳</strong>
                <span>(<?= isset($_SESSION['cart_items']) ? $_SESSION['cart_items'] : 0 ?> items)</span>
            </div>
        </div>
    </div>
</div>

<!-- Category Bar with Dropdown -->
<div class="category-bar">
    <div class="container d-flex align-items-center">
        <div class="category-dropdown">
            <span><i class="fas fa-bars"></i> Browse Categories</span>
            <div class="dropdown-menu">
                <?php
                $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
                foreach ($categories as $cat): ?>
                    <a href="category?id=<?= $cat['id'] ?>"> <?= htmlspecialchars($cat['name']) ?> </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

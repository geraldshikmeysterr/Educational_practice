<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base = getBasePath();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? 'Блог') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Source+Sans+3:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="<?= $base ?>index.php" class="logo">
            <span class="logo-icon">✦</span>
            <span class="logo-text">Блог</span>
        </a>
        <button class="burger" id="burger" aria-label="Меню">
            <span></span><span></span><span></span>
        </button>
        <nav class="nav" id="nav">
            <a href="<?= $base ?>index.php" class="nav-link">Главная</a>
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="<?= $base ?>admin/posts.php" class="nav-link nav-admin">Админ</a>
                <?php endif; ?>
                <span class="nav-user">👤 <?= h($_SESSION['name'] ?? '') ?></span>
                <a href="<?= $base ?>logout.php" class="nav-link btn-logout">Выйти</a>
            <?php else: ?>
                <a href="<?= $base ?>login.php" class="nav-link">Войти</a>
                <a href="<?= $base ?>register.php" class="nav-link btn-register">Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="main-content">
<div class="container">

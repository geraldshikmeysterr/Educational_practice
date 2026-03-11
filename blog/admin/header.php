<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$base = '../';
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? 'Админ') ?> — Панель управления</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Source+Sans+3:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="../index.php" class="logo">
            <span class="logo-icon">✦</span>
            <span class="logo-text">Блог</span>
        </a>
        <nav class="nav">
            <span class="nav-user">👤 <?= h($_SESSION['name'] ?? '') ?></span>
            <a href="../index.php" class="nav-link">← На сайт</a>
            <a href="../logout.php" class="nav-link btn-logout">Выйти</a>
        </nav>
    </div>
</header>
<main class="main-content">
<div class="container">
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-title">Панель</div>
        <a href="posts.php" class="admin-nav-link <?= $currentFile === 'posts.php' ? 'active' : '' ?>">📝 Посты</a>
        <a href="posts_form.php" class="admin-nav-link <?= $currentFile === 'posts_form.php' ? 'active' : '' ?>">➕ Новый пост</a>
        <a href="comments.php" class="admin-nav-link <?= $currentFile === 'comments.php' ? 'active' : '' ?>">💬 Комментарии</a>
    </aside>
    <div class="admin-content">

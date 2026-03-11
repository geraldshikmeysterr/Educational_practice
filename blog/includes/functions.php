<?php
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin($redirect = '/login.php') {
    if (!isLoggedIn()) {
        redirect($redirect);
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect('/index.php');
    }
}

function formatDate($dateStr) {
    $date = new DateTime($dateStr);
    $months = ['янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек'];
    return $date->format('d') . ' ' . $months[(int)$date->format('m') - 1] . ' ' . $date->format('Y');
}

function getBasePath() {
    $script = $_SERVER['SCRIPT_NAME'];
    if (strpos($script, '/admin/') !== false) {
        return '../';
    }
    return '';
}

<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'invalid_method']);
    exit;
}

$postId = (int)($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if (!$postId || !$content) {
    echo json_encode(['success' => false, 'error' => 'empty_fields']);
    exit;
}

if (mb_strlen($content) > 2000) {
    echo json_encode(['success' => false, 'error' => 'too_long']);
    exit;
}

$pdo = getDB();

// Check post exists
$pStmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
$pStmt->execute([$postId]);
if (!$pStmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'post_not_found']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
$stmt->execute([$postId, $_SESSION['user_id'], $content]);
$commentId = $pdo->lastInsertId();

$now = new DateTime();
$months = ['янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек'];
$dateStr = $now->format('d') . ' ' . $months[(int)$now->format('m') - 1] . ' ' . $now->format('Y');

echo json_encode([
    'success' => true,
    'comment' => [
        'id'      => $commentId,
        'name'    => $_SESSION['name'],
        'content' => $content,
        'date'    => $dateStr,
    ]
]);

<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit;
}

$commentId = (int)($_POST['id'] ?? 0);
if (!$commentId) { echo json_encode(['success' => false]); exit; }

$pdo = getDB();

try {
    $pdo->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)")->execute([$commentId, $_SESSION['user_id']]);
    $pdo->prepare("UPDATE comments SET likes = likes + 1 WHERE id = ?")->execute([$commentId]);
    $liked = true;
} catch (PDOException $e) {
    $pdo->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?")->execute([$commentId, $_SESSION['user_id']]);
    $pdo->prepare("UPDATE comments SET likes = GREATEST(likes - 1, 0) WHERE id = ?")->execute([$commentId]);
    $liked = false;
}

$countStmt = $pdo->prepare("SELECT likes FROM comments WHERE id = ?");
$countStmt->execute([$commentId]);
$likes = (int)$countStmt->fetchColumn();

echo json_encode(['success' => true, 'liked' => $liked, 'likes' => $likes]);

<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit;
}

$postId = (int)($_POST['id'] ?? 0);
if (!$postId) { echo json_encode(['success' => false]); exit; }

$pdo = getDB();

// Try to insert like
try {
    $stmt = $pdo->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
    $stmt->execute([$postId, $_SESSION['user_id']]);
    // Added like
    $pdo->prepare("UPDATE posts SET likes = likes + 1 WHERE id = ?")->execute([$postId]);
    $liked = true;
} catch (PDOException $e) {
    // Already liked — remove it
    $pdo->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?")->execute([$postId, $_SESSION['user_id']]);
    $pdo->prepare("UPDATE posts SET likes = GREATEST(likes - 1, 0) WHERE id = ?")->execute([$postId]);
    $liked = false;
}

$countStmt = $pdo->prepare("SELECT likes FROM posts WHERE id = ?");
$countStmt->execute([$postId]);
$likes = (int)$countStmt->fetchColumn();

echo json_encode(['success' => true, 'liked' => $liked, 'likes' => $likes]);

<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('index.php');

$pdo = getDB();

$stmt = $pdo->prepare("
    SELECT p.*, u.name AS author_name
    FROM posts p JOIN users u ON p.author_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post) { http_response_code(404); die('Пост не найден'); }

// Comments
$cStmt = $pdo->prepare("
    SELECT c.*, u.name AS user_name
    FROM comments c JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ?
    ORDER BY c.created_at DESC
");
$cStmt->execute([$id]);
$comments = $cStmt->fetchAll();

// Check if current user liked the post
$userLiked = false;
if (isLoggedIn()) {
    $lStmt = $pdo->prepare("SELECT 1 FROM post_likes WHERE post_id = ? AND user_id = ?");
    $lStmt->execute([$id, $_SESSION['user_id']]);
    $userLiked = (bool)$lStmt->fetchColumn();
}

$pageTitle = h($post['title']) . ' — Блог';
include 'includes/header.php';
?>

<div class="post-single">
    <div class="breadcrumb">
        <a href="index.php">Главная</a>
        <span class="breadcrumb-sep">/</span>
        <span><?= h(mb_strimwidth($post['title'], 0, 40, '…')) ?></span>
    </div>

    <article>
        <header class="post-header">
            <div class="post-meta" style="margin-bottom:14px;">
                <span>👤 <?= h($post['author_name']) ?></span>
                <span class="post-meta-dot">·</span>
                <span>📅 <?= formatDate($post['created_at']) ?></span>
            </div>
            <h1 class="post-title"><?= h($post['title']) ?></h1>
        </header>

        <?php if ($post['image']): ?>
        <img src="<?= h($post['image']) ?>" alt="<?= h($post['title']) ?>" class="post-image-full">
        <?php endif; ?>

        <div class="post-body">
            <?php
            $paragraphs = array_filter(explode("\n", $post['content']));
            foreach ($paragraphs as $para):
            ?>
            <p><?= h(trim($para)) ?></p>
            <?php endforeach; ?>
        </div>

        <div style="display:flex; align-items:center; gap:12px; padding: 20px 0; border-top:1px solid var(--border); border-bottom:1px solid var(--border); margin-bottom:40px;">
            <?php if (isLoggedIn()): ?>
            <button class="like-btn <?= $userLiked ? 'liked' : '' ?>" data-type="post" data-id="<?= $post['id'] ?>">
                <span class="heart">♥</span>
                <span class="like-count"><?= (int)$post['likes'] ?></span>
                <span><?= $userLiked ? 'Понравилось' : 'Нравится' ?></span>
            </button>
            <?php else: ?>
            <span style="color:var(--ink-light); font-size:0.93rem;">♥ <?= (int)$post['likes'] ?> лайков</span>
            <?php endif; ?>
        </div>
    </article>

    <!-- Comments -->
    <section class="comments-section">
        <h2 class="comments-title">
            Комментарии
            <span class="comments-count"><?= count($comments) ?></span>
        </h2>

        <div id="comments-list">
            <?php if (empty($comments)): ?>
            <p id="no-comments" style="color:var(--ink-light);">Комментариев пока нет. Будьте первым!</p>
            <?php endif; ?>
            <?php foreach ($comments as $c): ?>
            <div class="comment-item" id="comment-<?= $c['id'] ?>">
                <div class="comment-header">
                    <div class="comment-avatar"><?= mb_strtoupper(mb_substr($c['user_name'], 0, 1)) ?></div>
                    <span class="comment-author"><?= h($c['user_name']) ?></span>
                    <span class="comment-date"><?= formatDate($c['created_at']) ?></span>
                </div>
                <div class="comment-text"><?= h($c['content']) ?></div>
                <div class="comment-footer">
                    <?php if (isLoggedIn()): ?>
                    <?php
                    $clStmt = $pdo->prepare("SELECT 1 FROM comment_likes WHERE comment_id = ? AND user_id = ?");
                    $clStmt->execute([$c['id'], $_SESSION['user_id']]);
                    $cLiked = (bool)$clStmt->fetchColumn();
                    ?>
                    <button class="comment-like-btn <?= $cLiked ? 'liked' : '' ?>" data-id="<?= $c['id'] ?>">
                        ♥ <span class="like-count"><?= (int)$c['likes'] ?></span>
                    </button>
                    <?php else: ?>
                    <span style="font-size:0.82rem; color:var(--ink-light);">♥ <?= (int)$c['likes'] ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="comment-form-wrapper">
            <h3 class="comment-form-title">Оставить комментарий</h3>
            <?php if (isLoggedIn()): ?>
            <form id="comment-form">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <div class="form-group">
                    <textarea name="content" class="form-control" placeholder="Ваш комментарий…" rows="4" required maxlength="2000"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Отправить</button>
            </form>
            <?php else: ?>
            <div class="comment-login-notice">
                Чтобы оставить комментарий, <a href="login.php">войдите</a> или <a href="register.php">зарегистрируйтесь</a>.
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>

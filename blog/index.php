<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Блог — Главная';
$perPage = 5;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$pdo = getDB();

$totalStmt = $pdo->query("SELECT COUNT(*) FROM posts");
$total = (int)$totalStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare("
    SELECT p.id, p.title, p.content, p.image, p.likes, p.created_at,
           u.name AS author_name
    FROM posts p
    JOIN users u ON p.author_id = u.id
    ORDER BY p.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

include 'includes/header.php';
?>

<?php if ($page === 1): ?>
<div class="hero">
    <div class="container hero-inner">
        <div class="hero-label">Добро пожаловать</div>
        <h1>Мысли, истории<br>и идеи</h1>
        <p>Читайте интересные статьи, делитесь мнением и участвуйте в обсуждениях.</p>
    </div>
</div>
<?php endif; ?>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 class="page-title" style="font-size:1.6rem;">Все публикации</h2>
        <p class="page-subtitle">Страница <?= $page ?> из <?= max(1, $totalPages) ?></p>
    </div>
</div>

<?php if (empty($posts)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">📝</div>
        <p>Публикаций пока нет.</p>
    </div>
<?php else: ?>
<div class="posts-grid">
    <?php foreach ($posts as $post): ?>
    <article class="post-card <?= $post['image'] ? 'has-image' : '' ?>">
        <?php if ($post['image']): ?>
        <div class="post-card-image">
            <img src="<?= h($post['image']) ?>" alt="<?= h($post['title']) ?>" loading="lazy">
        </div>
        <?php else: ?>
        <div class="post-card-image no-image">📄</div>
        <?php endif; ?>
        <div class="post-card-body">
            <div class="post-meta">
                <span>👤 <?= h($post['author_name']) ?></span>
                <span class="post-meta-dot">·</span>
                <span>📅 <?= formatDate($post['created_at']) ?></span>
            </div>
            <div class="post-card-title">
                <a href="post.php?id=<?= $post['id'] ?>"><?= h($post['title']) ?></a>
            </div>
            <p class="post-card-excerpt">
                <?= h(mb_strimwidth(strip_tags($post['content']), 0, 200, '…')) ?>
            </p>
            <div class="post-card-footer">
                <a href="post.php?id=<?= $post['id'] ?>" class="read-more">Читать</a>
                <span style="font-size:0.85rem; color:var(--ink-light);">♥ <?= (int)$post['likes'] ?></span>
            </div>
        </div>
    </article>
    <?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>" class="prev">← Назад</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i == $page): ?>
            <span class="active"><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>" class="next">Вперёд →</a>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

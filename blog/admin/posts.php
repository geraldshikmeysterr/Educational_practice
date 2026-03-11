<?php
$pageTitle = 'Управление постами';
require_once 'header.php';

$pdo = getDB();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    // Delete image file if exists
    $imgStmt = $pdo->prepare("SELECT image FROM posts WHERE id = ?");
    $imgStmt->execute([$deleteId]);
    $imgRow = $imgStmt->fetch();
    if ($imgRow && $imgRow['image'] && file_exists(__DIR__ . '/../' . $imgRow['image'])) {
        unlink(__DIR__ . '/../' . $imgRow['image']);
    }
    // Comments cascade via FK, but also delete manually for safety
    $pdo->prepare("DELETE FROM comments WHERE post_id = ?")->execute([$deleteId]);
    $pdo->prepare("DELETE FROM post_likes WHERE post_id = ?")->execute([$deleteId]);
    $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$deleteId]);
    $successMsg = 'Пост удалён.';
}

$posts = $pdo->query("
    SELECT p.id, p.title, p.created_at, p.likes, u.name AS author_name,
           (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
    FROM posts p JOIN users u ON p.author_id = u.id
    ORDER BY p.created_at DESC
")->fetchAll();
?>

<div class="admin-header">
    <div>
        <h1 class="page-title">Посты</h1>
        <p class="page-subtitle">Всего: <?= count($posts) ?></p>
    </div>
    <a href="posts_form.php" class="btn btn-primary">+ Новый пост</a>
</div>

<?php if (isset($successMsg)): ?>
<div class="alert alert-success"><?= h($successMsg) ?></div>
<?php endif; ?>

<?php if (empty($posts)): ?>
<div class="empty-state">
    <div class="empty-state-icon">📝</div>
    <p>Постов ещё нет. <a href="posts_form.php">Создать первый</a></p>
</div>
<?php else: ?>
<table class="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Заголовок</th>
            <th>Автор</th>
            <th>Дата</th>
            <th>♥ Лайки</th>
            <th>💬</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($posts as $post): ?>
        <tr>
            <td><?= $post['id'] ?></td>
            <td>
                <a href="../post.php?id=<?= $post['id'] ?>" target="_blank">
                    <?= h(mb_strimwidth($post['title'], 0, 50, '…')) ?>
                </a>
            </td>
            <td><?= h($post['author_name']) ?></td>
            <td><?= formatDate($post['created_at']) ?></td>
            <td><?= (int)$post['likes'] ?></td>
            <td><?= (int)$post['comment_count'] ?></td>
            <td>
                <div class="td-actions">
                    <a href="posts_form.php?id=<?= $post['id'] ?>" class="btn btn-outline btn-sm">✏️ Ред.</a>
                    <form method="POST" onsubmit="return confirm('Удалить пост и все его комментарии?')">
                        <input type="hidden" name="delete_id" value="<?= $post['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">🗑 Удал.</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require_once 'footer.php'; ?>

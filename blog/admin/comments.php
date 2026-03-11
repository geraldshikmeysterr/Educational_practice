<?php
$pageTitle = 'Комментарии';
require_once 'header.php';

$pdo = getDB();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    $pdo->prepare("DELETE FROM comment_likes WHERE comment_id = ?")->execute([$deleteId]);
    $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$deleteId]);
    $successMsg = 'Комментарий удалён.';
}

$comments = $pdo->query("
    SELECT c.id, c.content, c.created_at, c.likes,
           u.name AS user_name,
           p.id AS post_id, p.title AS post_title
    FROM comments c
    JOIN users u ON c.user_id = u.id
    JOIN posts p ON c.post_id = p.id
    ORDER BY c.created_at DESC
    LIMIT 100
")->fetchAll();
?>

<div class="admin-header">
    <div>
        <h1 class="page-title">Комментарии</h1>
        <p class="page-subtitle">Последние <?= count($comments) ?></p>
    </div>
</div>

<?php if (isset($successMsg)): ?>
<div class="alert alert-success"><?= h($successMsg) ?></div>
<?php endif; ?>

<?php if (empty($comments)): ?>
<div class="empty-state">
    <div class="empty-state-icon">💬</div>
    <p>Комментариев пока нет.</p>
</div>
<?php else: ?>
<table class="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Автор</th>
            <th>Пост</th>
            <th>Комментарий</th>
            <th>Дата</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($comments as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= h($c['user_name']) ?></td>
            <td>
                <a href="../post.php?id=<?= $c['post_id'] ?>" target="_blank">
                    <?= h(mb_strimwidth($c['post_title'], 0, 35, '…')) ?>
                </a>
            </td>
            <td><?= h(mb_strimwidth($c['content'], 0, 80, '…')) ?></td>
            <td><?= formatDate($c['created_at']) ?></td>
            <td>
                <form method="POST" onsubmit="return confirm('Удалить комментарий?')">
                    <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">🗑 Удалить</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require_once 'footer.php'; ?>

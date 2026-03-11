<?php
$isEdit = isset($_GET['id']) && (int)$_GET['id'] > 0;
$editId = $isEdit ? (int)$_GET['id'] : 0;
$pageTitle = $isEdit ? 'Редактировать пост' : 'Новый пост';

require_once 'header.php';

$pdo = getDB();
$post = null;
$errors = [];
$success = '';

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$editId]);
    $post = $stmt->fetch();
    if (!$post) { echo '<div class="alert alert-error">Пост не найден.</div>'; require_once 'footer.php'; exit; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (!$title) $errors[] = 'Введите заголовок.';
    if (!$content) $errors[] = 'Введите текст поста.';

    // Handle image upload
    $imagePath = $post['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Допустимые форматы: JPG, PNG, GIF, WebP.';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Файл слишком большой (макс. 5MB).';
        } else {
            $uploadsDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
            $newName = uniqid('img_') . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadsDir . $newName)) {
                // Remove old image
                if ($imagePath && file_exists(__DIR__ . '/../' . $imagePath)) {
                    unlink(__DIR__ . '/../' . $imagePath);
                }
                $imagePath = 'uploads/' . $newName;
            } else {
                $errors[] = 'Ошибка загрузки файла.';
            }
        }
    }

    // Remove image checkbox
    if (isset($_POST['remove_image']) && $imagePath) {
        if (file_exists(__DIR__ . '/../' . $imagePath)) unlink(__DIR__ . '/../' . $imagePath);
        $imagePath = null;
    }

    if (empty($errors)) {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, image = ? WHERE id = ?");
            $stmt->execute([$title, $content, $imagePath, $editId]);
            $success = 'Пост обновлён!';
            // Refresh post data
            $stmt2 = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
            $stmt2->execute([$editId]);
            $post = $stmt2->fetch();
        } else {
            $stmt = $pdo->prepare("INSERT INTO posts (title, content, image, author_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $content, $imagePath, $_SESSION['user_id']]);
            $newId = $pdo->lastInsertId();
            $success = 'Пост создан! <a href="../post.php?id=' . $newId . '" target="_blank">Просмотреть</a>';
        }
    }
}
?>

<div class="admin-header">
    <div>
        <h1 class="page-title"><?= $isEdit ? 'Редактировать пост' : 'Новый пост' ?></h1>
    </div>
    <a href="posts.php" class="btn btn-outline">← К списку</a>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php foreach ($errors as $err): ?>
<div class="alert alert-error"><?= h($err) ?></div>
<?php endforeach; ?>

<div style="background:var(--warm-white); border:1px solid var(--border); border-radius:var(--radius-lg); padding:32px;">
<form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label class="form-label" for="title">Заголовок</label>
        <input type="text" id="title" name="title" class="form-control" required maxlength="255"
               value="<?= h($_POST['title'] ?? $post['title'] ?? '') ?>"
               placeholder="Заголовок поста">
    </div>

    <div class="form-group">
        <label class="form-label" for="content">Текст</label>
        <textarea id="content" name="content" class="form-control" required
                  style="min-height:320px;" placeholder="Текст статьи…"><?= h($_POST['content'] ?? $post['content'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label class="form-label">Изображение</label>
        <label class="file-upload-label" for="image-upload">
            📎 <span id="file-label-text">Выберите файл</span>
        </label>
        <input type="file" id="image-upload" name="image" accept="image/*">
        <small style="color:var(--ink-light); display:block; margin-top:6px;">JPG, PNG, GIF, WebP · макс. 5MB</small>
        <?php if (!empty($post['image'])): ?>
        <div style="margin-top:12px; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
            <img src="../<?= h($post['image']) ?>" class="current-image" alt="Текущее изображение">
            <label style="display:flex; align-items:center; gap:6px; font-size:0.9rem; cursor:pointer;">
                <input type="checkbox" name="remove_image" value="1"> Удалить изображение
            </label>
        </div>
        <?php endif; ?>
    </div>

    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:24px;">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? '💾 Сохранить' : '➕ Создать пост' ?></button>
        <a href="posts.php" class="btn btn-outline">Отмена</a>
    </div>
</form>
</div>

<?php require_once 'footer.php'; ?>

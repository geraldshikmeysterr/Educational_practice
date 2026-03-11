<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

if (isLoggedIn()) redirect('index.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = 'Заполните все поля.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];
            redirect('index.php');
        } else {
            $errors[] = 'Неверный email или пароль.';
        }
    }
}

$pageTitle = 'Вход';
include 'includes/header.php';
?>

<div class="form-wrapper">
    <h1 class="form-title">Вход</h1>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= h($err) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control"
                   value="<?= h($_POST['email'] ?? '') ?>" required
                   placeholder="you@example.com">
        </div>
        <div class="form-group">
            <label class="form-label" for="password">Пароль</label>
            <input type="password" id="password" name="password" class="form-control"
                   required placeholder="Ваш пароль">
        </div>
        <button type="submit" class="btn btn-primary btn-full">Войти</button>
    </form>
    <p style="text-align:center; margin-top:18px; font-size:0.9rem; color:var(--ink-light);">
        Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
    </p>
    <p style="text-align:center; margin-top:10px; font-size:0.82rem; color:var(--ink-light);">
        Демо-аккаунт: <code>admin@blog.com</code> / <code>password</code>
    </p>
</div>

<?php include 'includes/footer.php'; ?>

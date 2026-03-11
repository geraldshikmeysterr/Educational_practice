<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

if (isLoggedIn()) redirect('index.php');

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!$name || mb_strlen($name) < 2) {
        $errors[] = 'Имя должно быть не менее 2 символов.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email.';
    }
    if (mb_strlen($password) < 6) {
        $errors[] = 'Пароль должен быть не менее 6 символов.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Пароли не совпадают.';
    }

    if (empty($errors)) {
        $pdo = getDB();
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'Пользователь с таким email уже существует.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hash]);
            $success = 'Регистрация прошла успешно! <a href="login.php">Войти</a>';
        }
    }
}

$pageTitle = 'Регистрация';
include 'includes/header.php';
?>

<div class="form-wrapper">
    <h1 class="form-title">Регистрация</h1>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= h($err) ?></div>
    <?php endforeach; ?>

    <?php if (!$success): ?>
    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label" for="name">Имя</label>
            <input type="text" id="name" name="name" class="form-control"
                   value="<?= h($_POST['name'] ?? '') ?>" required minlength="2" maxlength="100"
                   placeholder="Ваше имя">
        </div>
        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control"
                   value="<?= h($_POST['email'] ?? '') ?>" required
                   placeholder="you@example.com">
        </div>
        <div class="form-group">
            <label class="form-label" for="password">Пароль</label>
            <input type="password" id="password" name="password" class="form-control"
                   required minlength="6" placeholder="Не менее 6 символов">
        </div>
        <div class="form-group">
            <label class="form-label" for="confirm">Подтверждение пароля</label>
            <input type="password" id="confirm" name="confirm" class="form-control"
                   required placeholder="Повторите пароль">
        </div>
        <button type="submit" class="btn btn-primary btn-full">Зарегистрироваться</button>
    </form>
    <p style="text-align:center; margin-top:18px; font-size:0.9rem; color:var(--ink-light);">
        Уже есть аккаунт? <a href="login.php">Войти</a>
    </p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

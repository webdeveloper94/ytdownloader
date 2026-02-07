<?php
// register.php
require_once 'config/db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $login = $_POST['login'];
    $password = $_POST['password'];

    if (empty($name) || empty($login) || empty($password)) {
        $error = "Barcha maydonlarni to'ldiring!";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $error = "Ushbu login band!";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, login, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $login, $hashedPassword]);
            $success = "Ro'yxatdan o'tdingiz! Endi tizimga kirishingiz mumkin.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ro'yxatdan o'tish - YT Downloader</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>Ro'yxatdan o'tish</h2>
            <?php if ($error): ?>
                <div style="color: #ff4d4d; margin-bottom: 20px; text-align: center;"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div style="color: #2ecc71; margin-bottom: 20px; text-align: center;"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Ismingiz</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Login</label>
                    <input type="text" name="login" required>
                </div>
                <div class="form-group">
                    <label>Parol</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn">Ro'yxatdan o'tish</button>
            </form>
            <div class="auth-links">
                <a href="login.php">Akkauntingiz bormi? Kirish</a>
            </div>
        </div>
    </div>
</body>
</html>

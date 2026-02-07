<?php
// login.php
require_once 'config/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: user/dashboard.php");
        }
        exit();
    } else {
        $error = "Login yoki parol xato!";
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirish - YT Downloader</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>Kirish</h2>
            <?php if ($error): ?>
                <div style="color: #ff4d4d; margin-bottom: 20px; text-align: center;"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Login</label>
                    <input type="text" name="login" required>
                </div>
                <div class="form-group">
                    <label>Parol</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn">Kirish</button>
            </form>
            <div class="auth-links">
                <a href="register.php">Akkauntingiz yo'qmi? Ro'yxatdan o'tish</a>
            </div>
        </div>
    </div>
</body>
</html>

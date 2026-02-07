<?php
// admin_create.php
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
            $stmt = $pdo->prepare("INSERT INTO users (name, login, password, role) VALUES (?, ?, ?, 'admin')");
            $stmt->execute([$name, $login, $hashedPassword]);
            $success = "Yangi Admin muvaffaqiyatli yaratildi!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yangi Admin Yaratish - Maxfiy Sahifa</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-dark text-white">
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>Admin Yaratish</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group mb-3">
                    <label class="form-label text-white-50">Ism</label>
                    <input type="text" name="name" class="form-control bg-dark text-white border-secondary" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label text-white-50">Login</label>
                    <input type="text" name="login" class="form-control bg-dark text-white border-secondary" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label text-white-50">Parol</label>
                    <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required>
                </div>
                <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">Yaratish</button>
            </form>
            <div class="mt-3 text-center">
                <a href="login.php" class="text-secondary text-decoration-none small">Kirish sahifasiga o'tish</a>
            </div>
        </div>
    </div>
</body>
</html>

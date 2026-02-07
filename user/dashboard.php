<?php
// user/dashboard.php
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();

// User ma'lumotlarini olish va obuna holatini tekshirish
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$isSubscribed = false;
if ($user['subscription_expires_at'] && strtotime($user['subscription_expires_at']) > time()) {
    $isSubscribed = true;
}

// Sozlamalardan narxlarni olish
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - YT Downloader</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .navbar {
            background: var(--secondary-color) !important;
            border-bottom: 1px solid var(--glass);
        }
        .nav-link { color: white !important; }
        .nav-link:hover { color: var(--primary-color) !important; }
    </style>
</head>
<body class="bg-dark text-white">
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-danger fs-3" href="dashboard.php">YT Downloader</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="userNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link px-3" href="dashboard.php">Asosiy</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="payment.php">To'lov</a></li>
                    <li class="nav-item"><a class="nav-link px-3 text-warning" href="../logout.php">Chiqish</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <h1>Xush kelibsiz, <?php echo htmlspecialchars($user['name']); ?>!</h1>
            <p style="margin-top: 10px;">
                Obuna holati: 
                <?php if ($isSubscribed): ?>
                    <span style="color: #2ecc71; font-weight: bold;">Faol (Gacha: <?php echo $user['subscription_expires_at']; ?>)</span>
                <?php else: ?>
                    <span style="color: #ff4d4d; font-weight: bold;">Faol emas</span>
                <?php endif; ?>
            </p>
            <p style="margin-top: 5px;">
                Qolgan videolar: <span style="font-weight: bold; color: var(--accent-color);"><?php echo $user['downloads_left']; ?> ta</span>
            </p>
        </div>

        <?php if ($isSubscribed || $user['downloads_left'] > 0): ?>
            <div class="card">
                <h3>Video yuklash</h3>
                <form action="download.php" method="POST" style="margin-top: 20px;">
                    <div class="form-group">
                        <label>YouTube Video Linki</label>
                        <input type="url" name="url" placeholder="https://www.youtube.com/watch?v=..." required>
                    </div>
                    <button type="submit" class="btn">Yuklash haqida ma'lumot olish</button>
                </form>
            </div>
        <?php else: ?>
            <div class="card" style="text-align: center;">
                <h3>Video yuklash uchun obuna bo'lishingiz kerak</h3>
                <p style="margin: 15px 0;">Sizda hozircha faol obuna mavjud emas. Video yuklash xizmatidan foydalanish uchun to'lov qiling.</p>
                <a href="payment.php" class="btn" style="display: inline-block; width: auto; padding: 12px 30px; text-decoration: none;">To'lov sahifasiga o'tish</a>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

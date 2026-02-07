<?php
// admin/settings.php
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();
requireAdmin();

$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST as $key => $value) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
    }
    $success = "Sozlamalar saqlandi!";
}

$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sozlamalar - Admin</title>
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
            <a class="navbar-brand fw-bold text-danger fs-3" href="dashboard.php">YT Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link px-3" href="dashboard.php">Asosiy</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="users.php">Foydalanuvchilar</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="payments.php">To'lovlar</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="settings.php">Sozlamalar</a></li>
                    <li class="nav-item"><a class="nav-link px-3 text-warning" href="../logout.php">Chiqish</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <h2>Tizim Sozlamalari</h2>
            <?php if ($success): ?>
                <div style="color: #2ecc71; margin-bottom: 20px;"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Karta raqami</label>
                    <input type="text" name="card_number" value="<?php echo htmlspecialchars($settings['card_number']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Karta egasi</label>
                    <input type="text" name="card_holder" value="<?php echo htmlspecialchars($settings['card_holder']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Oylik obuna narxi (so'm)</label>
                    <input type="number" name="monthly_price" value="<?php echo htmlspecialchars($settings['monthly_price']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Bitta video yuklash narxi (so'm)</label>
                    <input type="number" name="per_video_price" value="<?php echo htmlspecialchars($settings['per_video_price']); ?>" required>
                </div>
                <button type="submit" class="btn">Saqlash</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

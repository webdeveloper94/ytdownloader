<?php
// admin/dashboard.php
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();
requireAdmin();

// Statisika
$userCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$pendingPayments = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();
$totalEarnings = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'approved'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - YT Downloader</title>
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
                    <li class="nav-item"><a class="nav-link px-3" href="payments.php">To'lovlar (<?php echo $pendingPayments; ?>)</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="settings.php">Sozlamalar</a></li>
                    <li class="nav-item"><a class="nav-link px-3 text-warning" href="../logout.php">Chiqish</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Admin Panel</h1>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
            <div class="card" style="text-align: center;">
                <h3>Foydalanuvchilar</h3>
                <p style="font-size: 32px; font-weight: bold; margin-top: 10px;"><?php echo $userCount; ?></p>
            </div>
            <div class="card" style="text-align: center;">
                <h3>Kutilayotgan to'lovlar</h3>
                <p style="font-size: 32px; font-weight: bold; margin-top: 10px; color: #f1c40f;"><?php echo $pendingPayments; ?></p>
            </div>
            <div class="card" style="text-align: center;">
                <h3>Umumiy daromad</h3>
                <p style="font-size: 32px; font-weight: bold; margin-top: 10px; color: #2ecc71;"><?php echo number_format($totalEarnings); ?> so'm</p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

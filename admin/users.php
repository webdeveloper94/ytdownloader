<?php
// admin/users.php
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();
requireAdmin();

$users = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foydalanuvchilar - Admin</title>
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
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--glass); }
        .sub-active { color: #2ecc71; }
        .sub-inactive { color: #e74c3c; }
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
            <h2>Foydalanuvchilar ro'yxati</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ism</th>
                        <th>Login</th>
                        <th>Obuna tugash muddati</th>
                        <th>Ro'yxatdan o'tgan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Hozircha foydalanuvchilar yo'q.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['name']); ?></td>
                                <td><?php echo htmlspecialchars($u['login']); ?></td>
                                <td class="<?php echo ($u['subscription_expires_at'] && strtotime($u['subscription_expires_at']) > time()) ? 'sub-active' : 'sub-inactive'; ?>">
                                    <?php echo $u['subscription_expires_at'] ?? 'Faol emas'; ?>
                                </td>
                                <td><?php echo $u['created_at']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

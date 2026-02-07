<?php
// admin/payments.php
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();
requireAdmin();

// Tasdiqlash yoki rad etish
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        // To'lovni tasdiqlash
        $stmt = $pdo->prepare("UPDATE payments SET status = 'approved' WHERE id = ?");
        $stmt->execute([$id]);

        // To'lov ma'lumotlarini olish
        $paymentStmt = $pdo->prepare("SELECT user_id, subscription_type, video_count FROM payments WHERE id = ?");
        $paymentStmt->execute([$id]);
        $payment = $paymentStmt->fetch();
        $userId = $payment['user_id'];

        if ($payment['subscription_type'] === 'per_video') {
            // Video-ba-video: userga videolar sonini qo'shish
            $stmt = $pdo->prepare("UPDATE users SET downloads_left = downloads_left + ? WHERE id = ?");
            $stmt->execute([$payment['video_count'], $userId]);
        } else {
            // Oylik obuna: 1 oy qo'shish
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 month'));
            $stmt = $pdo->prepare("UPDATE users SET subscription_expires_at = ? WHERE id = ?");
            $stmt->execute([$expiresAt, $userId]);
        }
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE payments SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: payments.php");
    exit();
}

$payments = $pdo->query("SELECT p.*, u.name as user_name FROM payments p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To'lovlar - Admin</title>
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
        .status-pending { color: #f1c40f; }
        .status-approved { color: #2ecc71; }
        .status-rejected { color: #e74c3c; }
        .btn-small { padding: 5px 10px; font-size: 12px; width: auto; margin-right: 5px; text-decoration: none; }
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
            <h2>To'lovlar ro'yxati</h2>
            <table>
                <thead>
                    <tr>
                        <th>Foydalanuvchi</th>
                        <th>Tur</th>
                        <th>Summa</th>
                        <th>Chek</th>
                        <th>Holat</th>
                        <th>Sana</th>
                        <th>Amalllar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Hozircha to'lovlar yo'q.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['user_name']); ?></td>
                            <td><?php echo $p['subscription_type'] === 'monthly' ? 'Oylik' : 'Video (' . $p['video_count'] . ')'; ?></td>
                            <td><?php echo number_format($p['amount']); ?> so'm</td>
                            <td><a href="../uploads/<?php echo $p['screenshot_path']; ?>" target="_blank" style="color: var(--accent-color);">Ko'rish</a></td>
                                <td class="status-<?php echo $p['status']; ?>"><?php echo ucfirst($p['status']); ?></td>
                                <td><?php echo $p['created_at']; ?></td>
                                <td>
                                    <?php if ($p['status'] === 'pending'): ?>
                                        <a href="?action=approve&id=<?php echo $p['id']; ?>" class="btn btn-small" style="background: #2ecc71;">Tasdiqlash</a>
                                        <a href="?action=reject&id=<?php echo $p['id']; ?>" class="btn btn-small" style="background: #e74c3c;">Rad etish</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
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

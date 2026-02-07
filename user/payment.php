<?php
// user/payment.php
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();

// Sozlamalardan karta ma'lumotlarini olish
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['screenshot'])) {
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $video_count = 0;
    
    if ($type === 'per_video') {
        // Har bir video uchun 1ta yuklab olish huquqi (yoki summa bo'yicha hisoblash mumkin)
        // Biz hozircha oddiyroq qilamiz: agar per_video tanlansa, 
        // to'langan summa / bitta video narxi = videolar soni
        $perVideoPrice = (int)$settings['per_video_price'];
        $video_count = floor($amount / $perVideoPrice);
    }
    
    $file = $_FILES['screenshot'];
    $fileName = time() . '_' . $file['name'];
    $targetPath = '../uploads/' . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("INSERT INTO payments (user_id, amount, screenshot_path, subscription_type, video_count) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $amount, $fileName, $type, $video_count]);
        $success = "To'lov cheki yuborildi! Admin tasdiqlashini kuting.";
    } else {
        $error = "Fayl yuklashda xatolik yuz berdi.";
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To'lov - YT Downloader</title>
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
            <h2>To'lov qilish</h2>
            <p style="margin-bottom: 20px;">Quyidagi karta raqamiga to'lov qiling va chekni yuklang:</p>
            
            <div style="background: #2a2a2a; padding: 20px; border-radius: 10px; margin-bottom: 25px;">
                <p><strong>Karta raqami:</strong> <?php echo $settings['card_number']; ?></p>
                <p><strong>Ega:</strong> <?php echo $settings['card_holder']; ?></p>
                <hr style="border-color: rgba(255,255,255,0.1)">
                <p><strong>Oylik obuna:</strong> <?php echo number_format($settings['monthly_price']); ?> so'm</p>
                <p><strong>Bitta video uchun:</strong> <?php echo number_format($settings['per_video_price']); ?> so'm</p>
            </div>

            <?php if ($error): ?>
                <div style="color: #ff4d4d; margin-bottom: 20px;"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div style="color: #2ecc71; margin-bottom: 20px;"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>To'lov summasi (so'mda)</label>
                    <input type="number" name="amount" id="amount" value="<?php echo $settings['monthly_price']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Obuna turi</label>
                    <select name="type" id="type" onchange="updateAmount()" style="width: 100%; padding: 12px; border-radius: 8px; background: #2a2a2a; color: white; border: 1px solid var(--glass);">
                        <option value="monthly" data-price="<?php echo $settings['monthly_price']; ?>">Oylik obuna</option>
                        <option value="per_video" data-price="<?php echo $settings['per_video_price']; ?>">Video-ba-video</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>To'lov cheki (Screenshot)</label>
                    <input type="file" name="screenshot" accept="image/*" required>
                </div>
                <button type="submit" class="btn">Chekni yuborish</button>
            </form>
        </div>
    </div>

    <script>
        function updateAmount() {
            const select = document.getElementById('type');
            const amountInput = document.getElementById('amount');
            const selectedOption = select.options[select.selectedIndex];
            amountInput.value = selectedOption.getAttribute('data-price');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

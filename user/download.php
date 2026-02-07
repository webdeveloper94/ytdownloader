<?php
// user/download.php
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();

// ==========================================
// SERVER-SIDE PROXY DOWNLOAD LOGIC
// ==========================================
if (isset($_GET['url'])) {
    $downloadUrl = $_GET['url'];
    
    // Obunani yoki qolgan videolar sonini tekshirish (Xavfsizlik uchun qayta tekshiruv)
    $stmt = $pdo->prepare("SELECT subscription_expires_at, downloads_left FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userStatus = $stmt->fetch();
    $isSubscribed = ($userStatus['subscription_expires_at'] && strtotime($userStatus['subscription_expires_at']) > time());
    $hasDownloads = ($userStatus['downloads_left'] > 0);

    if (!$isSubscribed && !$hasDownloads) {
        die("Xatolik: Yuklab olish ruxsati yo'q.");
    }

    // Video oqimini (stream) boshlash
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="video_' . time() . '.mp4"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $downloadUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // Ma'lumotni to'g'ridan-to'g'ri uzatish
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0); // Vaqt cheklovini olib tashlash (katta videolar uchun)
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    
    // YouTube DASH cheklovlarini chetlab o'tish uchun headerlar
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Referer: https://www.youtube.com/'
    ]);

    // Oqimni boshlash
    curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        // Xatolik bo'lsa logga yozish
        file_put_contents('../api_debug.log', "[" . date('Y-m-d H:i:s') . "] Proxy Error: $curl_error" . PHP_EOL, FILE_APPEND);
    }
    exit();
}

// Obunani yoki qolgan videolar sonini tekshirish
$stmt = $pdo->prepare("SELECT subscription_expires_at, downloads_left FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userStatus = $stmt->fetch();

$isSubscribed = ($userStatus['subscription_expires_at'] && strtotime($userStatus['subscription_expires_at']) > time());
$hasDownloads = ($userStatus['downloads_left'] > 0);

if (!$isSubscribed && !$hasDownloads) {
    header("Location: dashboard.php");
    exit();
}

// RapidAPI orqali video ma'lumotlarini olish (Faqat info qismi qoldi)
$videoInfo = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['url'])) {
    $url = $_POST['url'];
    
    // Sozlamalardan API keyni olish
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'rapidapi_key'");
    $apiKey = trim($stmt->fetchColumn());

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://yt-video-audio-downloader-api.p.rapidapi.com/video_info",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode(["url" => $url]),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "X-RapidAPI-Key: " . $apiKey,
            "X-RapidAPI-Host: yt-video-audio-downloader-api.p.rapidapi.com"
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        $error = "API Error: " . $err;
    } else {
        $videoInfo = json_decode($response, true);
        
        if (isset($videoInfo['message']) && strpos($videoInfo['message'], 'not subscribed') !== false) {
            $error = "RapidAPI xatosi: Obuna yo'q.";
            $videoInfo = null;
        } elseif (isset($videoInfo['status']) && ($videoInfo['status'] === 'fail' || $videoInfo['status'] === 'error')) {
            $error = "Video ma'lumotlarini olib bo'lmadi.";
            $videoInfo = null;
        } elseif (isset($videoInfo['title'])) {
            // Download tarixiga qo'shish
            $stmt = $pdo->prepare("INSERT INTO downloads (user_id, video_link, video_title) VALUES (?, ?, ?)");
            $title = $videoInfo['title'] ?? 'YouTube Video';
            $stmt->execute([$_SESSION['user_id'], $url, $title]);

            // Agar oylik obuna bo'lmasa, downloads_left ni kamaytirish
            if (!$isSubscribed && $hasDownloads) {
                $stmt = $pdo->prepare("UPDATE users SET downloads_left = downloads_left - 1 WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
            }
        } else {
            $error = "API dan kutilmagan javob keldi.";
            $videoInfo = null;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Yuklash - YT Downloader</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .navbar { background: var(--secondary-color) !important; border-bottom: 1px solid var(--glass); }
        .nav-link { color: white !important; }
        .nav-link:hover { color: var(--primary-color) !important; }
        .download-item {
            background: #2a2a2a; padding: 12px 18px; border-radius: 10px;
            display: flex; justify-content: space-between; align-items: center;
            border: 1px solid var(--glass); transition: 0.3s; margin-bottom: 10px;
        }
        .download-item:hover { border-color: var(--primary-color); background: #333; }
    </style>
</head>
<body class="bg-dark text-white">
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-danger fs-3" href="dashboard.php">YT Downloader</a>
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
            <h2>Video Natijasi</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <a href="dashboard.php" class="btn btn-secondary">Orqaga</a>
            <?php elseif ($videoInfo): ?>
                <div>
                    <h3 class="mb-3"><?php echo htmlspecialchars($videoInfo['title']); ?></h3>
                    <?php if (isset($videoInfo['thumbnail'])): ?>
                        <img src="<?php echo $videoInfo['thumbnail']; ?>" class="img-fluid rounded mb-4" style="max-width: 480px;">
                    <?php endif; ?>
                    
                    <h4 class="mb-3">Yuklab olish formatlari:</h4>
                    <div class="format-list">
                        <?php 
                        $medias = $videoInfo['formats'] ?? $videoInfo['medias'] ?? [];
                        if (!empty($medias)):
                            foreach ($medias as $index => $media):
                                $qualityLabel = $media['formatNote'] ?? $media['quality'] ?? 'HD';
                                $extText = $media['extension'] ?? $media['type'] ?? 'MP4';
                                $googlevideoUrl = $media['url'] ?? '';

                                // Server-Side Proxy Link
                                $proxyDownloadLink = "download.php?url=" . urlencode($googlevideoUrl);
                            ?>
                            <div class="download-item">
                                <div class="item-info">
                                    <span class="badge bg-danger me-2"><?php echo strtoupper(htmlspecialchars($extText)); ?></span>
                                    <span class="fw-bold"><?php echo htmlspecialchars($qualityLabel); ?></span>
                                    <?php if(isset($media['filesize'])): ?>
                                        <small class="text-muted ms-2">(<?php echo round($media['filesize'] / 1024 / 1024, 2); ?> MB)</small>
                                    <?php endif; ?>
                                </div>
                                <div class="action-btn">
                                    <a href="<?php echo $proxyDownloadLink; ?>" class="btn btn-primary btn-sm" onclick="showLoading()">Yuklab olish</a>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <p class="text-warning">Yuklab olish linklari topilmadi.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 9999; justify-content: center; align-items: center; flex-direction: column; text-align: center; padding: 20px;">
        <div class="spinner-border text-danger mb-3" role="status" style="width: 3.5rem; height: 3.5rem;"></div>
        <h4 class="mb-2">Server bilan bog'lanilmoqda...</h4>
        <p class="text-white-50">Video oqimi server orqali tunnel qilinmoqda. <br> Yuklab olish brauzeringizda avtomatik boshlanadi.</p>
        <button class="btn btn-sm btn-outline-light mt-3" onclick="hideLoading()">Yopish</button>
    </div>

    <script>
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            // Brauzer yuklashni boshlaguncha modalni ko'rsatamiz
            setTimeout(hideLoading, 5000); 
        }
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
requireLogin();

// User limit check
$stmt = $pdo->prepare("SELECT subscription_expires_at, downloads_left FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$isSubscribed = ($user['subscription_expires_at'] &&
    strtotime($user['subscription_expires_at']) > time());

$hasDownloads = ($user['downloads_left'] > 0);

$error = '';
$videoUrl = '';
$videoInfo = null;

// POST request - video URL qabul qilish
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $videoUrl = $_POST['url'] ?? '';
    
    if (!$videoUrl) {
        $error = "Video URL kiritilmadi!";
    } elseif (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
        $error = "Noto'g'ri URL formati!";
    } elseif (strpos($videoUrl, 'youtube.com') === false && strpos($videoUrl, 'youtu.be') === false) {
        $error = "Faqat YouTube videolarni yuklab olish mumkin!";
    } else {
        // API dan video ma'lumotlarini olish
        // LOCAL MODE: Lokal serverda test qilish uchun localhost ishlatamiz
        // PRODUCTION MODE: VPS serverni ishlatish uchun quyidagi qatorlarni o'zgartiring
        $useLocal = true; // true = localhost (Windows XAMPP), false = VPS server
        
        if ($useLocal) {
            // Lokal server (XAMPP) - yt-dlp Windows da o'rnatilgan
            $baseUrl = "http://localhost/ytdownloader/";
            $infoApi1 = $baseUrl . "yt_info.php?url=" . urlencode($videoUrl);
            $infoApi2 = $baseUrl . "yt_api.php?info=1&url=" . urlencode($videoUrl);
        } else {
            // VPS server (95.111.250.26)
            $infoApi1 = "http://95.111.250.26/yt_info.php?info=1&url=" . urlencode($videoUrl);
            $infoApi2 = "http://95.111.250.26/yt_api.php?info=1&url=" . urlencode($videoUrl);
        }

        
        // Debug log
        $debugLog = "[" . date('Y-m-d H:i:s') . "] INFO REQUEST\n";
        $debugLog .= "Trying: " . $infoApi1 . "\n";
        
        $response = null;
        $httpCode = 0;
        $curlError = '';
        $curlErrno = 0;
        $apiUsed = '';
        
        // Birinchi variant: yt_info.php
        $ch = curl_init($infoApi1);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);
        
        $apiUsed = $infoApi1;
        
        // Debug log yozish
        $debugLog .= "API 1 (yt_info.php) - HTTP Code: " . $httpCode . "\n";
        $debugLog .= "CURL Error: " . ($curlError ?: 'None') . "\n";
        $debugLog .= "CURL Errno: " . ($curlErrno ?: '0') . "\n";
        
        // Agar 404 bo'lsa, ikkinchi variantni sinab ko'ramiz
        if ($httpCode == 404) {
            $debugLog .= "\nTrying alternative: " . $infoApi2 . "\n";
            
            $ch = curl_init($infoApi2);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => 90,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => true,
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);
            
            $apiUsed = $infoApi2;
            $debugLog .= "API 2 (yt_api.php?info=1) - HTTP Code: " . $httpCode . "\n";
        }
        
        $debugLog .= "Response Length: " . strlen($response) . " bytes\n";
        
        if ($response) {
            $debugLog .= "Response Preview: " . substr($response, 0, 500) . "\n";
        }
        
        if ($httpCode == 200 && $response) {
            $videoInfo = json_decode($response, true);
            $jsonError = json_last_error();
            
            $debugLog .= "JSON Decode Error: " . ($jsonError ? json_last_error_msg() : 'None') . "\n";
            
            if ($jsonError === JSON_ERROR_NONE && $videoInfo) {
                if (isset($videoInfo['error'])) {
                    // VPS-dan xato qaytdi
                    $vpsError = $videoInfo['error'] ?? "Video ma'lumotlari olinmadi";
                    $rawError = $videoInfo['raw'] ?? '';
                    
                    // Raw outputdan JSON ni ajratib olishga harakat qilish
                    if ($rawError) {
                        $lines = explode("\n", $rawError);
                        $extractedJson = null;
                        
                        foreach ($lines as $line) {
                            $trimmed = trim($line);
                            if ($trimmed && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
                                $extractedJson = json_decode($trimmed, true);
                                if ($extractedJson && json_last_error() === JSON_ERROR_NONE) {
                                    // JSON muvaffaqiyatli topildi!
                                    $videoInfo = $extractedJson;
                                    $error = ''; // Xatoni olib tashlash
                                    $debugLog .= "SUCCESS: JSON extracted from raw output\n";
                                    $debugLog .= "Video Title: " . ($videoInfo['title'] ?? 'N/A') . "\n";
                                    break;
                                }
                            }
                        }
                    }
                    
                    // Agar JSON topilmagan bo'lsa, xatolikni ko'rsatish
                    if ($error !== '') {
                        // yt-dlp versiya xatosini aniqlash
                        if (strpos($rawError, 'Precondition check failed') !== false || 
                            strpos($rawError, 'HTTP Error 400') !== false) {
                            $error = "VPS serverda yt-dlp versiyasi eskigan. ";
                            $error .= "YouTube-ning yangi talablariga mos emas. ";
                            $error .= "VPS serverda 'sudo pip3 install --upgrade yt-dlp' buyrug'ini bajaring.";
                        } elseif (strpos($rawError, 'No supported JavaScript runtime') !== false) {
                            $error = "VPS serverda JavaScript runtime (Node.js) o'rnatilmagan yoki to'g'ri sozlanmagan. ";
                            $error .= "VPS serverda 'sudo apt install -y nodejs' buyrug'ini bajaring.";
                        } else {
                            $error = "VPS API xatosi: " . $vpsError;
                        }
                        
                        $debugLog .= "API Error: " . $vpsError . "\n";
                        if ($rawError) {
                            $debugLog .= "Raw Error: " . substr($rawError, 0, 500) . "\n";
                        }
                    }
                } else {
                    $debugLog .= "SUCCESS: Video info received from " . $apiUsed . "\n";
                    $debugLog .= "Video Title: " . ($videoInfo['title'] ?? 'N/A') . "\n";
                    $debugLog .= "Formats Count: " . (isset($videoInfo['formats']) ? count($videoInfo['formats']) : 0) . "\n";
                }
            } else {
                $error = "Video ma'lumotlari JSON formatida emas";
                $debugLog .= "ERROR: Invalid JSON response\n";
            }
        } else {
            if ($curlErrno == 28) {
                $error = "VPS API so'rovni bajarishda vaqt tugadi (Timeout). Iltimos, qaytadan urinib ko'ring yoki URL manzilini tekshiring.";
            } elseif ($httpCode == 0 || $curlErrno) {
                $error = "VPS API ga ulanib bo'lmadi (CURL error: $curlError). Server ishlamayapti yoki internet aloqasi yo'q.";
            } elseif ($httpCode == 404) {
                $error = "VPS API endpoint topilmadi. Iltimos, VPS serverda quyidagi fayllardan birini yarating:\n";
                $error .= "1. yt_info.php (http://95.111.250.26/yt_info.php)\n";
                $error .= "2. Yoki yt_api.php ga ?info=1 parametri qo'shish";
            } elseif ($httpCode >= 500) {
                $error = "VPS serverda xatolik yuz berdi (HTTP $httpCode)";
            } else {
                $error = "Video ma'lumotlarini olishda xatolik yuz berdi (HTTP $httpCode)";
            }
            $debugLog .= "ERROR: " . $error . " (CURL Errno: $curlErrno)\n";
            $debugLog .= "Used API: " . $apiUsed . "\n";
        }
        
        // Debug log faylga yozish
        file_put_contents('../api_debug.log', $debugLog . "\n", FILE_APPEND);
    }
}

// GET request - video yuklab olish
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['url'])) {
    $videoUrl = $_GET['url'] ?? '';
    
    if (!$videoUrl) {
        die("Video URL kerak");
    }
    
    // Limit tekshirish va kamaytirish
    if (!$isSubscribed && !$hasDownloads) {
        die("Yuklab olish limiti tugagan.");
    }
    
    // Limit kamaytirish
    if (!$isSubscribed && $hasDownloads) {
        $pdo->prepare("
            UPDATE users
            SET downloads_left = downloads_left - 1
            WHERE id=? AND downloads_left>0
        ")->execute([$_SESSION['user_id']]);
    }
    
    // Video streaming API
    $useLocal = true; // Yuqoridagi qiymat bilan bir xil
    
    if ($useLocal) {
        $api = "http://localhost/ytdownloader/yt_api.php?url=" . urlencode($videoUrl);
    } else {
        $api = "http://95.111.250.26/yt_api.php?url=" . urlencode($videoUrl);
    }
    
    set_time_limit(0);
    ignore_user_abort(true);
    
    header('Content-Type: video/mp4');
    header('Content-Disposition: attachment; filename="video.mp4"');
    header('Cache-Control: no-cache');
    
    $ch = curl_init($api);
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_BUFFERSIZE => 8192,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_WRITEFUNCTION => function ($ch, $data) {
            echo $data;
            flush();
            return strlen($data);
        }
    ]);
    
    curl_exec($ch);
    
    if (curl_errno($ch)) {
        $err = curl_error($ch);
        $errno = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        $debugLog = "[" . date('Y-m-d H:i:s') . "] VPS STREAM ERROR\n";
        $debugLog .= "URL: " . $api . "\n";
        $debugLog .= "HTTP Code: " . $httpCode . "\n";
        $debugLog .= "CURL Error: " . $err . "\n";
        $debugLog .= "CURL Errno: " . $errno . "\n\n";
        
        file_put_contents('../api_debug.log', $debugLog, FILE_APPEND);
    } else {
        // Muvaffaqiyatli streaming
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $debugLog = "[" . date('Y-m-d H:i:s') . "] VPS STREAM SUCCESS\n";
        $debugLog .= "URL: " . $api . "\n";
        $debugLog .= "HTTP Code: " . $httpCode . "\n\n";
        file_put_contents('../api_debug.log', $debugLog, FILE_APPEND);
    }
    
    curl_close($ch);
    exit();
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
        .download-card {
            background: #2a2a2a; padding: 30px; border-radius: 15px;
            border: 1px solid var(--glass); margin-top: 20px;
        }
        .download-btn {
            padding: 15px 40px; font-size: 18px; font-weight: bold;
            border-radius: 10px; transition: 0.3s;
        }
        .download-btn:hover {
            transform: scale(1.05);
        }
        .thumbnail-box img {
            max-width: 100%;
            border-radius: 10px;
            border: 1px solid var(--glass);
        }
        .download-item {
            background: #2a2a2a;
            padding: 12px 18px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--glass);
            transition: 0.3s;
            margin-bottom: 10px;
        }
        .download-item:hover {
            border-color: var(--primary-color);
            background: #333;
        }
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
        <?php if ($error): ?>
            <div class="card">
                <div class="alert alert-danger px-4 py-3">
                    <h4>Xatolik!</h4>
                    <p><?php echo htmlspecialchars($error); ?></p>
                    <?php if (file_exists('../api_debug.log')): ?>
                        <details class="mt-3">
                            <summary class="text-white-50" style="cursor: pointer;">Debug ma'lumotlari</summary>
                            <pre class="mt-2 p-2 bg-dark text-white-50 small" style="max-height: 200px; overflow-y: auto; border-radius: 5px;"><?php 
                                $logContent = file_get_contents('../api_debug.log');
                                echo htmlspecialchars(substr($logContent, -2000)); // Oxirgi 2000 belgi
                            ?></pre>
                        </details>
                    <?php endif; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="dashboard.php" class="btn btn-secondary px-5">Orqaga</a>
                </div>
            </div>
        <?php elseif ($videoUrl && $videoInfo): ?>
            <div class="card p-4">
                <h2 class="mb-4 text-center">Video Natijasi</h2>
                
                <div class="row align-items-start">
                    <div class="col-md-5 mb-4 thumbnail-box">
                        <?php 
                        $thumb = $videoInfo['thumbnail'] ?? '';
                        if (isset($videoInfo['thumbnails']) && is_array($videoInfo['thumbnails']) && !empty($videoInfo['thumbnails'])) {
                            $thumb = end($videoInfo['thumbnails'])['url'] ?? $thumb;
                        }
                        if ($thumb):
                        ?>
                            <img src="<?php echo htmlspecialchars($thumb); ?>" class="img-fluid shadow-lg" alt="Video thumbnail">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-7">
                        <h4 class="mb-4 text-primary"><?php echo htmlspecialchars($videoInfo['title'] ?? 'Noma\'lum video'); ?></h4>
                        
                        <?php if (isset($videoInfo['description'])): ?>
                            <p class="text-white-50 mb-4 small"><?php echo htmlspecialchars(substr($videoInfo['description'], 0, 200)) . (strlen($videoInfo['description']) > 200 ? '...' : ''); ?></p>
                        <?php endif; ?>
                        
                        <div class="format-list">
                            <?php 
                            $formats = $videoInfo['formats'] ?? [];
                            $shownFormats = [];
                            
                            // Formatlarni saralash - eng yaxshi sifatlarni birinchi ko'rsatish
                            usort($formats, function($a, $b) {
                                $heightA = $a['height'] ?? 0;
                                $heightB = $b['height'] ?? 0;
                                return $heightB - $heightA;
                            });
                            
                            foreach ($formats as $f): 
                                // Faqat video+audio yoki audio formatlarni ko'rsatish
                                $hasVideo = isset($f['vcodec']) && $f['vcodec'] !== 'none';
                                $hasAudio = isset($f['acodec']) && $f['acodec'] !== 'none';
                                
                                if (!$hasVideo && !$hasAudio) continue;
                                
                                $formatId = $f['format_id'] ?? '';
                                $quality = '';
                                
                                if ($hasVideo) {
                                    $height = $f['height'] ?? 0;
                                    $quality = $height . 'p';
                                } else {
                                    $quality = 'Audio';
                                }
                                
                                // Dublikatlarni oldini olish
                                $formatKey = $quality . '_' . ($f['ext'] ?? 'mp4');
                                if (in_array($formatKey, $shownFormats)) continue;
                                $shownFormats[] = $formatKey;
                                
                                $ext = $f['ext'] ?? 'mp4';
                                $size = $f['filesize'] ?? $f['filesize_approx'] ?? 0;
                                
                                $downloadLink = "download.php?url=" . urlencode($videoUrl);
                            ?>
                                <div class="download-item">
                                    <div class="item-info">
                                        <span class="badge bg-danger me-2"><?php echo strtoupper(htmlspecialchars($ext)); ?></span>
                                        <span class="fw-bold fs-6"><?php echo htmlspecialchars($quality); ?></span>
                                        <?php if($size > 0): ?>
                                            <small class="text-white-50 ms-2">(<?php echo round($size / 1024 / 1024, 2); ?> MB)</small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="action-btn">
                                        <a href="<?php echo $downloadLink; ?>" 
                                           class="btn btn-primary btn-sm px-4" 
                                           onclick="showLoading()">
                                            Yuklab olish
                                        </a>
                                    </div>
                                </div>
                            <?php 
                            endforeach; 
                            ?>
                        </div>
                        
                        <?php if (empty($shownFormats)): ?>
                            <p class="text-warning">Yuklab olish formatlari topilmadi.</p>
                            <a href="download.php?url=<?php echo urlencode($videoUrl); ?>" 
                               class="btn btn-danger" 
                               onclick="showLoading()">
                                <i class="fa-solid fa-download me-2"></i>Video yuklab olish
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php elseif ($videoUrl): ?>
            <div class="card download-card text-center">
                <h2 class="mb-4">Video tayyor</h2>
                <p class="mb-4 text-white-50">Quyidagi tugmani bosib videoni yuklab oling</p>
                
                <div class="mb-4">
                    <p class="text-white-50 small">
                        <strong>Video URL:</strong><br>
                        <span class="text-white"><?php echo htmlspecialchars($videoUrl); ?></span>
                    </p>
                </div>
                
                <a href="download.php?url=<?php echo urlencode($videoUrl); ?>" 
                   class="btn btn-danger download-btn" 
                   onclick="showLoading()">
                    <i class="fa-solid fa-download me-2"></i>Video yuklab olish
                </a>
                
                <div class="mt-4">
                    <p class="text-white-50 small">
                        <i class="fa-solid fa-info-circle me-2"></i>
                        Video serverda tayyorlanmoqda. Bu bir necha daqiqa vaqt olishi mumkin.
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <h2>Video yuklash</h2>
                <form method="POST" style="margin-top: 20px;">
                    <div class="form-group">
                        <label>YouTube Video Linki</label>
                        <input type="url" name="url" placeholder="https://www.youtube.com/watch?v=..." required>
                    </div>
                    <button type="submit" class="btn">Yuklab olish</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 9999; justify-content: center; align-items: center; flex-direction: column; text-align: center; padding: 20px;">
        <div class="spinner-border text-danger mb-3" role="status" style="width: 3.5rem; height: 3.5rem;"></div>
        <h4 class="mb-2">Video tayyorlanmoqda...</h4>
        <p class="text-white-50">Video serverda yuklab olinmoqda. <br> Bu bir necha daqiqa vaqt olishi mumkin.</p>
        <button class="btn btn-sm btn-outline-light mt-3" onclick="hideLoading()">Yopish</button>
    </div>

    <script>
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>

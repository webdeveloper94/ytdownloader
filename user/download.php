<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/rapidapi.php';
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
        // RapidAPI dan video ma'lumotlarini olish
        set_time_limit(60);
        
        // Debug log
        $debugLog = "[" . date('Y-m-d H:i:s') . "] RAPIDAPI REQUEST\n";
        $debugLog .= "Video URL: " . $videoUrl . "\n";
        
        $videoInfo = getVideoInfo($videoUrl);
        
        if (isset($videoInfo['error'])) {
            $error = "Video ma'lumotlari olinmadi: " . $videoInfo['error'];
            $debugLog .= "ERROR: " . $videoInfo['error'] . "\n";
        } else {
            $debugLog .= "SUCCESS: Video info received\n";
            $debugLog .= "Title: " . ($videoInfo['title'] ?? 'N/A') . "\n";
            $debugLog .= "Formats: " . (isset($videoInfo['formats']) ? count($videoInfo['formats']) : 0) . "\n";
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
    
    // Video info olish
    $videoInfo = getVideoInfo($videoUrl);
    
    if (isset($videoInfo['error'])) {
        die("Video ma'lumotlari olinmadi: " . $videoInfo['error']);
    }
    
    // Download URL olish
    $downloadUrl = getDownloadUrl($videoInfo);
    
    if (!$downloadUrl) {
        die("Video yuklab olish URL topilmadi");
    }
    
    // Video streaming
    set_time_limit(0);
    ignore_user_abort(true);
    
    header('Content-Type: video/mp4');
    header('Content-Disposition: attachment; filename="video.mp4"');
    header('Cache-Control: no-cache');
    
    $ch = curl_init($downloadUrl);
    
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
        
        $debugLog = "[" . date('Y-m-d H:i:s') . "] STREAM ERROR\n";
        $debugLog .= "CURL Error: " . $err . "\n";
        $debugLog .= "CURL Errno: " . $errno . "\n\n";
        
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
                            
                            if (empty($formats)) {
                                echo '<p class="text-warning">Formatlar topilmadi.</p>';
                            } else {
                                // Sort by quality (higher first)
                                usort($formats, function($a, $b) {
                                    $qualityA = $a['quality'] ?? 0;
                                    $qualityB = $b['quality'] ?? 0;
                                    
                                    // If quality is numeric, compare as numbers
                                    if (is_numeric($qualityA) && is_numeric($qualityB)) {
                                        return $qualityB - $qualityA;
                                    }
                                    
                                    return 0;
                                });
                                
                                foreach ($formats as $f): 
                                    // Robust detection of video/audio availability
                                    $hasVideo = false;
                                    $hasAudio = false;
                                    $muxed = isset($f['muxed']) && $f['muxed'];
                                    
                                    // Check for new API keys
                                    if (isset($f['hasVideo'])) $hasVideo = (bool)$f['hasVideo'];
                                    if (isset($f['hasAudio'])) $hasAudio = (bool)$f['hasAudio'];
                                    
                                    // Fallback to traditional keys if new keys are missing
                                    if (!$hasVideo && isset($f['vcodec']) && $f['vcodec'] !== 'none') $hasVideo = true;
                                    if (!$hasAudio && isset($f['acodec']) && $f['acodec'] !== 'none') $hasAudio = true;
                                    
                                    // If both detected, it's muxed
                                    if ($hasVideo && $hasAudio) $muxed = true;
                                    
                                    $itag = $f['itag'] ?? '';
                                    $quality = $f['quality'] ?? ($f['height'] ?? 'N/A');
                                    $formatType = $f['format'] ?? ($f['ext'] ?? 'mp4');
                                    
                                    // Determine display label
                                    $label = '';
                                    if ($muxed) {
                                        $label = "{$quality}p";
                                    } elseif ($hasVideo) {
                                        $label = "{$quality}p (Video only)";
                                    } elseif ($hasAudio) {
                                        $label = "Audio";
                                    } else {
                                        continue; // Skip unknown formats
                                    }
                                    
                                    // Build download link with format parameter
                                    $downloadLink = "download.php?url=" . urlencode($videoUrl) . "&itag=" . urlencode($itag);
                                ?>
                                    <div class="download-item">
                                        <div class="item-info">
                                            <span class="badge bg-danger me-2"><?php echo strtoupper(htmlspecialchars($formatType)); ?></span>
                                            <span class="fw-bold fs-6"><?php echo htmlspecialchars($label); ?></span>
                                            <?php if ($muxed): ?>
                                                <small class="text-success ms-2">✓ Video+Audio</small>
                                            <?php elseif ($hasVideo): ?>
                                                <small class="text-warning ms-2">Video only</small>
                                            <?php elseif ($hasAudio): ?>
                                                <small class="text-info ms-2">Audio</small>
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
                            }
                            ?>
                        </div>
                        
                        <?php if (empty($formats)): // Changed from $shownFormats to $formats ?>
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

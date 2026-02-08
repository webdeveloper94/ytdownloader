<?php
// VPS serverda root directory: /var/www/html/ yoki /var/www/html/ytdownloader/
// Bu fayl: yt_info.php

header('Content-Type: application/json');
set_time_limit(300); // 5 daqiqa PHP timeout

$url = $_GET['url'] ?? '';

if (!$url) {
    echo json_encode(['error' => 'URL kerak']);
    exit;
}

// yt-dlp dan JSON formatida video ma'lumotlarini olish
// VPS da Node.js runtime va ffmpeg o'rnatilgan bo'lishi kerak
$cmd = "yt-dlp -J --extractor-args youtube:player_client=web " . escapeshellarg($url) . " 2>&1";
$output = shell_exec($cmd);

if ($output) {
    // JSON ni decode qilish
    $json = json_decode($output, true);
    if ($json && !isset($json['error'])) {
        // Muvaffaqiyatli JSON qaytarish
        echo json_encode($json);
    } else {
        // Xato yoki yaroqsiz JSON
        echo json_encode(['error' => 'Video ma\'lumotlari olinmadi', 'raw' => $output]);
    }
} else {
    echo json_encode(['error' => 'yt-dlp ishlamadi']);
}
?>

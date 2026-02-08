<?php
// VPS serverda root directory: /var/www/html/ yoki /var/www/html/ytdownloader/
// Bu fayl: yt_api.php

set_time_limit(600); // 10 daqiqa PHP timeout (video yuklab olish uchun)

$url = $_GET['url'] ?? '';
$info = isset($_GET['info']) && $_GET['info'] == '1';

if (!$url) {
    die(json_encode(['error' => 'URL kerak']));
}

if ($info) {
    // Video ma'lumotlarini JSON formatida qaytarish
    header('Content-Type: application/json');
    
    $cmd = "yt-dlp -J --extractor-args youtube:player_client=web " . escapeshellarg($url) . " 2>&1";
    $output = shell_exec($cmd);
    
    if ($output) {
        $json = json_decode($output, true);
        if ($json && !isset($json['error'])) {
            echo json_encode($json);
        } else {
            echo json_encode(['error' => 'Video ma\'lumotlari olinmadi', 'raw' => $output]);
        }
    } else {
        echo json_encode(['error' => 'yt-dlp ishlamadi']);
    }
    exit;
}

// Video yuklab olish (eng yaxshi sifat)
$tmp = sys_get_temp_dir() . '/yt_' . uniqid() . '.mp4';

$cmd = "yt-dlp -f \"bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best\" " .
       "--merge-output-format mp4 " .
       "--extractor-args youtube:player_client=web " .
       "-o " . escapeshellarg($tmp) . " " .
       escapeshellarg($url) . " 2>&1";

exec($cmd, $cmdOutput, $returnCode);

if (file_exists($tmp) && filesize($tmp) > 0) {
    header('Content-Type: video/mp4');
    header('Content-Disposition: attachment; filename="video.mp4"');
    header('Content-Length: ' . filesize($tmp));
    
    readfile($tmp);
    unlink($tmp);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Video yuklab olinmadi',
        'details' => implode("\n", $cmdOutput),
        'return_code' => $returnCode
    ]);
}
?>

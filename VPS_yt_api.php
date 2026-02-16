<?php
// VPS_yt_api.php - Production version (identical to yt_api.php)

require_once 'includes/rapidapi.php';

set_time_limit(300); // 5 minutes for video download

$url = $_GET['url'] ?? '';
$info = isset($_GET['info']) && $_GET['info'] == '1';

if (!$url) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'URL parameter required']);
    exit;
}

if ($info) {
    // Return video information in JSON format
    header('Content-Type: application/json');
    $videoInfo = getVideoInfo($url);
    echo json_encode($videoInfo);
    exit;
}

// Get video info first
$videoInfo = getVideoInfo($url);

if (isset($videoInfo['error'])) {
    header('Content-Type: application/json');
    echo json_encode($videoInfo);
    exit;
}

// Get download URL
$downloadUrl = getDownloadUrl($videoInfo);

if (!$downloadUrl) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No download URL found']);
    exit;
}

// Stream the video from the download URL
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
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Download failed',
        'details' => curl_error($ch)
    ]);
}

curl_close($ch);
?>

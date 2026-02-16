<?php
// yt_info.php - Get video information using RapidAPI (NO yt-dlp)

require_once 'includes/rapidapi.php';

header('Content-Type: application/json');
set_time_limit(60);

$url = $_GET['url'] ?? '';

if (!$url) {
    echo json_encode(['error' => 'URL parameter required']);
    exit;
}

// Validate YouTube URL
if (strpos($url, 'youtube.com') === false && strpos($url, 'youtu.be') === false) {
    echo json_encode(['error' => 'Invalid YouTube URL']);
    exit;
}

// Get video info from RapidAPI
$videoInfo = getVideoInfo($url);

// Return the response
echo json_encode($videoInfo);
?>

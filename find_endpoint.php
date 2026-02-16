<?php
// find_endpoint.php - Find correct path for YT Video & Audio Downloader API

$videoId = "dQw4w9WgXcQ";
$videoUrl = "https://www.youtube.com/watch?v=$videoId";
$apiKey = "7f149b0197mshd5473eae0770553p1c70c4jsnd254e7210c7d";
$host = "yt-video-audio-downloader.p.rapidapi.com";

echo "=== Finding Correct Endpoint Path ===\n\n";
echo "Host: $host\n";
echo "Testing different path formats...\n\n";

$paths = [
    // Common patterns
    "/video/$videoId",
    "/video?id=$videoId",
    "/video?v=$videoId",
    "/video?url=" . urlencode($videoUrl),
    "/dl/$videoId",
    "/dl?id=$videoId",
    "/dl?url=" . urlencode($videoUrl),
    "/download?id=$videoId",
    "/download?url=" . urlencode($videoUrl),
    "/info?id=$videoId",
    "/info?url=" . urlencode($videoUrl),
    // Root paths
    "/?id=$videoId",
    "/?url=" . urlencode($videoUrl),
    "/?v=$videoId",
];

foreach ($paths as $path) {
    $url = "https://$host$path";
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HTTPHEADER => [
            "X-RapidAPI-Host: $host",
            "X-RapidAPI-Key: $apiKey"
        ],
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "✅ FOUND IT! HTTP $httpCode\n";
        echo "Path: $path\n";
        echo "Response: " . substr($response, 0, 200) . "...\n";
        break;
    } elseif ($httpCode == 429) {
        echo "⏳ HTTP 429 (Rate limited) - $path [CORRECT ENDPOINT!]\n";
        echo "   Wait a bit and this path should work.\n";
        break;
    } elseif ($httpCode != 404) {
        echo "⚠️  HTTP $httpCode - $path\n";
    } else {
        echo "❌ HTTP $httpCode - $path\n";
    }
    
    usleep(100000); // 100ms delay between requests
}

echo "\n=== Done ===\n";
?>

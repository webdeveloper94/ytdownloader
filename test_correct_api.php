<?php
// test_correct_api.php - Testing YT Video & Audio Downloader API

$videoId = "dQw4w9WgXcQ";
$apiKey = "7f149b0197mshd5473eae0770553p1c70c4jsnd254e7210c7d";

echo "=== Testing YT Video & Audio Downloader API ===\n\n";

// Possible endpoints for this API
$endpoints = [
    "yt-video-audio-downloader.p.rapidapi.com",
    "youtube-video-and-audio-downloader.p.rapidapi.com",
    "yt-video-audio-download.p.rapidapi.com",
];

foreach ($endpoints as $host) {
    echo "Testing: $host\n";
    
    // Try different path variations
    $paths = [
        "/video?id=$videoId",
        "/dl?id=$videoId",
        "/download?id=$videoId",
        "/?id=$videoId",
    ];
    
    foreach ($paths as $path) {
        $url = "https://$host$path";
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: $host",
                "X-RapidAPI-Key: $apiKey"
            ],
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            echo "  ✅ SUCCESS! Path: $path (HTTP $httpCode)\n";
            echo "  Response preview: " . substr($response, 0, 150) . "\n";
            echo "\n=== FOUND CORRECT ENDPOINT ===\n";
            echo "Host: $host\n";
            echo "Path: $path\n";
            exit(0);
        } elseif ($httpCode != 404) {
            echo "  HTTP $httpCode - $path\n";
        }
    }
    echo "\n";
}

echo "Note: Could not find working endpoint. Please check RapidAPI dashboard for exact endpoint.\n";
?>

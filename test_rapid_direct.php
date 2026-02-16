<?php
// test_rapid_direct.php - Direct RapidAPI cURL test

$videoId = "dQw4w9WgXcQ";
$apiKey = "7f149b0197mshd5473eae0770553p1c70c4jsnd254e7210c7d";

echo "=== Direct RapidAPI CURL Test ===\n\n";

// Test 1: YT API endpoint
echo "Test 1: yt-api.p.rapidapi.com/dl\n";
$url1 = "https://yt-api.p.rapidapi.com/dl?id=$videoId";

$ch = curl_init($url1);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "X-RapidAPI-Host: yt-api.p.rapidapi.com",
        "X-RapidAPI-Key: $apiKey"
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: HTTP $httpCode\n";
echo "Response: " . substr($response, 0, 200) . "\n\n";

// Test 2: Alternative endpoint - video details
echo "Test 2: yt-api.p.rapidapi.com/video/info\n";
$url2 = "https://yt-api.p.rapidapi.com/video/info?id=$videoId";

$ch = curl_init($url2);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "X-RapidAPI-Host: yt-api.p.rapidapi.com",
        "X-RapidAPI-Key: $apiKey"
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: HTTP $httpCode\n";
echo "Response: " . substr($response, 0, 200) . "\n\n";

// Test 3: YouTube Media Downloader
echo "Test 3: youtube-media-downloader.p.rapidapi.com\n";
$url3 = "https://youtube-media-downloader.p.rapidapi.com/v2/video/details?videoId=$videoId";

$ch = curl_init($url3);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "X-RapidAPI-Host: youtube-media-downloader.p.rapidapi.com",
        "X-RapidAPI-Key: $apiKey"
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: HTTP $httpCode\n";
echo "Response: " . substr($response, 0, 200) . "\n\n";

echo "=== Test Complete ===\n";
?>

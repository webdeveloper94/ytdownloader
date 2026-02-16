<?php
// test_api_direct.php - Direct RapidAPI Test with detailed debugging

// Environment variables
require_once 'includes/rapidapi.php';
loadEnv();

$apiKey = getenv('RAPIDAPI_KEY');
$apiHost = getenv('RAPIDAPI_HOST');

echo "=== Direct RapidAPI Test ===\n\n";
echo "API Key: " . substr($apiKey, 0, 20) . "...\n";
echo "API Host: $apiHost\n\n";

// Test video
$videoId = "dQw4w9WgXcQ";
$url = "https://{$apiHost}/dl?id={$videoId}";

echo "Testing URL: $url\n\n";

// Make request
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        "X-RapidAPI-Host: {$apiHost}",
        "X-RapidAPI-Key: {$apiKey}"
    ],
    CURLOPT_VERBOSE => true,
    CURLOPT_HEADER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

echo "HTTP Code: $httpCode\n";
echo "CURL Error: " . ($error ?: 'None') . "\n\n";

if ($httpCode == 403) {
    echo "❌ 403 ERROR - Possible issues:\n";
    echo "1. API Host might be wrong\n";
    echo "2. API Key format incorrect\n";
    echo "3. Endpoint changed\n\n";
    echo "Full Response Headers:\n";
    echo substr($response, 0, 500) . "\n";
} else if ($httpCode == 200) {
    echo "✅ SUCCESS! API is working\n";
    // Extract body
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr($response, $headerSize);
    $data = json_decode($body, true);
    if ($data) {
        echo "Title: " . ($data['title'] ?? 'N/A') . "\n";
    }
}

curl_close($ch);

echo "\n=== Please check your RapidAPI dashboard for correct HOST ===\n";
echo "Go to: https://rapidapi.com/ytjar/api/yt-api/\n";
echo "Look for 'Code Snippets' section and check the correct host\n";
?>

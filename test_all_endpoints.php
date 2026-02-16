<?php
// test_all_endpoints.php - Test different possible endpoints

$videoId = "dQw4w9WgXcQ";
$videoUrl = "https://www.youtube.com/watch?v={$videoId}";
$apiKey = "7f149b0197mshd5473eae0770553p1c70c4jsnd254e7210c7d";
$apiHost = "yt-api.p.rapidapi.com";

echo "=== Testing Multiple Endpoints ===\n\n";

// List of possible endpoints to try
$endpoints = [
    "1. /dl?id={$videoId}",
    "2. /download?url=" . urlencode($videoUrl),
    "3. /video/info?url=" . urlencode($videoUrl),
    "4. /info?id={$videoId}",
    "5. /videoInfo?id={$videoId}",
    "6. /hype" // This one worked in your example
];

foreach ($endpoints as $endpoint) {
    list($num, $path) = explode(". ", $endpoint, 2);
    
    $url = "https://{$apiHost}/{$path}";
    echo "\n{$num}. Testing: /{$path}\n";
    echo "   Full URL: {$url}\n";
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: {$apiHost}",
            "x-rapidapi-key: {$apiKey}"
        ],
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    echo "   HTTP Code: {$httpCode} - ";
    
    if ($httpCode == 200) {
        echo "✅ SUCCESS!\n";
        $data = json_decode($response, true);
        if ($data && isset($data['title'])) {
            echo "   Title: {$data['title']}\n";
            echo "   ✅ THIS ENDPOINT WORKS FOR VIDEOS!\n";
            break; // Found working endpoint
        } elseif ($data) {
            echo "   Response keys: " . implode(", ", array_keys($data)) . "\n";
        }
    } else if ($httpCode == 403) {
        echo "❌ Not subscribed to this endpoint\n";
    } else if ($httpCode == 404) {
        echo "❌ Endpoint not found\n";
    } else {
        echo "⚠️ Error\n";
        echo "   Response: " . substr($response, 0, 100) . "\n";
    }
}

echo "\n\n=== RECOMMENDATION ===\n";
echo "Please check your RapidAPI subscription page:\n";
echo "https://rapidapi.com/hub/11547150/billing/subscriptions-and-usage\n";
echo "Look at 'API Name' column to see which exact API you subscribed to.\n";
echo "It might be 'YouTube Video Downloader' or similar, not 'YT API'.\n";
?>

<?php
// test_correct_endpoint.php - Test /dl endpoint with correct credentials

$videoId = "dQw4w9WgXcQ"; // Test video
$apiKey = "7f149b0197mshd5473eae0770553p1c70c4jsnd254e7210c7d";
$apiHost = "yt-api.p.rapidapi.com";

// Correct endpoint for video download
$url = "https://{$apiHost}/dl?id={$videoId}";

echo "=== Testing Correct Endpoint ===\n\n";
echo "URL: $url\n";
echo "API Key: " . substr($apiKey, 0, 20) . "...\n\n";

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
        "x-rapidapi-host: {$apiHost}",
        "x-rapidapi-key: {$apiKey}"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

curl_close($curl);

echo "HTTP Code: $httpCode\n";

if ($err) {
    echo "❌ cURL Error: " . $err . "\n";
} else {
    if ($httpCode == 200) {
        echo "✅ SUCCESS!\n\n";
        $data = json_decode($response, true);
        if ($data) {
            echo "Title: " . ($data['title'] ?? 'N/A') . "\n";
            echo "Formats: " . (isset($data['formats']) ? count($data['formats']) : 0) . "\n";
            echo "Thumbnail: " . ($data['thumbnail'] ?? 'N/A') . "\n";
        } else {
            echo "Response: " . substr($response, 0, 500) . "\n";
        }
    } else {
        echo "❌ HTTP Error $httpCode\n";
        echo "Response: " . substr($response, 0, 500) . "\n";
    }
}

echo "\n=== Test Complete ===\n";
?>

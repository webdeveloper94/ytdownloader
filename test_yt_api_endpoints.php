<?php
// test_yt_api_endpoints.php - Test yt-api.p.rapidapi.com endpoints

$videoId = "dQw4w9WgXcQ";
$apiKey = "7f149b0197mshd5473eae0770553p1c70c4jsnd254e7210c7d";
$host = "yt-api.p.rapidapi.com";

echo "=== Testing yt-api.p.rapidapi.com Endpoints ===\n\n";

$endpoints = [
    // Video info endpoints
    "/dl?id=$videoId",
    "/video/info?id=$videoId",
    "/video?id=$videoId", 
    "/info?id=$videoId",
    "/download?id=$videoId",
    // Hype endpoint (from user's snippet)
    "/hype",
];

foreach ($endpoints as $path) {
    $url = "https://$host$path";
    
    echo "Testing: $path\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: $host",
            "x-rapidapi-key: $apiKey"
        ],
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        echo "  ❌ cURL Error: $err\n\n";
    } elseif ($httpCode == 200) {
        echo "  ✅ SUCCESS! HTTP $httpCode\n";
        $data = json_decode($response, true);
        if ($data) {
            echo "  Response keys: " . implode(', ', array_keys($data)) . "\n";
            if (isset($data['title'])) {
                echo "  Title: " . $data['title'] . "\n";
            }
        } else {
            echo "  Response: " . substr($response, 0, 150) . "\n";
        }
        echo "\n  ✨ THIS IS THE CORRECT ENDPOINT! ✨\n\n";
        break;
    } else {
        echo "  HTTP $httpCode\n";
        if ($response) {
            echo "  " . substr($response, 0, 100) . "\n";
        }
        echo "\n";
    }
}

echo "=== Test Complete ===\n";
?>

<?php
// test_endpoint_variations.php - Try different parameter formats

$videoUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
$videoId = "dQw4w9WgXcQ";
$apiKey = "7f149b0197mshd5473eae0770553p1c70c4jsnd254e7210c7d";
$apiHost = "yt-video-audio-downloader-api.p.rapidapi.com";

echo "=== Testing Different Endpoint Variations ===\n\n";

$variations = [
    "/downloadVideo?url=" . urlencode($videoUrl),
    "/downloadVideo?id={$videoId}",
    "/downloadVideo",  // Maybe POST with body?
    "/download?url=" . urlencode($videoUrl),
    "/video/download?url=" . urlencode($videoUrl),
    "/api/download?url=" . urlencode($videoUrl),
];

foreach ($variations as $index => $endpoint) {
    $num = $index + 1;
    $url = "https://{$apiHost}{$endpoint}";
    
    echo "{$num}. Testing: {$endpoint}\n";
    
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
    
    echo "   HTTP Code: {$httpCode} ";
    
    if ($httpCode == 200) {
        echo "✅ SUCCESS!\n";
        $data = json_decode($response, true);
        if ($data && isset($data['title'])) {
            echo "   Title: {$data['title']}\n";
            echo "   ✅ THIS IS THE CORRECT ENDPOINT!\n";
            break;
        }
    } else if ($httpCode == 404) {
        echo "❌ Not found\n";
    } else if ($httpCode == 403) {
        echo "❌ Forbidden\n";
    } else {
        echo "⚠️ Other error\n";
    }
    
    echo "\n";
}

echo "\n=== IMPORTANT ===\n";
echo "Iltimos, RapidAPI dashboard dan PHP code snippet ni\n";
echo "TO'LIQ nusxalab yuboring. Kod quyidagicha boshlanadi:\n\n";
echo "<?php\n";
echo "\$curl = curl_init();\n";
echo "curl_setopt_array(\$curl, [\n";
echo "    CURLOPT_URL => \"https://...\",\n";
echo "    ...\n\n";
echo "Bu kodni yuboring - men to'g'ri endpoint va parametrlarni topaman.\n";
?>

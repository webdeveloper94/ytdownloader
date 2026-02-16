<?php
// test_cgeo_endpoint.php - Test with cgeo parameter

$videoId = "dQw4w9WgXcQ";
$apiKey = "7f149b0197mshd5473eae0770553p1c70c4jsnd254e7210c7d";
$apiHost = "yt-api.p.rapidapi.com";

// Endpoint with cgeo parameter (country geo)
$url = "https://{$apiHost}/dl?id={$videoId}&cgeo=DE";

echo "=== Testing /dl with cgeo Parameter ===\n\n";
echo "URL: {$url}\n\n";

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

echo "HTTP Code: {$httpCode}\n\n";

if ($err) {
    echo "❌ cURL Error: " . $err . "\n";
} else {
    if ($httpCode == 200) {
        echo "✅ SUCCESS! API is working!\n\n";
        $data = json_decode($response, true);
        if ($data) {
            echo "Title: " . ($data['title'] ?? 'N/A') . "\n";
            echo "Thumbnail: " . ($data['thumbnail'] ?? 'N/A') . "\n";
            echo "Formats count: " . (isset($data['formats']) ? count($data['formats']) : 0) . "\n\n";
            
            if (isset($data['formats']) && count($data['formats']) > 0) {
                echo "Sample format:\n";
                $format = $data['formats'][0];
                echo "  - ID: " . ($format['format_id'] ?? 'N/A') . "\n";
                echo "  - Ext: " . ($format['ext'] ?? 'N/A') . "\n";
                echo "  - Quality: " . ($format['height'] ?? 'N/A') . "p\n";
            }
        }
    } else {
        echo "❌ Error HTTP {$httpCode}\n";
        echo "Response: {$response}\n";
    }
}

echo "\n=== Test Complete ===\n";
?>

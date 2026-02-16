<?php
// debug_full_response.php - Check complete raw API response

require_once 'includes/rapidapi.php';
loadEnv();

$videoUrl = "https://www.youtube.com/watch?v=Xww1EeTdt7I"; // User's test video
$apiKey = getenv('RAPIDAPI_KEY');
$apiHost = getenv('RAPIDAPI_HOST');

echo "Testing: {$videoUrl}\n\n";

// Call API directly to see raw response
$url = "https://{$apiHost}/video_info";
$postData = json_encode(['url' => $videoUrl]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "x-rapidapi-host: {$apiHost}",
        "x-rapidapi-key: {$apiKey}"
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n\n";

if ($httpCode == 200) {
    $data = json_decode($response, true);
    
    echo "=== VIDEO INFO ===\n";
    if (isset($data['videoDetails'])) {
        $vd = $data['videoDetails'];
        echo "Title: " . ($vd['title'] ?? 'N/A') . "\n";
        echo "Duration: " . ($vd['duration'] ?? 'N/A') . "\n\n";
    }
    
    echo "=== FORMATS ===\n";
    if (isset($data['videoDetails']['formats'])) {
        $formats = $data['videoDetails']['formats'];
        echo "Total formats in API response: " . count($formats) . "\n\n";
        
        foreach ($formats as $index => $f) {
            echo "Format " . ($index + 1) . ":\n";
            foreach ($f as $key => $val) {
                if (is_bool($val)) {
                    $val = $val ? 'true' : 'false';
                } elseif (is_array($val)) {
                    $val = '[array]';
                } elseif (is_string($val) && strlen($val) > 60) {
                    $val = substr($val, 0, 60) . '...';
                }
                echo "  {$key}: {$val}\n";
            }
            echo "\n";
        }
    } else {
        echo "No formats found in response!\n";
        echo "Response structure: " . implode(", ", array_keys($data)) . "\n";
    }
} else {
    echo "ERROR: HTTP {$httpCode}\n";
    echo "Response: {$response}\n";
}
?>

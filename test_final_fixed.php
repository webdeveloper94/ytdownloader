<?php
// test_final_fixed.php - Test with correct API and endpoint

require_once 'includes/rapidapi.php';

echo "=== Testing Updated Configuration ===\n\n";

loadEnv();

$apiHost = getenv('RAPIDAPI_HOST');
$apiKey = getenv('RAPIDAPI_KEY');

echo "API Host: {$apiHost}\n";
echo "API Key: " . substr($apiKey, 0, 20) . "...\n\n";

// Test video
$testUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
echo "Testing video: {$testUrl}\n\n";

echo "Calling getVideoInfo()...\n";
$result = getVideoInfo($testUrl);

if (isset($result['error'])) {
    echo "❌ ERROR: " . $result['error'] . "\n";
} else {
    echo "✅ SUCCESS!\n\n";
    echo "Title: " . ($result['title'] ?? 'N/A') . "\n";
    echo "Thumbnail: " . (isset($result['thumbnail']) ? 'Yes' : 'No') . "\n";
    echo "Formats: " . (isset($result['formats']) ? count($result['formats']) : 0) . "\n";
    
    if (isset($result['formats']) && count($result['formats']) > 0) {
        echo "\nSample formats:\n";
        $count = 0;
        foreach ($result['formats'] as $format) {
            if ($count >= 3) break;
            $quality = $format['height'] ?? 'audio';
            $ext = $format['ext'] ?? 'unknown';
            echo "  - {$quality}p / {$ext}\n";
            $count++;
        }
    }
}

echo "\n=== Test Complete ===\n";
?>

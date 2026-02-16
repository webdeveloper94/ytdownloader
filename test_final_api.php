<?php
// test_final_api.php - Final test with correct endpoint

require_once 'includes/rapidapi.php';

echo "=== Final API Test ===\n\n";

// Load environment
loadEnv();

echo "Configuration:\n";
echo "  API Host: " . getenv('RAPIDAPI_HOST') . "\n";
echo "  API Key: " . substr(getenv('RAPIDAPI_KEY'), 0, 20) . "...\n\n";

// Test with a simple video
$testUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
echo "Testing video: $testUrl\n\n";

$result = getVideoInfo($testUrl);

if (isset($result['error'])) {
    echo "❌ Error: " . $result['error'] . "\n";
    
    if (strpos($result['error'], '429') !== false) {
        echo "\n⚠️ Rate limit exceeded. Wait a few minutes and try again.\n";
        echo "   Free tier has limited requests.\n";
    }
} else {
    echo "✅ SUCCESS!\n\n";
    echo "Title: " . ($result['title'] ?? 'N/A') . "\n";
    echo "Duration: " . ($result['duration'] ?? 'N/A') . "\n";
    echo "Formats available: " . (isset($result['formats']) ? count($result['formats']) : 0) . "\n";
    
    if (isset($result['formats']) && count($result['formats']) > 0) {
        echo "\nSample formats:\n";
        $count = 0;
        foreach ($result['formats'] as $format) {
            if ($count++ >= 3) break;
            $quality = $format['height'] ?? 'audio';
            $ext = $format['ext'] ?? 'unknown';
            echo "  - {$quality}p {$ext}\n";
        }
    }
}

echo "\n=== Test Complete ===\n";
?>

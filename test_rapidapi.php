<?php
// test_rapidapi.php - RapidAPI Connection Test

require_once 'includes/rapidapi.php';

echo "=== RapidAPI Test ===\n\n";

// Load environment
loadEnv();

// Check variables
echo "1. Environment Variables:\n";
echo "   RAPIDAPI_KEY: " . (getenv('RAPIDAPI_KEY') ? getenv('RAPIDAPI_KEY') : 'NOT SET') . "\n";
echo "   RAPIDAPI_HOST: " . (getenv('RAPIDAPI_HOST') ? getenv('RAPIDAPI_HOST') : 'NOT SET') . "\n\n";

// Test video ID extraction
echo "2. Video ID Extraction:\n";
$testUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
$videoId = extractVideoId($testUrl);
echo "   URL: $testUrl\n";
echo "   Video ID: " . ($videoId ? $videoId : 'FAILED') . "\n\n";

// Test API call
echo "3. API Call Test:\n";
echo "   Calling RapidAPI...\n";
$result = getVideoInfo($testUrl);

if (isset($result['error'])) {
    echo "   ❌ ERROR: " . $result['error'] . "\n";
} else {
    echo "   ✅ SUCCESS!\n";
    echo "   Title: " . ($result['title'] ?? 'N/A') . "\n";
    echo "   Formats: " . (isset($result['formats']) ? count($result['formats']) : 0) . "\n";
}

echo "\n=== Test Complete ===\n";
?>

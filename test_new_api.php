<?php
// test_new_api.php - Test updated RapidAPI integration

require_once 'includes/rapidapi.php';

echo "=== Testing New API Integration ===\n\n";

loadEnv();
echo "API Host: " . getenv('RAPIDAPI_HOST') . "\n";
echo "API Key: " . substr(getenv('RAPIDAPI_KEY'), 0, 20) . "...\n\n";

// Test video
$testUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
echo "Testing URL: {$testUrl}\n\n";

echo "Calling getVideoInfo()...\n";
$result = getVideoInfo($testUrl);

if (isset($result['error'])) {
    echo "❌ ERROR: " . $result['error'] . "\n";
} else {
    echo "✅ SUCCESS!\n\n";
    echo "Title: " . ($result['title'] ?? 'N/A') . "\n";
    echo "Duration: " . ($result['duration'] ?? 'N/A') . "\n";
    echo "Uploader: " . ($result['uploader'] ?? 'N/A') . "\n";
    echo "Thumbnail: " . (isset($result['thumbnail']) ? 'Yes' : 'No') . "\n";
    echo "Formats count: " . (isset($result['formats']) ? count($result['formats']) : 0) . "\n";
    
    if (isset($result['formats']) && count($result['formats']) > 0) {
        echo "\nAvailable formats:\n";
        $count = 0;
        foreach ($result['formats'] as $format) {
            if ($count >= 5) break;
            
            $itag = $format['itag'] ?? 'N/A';
            $quality = $format['quality'] ?? 'N/A';
            $formatType = $format['format'] ?? 'N/A';
            $hasAudio = $format['hasAudio'] ?? false;
            $hasVideo = $format['hasVideo'] ?? false;
            
            $type = '';
            if ($hasVideo && $hasAudio) $type = 'Video+Audio';
            elseif ($hasVideo) $type = 'Video only';
            elseif ($hasAudio) $type = 'Audio only';
            
            echo "  [{$itag}] {$quality} - {$formatType} ({$type})\n";
            $count++;
        }
    }
}

echo "\n=== Test Complete ===\n";
?>

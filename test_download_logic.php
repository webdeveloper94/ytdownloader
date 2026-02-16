<?php
// test_download_logic.php - Verify itag selection and URL extraction

require_once 'includes/rapidapi.php';

echo "=== Testing Download Logic ===\n\n";

$testUrl = "https://www.youtube.com/watch?v=Xww1EeTdt7I";
$videoInfo = getVideoInfo($testUrl);

if (isset($videoInfo['error'])) {
    die("Error getting info: " . $videoInfo['error']);
}

$testItags = [18, 135, 299, 140]; // 360p, 480p, 1080p, audio

foreach ($testItags as $itag) {
    echo "Testing itag: $itag\n";
    $url = getDownloadUrl($videoInfo, $itag);
    
    if ($url) {
        $prefix = substr($url, 0, 50);
        echo "  ✅ SUCCESS\n";
        echo "  URL Prefix: $prefix...\n";
        
        // Try to identify format details
        foreach ($videoInfo['formats'] as $f) {
            if ($f['itag'] == $itag) {
                $q = $f['quality'] ?? 'N/A';
                $ext = $f['format'] ?? 'N/A';
                echo "  Format: $q - $ext\n";
                break;
            }
        }
    } else {
        echo "  ❌ FAILED to get URL\n";
    }
    echo "\n";
}

echo "=== Test Complete ===\n";
?>

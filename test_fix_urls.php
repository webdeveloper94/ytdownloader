<?php
// test_fix_urls.php - Test if URLs are correctly extracted now

require_once 'includes/rapidapi.php';
loadEnv();

$videoUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
$res = getVideoInfo($videoUrl);

if (!isset($res['formats'])) {
    die("No formats in response! Response keys: " . implode(", ", array_keys($res)));
}

echo "Title: " . ($res['title'] ?? 'N/A') . "\n";
echo "Total formats: " . count($res['formats']) . "\n\n";

$testItags = [18, 140]; // 360p, audio

foreach ($testItags as $itag) {
    echo "Testing itag: $itag\n";
    $url = getDownloadUrl($res, $itag);
    
    if ($url) {
        echo "  ✅ SUCCESS: URL found (length " . strlen($url) . ")\n";
        echo "  Start: " . substr($url, 0, 80) . "...\n";
    } else {
        echo "  ❌ FAILED: No URL found\n";
    }
    echo "\n";
}
?>

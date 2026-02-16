<?php
require_once 'includes/rapidapi.php';
loadEnv();

$videoUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
$result = getVideoInfo($videoUrl);

echo "=== Checking URL presence for all formats ===\n\n";

if (isset($result['formats'])) {
    foreach ($result['formats'] as $i => $f) {
        $itag = $f['itag'] ?? 'N/A';
        $quality = $f['quality'] ?? 'N/A';
        $url = $f['url'] ?? 'MISSING';
        $direct = $f['directDownload'] ?? 'MISSING';
        
        echo "Format $i (itag $itag, quality $quality):\n";
        echo "  url: " . ($url === 'MISSING' ? 'MISSING' : (empty($url) ? 'EMPTY' : 'PRESENT')) . "\n";
        echo "  directDownload: " . ($direct === 'MISSING' ? 'MISSING' : (empty($direct) ? 'EMPTY' : 'PRESENT')) . "\n";
        if (!empty($direct) && $direct !== 'MISSING') {
            echo "  Sample directDownload: " . substr($direct, 0, 50) . "...\n";
        }
        echo "\n";
    }
} else {
    echo "No formats found!\n";
}
?>

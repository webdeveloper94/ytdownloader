<?php
require_once 'includes/rapidapi.php';
loadEnv();
$videoUrl = "https://www.youtube.com/watch?v=Xww1EeTdt7I";
$result = getVideoInfo($videoUrl);

echo "API Response Keys: " . implode(", ", array_keys($result)) . "\n";

$formats = $result['formats'] ?? [];
echo "Total formats: " . count($formats) . "\n\n";

foreach ($formats as $i => $f) {
    echo "Format $i:\n";
    $itag = $f['itag'] ?? 'N/A';
    $quality = $f['quality'] ?? 'N/A';
    $hasVideo = isset($f['hasVideo']) ? ($f['hasVideo'] ? 'true' : 'false') : 'MISSING';
    $hasAudio = isset($f['hasAudio']) ? ($f['hasAudio'] ? 'true' : 'false') : 'MISSING';
    $muxed = isset($f['muxed']) ? ($f['muxed'] ? 'true' : 'false') : 'MISSING';
    
    echo "  itag: $itag\n";
    echo "  quality: $quality\n";
    echo "  hasVideo: $hasVideo\n";
    echo "  hasAudio: $hasAudio\n";
    echo "  muxed: $muxed\n";
    
    // Check if it would pass the current filter in download.php
    $pass = ($muxed === 'true' || ($hasVideo === 'true' && $hasAudio === 'true') || $hasVideo === 'true' || $hasAudio === 'true');
    echo "  Would show in UI: " . ($pass ? "YES" : "NO") . "\n";
    
    if ($i > 10) break;
}
?>

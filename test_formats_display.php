<?php
// test_formats_display.php - Test format display logic

require_once 'includes/rapidapi.php';

echo "=== Testing Format Display ===\n\n";

$testUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
echo "Video: {$testUrl}\n\n";

$videoInfo = getVideoInfo($testUrl);

if (isset($videoInfo['error'])) {
    echo "ERROR: " . $videoInfo['error'] . "\n";
    exit;
}

echo "Title: " . $videoInfo['title'] . "\n";
echo "Total formats: " . count($videoInfo['formats'] ?? []) . "\n\n";

$formats = $videoInfo['formats'] ?? [];

if (empty($formats)) {
    echo "No formats found!\n";
} else {
    echo "All available formats:\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($formats as $index => $f) {
        $itag = $f['itag'] ?? 'N/A';
        $quality = $f['quality'] ?? 'N/A';
        $formatType = $f['format'] ?? 'N/A';
        $hasVideo = $f['hasVideo'] ?? false;
        $hasAudio = $f['hasAudio'] ?? false;
        $muxed = $f['muxed'] ?? false;
        
        $type = '';
        if ($muxed || ($hasVideo && $hasAudio)) {
            $type = 'Video+Audio';
        } elseif ($hasVideo) {
            $type = 'Video only';
        } elseif ($hasAudio) {
            $type = 'Audio only';
        }
        
        printf(
            "%2d. [%s] %6s %-10s %-15s\n",
            $index + 1,
            $itag,
            $quality,
            $formatType,
            "($type)"
        );
    }
    
    echo str_repeat("-", 80) . "\n";
    echo "\nTotal: " . count($formats) . " formats will be displayed\n";
}
?>

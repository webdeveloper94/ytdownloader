<?php
// test_formats_final.php - Final verification of format detection logic

require_once 'includes/rapidapi.php';

echo "=== Final Verification of Format Detection ===\n\n";

$testUrl = "https://www.youtube.com/watch?v=Xww1EeTdt7I"; // The problematic video
echo "Video: {$testUrl}\n\n";

$videoInfo = getVideoInfo($testUrl);

if (isset($videoInfo['error'])) {
    echo "ERROR: " . $videoInfo['error'] . "\n";
    exit;
}

$formats = $videoInfo['formats'] ?? [];
echo "Total raw formats from API: " . count($formats) . "\n\n";

foreach ($formats as $index => $f) {
    // Mirroring the logic in download.php
    $hasVideo = false;
    $hasAudio = false;
    $muxed = isset($f['muxed']) && $f['muxed'];
    
    if (isset($f['hasVideo'])) $hasVideo = (bool)$f['hasVideo'];
    if (isset($f['hasAudio'])) $hasAudio = (bool)$f['hasAudio'];
    
    if (!$hasVideo && isset($f['vcodec']) && $f['vcodec'] !== 'none') $hasVideo = true;
    if (!$hasAudio && isset($f['acodec']) && $f['acodec'] !== 'none') $hasAudio = true;
    
    if ($hasVideo && $hasAudio) $muxed = true;
    
    $itag = $f['itag'] ?? 'N/A';
    $quality = $f['quality'] ?? ($f['height'] ?? 'N/A');
    $formatType = $f['format'] ?? ($f['ext'] ?? '??');
    
    $label = 'SKIP';
    if ($muxed) $label = "{$quality}p";
    elseif ($hasVideo) $label = "{$quality}p (Video only)";
    elseif ($hasAudio) $label = "Audio";
    
    printf(
        "%d. [%s] %-15s %-10s %s\n",
        $index + 1,
        $itag,
        $label,
        $formatType,
        $muxed ? "(Muxed)" : ($hasVideo ? "(VideoOnly)" : ($hasAudio ? "(AudioOnly)" : "(Unknown)"))
    );
}
?>

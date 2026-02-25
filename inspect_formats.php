<?php
require_once 'includes/rapidapi.php';
loadEnv();

$videoUrl = "https://www.youtube.com/watch?v=Xww1EeTdt7I";
$info = getVideoInfo($videoUrl);

echo "Title: " . ($info['title'] ?? 'N/A') . "\n";
echo "Formats:\n";
foreach ($info['formats'] as $f) {
    $itag = $f['itag'] ?? 'N/A';
    $ext = $f['ext'] ?? $f['format'] ?? 'N/A';
    $quality = $f['quality'] ?? $f['qualityLabel'] ?? 'N/A';
    $vcodec = $f['vcodec'] ?? 'none';
    $acodec = $f['acodec'] ?? 'none';
    echo "Itag: $itag | Ext: $ext | Quality: $quality | V: $vcodec | A: $acodec\n";
}
?>

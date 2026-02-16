<?php
require_once 'includes/rapidapi.php';
loadEnv();

$videoUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
$res = getVideoInfo($videoUrl);

if (isset($res['formats'])) {
    foreach ($res['formats'] as $f) {
        if (($f['itag'] ?? '') == 18) {
            echo "Format 18 details:\n";
            echo "URL value: " . ($f['url'] ?? 'NULL') . "\n";
            echo "DirectDownload value: " . ($f['directDownload'] ?? 'NULL') . "\n";
            break;
        }
    }
}
?>

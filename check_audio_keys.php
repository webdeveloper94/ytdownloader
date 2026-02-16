<?php
require_once 'includes/rapidapi.php';
loadEnv();

$videoUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
$res = getVideoInfo($videoUrl);

if (isset($res['formats'])) {
    foreach ($res['formats'] as $f) {
        if (($f['itag'] ?? '') == 140) {
            echo "Format 140 (Audio) details:\n";
            echo "vcodec: " . ($f['vcodec'] ?? 'MISSING') . "\n";
            echo "acodec: " . ($f['acodec'] ?? 'MISSING') . "\n";
            echo "hasVideo: " . (isset($f['hasVideo']) ? ($f['hasVideo'] ? 'true' : 'false') : 'MISSING') . "\n";
            echo "hasAudio: " . (isset($f['hasAudio']) ? ($f['hasAudio'] ? 'true' : 'false') : 'MISSING') . "\n";
            echo "muxed: " . (isset($f['muxed']) ? ($f['muxed'] ? 'true' : 'false') : 'MISSING') . "\n";
            break;
        }
    }
}
?>

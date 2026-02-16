<?php
require_once 'includes/rapidapi.php';
loadEnv();

$videoUrl = "https://www.youtube.com/watch?v=Xww1EeTdt7I"; // Primary test
$videoUrl2 = "https://www.youtube.com/watch?v=hEHHltE_NxY"; // From screenshot

function inspect($url) {
    echo "--- Testing URL: $url ---\n";
    $res = getVideoInfo($url);
    if (!isset($res['formats']) || empty($res['formats'])) {
        echo "No formats found!\n\n";
        return;
    }
    
    $f = $res['formats'][0];
    echo "Sample format keys: " . implode(", ", array_keys($f)) . "\n";
    foreach ($res['formats'] as $i => $format) {
        $id = $format['itag'] ?? $format['id'] ?? $format['format_id'] ?? 'NONE';
        echo "Format $i: id_value=$id, quality=" . ($format['quality'] ?? 'N/A') . "\n";
    }
    echo "\n";
}

inspect($videoUrl);
inspect($videoUrl2);
?>

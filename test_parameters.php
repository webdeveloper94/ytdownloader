<?php
require_once 'includes/rapidapi.php';
loadEnv();

$playlistUrl = "https://www.youtube.com/watch?v=hEHHltE_NxY&list=RDXww1EeTdt7I&index=3";
$plainUrl = "https://www.youtube.com/watch?v=hEHHltE_NxY";

function test($url) {
    echo "--- Testing: $url ---\n";
    $res = getVideoInfo($url);
    if (isset($res['error'])) {
        echo "Error: " . $res['error'] . "\n\n";
        return;
    }
    echo "Formats count: " . (isset($res['formats']) ? count($res['formats']) : 0) . "\n";
    if (isset($res['formats']) && !empty($res['formats'])) {
        $f = $res['formats'][0];
        echo "Format 0 Keys: " . implode(", ", array_keys($f)) . "\n";
        echo "Format 0 itag: " . ($f['itag'] ?? 'MISSING') . "\n";
        echo "Format 0 URL: " . (isset($f['url']) || isset($f['directDownload']) ? 'PRESENT' : 'MISSING') . "\n";
    }
    echo "\n";
}

test($playlistUrl);
test($plainUrl);
?>

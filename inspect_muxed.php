<?php
require_once 'includes/rapidapi.php';
loadEnv();
$videoUrl = "https://www.youtube.com/watch?v=Xww1EeTdt7I";
$info = getVideoInfo($videoUrl);
foreach ($info['formats'] as $f) {
    if ($f['itag'] == 18 || $f['itag'] == 140) {
        echo "Itag: " . $f['itag'] . "\n";
        echo "URL: " . (isset($f['url']) ? "SET" : "MISSING") . "\n";
        echo "DirectDownload: " . (isset($f['directDownload']) ? "SET" : "MISSING") . "\n";
        if (isset($f['url'])) echo "URL: " . substr($f['url'], 0, 50) . "...\n";
        if (isset($f['directDownload'])) echo "DirectDownload: " . (is_string($f['directDownload']) ? substr($f['directDownload'], 0, 50) : $f['directDownload']) . "...\n";
        echo "-------------------\n";
    }
}
?>

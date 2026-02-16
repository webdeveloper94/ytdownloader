<?php
require_once 'includes/rapidapi.php';
loadEnv();

$videoUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";

function testQuality($q, $f = 'mp4') {
    echo "\n--- Testing Quality: $q, Format: $f ---\n";
    $start = startDownloadAsync($GLOBALS['videoUrl'], $f, $q);
    print_r($start);
    
    if (isset($start['jobId'])) {
        $id = $start['jobId'];
        echo "Job ID: $id. Waiting 30s...\n";
        sleep(30);
        $status = pollDownloadStatus($id);
        print_r($status);
    }
}

testQuality(720);
testQuality(1080);
testQuality(360);
?>

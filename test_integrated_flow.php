<?php
require_once 'includes/rapidapi.php';
loadEnv();

$videoUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
$itag = 18; // 360p mp4

echo "Simulating integrated download flow for $videoUrl (itag $itag)...\n";

$videoInfo = getVideoInfo($videoUrl);
$selectedFormatDetails = null;

if (isset($videoInfo['formats'])) {
    foreach ($videoInfo['formats'] as $f) {
        if ($f['itag'] == $itag) {
            $selectedFormatDetails = $f;
            break;
        }
    }
}

if ($selectedFormatDetails) {
    echo "Found format details. Initiating async job...\n";
    $quality = $selectedFormatDetails['quality'] ?? 720;
    $format = $selectedFormatDetails['format'] ?? $selectedFormatDetails['ext'] ?? 'mp4';
    
    $startRes = startDownloadAsync($videoUrl, $format, $quality);
    
    if (isset($startRes['jobId'])) {
        $jobId = $startRes['jobId'];
        echo "Job ID: $jobId. Polling...\n";
        
        $finalUrl = '';
        for ($i = 0; $i < 40; $i++) {
            $statusRes = pollDownloadStatus($jobId);
            echo "Attempt $i: " . ($statusRes['status'] ?? 'unknown') . " | " . ($statusRes['progress'] ?? '0%') . "\n";
            
            if (isset($statusRes['url']) && !empty($statusRes['url'])) {
                $finalUrl = $statusRes['url'];
                break;
            }
            sleep(2);
        }
        
        if ($finalUrl) {
            echo "\n✅ SUCCESS: Final URL found: " . substr($finalUrl, 0, 100) . "...\n";
            
            // Test headers of final URL
            echo "Testing final URL headers...\n";
            $ch = curl_init($finalUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0'
            ]);
            curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            
            echo "Final HTTP Status: " . $info['http_code'] . "\n";
            echo "Final Content Length: " . $info['download_content_length'] . " bytes\n";
            
            if ($info['http_code'] == 200 && $info['download_content_length'] > 0) {
                echo "\n🎉 FINAL VERIFICATION PASSED!\n";
            } else {
                echo "\n❌ FINAL VERIFICATION FAILED (Bad headers).\n";
            }
        } else {
            echo "\n❌ FAILED: Timeout waiting for URL.\n";
        }
    } else {
        echo "\n❌ FAILED: No jobId returned.\n";
        print_r($startRes);
    }
} else {
    echo "\n❌ FAILED: Format not found.\n";
}
?>

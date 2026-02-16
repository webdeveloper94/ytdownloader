<?php
require_once 'includes/rapidapi.php';
loadEnv();

$videoUrl = "https://www.youtube.com/watch?v=Xww1EeTdt7I";
$info = getVideoInfo($videoUrl);

foreach ($info['formats'] as $f) {
    if ((isset($f['acodec']) && $f['acodec'] !== 'none' && (empty($f['vcodec']) || $f['vcodec'] === 'none')) || 
        (isset($f['hasAudio']) && $f['hasAudio'] && isset($f['hasVideo']) && !$f['hasVideo'])) {
        
        $itag = $f['itag'] ?? '';
        echo "Testing Audio Itag: $itag\n";
        
        $url = getDownloadUrl($info, $itag);
        if ($url) {
            echo "URL found: " . substr($url, 0, 50) . "...\n";
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0'
            ]);
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
            curl_close($ch);
            
            echo "HTTP Code: $code\n";
            echo "Content-Length: $size bytes\n";
            if ($code == 200 && $size > 0) {
                echo "✅ Audio direct link works!\n";
            } else {
                echo "❌ Audio direct link failed or 0 bytes.\n";
            }
        } else {
            echo "❌ No direct URL for audio itag $itag\n";
        }
        echo "-------------------\n";
    }
}
?>

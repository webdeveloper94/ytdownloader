<?php
// test_streaming_headers.php - Verify we can get remote content length

require_once 'includes/rapidapi.php';
loadEnv();

$videoUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
$res = getVideoInfo($videoUrl);
$url = getDownloadUrl($res, 18);

echo "Testing URL: " . substr($url, 0, 100) . "...\n\n";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_NOBODY => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
]);

$head = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "HTTP Code: " . $info['http_code'] . "\n";
echo "Content Length: " . $info['download_content_length'] . " bytes\n";
echo "Content Type: " . $info['content_type'] . "\n";

if ($info['http_code'] == 200 && $info['download_content_length'] > 0) {
    echo "\n✅ SUCCESS: Remote server is responding with valid data.\n";
} else {
    echo "\n❌ FAILURE: Remote server is not providing data.\n";
}
?>

<?php
// debug_curl_verbose.php - Verbose debug of curl request to googlevideo.com

require_once 'includes/rapidapi.php';
loadEnv();

$videoUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
$res = getVideoInfo($videoUrl);
$url = getDownloadUrl($res, 18);

echo "Testing URL: " . substr($url, 0, 100) . "...\n\n";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_VERBOSE => true,
    CURLOPT_STDERR => fopen('curl_debug.log', 'w+'),
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    CURLOPT_TIMEOUT => 15
]);

$res = curl_exec($ch);
$info = curl_getinfo($ch);
$err = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $info['http_code'] . "\n";
echo "CURL Error: " . $err . "\n";
echo "Total Time: " . $info['total_time'] . "s\n";
echo "\nCheck curl_debug.log for details.\n";
?>

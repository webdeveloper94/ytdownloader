<?php
require_once 'includes/rapidapi.php';
loadEnv();

$apiKey = getenv('RAPIDAPI_KEY');
$apiHost = getenv('RAPIDAPI_HOST');

function startDownload($url, $format = 'mp4', $quality = 720) {
    global $apiKey, $apiHost;
    $ch = curl_init();
    $postData = json_encode(['url' => $url, 'format' => $format, 'quality' => $quality]);
    
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://{$apiHost}/download",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-rapidapi-host: $apiHost",
            "x-rapidapi-key: $apiKey"
        ],
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

function checkStatus($jobId) {
    global $apiKey, $apiHost;
    $ch = curl_init();
    // Assuming GET /status?jobId={jobId} or GET /status/{jobId}
    // Let's try /status?jobId=... first as it's common in RapidAPI
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://{$apiHost}/status?jobId={$jobId}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: $apiHost",
            "x-rapidapi-key: $apiKey"
        ],
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

$url = "https://youtube.com/watch?v=dQw4w9WgXcQ";
echo "Starting job for $url...\n";
$startRes = startDownload($url);
print_r($startRes);

if (isset($startRes['jobId'])) {
    $jobId = $startRes['jobId'];
    echo "Job ID: $jobId. Polling status...\n";
    
    for ($i = 0; $i < 10; $i++) {
        sleep(2);
        $statusRes = checkStatus($jobId);
        echo "Attempt $i: ";
        print_r($statusRes);
        
        if (isset($statusRes['url']) && !empty($statusRes['url'])) {
            echo "\n✅ SUCCESS: Final URL: " . $statusRes['url'] . "\n";
            break;
        }
        
        if (isset($statusRes['status']) && $statusRes['status'] === 'failed') {
            echo "❌ Job failed.\n";
            break;
        }
    }
} else {
    echo "Failed to get jobId.\n";
}
?>

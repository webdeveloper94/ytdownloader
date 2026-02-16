<?php
require_once 'includes/rapidapi.php';
loadEnv();

$apiKey = getenv('RAPIDAPI_KEY');
$apiHost = getenv('RAPIDAPI_HOST');

function startDownload($url) {
    global $apiKey, $apiHost;
    $ch = curl_init();
    $postData = json_encode(['url' => $url, 'format' => 'mp4', 'quality' => 720]);
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
$start = startDownload($url);

if (isset($start['jobId'])) {
    $id = $start['jobId'];
    echo "Job ID: $id\n";
    echo "Waiting 15 seconds...\n";
    sleep(15);
    $status = checkStatus($id);
    echo "Status Response:\n";
    print_r($status);
} else {
    echo "Start Result:\n";
    print_r($start);
}
?>

<?php
require_once 'includes/rapidapi.php';
loadEnv();

$apiKey = getenv('RAPIDAPI_KEY');
$apiHost = getenv('RAPIDAPI_HOST');

$curl = curl_init();

$postData = [
    'url' => 'https://youtube.com/watch?v=dQw4w9WgXcQ',
    'format' => 'mp4',
    'quality' => 720
];

echo "Testing POST /download endpoint...\n";
echo "Host: $apiHost\n";

curl_setopt_array($curl, [
    CURLOPT_URL => "https://{$apiHost}/download",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($postData),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "x-rapidapi-host: $apiHost",
        "x-rapidapi-key: $apiKey"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
$info = curl_getinfo($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err . "\n";
} else {
    echo "HTTP Status: " . $info['http_code'] . "\n";
    echo "Response: " . $response . "\n";
    
    $data = json_decode($response, true);
    if (isset($data['url'])) {
        echo "\n✅ SUCCESS: Found download URL: " . substr($data['url'], 0, 80) . "...\n";
    }
}
?>

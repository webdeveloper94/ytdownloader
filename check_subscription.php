<?php
// check_subscription.php - Check which endpoints work

$apiKey = "7f149b0197mshd5473eae0770553p1c70c4jsnd254e7210c7d";
$host = "yt-api.p.rapidapi.com";

echo "=== Checking yt-api.p.rapidapi.com Subscription ===\n\n";

// Test /hype (working from user's snippet)
echo "1. Testing /hype (from your snippet):\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://$host/hype",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => [
        "x-rapidapi-host: $host",
        "x-rapidapi-key: $apiKey"
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Status: HTTP $httpCode\n";
if ($httpCode == 200) {
    echo "   ✅ Working!\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "   Available keys: " . implode(', ', array_keys($data)) . "\n";
    }
} elseif ($httpCode == 429) {
    echo "   ⏳ Rate limited (too many requests)\n";
} else {
    echo "   Response: " . substr($response, 0, 150) . "\n";
}

echo "\n2. Subscription includes:\n";
echo "   - /hype (trending videos)\n";
echo "   - Need to find video info/download endpoint\n";

echo "\n3. Possible issue:\n";
echo "   '/dl' endpoint might be premium-only\n";
echo "   Need to check RapidAPI docs for free tier endpoints\n";

echo "\n=== Recommendation ===\n";
echo "Please check RapidAPI dashboard:\n";
echo "1. Which endpoints are included in your plan?\n";
echo "2. Is there a /video or /info endpoint?\n";
echo "3. Screenshot the 'Endpoints' tab\n";

?>

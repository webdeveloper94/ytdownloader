<?php
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://yt-video-audio-downloader-api.p.rapidapi.com/getVideoInfo",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        "url" => "https://youtube.com/watch?v=dQw4w9WgXcQ"
    ]),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "X-RapidAPI-Key: SIZNING_KEY",
        "X-RapidAPI-Host: yt-video-audio-downloader-api.p.rapidapi.com"
    ],
]);

$response = curl_exec($curl);
curl_close($curl);

echo $response;

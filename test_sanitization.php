<?php
// test_sanitization.php - Test URL sanitization logic

$testUrls = [
    "https://www.youtube.com/watch?v=hEHHltE_NxY&list=RDXww1EeTdt7I&index=3",
    "https://youtu.be/hEHHltE_NxY?si=abcde",
    "https://www.youtube.com/watch?v=dQw4w9WgXcQ"
];

function sanitize($url) {
    if (preg_match('/[?&]v=([^&]+)/', $url, $matches)) {
        return "https://www.youtube.com/watch?v=" . $matches[1];
    }
    // Handle youtu.be links too
    if (preg_match('/youtu\.be\/([^?&]+)/', $url, $matches)) {
        return "https://www.youtube.com/watch?v=" . $matches[1];
    }
    return $url;
}

foreach ($testUrls as $url) {
    echo "Original: $url\n";
    echo "Sanitized: " . sanitize($url) . "\n\n";
}
?>

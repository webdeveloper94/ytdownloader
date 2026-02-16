<?php
// debug_formats.php - Check actual API response format structure

require_once 'includes/rapidapi.php';

echo "=== Debugging API Response Structure ===\n\n";

$testUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
echo "Testing: {$testUrl}\n\n";

$result = getVideoInfo($testUrl);

if (isset($result['error'])) {
    echo "ERROR: " . $result['error'] . "\n";
    exit;
}

echo "=== RAW RESPONSE ===\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo "\n\n=== FORMATS STRUCTURE ===\n";
if (isset($result['formats'])) {
    echo "Total formats: " . count($result['formats']) . "\n\n";
    
    foreach ($result['formats'] as $index => $format) {
        echo "Format #{$index}:\n";
        echo "  Keys: " . implode(", ", array_keys($format)) . "\n";
        echo "  Sample data:\n";
        foreach ($format as $key => $value) {
            if (is_array($value)) {
                echo "    {$key}: [array]\n";
            } else {
                $val = is_string($value) ? substr($value, 0, 50) : $value;
                echo "    {$key}: {$val}\n";
            }
        }
        echo "\n";
        
        if ($index >= 2) {
            echo "... (showing first 3 formats only)\n";
            break;
        }
    }
} else {
    echo "No formats array found!\n";
}
?>

<?php
// includes/rapidapi.php - RapidAPI Helper Functions

/**
 * Load environment variables from .env file
 */
function loadEnv() {
    static $loaded = false;
    if ($loaded) return;
    
    $envPath = dirname(__DIR__);
    if (file_exists($envPath . '/.env')) {
        $lines = file($envPath . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
    $loaded = true;
}

/**
 * Extract video ID from YouTube URL
 */
function extractVideoId($url) {
    $patterns = [
        '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/',
        '/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/',
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

/**
 * Get video information from RapidAPI
 */
function getVideoInfo($videoUrl) {
    loadEnv();
    
    // Validate YouTube URL
    if (strpos($videoUrl, 'youtube.com') === false && strpos($videoUrl, 'youtu.be') === false) {
        return ['error' => 'Invalid YouTube URL'];
    }
    
    $apiKey = getenv('RAPIDAPI_KEY');
    $apiHost = getenv('RAPIDAPI_HOST');
    
    if (!$apiKey || !$apiHost) {
        return ['error' => 'RapidAPI credentials not configured'];
    }
    
    // Use POST /video_info endpoint
    $url = "https://{$apiHost}/video_info";
    
    // Prepare JSON body
    $postData = json_encode(['url' => $videoUrl]);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-rapidapi-host: {$apiHost}",
            "x-rapidapi-key: {$apiKey}"
        ],
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => 'CURL Error: ' . $error];
    }
    
    if ($httpCode !== 200) {
        return ['error' => "API Error: HTTP {$httpCode} - " . substr($response, 0, 200)];
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Invalid JSON response from API'];
    }
    
    // Check for API error in response
    if (isset($data['success']) && $data['success'] === false) {
        return ['error' => $data['message'] ?? 'Unknown API error'];
    }
    
    // Transform response to match expected format
    if (isset($data['videoDetails'])) {
        $videoDetails = $data['videoDetails'];
        
        return [
            'title' => $videoDetails['title'] ?? 'Unknown',
            'thumbnail' => $videoDetails['thumbnail'] ?? '',
            'duration' => $videoDetails['duration'] ?? '',
            'uploader' => $videoDetails['uploader'] ?? '',
            'formats' => $videoDetails['formats'] ?? []
        ];
    }
    
    return $data;
}

/**
 * Get download URL for specific format
 */
function getDownloadUrl($videoInfo, $formatId = null) {
    if (isset($videoInfo['error'])) {
        return null;
    }
    
    // If format ID specified, find it
    if ($formatId && isset($videoInfo['formats'])) {
        foreach ($videoInfo['formats'] as $format) {
            if (isset($format['format_id']) && $format['format_id'] == $formatId) {
                return $format['url'] ?? null;
            }
        }
    }
    
    // Otherwise return best quality URL
    if (isset($videoInfo['formats']) && !empty($videoInfo['formats'])) {
        // Sort by quality (height)
        $formats = $videoInfo['formats'];
        usort($formats, function($a, $b) {
            $heightA = $a['height'] ?? 0;
            $heightB = $b['height'] ?? 0;
            return $heightB - $heightA;
        });
        
        foreach ($formats as $format) {
            if (isset($format['url'])) {
                return $format['url'];
            }
        }
    }
    
    return null;
}

/**
 * Format file size to human readable
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>

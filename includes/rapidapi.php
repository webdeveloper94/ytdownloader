<?php
// includes/rapidapi.php - RapidAPI Helper Functions

/**
 * Load environment variables from .env file
 */
function loadEnv() {
    static $loaded = false;
    if ($loaded) return;
    
    // Try different paths to find .env file (root of project)
    $possiblePaths = [
        dirname(__DIR__) . '/.env',                 // root/includes/.. -> root/
        dirname(dirname(__FILE__)) . '/.env',       // root/includes/.. -> root/
        $_SERVER['DOCUMENT_ROOT'] . '/.env',        // /var/www/html/
        $_SERVER['DOCUMENT_ROOT'] . '/ytdownloader/.env',
        './.env',
        '../.env'
    ];
    
    $envFile = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $envFile = $path;
            break;
        }
    }
    
    if ($envFile) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Clean value (quotes)
            $value = trim($value, "\"' \t\n\r\0\x0B");
            
            // Populate $_ENV and $_SERVER as fallback
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            
            // Still try putenv if available, though it might fail on server
            if (function_exists('putenv')) {
                @putenv("$name=$value");
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
    
    $apiKey = $_ENV['RAPIDAPI_KEY'] ?? '';
    $apiHost = $_ENV['RAPIDAPI_HOST'] ?? '';
    
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
    // The API might return details at top level or wrapped in videoDetails
    $details = isset($data['videoDetails']) ? $data['videoDetails'] : $data;
    
    return [
        'title' => $details['title'] ?? ($details['videoDetails']['title'] ?? 'Unknown'),
        'thumbnail' => $details['thumbnail'] ?? ($details['videoDetails']['thumbnail'] ?? ''),
        'duration' => $details['duration'] ?? ($details['videoDetails']['duration'] ?? ''),
        'uploader' => $details['uploader'] ?? ($details['channelTitle'] ?? ''),
        'formats' => $details['formats'] ?? ($details['videoDetails']['formats'] ?? [])
    ];
}

/**
 * Get download URL for specific format
 */
function getDownloadUrl($videoInfo, $itag = null) {
    if (isset($videoInfo['error'])) {
        return null;
    }
    
    $formats = $videoInfo['formats'] ?? [];
    if (empty($formats)) {
        return null;
    }
    
    // If itag specified, find exact format
    if ($itag) {
        foreach ($formats as $format) {
            if (isset($format['itag']) && $format['itag'] == $itag) {
                // Try different URL keys returned by RapidAPI
                // Some APIs return true/false in directDownload, others return the URL.
                $direct = $format['directDownload'] ?? '';
                $url = $format['url'] ?? '';
                
                // Only use if it looks like a URL
                if (is_string($direct) && strpos($direct, 'http') === 0) return $direct;
                if (is_string($url) && strpos($url, 'http') === 0) return $url;
                
                return null;
            }
        }
    }
    
    // Otherwise return best quality muxed (video+audio) URL as default
    usort($formats, function($a, $b) {
        $qualityA = (int)($a['quality'] ?? 0);
        $qualityB = (int)($b['quality'] ?? 0);
        return $qualityB - $qualityA;
    });
    
    foreach ($formats as $format) {
        $isMuxed = isset($format['muxed']) && $format['muxed'];
        $direct = $format['directDownload'] ?? '';
        $url = $format['url'] ?? '';
        
        $link = null;
        if (is_string($direct) && strpos($direct, 'http') === 0) $link = $direct;
        elseif (is_string($url) && strpos($url, 'http') === 0) $link = $url;
        
        if ($isMuxed && $link) {
            return $link;
        }
    }
    
    // Fallback: Just return any first available URL
    foreach ($formats as $format) {
        $direct = $format['directDownload'] ?? '';
        $url = $format['url'] ?? '';
        
        if (is_string($direct) && strpos($direct, 'http') === 0) return $direct;
        if (is_string($url) && strpos($url, 'http') === 0) return $url;
    }
    
    return null;
}

/**
 * Initiate an asynchronous download job (POST /download)
 */
function startDownloadAsync($videoUrl, $format = 'mp4', $quality = 720) {
    $apiKey = $_ENV['RAPIDAPI_KEY'] ?? '';
    $apiHost = $_ENV['RAPIDAPI_HOST'] ?? '';
    
    $ch = curl_init();
    $postData = json_encode([
        'url' => $videoUrl,
        'format' => $format,
        'quality' => (int)$quality
    ]);
    
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://{$apiHost}/download",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-rapidapi-host: {$apiHost}",
            "x-rapidapi-key: {$apiKey}"
        ],
    ]);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    file_put_contents('../api_debug.log', "[" . date('Y-m-d H:i:s') . "] START ASYNC: $response | Err: $error\n", FILE_APPEND);
    
    if ($error) {
        return ['error' => "Curl error: " . $error];
    }
    
    return json_decode($response, true);
}

/**
 * Poll for job status (GET /status/{jobId})
 */
function pollDownloadStatus($jobId) {
    $apiKey = $_ENV['RAPIDAPI_KEY'] ?? '';
    $apiHost = $_ENV['RAPIDAPI_HOST'] ?? '';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://{$apiHost}/status/{$jobId}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: {$apiHost}",
            "x-rapidapi-key: {$apiKey}"
        ],
    ]);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => "Curl error: " . $error];
    }
    
    return json_decode($response, true);
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

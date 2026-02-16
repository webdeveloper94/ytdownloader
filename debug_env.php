<?php
// debug_env.php - Run this on your server to check environment loading
require_once 'includes/rapidapi.php';

echo "<h3>Environment Debug</h3>";
echo "Current Directory: " . getcwd() . "<br>";
echo "Project Root (detected): " . dirname(__FILE__) . "<br>";

$envFile = dirname(__FILE__) . '/.env';
echo ".env File Path: " . $envFile . "<br>";
echo ".env File Exists: " . (file_exists($envFile) ? "YES" : "NO") . "<br>";

if (file_exists($envFile)) {
    echo ".env File Readable: " . (is_readable($envFile) ? "YES" : "NO") . "<br>";
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "Total lines in .env: " . count($lines) . "<br>";
}

loadEnv();

echo "<h4>$_ENV Check:</h4>";
echo "RAPIDAPI_KEY: " . (isset($_ENV['RAPIDAPI_KEY']) ? "SET" : "MISSING") . "<br>";
echo "RAPIDAPI_HOST: " . (isset($_ENV['RAPIDAPI_HOST']) ? "SET" : "MISSING") . "<br>";

echo "<h4>getenv() Check:</h4>";
echo "RAPIDAPI_KEY: " . (getenv('RAPIDAPI_KEY') ?: "MISSING") . "<br>";
echo "RAPIDAPI_HOST: " . (getenv('RAPIDAPI_HOST') ?: "MISSING") . "<br>";
?>

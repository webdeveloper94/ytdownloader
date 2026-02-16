<?php
// config/db.php - Database configuration with .env support

// Load environment variables
$possiblePaths = [
    dirname(__DIR__) . '/.env',
    './.env',
    '../.env',
    $_SERVER['DOCUMENT_ROOT'] . '/.env',
    $_SERVER['DOCUMENT_ROOT'] . '/ytdownloader/.env'
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
        $value = trim($value, "\"' \t\n\r\0\x0B");
        
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost:3307';
$db   = $_ENV['DB_NAME'] ?? 'ytdownloader';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? 'root';
$charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>

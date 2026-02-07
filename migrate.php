<?php
require_once 'config/db.php';
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN downloads_left INT DEFAULT 0 AFTER subscription_expires_at");
    echo "Migration successful: Column 'downloads_left' added to 'users' table.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Migration skipped: Column 'downloads_left' already exists.";
    } else {
        echo "Migration failed: " . $e->getMessage();
    }
}
?>

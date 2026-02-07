<?php
// api/bot.php
header('Content-Type: application/json');
require_once '../config/db.php';

// Bu API kelajakda Telegram bot bilan ishlash uchun mo'ljallangan
// Masalan: bot.php?action=check_user&login=user123

$action = $_GET['action'] ?? '';

if ($action === 'check_user') {
    $login = $_GET['login'] ?? '';
    
    $stmt = $pdo->prepare("SELECT name, subscription_expires_at FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ($user) {
        $isSubscribed = ($user['subscription_expires_at'] && strtotime($user['subscription_expires_at']) > time());
        echo json_encode([
            'status' => 'success',
            'user' => [
                'name' => $user['name'],
                'is_subscribed' => $isSubscribed,
                'expires_at' => $user['subscription_expires_at']
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Foydalanuvchi topilmadi']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Yaroqsiz amal']);
}
?>

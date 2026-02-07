<?php
// VPS serverda yt_api.php faylini shunday yangilang:

$url = $_GET['url'] ?? '';
$info = isset($_GET['info']) && $_GET['info'] == '1';

if (!$url) die("URL kerak");

if ($info) {
    // Video ma'lumotlarini JSON formatida qaytarish
    header('Content-Type: application/json');
    
    $cmd = "yt-dlp -J " . escapeshellarg($url) . " 2>&1";
    $output = shell_exec($cmd);
    
    if ($output) {
        $json = json_decode($output, true);
        if ($json) {
            echo json_encode($json);
        } else {
            echo json_encode(['error' => 'Video ma\'lumotlari olinmadi', 'raw' => $output]);
        }
    } else {
        echo json_encode(['error' => 'yt-dlp ishlamadi']);
    }
    exit;
}

// Oddiy video yuklab olish (eski kod)
$tmp = sys_get_temp_dir() . '/yt_' . uniqid() . '.mp4';

$cmd = "yt-dlp -f bestvideo+bestaudio --merge-output-format mp4 -o "
    . escapeshellarg($tmp) . " "
    . escapeshellarg($url);

exec($cmd);

header('Content-Type: video/mp4');
header('Content-Disposition: attachment; filename="video.mp4"');

readfile($tmp);
unlink($tmp);
?>


<?php
require_once 'includes/rapidapi.php';
loadEnv();
$videoUrl = "https://www.youtube.com/watch?v=Xww1EeTdt7I";
$result = getVideoInfo($videoUrl);
$f = $result['formats'][1]; // Format 1 (480p)
echo "Keys for Format 1 (480p):\n";
print_r(array_keys($f));
echo "\nValues for Format 1:\n";
print_r($f);
?>

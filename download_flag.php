<?php
// download_flag.php - Run once to download the Ethiopian flag image
// Access: http://localhost/kebele-management-system/download_flag.php
$url = 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/71/Flag_of_Ethiopia.svg/800px-Flag_of_Ethiopia.svg.png';
$save_path = __DIR__ . '/assets/images/ethiopia-flag.png';

$image = file_get_contents($url);
if ($image !== false) {
    file_put_contents($save_path, $image);
    echo "✅ Ethiopian flag image saved to: " . $save_path;
} else {
    echo "❌ Failed to download. Please save the flag image manually to: " . $save_path;
}
?>

<?php
$dir = __DIR__ . '/storage/framework/views/';
if (is_dir($dir)) {
    $files = glob($dir . '*.php');
    foreach ($files as $file) {
        unlink($file);
    }
    echo "Cleared " . count($files) . " cached view files\n";
}
$bootstrapCache = __DIR__ . '/bootstrap/cache/services.php';
if (file_exists($bootstrapCache)) {
    echo "Bootstrap cache exists\n";
}
echo "Done\n";

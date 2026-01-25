<?php
/**
 * Скрипт для очистки кеша OG-изображений
 */

$cache_dir = __DIR__ . '/../cache/og_images/';

if (!is_dir($cache_dir)) {
    echo "Cache directory does not exist: $cache_dir\n";
    exit(1);
}

$files = glob($cache_dir . '*.{png}', GLOB_BRACE);

if (!$files) {
    echo "No cached images found in $cache_dir\n";
    exit(0);
}

$count = 0;
foreach ($files as $file) {
    if (unlink($file)) {
        echo "Deleted: $file\n";
        $count++;
    } else {
        echo "Failed to delete: $file\n";
    }
}

echo "Deleted $count cached images.\n";

// Удаляем маркер, чтобы кеш перегенерировался при следующем запуске
$marker_file = __DIR__ . '/../.og-cache-generated';
if (file_exists($marker_file)) {
    unlink($marker_file);
    echo "Removed cache marker file.\n";
}
<?php
declare(strict_types=1);

require __DIR__ . '/../settings.php';

// Define helper functions at the global scope to avoid redeclaration
if (!function_exists('roundedRect')) {
    function roundedRect($im, int $x, int $y, int $w, int $h, int $r, int $c): void {
        imagefilledrectangle($im, $x + $r, $y, $x + $w - $r, $y + $h, $c);
        imagefilledrectangle($im, $x, $y + $r, $x + $w, $y + $h - $r, $c);

        imagefilledellipse($im, $x + $r, $y + $r, $r * 2, $r * 2, $c);
        imagefilledellipse($im, $x + $w - $r, $y + $r, $r * 2, $r * 2, $c);
        imagefilledellipse($im, $x + $r, $y + $h - $r, $r * 2, $r * 2, $c);
        imagefilledellipse($im, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $c);
    }
}

if (!function_exists('ttfWrap')) {
    function ttfWrap(
        $im,
        int $size,
        int $x,
        int $y,
        int $maxWidth,
        int $lineHeight,
        int $color,
        string $font,
        string $text,
        int $maxLines = 10 // Ограничение на количество строк
    ): array {
        $words = preg_split('/\s+/u', $text);
        $line = '';
        $lines = [];
        $currentY = $y;

        foreach ($words as $word) {
            $test = $line === '' ? $word : $line . ' ' . $word;
            $box = imagettfbbox($size, 0, $font, $test);
            $width = $box[2] - $box[0];

            if ($width > $maxWidth && $line !== '') {
                $lines[] = $line;
                $line = $word;
                
                if (count($lines) >= $maxLines) {
                    break;
                }
            } else {
                $line = $test;
            }
        }
        
        if ($line !== '' && count($lines) < $maxLines) {
            $lines[] = $line;
        }

        $actualLinesCount = 0;
        foreach ($lines as $i => $ln) {
            if ($i >= $maxLines) break;
            imagettftext($im, $size, 0, $x, $currentY + $i * $lineHeight, $color, $font, $ln);
            $actualLinesCount++;
        }

        return [$actualLinesCount, $currentY + ($actualLinesCount - 1) * $lineHeight];
    }
}

// Create cache directory if it doesn't exist
$cache_dir = __DIR__ . '/../cache/og_images/';
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

echo "Starting OG image cache generation...\n";

try {
    // Get all functions from database
    $stmt = $Database->query("SELECT `Function`, `IncludeName`, `Type`, `Comment` FROM `" . $Columns['Functions'] . "`");
    $functions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $generated_count = 0;
    
    foreach ($functions as $func) {
        $title = trim((string)$func['Function']);
        $subtitle = trim((string)$func['Type']) . ' · ' . trim((string)$func['IncludeName']);
        $comment = trim((string)$func['Comment']);
        
        // Limit comment length for display
        if (strlen($comment) > 100) {
            $comment = substr($comment, 0, 100) . '...';
        }
        
        $params = [
            'title' => $title,
            'subtitle' => $subtitle,
            'tag' => 'Function',
            'theme' => 'light'
        ];
        
        $cache_key = 'func_' . md5(serialize($params));
        $cache_file = $cache_dir . $cache_key . '.png';
        
        if (!file_exists($cache_file)) {
            // Generate the image using the same logic as card.png.php
            generateAndSaveImage($params, $cache_file);
            echo "Generated: $cache_file\n";
            $generated_count++;
        }
    }

    // Get all constants from database
    $stmt = $Database->query("SELECT `Constant`, `IncludeName`, `Comment` FROM `" . $Columns['Constants'] . "`");
    $constants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($constants as $const) {
        $title = trim((string)$const['Constant']);
        $subtitle = 'Constant · ' . trim((string)$const['IncludeName']);
        $comment = trim((string)$const['Comment']);
        
        // Limit comment length for display
        if (strlen($comment) > 100) {
            $comment = substr($comment, 0, 100) . '...';
        }
        
        $params = [
            'title' => $title,
            'subtitle' => $subtitle,
            'tag' => 'Constant',
            'theme' => 'light'
        ];
        
        $cache_key = 'const_' . md5(serialize($params));
        $cache_file = $cache_dir . $cache_key . '.png';
        
        if (!file_exists($cache_file)) {
            // Generate the image using the same logic as card.png.php
            generateAndSaveImage($params, $cache_file);
            echo "Generated: $cache_file\n";
            $generated_count++;
        }
    }

    // Get all files from database
    $stmt = $Database->query("SELECT `IncludeName` FROM `" . $Columns['Files'] . "`");
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($files as $file) {
        $title = trim((string)$file['IncludeName']);
        $subtitle = '.inc file';
        
        $params = [
            'title' => $title,
            'subtitle' => $subtitle,
            'tag' => 'File',
            'theme' => 'light'
        ];
        
        $cache_key = 'file_' . md5(serialize($params));
        $cache_file = $cache_dir . $cache_key . '.png';
        
        if (!file_exists($cache_file)) {
            // Generate the image using the same logic as card.png.php
            generateAndSaveImage($params, $cache_file);
            echo "Generated: $cache_file\n";
            $generated_count++;
        }
    }
    
    echo "OG image cache generation completed. Generated $generated_count new images.\n";
} catch (Exception $e) {
    echo "Error during OG image cache generation: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Generates an image and saves it to the specified file
 */
function generateAndSaveImage(array $params, string $output_file): void {
    // ==================================================
    // Image size (OpenGraph стандарт)
    // ==================================================
    $W = 1200;
    $H = 630;

    // ==================================================
    // Fonts
    // ==================================================
    // Попробуем различные возможные пути к шрифтам
    $possibleFontPaths = [
        // Путь в контейнере для веб-сервера (где на самом деле находятся шрифты)
        '/var/www/html/assets/fonts/',
        // Путь из контейнера
        '/srv/pawn-docgen/www/assets/fonts/',
        // Путь относительно текущего файла
        __DIR__ . '/../www/assets/fonts/',
        // Путь относительно корня приложения
        dirname(__DIR__, 3) . '/www/assets/fonts/',
        // Путь в текущем рабочем каталоге
        getcwd() . '/www/assets/fonts/',
        // Абсолютный путь внутри контейнера
        '/var/www/html/www/assets/fonts/'
    ];

    $fontRegular = null;
    $fontSemi = null;
    
    foreach ($possibleFontPaths as $fontDir) {
        $fontDir = rtrim($fontDir, '/') . '/';
        $regularPath = $fontDir . 'IBMPlexSans-Regular.ttf';
        $semiPath = $fontDir . 'IBMPlexSans-SemiBold.ttf';
        
        if (is_readable($regularPath) && is_readable($semiPath)) {
            $fontRegular = $regularPath;
            $fontSemi = $semiPath;
            break;
        }
    }
    
    if (!$fontRegular || !$fontSemi) {
        throw new Exception('Font files not found in any of the expected locations');
    }

    // Extract parameters
    $title = $params['title'] ?? 'register_plugin';
    $subtitle = $params['subtitle'] ?? 'AMX Mod X native';
    $tag = $params['tag'] ?? 'Pawn';
    $theme = $params['theme'] ?? 'light';

    // ==================================================
    // Image base
    // ==================================================
    $im = imagecreatetruecolor($W, $H);
    imageantialias($im, true);

    // ==================================================
    // Colors (derived from site CSS)
    // ==================================================
    if ($theme === 'dark') {
        $bg        = imagecolorallocate($im, 20, 16, 32);
        $cardBg   = imagecolorallocate($im, 30, 24, 48);
        $primary  = imagecolorallocate($im, 230, 230, 240);
        $muted    = imagecolorallocate($im, 160, 160, 180);
        $accent   = imagecolorallocate($im, 88, 140, 255);
        $border   = imagecolorallocate($im, 50, 45, 70);
        $white    = imagecolorallocate($im, 255, 255, 255);
    } else {
        $bg        = imagecolorallocate($im, 245, 246, 250);
        $cardBg   = imagecolorallocate($im, 255, 255, 255);
        $primary  = imagecolorallocate($im, 44, 30, 71);   // #2c1e47
        $muted    = imagecolorallocate($im, 120, 120, 120);
        $accent   = imagecolorallocate($im, 11, 94, 215);  // #0b5ed7
        $border   = imagecolorallocate($im, 230, 230, 230);
        $white    = imagecolorallocate($im, 255, 255, 255);
    }

    imagefill($im, 0, 0, $bg);

    // ==================================================
    // Card layout
    // ==================================================
    $cardX = 60;
    $cardY = 60;
    $cardW = $W - 120;
    $cardH = $H - 120;

    roundedRect($im, $cardX, $cardY, $cardW, $cardH, 28, $cardBg);
    imagerectangle($im, $cardX, $cardY, $cardX + $cardW, $cardY + $cardH, $border);

    // Accent stripe
    imagefilledrectangle(
        $im,
        $cardX,
        $cardY,
        $cardX + 10,
        $cardY + $cardH,
        $accent
    );

    // ==================================================
    // Tag badge
    // ==================================================
    $tagX = $cardX + 40;
    $tagY = $cardY + 40;
    $tagW = 140;
    $tagH = 36;

    roundedRect($im, $tagX, $tagY, $tagW, $tagH, 18, $accent);
    imagettftext(
        $im,
        16,
        0,
        $tagX + 20,
        $tagY + 24,
        $white,
        $fontSemi,
        $tag
    );

    // ==================================================
    // Title with adaptive font size
    // ==================================================
    $textX = $cardX + 40;
    $titleY = $cardY + 130; // Начальная Y-позиция заголовка
    $maxTextWidth = $cardW - 80;

    // Calculate initial font size for title
    $fontSize = 40;
    $maxTitleLines = 3;
    $titleBox = imagettfbbox($fontSize, 0, $fontSemi, $title);
    $titleWidth = $titleBox[2] - $titleBox[0];

    // Reduce font size if title is too wide
    while ($titleWidth > $maxTextWidth && $fontSize > 24) {
        $fontSize -= 1;
        $titleBox = imagettfbbox($fontSize, 0, $fontSemi, $title);
        $titleWidth = $titleBox[2] - $titleBox[0];
    }

    $titleLineHeight = (int)($fontSize * 1.3);

    list($titleLinesCount, $titleMaxY) = ttfWrap(
        $im,
        $fontSize,
        $textX,
        $titleY,
        $maxTextWidth,
        $titleLineHeight,
        $primary,
        $fontSemi,
        $title,
        $maxTitleLines
    );

    // ==================================================
    // Subtitle with adaptive font size and wrapping
    // ==================================================
    $subtitleY = $titleMaxY + 55; // ⬅️ ФИКС: всегда +25px от нижней границы заголовка
    $subtitleFontSize = 20;
    $subtitleMaxLines = 10;
    $subtitleLineHeight = 28;

    // Проверяем, есть ли место для subtitle до футера
    $footerHeight = 40;
    $footerY = $cardY + $cardH - 40;
    $maxSubtitleHeight = $footerY - $subtitleY - 30;
    $availableLinesForSubtitle = floor($maxSubtitleHeight / $subtitleLineHeight);

    // Ограничиваем количество строк subtitle
    $subtitleMaxLines = (int)min($subtitleMaxLines, $availableLinesForSubtitle, 8);

    list($subtitleLinesCount, $subtitleMaxY) = ttfWrap(
        $im,
        $subtitleFontSize,
        $textX,
        $subtitleY,
        $maxTextWidth,
        $subtitleLineHeight,
        $muted,
        $fontRegular,
        $subtitleMaxLines
    );

    // ==================================================
    // Footer
    // ==================================================
    imagettftext(
        $im,
        18,
        0,
        $cardX + $cardW - 260,
        $cardY + $cardH - 40,
        $muted,
        $fontSemi,
        'pawn-docgen'
    );

    // ==================================================
    // Save image to file
    // ==================================================
    imagepng($im, $output_file);
    imagedestroy($im);
}

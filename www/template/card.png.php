<?php
declare(strict_types=1);

// Generate cache key based on parameters
$params = [
    'title' => $_GET['title'] ?? 'register_plugin',
    'subtitle' => $_GET['subtitle'] ?? 'AMX Mod X native',
    'tag' => $_GET['tag'] ?? 'Pawn',
    'theme' => $_GET['theme'] ?? 'light'
];

$cache_key = md5(serialize($params));
$cache_dir = __DIR__ . '/../../cache/og_images/';
$cache_file = $cache_dir . $cache_key . '.png';

// Check if cached image exists and return it directly
if (file_exists($cache_file)) {
    header('Content-Type: image/png');
    readfile($cache_file);
    exit;
}

// Create cache directory if it doesn't exist
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

// Get debug flag
$debug = isset($_GET['debug']);

// ==================================================
// Image size (OpenGraph стандарт)
// ==================================================
$W = 1200;
$H = 630;

// ==================================================
// Fonts
// ==================================================
$fontDir = realpath(__DIR__ . '/../assets/fonts');

$fontRegular = $fontDir . '/IBMPlexSans-Regular.ttf';
$fontSemi    = $fontDir . '/IBMPlexSans-SemiBold.ttf';

// Проверяем оба возможных пути к шрифтам
if (!is_readable($fontRegular) || !is_readable($fontSemi)) {
    // Если шрифты не найдены в текущем пути, пробуем другой путь
    $fontDir = realpath(__DIR__ . '/../../www/assets/fonts');
    $fontRegular = $fontDir . '/IBMPlexSans-Regular.ttf';
    $fontSemi    = $fontDir . '/IBMPlexSans-SemiBold.ttf';
    
    if (!is_readable($fontRegular) || !is_readable($fontSemi)) {
        http_response_code(500);
        exit('Font files not found');
    }
}

// ==================================================
// Query params
// ==================================================

$title    = trim((string)($params['title']));
$subtitle = trim((string)($params['subtitle']));
$tag      = trim((string)($params['tag']));
$theme    = $params['theme'];

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
    $debugColor = imagecolorallocate($im, 255, 0, 0);
} else {
    $bg        = imagecolorallocate($im, 245, 246, 250);
    $cardBg   = imagecolorallocate($im, 255, 255, 255);
    $primary  = imagecolorallocate($im, 44, 30, 71);   // #2c1e47
    $muted    = imagecolorallocate($im, 120, 120, 120);
    $accent   = imagecolorallocate($im, 11, 94, 215);  // #0b5ed7
    $border   = imagecolorallocate($im, 230, 230, 230);
    $white    = imagecolorallocate($im, 255, 255, 255);
    $debugColor = imagecolorallocate($im, 255, 0, 0);
}

imagefill($im, 0, 0, $bg);

// ==================================================
// Helpers
// ==================================================
function roundedRect($im, int $x, int $y, int $w, int $h, int $r, int $c): void {
    imagefilledrectangle($im, $x + $r, $y, $x + $w - $r, $y + $h, $c);
    imagefilledrectangle($im, $x, $y + $r, $x + $w, $y + $h - $r, $c);

    imagefilledellipse($im, $x + $r, $y + $r, $r * 2, $r * 2, $c);
    imagefilledellipse($im, $x + $w - $r, $y + $r, $r * 2, $r * 2, $c);
    imagefilledellipse($im, $x + $r, $y + $h - $r, $r * 2, $r * 2, $c);
    imagefilledellipse($im, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $c);
}

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

// ==================================================
// Card layout
// ==================================================
$cardX = 60;
$cardY = 60;
$cardW = $W - 120;
$cardH = $H - 120;

roundedRect($im, $cardX, $cardY, $cardW, $cardH, 28, $cardBg);
imagerectangle($im, $cardX, $cardY, $cardX + $cardW, $cardY + $cardH, $border);

// Debug: границы карточки
if ($debug) {
    imagerectangle($im, $cardX, $cardY, $cardX + $cardW, $cardY + $cardH, $debugColor);
}

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

// Debug: границы области заголовка
if ($debug) {
    imagerectangle($im, $textX, $titleY + 10, $textX + $maxTextWidth, $titleMaxY - 40, $debugColor);
}

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
    $subtitle,
    $subtitleMaxLines
);

// Debug: границы области subtitle
if ($debug) {
    imagerectangle($im, $textX, $subtitleY - 25, $textX + $maxTextWidth, min($subtitleMaxY + 40, $footerY + 30), $debugColor);
}

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

// Debug: границы области футера
if ($debug) {
    $footerBox = imagettfbbox(18, 0, $fontSemi, 'pawn-docgen');
    $footerWidth = $footerBox[2] - $footerBox[0];
    imagerectangle($im, $cardX + $cardW - 260, $cardY + $cardH - 40 - 20, 
                   $cardX + $cardW - 260 + $footerWidth, $cardY + $cardH - 40 + 5, $debugColor);
}

// ==================================================
// Output
// ==================================================
header('Content-Type: image/png');
imagepng($im, $cache_file); // Save to cache
imagepng($im);
imagedestroy($im);

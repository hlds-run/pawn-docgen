<?php
/**
 * Dynamic robots.txt generator
 * Generates a proper robots.txt with sitemap URL
 */

// Build absolute URL for sitemap
// TODO: temporary force https scheme for better search indexing, fix it later on reverse proxy (NGinx)
$Scheme = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'https';

$Host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
$SitemapURL = $Scheme . '://' . $Host . rtrim( $GLOBALS['BaseURL'], '/' ) . '/sitemap.xml';

header( 'Content-Type: text/plain; charset=UTF-8' );
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Sitemap: " . $SitemapURL . "\n";

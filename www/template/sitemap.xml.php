<?php
/**
 * Dynamic sitemap.xml generator
 * Generates XML sitemap for search engines with proper priorities
 */

$Scheme = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$Host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
$BaseURLFull = $Scheme . '://' . $Host . rtrim( $GLOBALS['BaseURL'], '/' ) . '/';

header( 'Content-Type: application/xml; charset=UTF-8' );
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Home page - priority 0.8 (high, but not as specific content)
echo "\t<url>\n";
echo "\t\t<loc>" . htmlspecialchars( $BaseURLFull ) . "</loc>\n";
echo "\t\t<changefreq>monthly</changefreq>\n";
echo "\t\t<priority>0.8</priority>\n";
echo "\t</url>\n";

try {
	// Get all includes (files)
	$STH = $GLOBALS['Database']->query( 'SELECT `ID`, `IncludeName` FROM `' . $GLOBALS['Columns'][ 'Files' ] . '` ORDER BY `IncludeName` ASC' );
	$Files = $STH->fetchAll( PDO :: FETCH_KEY_PAIR );
	
	foreach( $Files as $FileID => $FileName )
	{
		// File constants page - priority 0.6 (overview page)
		echo "\t<url>\n";
		echo "\t\t<loc>" . htmlspecialchars( $BaseURLFull . $FileName ) . "</loc>\n";
		echo "\t\t<changefreq>monthly</changefreq>\n";
		echo "\t\t<priority>0.6</priority>\n";
		echo "\t</url>\n";
		
		// Functions list page - priority 0.7 (list of functions is important)
		echo "\t<url>\n";
		echo "\t\t<loc>" . htmlspecialchars( $BaseURLFull . $FileName . '/__functions' ) . "</loc>\n";
		echo "\t\t<changefreq>monthly</changefreq>\n";
		echo "\t\t<priority>0.7</priority>\n";
		echo "\t</url>\n";
		
		// Individual function pages - priority 1.0 (most important - actual content)
		$STH = $GLOBALS['Database']->prepare( 'SELECT `Function` FROM `' . $GLOBALS['Columns'][ 'Functions' ] . '` WHERE `IncludeName` = :includeName ORDER BY `Function` ASC' );
		$STH->bindValue( ':includeName', $FileName, PDO :: PARAM_STR );
		$STH->execute();
		
		while( $Function = $STH->fetch( PDO :: FETCH_ASSOC ) )
		{
			echo "\t<url>\n";
			echo "\t\t<loc>" . htmlspecialchars( $BaseURLFull . $FileName . '/' . $Function[ 'Function' ] ) . "</loc>\n";
			echo "\t\t<changefreq>monthly</changefreq>\n";
			echo "\t\t<priority>1.0</priority>\n";
			echo "\t</url>\n";
		}
	}
} catch( Exception $e ) {
	// Database error - return minimal sitemap (only home)
}

echo '</urlset>' . "\n";

<?php
	// Build full current page URL
	$Scheme = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
	$Host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
	$RequestURI = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
	$CurrentPageURL = $Scheme . '://' . $Host . $RequestURI;
	
	// Meta description helper function
	if( !function_exists( 'getTruncatedDescription' ) ) {
		function getTruncatedDescription( $text, $maxLength = 160 ) {
			if( empty( $text ) ) {
				return '';
			}
			$text = trim( str_replace( array( "\n", "\r", "\t" ), ' ', $text ) );
			if( strlen( $text ) > $maxLength ) {
				$text = substr( $text, 0, $maxLength );
				$lastSpace = strrpos( $text, ' ' );
				if( $lastSpace !== false ) {
					$text = substr( $text, 0, $lastSpace );
				}
				$text .= '...';
			}
			return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
		}
	}
	
	// Determine meta description based on page type
	$MetaDescription = '';
	
	if( !empty( $PageFunction ) ) {
		// Function page
		$MetaDescription = getTruncatedDescription( $PageFunction[ 'Comment' ] );
	} elseif( !empty( $IsRawView ) ) {
		// Raw file view
		$MetaDescription = 'Source code of ' . htmlspecialchars( $CurrentOpenFile ) . '.inc file';
	} elseif( !empty( $PageFunctions ) ) {
		// Functions list page (either via __functions or redirected from empty constants)
		$MetaDescription = 'List of functions in ' . htmlspecialchars( $CurrentOpenFile ) . '.inc file';
	} elseif( !empty( $CurrentOpenFile ) ) {
		// Constants page
		$MetaDescription = 'Constants and symbols from ' . htmlspecialchars( $CurrentOpenFile ) . '.inc file';
	}
	
	// Fallback to project description
	if( empty( $MetaDescription ) ) {
		$MetaDescription = htmlspecialchars( $Project . ' Scripting API Reference - Browse functions, constants and symbols' );
	}
	
	// Generate page title with pipe separators
	// Determine OG type based on page
	$OGType = 'website';
	
	if( !empty( $PageFunction ) ) {
		// Function page: FunctionName | Functions | FileName | SiteName
		$Title = htmlspecialchars( $PageFunction[ 'Function' ] ) . ' | Functions | ' . htmlspecialchars( $CurrentOpenFile ) . ' | ' . $Project;
		$OGType = 'article';
	} elseif( !empty( $IsRawView ) ) {
		// Raw file view: File content | FileName | SiteName
		$Title = 'File content | ' . htmlspecialchars( $CurrentOpenFile ) . ' | ' . $Project;
		$OGType = 'article';
	} elseif( !empty( $PageFunctions ) ) {
		// Functions list page: Functions | FileName | SiteName
		$Title = 'Functions | ' . htmlspecialchars( $CurrentOpenFile ) . ' | ' . $Project;
		$OGType = 'website';
	} elseif( !empty( $CurrentOpenFile ) ) {
		// Constants page: Constants | FileName | SiteName
		$Title = 'Constants | ' . htmlspecialchars( $CurrentOpenFile ) . ' | ' . $Project;
	} else {
		// Home page
		$Title = $Project . ' Scripting API Reference';
	}
	
	if( $RenderLayout ):
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?php echo $MetaDescription; ?>">
	
	<!-- Open Graph Meta Tags -->
	<meta property="og:title" content="<?php echo $Title; ?>">
	<meta property="og:description" content="<?php echo $MetaDescription; ?>">
	<meta property="og:url" content="<?php echo htmlspecialchars( $CurrentPageURL ?? $BaseURL ); ?>">
	<meta property="og:type" content="<?php echo $OGType; ?>">
	<meta property="og:site_name" content="<?php echo htmlspecialchars( $Project ); ?> Scripting API">
	<meta property="og:locale" content="en_US">
	
	<!-- Twitter Card Meta Tags -->
	<meta name="twitter:card" content="summary">
	<meta name="twitter:title" content="<?php echo $Title; ?>">
	<meta name="twitter:description" content="<?php echo $MetaDescription; ?>">
	<meta name="twitter:site" content="@">
	
	<!-- Canonical URL -->
	<link rel="canonical" href="<?php echo htmlspecialchars( $CurrentPageURL ?? $BaseURL ); ?>">
	
	<!-- Additional SEO Meta Tags -->
	<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
	<meta name="theme-color" content="#0d6efd">
	
	<!-- Favicon and App Icons -->
	<link rel="icon" type="image/x-icon" href="<?php echo $BaseURL; ?>favicon.ico">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $BaseURL; ?>favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $BaseURL; ?>favicon-16x16.png">
	<link rel="apple-touch-icon" href="<?php echo $BaseURL; ?>apple-touch-icon.png">
	
	<title><?php echo $Title; ?></title>
	
	<!-- Schema.org Structured Data -->
	<script type="application/ld+json">
	<?php
		// Generate breadcrumb schema
		$breadcrumbItems = array();
		$position = 1;
		
		// Add home
		$breadcrumbItems[] = array(
			'@type' => 'ListItem',
			'position' => $position++,
			'name' => $Project,
			'item' => $BaseURL
		);
		
		if( !empty( $CurrentOpenFile ) ) {
			$breadcrumbItems[] = array(
				'@type' => 'ListItem',
				'position' => $position++,
				'name' => $CurrentOpenFile . '.inc',
				'item' => $BaseURL . $CurrentOpenFile
			);
		}
		
		if( !empty( $PageFunction ) ) {
			$breadcrumbItems[] = array(
				'@type' => 'ListItem',
				'position' => $position++,
				'name' => 'Functions',
				'item' => $BaseURL . $CurrentOpenFile . '/__functions'
			);
			$breadcrumbItems[] = array(
				'@type' => 'ListItem',
				'position' => $position++,
				'name' => $PageFunction[ 'Function' ],
				'item' => $BaseURL . $CurrentOpenFile . '/' . htmlspecialchars( $PageFunction[ 'Function' ] )
			);
		} elseif( !empty( $CurrentOpenFile ) && isset( $_SERVER[ 'QUERY_STRING' ] ) && strpos( $_SERVER[ 'QUERY_STRING' ], '__functions' ) !== false ) {
			$breadcrumbItems[] = array(
				'@type' => 'ListItem',
				'position' => $position++,
				'name' => 'Functions',
				'item' => $BaseURL . $CurrentOpenFile . '/__functions'
			);
		} elseif( !empty( $CurrentOpenFile ) ) {
			$breadcrumbItems[] = array(
				'@type' => 'ListItem',
				'position' => $position++,
				'name' => 'Constants',
				'item' => $BaseURL . $CurrentOpenFile
			);
		}
		
		// Breadcrumb schema
		$breadcrumbSchema = array(
			'@context' => 'https://schema.org',
			'@type' => 'BreadcrumbList',
			'itemListElement' => $breadcrumbItems
		);
		
		echo json_encode( $breadcrumbSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	?>
	</script>
	
	<?php if( !empty( $PageFunction ) ): ?>
	<!-- TechArticle Schema for Function Pages -->
	<script type="application/ld+json">
	<?php
		$articleSchema = array(
			'@context' => 'https://schema.org',
			'@type' => 'TechArticle',
			'headline' => $PageFunction[ 'Function' ],
			'description' => getTruncatedDescription( $PageFunction[ 'Comment' ], 160 ),
			'url' => $BaseURL . $CurrentOpenFile . '/' . htmlspecialchars( $PageFunction[ 'Function' ] ),
			'isPartOf' => array(
				'@type' => 'WebSite',
				'name' => $Project . ' Scripting API Reference',
				'url' => $BaseURL
			),
			'author' => array(
				'@type' => 'Organization',
				'name' => 'AlliedModders'
			),
			'datePublished' => date( 'Y-m-d' ),
			'proficiencyLevel' => 'Intermediate',
			'keywords' => implode( ', ', array( $PageFunction[ 'Function' ], 'scripting', 'API', $CurrentOpenFile ) )
		);
		
		echo json_encode( $articleSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	?>
	</script>
	<?php endif; ?>
	
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.8/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo $BaseURL; ?>style.css">
</head>
<body data-baseurl="<?php echo $BaseURL; ?>">
	<div class="mobile-header">
		<button class="menu-toggle" aria-label="Toggle menu">☰</button>
		<div class="header-link">
			<a href="<?php echo $BaseURL; ?>"><?php echo $Project; ?> API</a>
		</div>
	</div>

	<div class="sidebar">
		<div class="header-link">
			<a href="<?php echo $BaseURL; ?>"><?php echo $Project; ?> API</a>
		</div>
		
		<input class="form-control typeahead" type="text" placeholder="Search functions">
		
		<noscript>
			<style>
				.typeahead {
					display: none;
				}
				
				.search-notice {
					padding: 10px;
					text-align: center;
					background-color: #e7f1ff;
					border: 1px solid #bee5eb;
					border-radius: 0.375rem;
				}
			</style>
			
			<p class="search-notice">Search requires javascript to work</p>
		</noscript>
		
		<?php require __DIR__ . '/sidebar.php'; ?>
	</div>
	
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12" id="pjax-container">
<?php else: ?>
<title><?php echo $Title; ?></title>
<?php endif; ?>

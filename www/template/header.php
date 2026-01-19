<?php
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
	} elseif( !empty( $CurrentOpenFile ) && isset( $_SERVER[ 'QUERY_STRING' ] ) && strpos( $_SERVER[ 'QUERY_STRING' ], '__functions' ) !== false ) {
		// Functions list page
		$MetaDescription = 'List of functions in ' . htmlspecialchars( $CurrentOpenFile ) . '.inc file';
	} elseif( !empty( $CurrentOpenFile ) ) {
		// Constants page
		$MetaDescription = 'Constants and symbols from ' . htmlspecialchars( $CurrentOpenFile ) . '.inc file';
	}
	
	// Fallback to project description
	if( empty( $MetaDescription ) ) {
		$MetaDescription = htmlspecialchars( $Project . ' Scripting API Reference - Browse functions, constants and symbols' );
	}
	
	$Title = ( empty( $HeaderTitle ) ? '' : ( htmlspecialchars( $HeaderTitle ) . ' · ' ) ) . $Project . ' Scripting API Reference';
	
	if( $RenderLayout ):
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?php echo $MetaDescription; ?>">
	
	<title><?php echo $Title; ?></title>
	
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

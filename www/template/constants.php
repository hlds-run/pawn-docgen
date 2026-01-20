<?php
	require __DIR__ . '/header.php';
?>

<nav aria-label="Breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="<?php echo $BaseURL; ?>"><?php echo $Project; ?></a></li>
		<li class="breadcrumb-item"><a href="<?php echo $BaseURL . $CurrentOpenFile; ?>"><?php echo $CurrentOpenFile; ?>.inc</a></li>
		<li class="breadcrumb-item active" aria-current="page">Constants</li>
		<li class="ms-auto"><a href="<?php echo $BaseURL . $CurrentOpenFile; ?>/__raw">File</a></li>
		<li><a href="<?php echo $BaseURL . $CurrentOpenFile; ?>/__functions">Functions</a></li>
	</ol>
</nav>

<article>

<h1 class="border-bottom pb-2 mb-3">Constants in <?php echo htmlspecialchars( $PageName ); ?>.inc</h1>

<h2>List of constants</h2>

<?php
	$InSection = 0;
	$InSectionBody = 0;
	
	foreach( $Results as $Result )
	{
		$ClosePanel = false;
		$Tags = json_decode( $Result[ 'Tags' ], true );
		
		if( substr( $Result[ 'Comment' ], 0, 8 ) === '@section' )
		{
			$InSection++;
			
			$Slug = StringToSlug( $Result[ 'Comment' ] );
			
			echo '<div class="card card-info mb-3" id="' . $Slug . '">';
			echo '<div class="card-header bg-info text-white">' . htmlspecialchars( substr( $Result[ 'Comment' ], 9 ) ) . '<a href="#' . $Slug . '" class="permalink float-end" aria-label="Copy link to this section">#</a></div>';
			
			if( Empty( $Tags ) && Empty( $Result[ 'Constant' ] ) )
			{
				$InSectionBody++;
				
				echo '<div class="card-body">';
				
				continue;
			}
		}
		else if( $InSection > 0 && $Result[ 'Comment' ] === '@endsection' )
		{
			$InSection--;
			
			if( $InSectionBody > 0 )
			{
				$InSectionBody--;
				
				echo '</div>';
			}
			
			echo '</div>';
			
			continue;
		}
		else
		{
			$Slug = StringToSlug( $Result[ 'Comment' ] );
			
			echo '<div class="card card-primary mb-3" id="' . $Slug . '">';
			echo '<div class="card-header bg-primary text-white">' . htmlspecialchars( $Result[ 'Comment' ] ) . '<a href="#' . $Slug . '" class="permalink float-end" aria-label="Copy link to ' . htmlspecialchars( $Result['Comment'] ) . '">#</a></div>';
			
			$ClosePanel = true;
		}
		
		if( !Empty( $Tags ) )
		{
			echo '<div class="card-body">';
			
			// Group tags by type to handle multiple items of same type
			$GroupedTags = Array();
			foreach( $Tags as $Tag )
			{
				$GroupedTags[ $Tag[ 'Tag' ] ][] = $Tag;
			}
			
			foreach( $GroupedTags as $TagType => $TagList )
			{
				echo '<h3 class="sub-header2">' . ucfirst( $TagType ) . '</h3>';
				
				// If multiple items of same type, use list
				if( count( $TagList ) > 1 )
				{
					echo '<ul>';
					foreach( $TagList as $Tag )
					{
						echo '<li><pre class="description">' . htmlspecialchars( $Tag[ 'Description' ] ) . '</pre></li>';
					}
					echo '</ul>';
				}
				else
				{
					// Single item - use pre
					echo '<pre class="description">' . htmlspecialchars( $TagList[0][ 'Description' ] ) . '</pre>';
				}
			}
			
			echo '</div>';
		}
		
		if( !Empty( $Result[ 'Constant' ] ) )
		{
			echo '<div class="card-footer"><pre class="description"><code class="language-clike">' . htmlspecialchars( $Result[ 'Constant' ] ) . '</code></pre></div>';
		}
		
		if( $ClosePanel )
		{
			echo '</div>';
		}
	}
	
	while( --$InSection > 0 )
	{
		echo '</div>';
	}
	
	while( --$InSectionBody > 0 )
	{
		echo '</div>';
	}
?>
</article>

<?php
	require __DIR__ . '/footer.php';
	
	function StringToSlug( $String )
	{
		$String = preg_replace( '/[^A-Za-z0-9-]+/', '-', $String );
		$String = trim( $String, "- \t\n\r\0\x0B" );
		
		return strtolower( $String );
	}
?>

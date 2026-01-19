<?php
	require __DIR__ . '/header.php';
	
	$Tags = json_decode( $PageFunction[ 'Tags' ], true );
	
	$Parameters = Array();
	$OtherTags = Array();
	
	foreach( $Tags as $Tag )
	{
		if( $Tag[ 'Tag' ] === 'param' )
		{
			$Parameters[ ] = $Tag;
		}
		else
		{
			$OtherTags[ ] = $Tag;
		}
	}
?>

<nav aria-label="Breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="<?php echo $BaseURL; ?>"><?php echo $Project; ?></a></li>
		<li class="breadcrumb-item"><a href="<?php echo $BaseURL . $CurrentOpenFile; ?>"><?php echo $CurrentOpenFile; ?>.inc</a></li>
		<li class="breadcrumb-item"><a href="<?php echo $BaseURL . $CurrentOpenFile; ?>/__functions">Functions</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars( $PageFunction[ 'Function' ] ); ?></li>
	</ol>
</nav>

<h1 class="border-bottom pb-2 mb-3"><?php echo htmlspecialchars( $PageFunction[ 'Function' ] ); ?></h1>

<h2 class="sub-header2">Syntax</h2>
<pre class="syntax"><?php echo htmlspecialchars( $PageFunction[ 'FullFunction' ] ); ?></pre>

<?php if( !empty( $Parameters ) ): ?>
<h2 class="sub-header2">Usage</h2>
<div class="table-responsive">
	<table class="table table-bordered table-hover">
		<thead>
			<tr>
				<th>Parameter</th>
				<th>Description</th>
			</tr>
		</thead>
		<?php
			foreach( $Parameters as $Tag )
			{
				echo '<tr><td>' . htmlspecialchars( $Tag[ 'Variable' ] ) . '</td><td><pre class="description">' . htmlspecialchars( $Tag[ 'Description' ] ) . '</pre></td></tr>';
			}
		?>
	</table>
</div>
<?php endif; ?>

<h2 class="sub-header2">Description</h2>

<?php if( !empty( $OtherTags ) ): ?>
<?php
	foreach( $OtherTags as $Tag )
	{
		switch( $Tag[ 'Tag' ] )
		{
			case 'noreturn':
			{
				echo '<h2 class="sub-header2">Return</h2>';
				echo '<pre class="description">' . ( $PageFunction[ 'Type' ] === 'forward' ? 'This forward ignores the returned value.' : 'This function has no return value.' ) . '</pre>';
				break;
			}
			case 'deprecated':
			{
				echo '<div class="alert alert-danger" role="alert" style="margin-top:20px">';
				echo '<p>This function has been deprecated, do NOT use it</p>';
				
				if( !empty( $Tag[ 'Description' ] ) )
				{
					echo '<p><strong>Reason:</strong> ' . htmlspecialchars( $Tag[ 'Description' ] ) . '</p>';
				}
				
				echo '</div>';
				break;
			}
			default:
			{
				echo '<h2 class="sub-header2">' . ucfirst( $Tag[ 'Tag' ] ) . '</h2>';
				echo '<pre class="description">' . htmlspecialchars( $Tag[ 'Description' ] ) . '</pre>';
			}
		}
	}
?>
<?php endif; ?>

<?php
	require __DIR__ . '/footer.php';
?>

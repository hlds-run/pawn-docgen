<?php
	require __DIR__ . '/header.php';
?>

<nav aria-label="Breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="<?php echo $BaseURL; ?>"><?php echo $Project; ?></a></li>
		<li class="breadcrumb-item"><a href="<?php echo $BaseURL . $CurrentOpenFile; ?>"><?php echo $CurrentOpenFile; ?>.inc</a></li>
		<li class="breadcrumb-item active" aria-current="page">Functions</li>
	</ol>
</nav>

<h1 class="border-bottom pb-2 mb-3">Functions in <?php echo htmlspecialchars( $CurrentOpenFile ); ?>.inc</h1>

<h2>List of functions</h2>

<div class="table-responsive">
	<table class="table table-bordered table-hover">
		<thead>
			<tr>
				<th>Function</th>
				<th>Description</th>
			</tr>
		</thead>
		<?php
			foreach( $PageFunctions as $Function )
			{
				echo '<tr><td><a href="' . $BaseURL . $CurrentOpenFile . '/' . htmlspecialchars( $Function[ 'Function' ] ) . '">' . htmlspecialchars( $Function[ 'Function' ] ) . '</a></td><td><pre class="description">' . htmlspecialchars( $Function[ 'Comment' ] ) . '</pre></td></tr>';
			}
		?>
	</table>
</div>

<?php
	require __DIR__ . '/footer.php';
?>

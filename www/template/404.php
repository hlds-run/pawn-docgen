<?php
	header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 404 Not Found' );
	
	require __DIR__ . '/header.php';
?>

<h3 class="border-bottom pb-2 mb-3">Nothing found</h3>

<?php
	require __DIR__ . '/footer.php';
?>

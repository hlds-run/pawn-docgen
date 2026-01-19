<?php
	require __DIR__ . '/header.php';
?>

<nav aria-label="Breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="<?php echo $BaseURL; ?>"><?php echo $Project; ?></a></li>
		<li class="breadcrumb-item"><a href="<?php echo $BaseURL . $CurrentOpenFile; ?>"><?php echo $CurrentOpenFile; ?>.inc</a></li>
		<li class="breadcrumb-item active" aria-current="page">File</li>
		<li class="ms-auto"><a href="<?php echo $BaseURL . $CurrentOpenFile; ?>/__functions">Functions</a></li>
		<li><a href="<?php echo $BaseURL . $CurrentOpenFile; ?>">Constants</a></li>
	</ol>
</nav>

<article>

<h1 class="border-bottom pb-2 mb-3">File content in <?php echo htmlspecialchars( $CurrentOpenFile ); ?>.inc</h1>

<style>
	#editor { width: 100%; height: 75vh; border: 1px solid #ddd; }
</style>

<div id="editor" role="region" aria-label="Source code"></div>

<noscript>
	<pre><?php echo htmlspecialchars( $PageFile[ 'Content' ] ); ?></pre>
</noscript>

<!-- Monaco Editor loader and init -->
<script>
	(function(){
		var content = <?php echo json_encode( $PageFile['Content'] ); ?>;
		// Load Monaco's AMD loader from CDN
		var loaderScript = document.createElement('script');
		loaderScript.src = 'https://unpkg.com/monaco-editor@0.55.1/min/vs/loader.js';
		loaderScript.onload = function() {
			require.config({ paths: { 'vs': 'https://unpkg.com/monaco-editor@0.55.1/min/vs' } });
			require(['vs/editor/editor.main'], function() {
				try {
					monaco.editor.create(document.getElementById('editor'), {
						// https://microsoft.github.io/monaco-editor/docs.html#interfaces/editor_editor_api.editor.IEditorOptions.html
						value: content,
						language: 'cpp',
						readOnly: true,
						automaticLayout: true,
						minimap: { enabled: false },
						scrollBeyondLastLine: false,
						find: true,
					});
				} catch(e) {
					// fallback: show content in pre if Monaco init fails
					var pre = document.createElement('pre');
					pre.textContent = content;
					var editor = document.getElementById('editor');
					editor.parentNode.replaceChild(pre, editor);
				}
			});
		};
		loaderScript.onerror = function() {
			// leave the <noscript> pre visible if loader can't load
			console.warn('Failed to load Monaco loader.');
		};
		document.head.appendChild(loaderScript);
	})();
</script>
</article>

<?php
	require __DIR__ . '/footer.php';
?>

<?php if( $RenderLayout ): ?>
			</main>
			
			<footer class="col-lg-12 footer">
				<p>This documentation was generated automatically using <a href="https://github.com/alliedmodders/pawn-docgen">pawn-docgen</a> written by <a href="//xpaw.me">xPaw</a> for <a href="//alliedmods.net/">AlliedMods</a>.</p>
			</footer>
		</div>
	</div>
	
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.pjax/2.0.1/jquery.pjax.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.8/js/bootstrap.bundle.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>
	<script>
		(function(){
			var prismRequested = false;

			function loadPrismResources(cb)
			{
				if(prismRequested) { if(cb) cb(); return; }
				prismRequested = true;

				var link = document.createElement('link');
				link.rel = 'stylesheet';
				link.href = '//cdnjs.cloudflare.com/ajax/libs/prism/1.30.0/themes/prism.min.css';
				document.head.appendChild(link);

				var script = document.createElement('script');
				script.src = '//cdnjs.cloudflare.com/ajax/libs/prism/1.30.0/prism.min.js';
				script.async = true;
				script.onload = function(){ if(cb) cb(); };
				document.body.appendChild(script);
			}

			function ensurePrism()
			{
				try {
					var codes = document.querySelectorAll('pre.description > code');
					if(codes.length === 0) return;
					codes.forEach(function(code){ if(!code.classList.length) code.classList.add('language-clike'); });
					if(window.Prism && typeof Prism.highlightAll === 'function') return Prism.highlightAll();
					// load prism and then highlight
					loadPrismResources(function(){ try{ if(window.Prism && typeof Prism.highlightAll === 'function') Prism.highlightAll(); }catch(e){} });
				} catch(e) { console && console.error && console.error(e); }
			}

			if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ensurePrism);
			else ensurePrism();

			if(window.jQuery && jQuery(document)) {
				jQuery(document).on('pjax:end pjax:complete', function(){
					ensurePrism();
				});
			}
		})();
	</script>
	<script src="<?php echo $BaseURL; ?>script.js"></script>
</body>
</html>
<?php endif; ?>

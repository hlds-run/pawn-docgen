(function () {
	// Initialize Bootstrap 5 popovers
	function initializePopovers() {
		var popoverTriggerList = [].slice.call(document.querySelectorAll('.function'));
		popoverTriggerList.forEach(function (popoverTriggerEl) {
			// Dispose existing popover if it exists
			var existingPopover = bootstrap.Popover.getInstance(popoverTriggerEl);
			if (existingPopover) {
				existingPopover.dispose();
			}

			// Create new popover with Bootstrap 5 API
			new bootstrap.Popover(popoverTriggerEl, {
				container: 'body',
				placement: 'right',
				trigger: 'hover',
				html: true,
				title: $(popoverTriggerEl).data('title'),
				content: $(popoverTriggerEl).data('content')
			});
		});
	}

	// Initialize popovers on page load
	initializePopovers();

	$('.file > a').on('click', function () {
		var nav = $('#file-' + $(this).text());

		var visibleNav = $('.nav-functions.show');

		if (!visibleNav.is(nav)) {
			visibleNav.removeClass('show');
		}

		if (nav.hasClass('show')) {
			nav.removeClass('show');
		}
		else {
			nav.addClass('show');
			// Reinitialize popovers for newly visible functions
			initializePopovers();
		}
	});

	// Menu toggle for mobile
	$('.menu-toggle').on('click', function () {
		$('.sidebar').toggleClass('open');
	});

	// Close sidebar when link inside is clicked (mobile)
	$('.sidebar a').on('click', function () {
		if (window.innerWidth < 768) {
			$('.sidebar').removeClass('open');
		}
	});

	var functions = [];

	$('.function').each(function () {
		var $this = $(this);

		functions.push({
			file: $this.data('file'),
			title: $this.data('title'),
			content: $this.data('content')
		});
	});

	var constantSearch = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: $('body').data('baseurl') + '__search/%QUERY',
			wildcard: '%QUERY'
		}
	});

	constantSearch.initialize();

	$('.typeahead').typeahead(
		{
			hint: true,
			highlight: true,
			minLength: 1
		},
		{
			name: 'functions',
			displayKey: 'value',
			templates:
			{
				header: '<h3 class="tt-name">Functions</h3>'
			},
			source: function (query, callback) {
				var matches = [], substrRegex = new RegExp(query, 'i');

				$.each(functions, function (i, func) {
					if (substrRegex.test(func.title)) {
						matches.push({ 
							includeName: func.file, 
							value: func.title,
							type: 'function'
						});
					}
				});

				callback(matches);
			}
		},
		{
			name: 'constants',
			displayKey: 'value',
			templates:
			{
				header: '<h3 class="tt-name">Constants</h3>'
			},
			source: function (query, syncResults, asyncResults) {
				if (query === '') {
					return;
				}

				constantSearch.search(query, function (results) {
					asyncResults(results);
				}, function (results) {
					asyncResults(results);
				});
			}
		}).on('typeahead:selected', function (a, b, source) {
			var baseUrl = $('body').data('baseurl');
			
			// Check result type for navigation
			if (b.type === 'function') {
				// Navigate to function page: /includeName/functionName
				window.location.href = baseUrl + b.includeName + '/' + b.value;
			} else if (b.type === 'constant') {
				// Navigate to constants page: /includeName
				window.location.href = baseUrl + b.includeName;
			} else if (b.includeName) {
				// Fallback - try to determine from source
				var isFunction = (source && source.name === 'functions');
				
				if (isFunction) {
					window.location.href = baseUrl + b.includeName + '/' + b.value;
				} else {
					window.location.href = baseUrl + b.includeName;
				}
				return;
			}

			// Handle functions selection from page (fallback)
			var func = $('[data-title="' + b.value + '"]');

			$('.nav-functions.show').removeClass('show');

			func
				.parent().addClass('show')
				.end()
				.find('a').click();
		});
}());

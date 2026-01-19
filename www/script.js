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

	// Initial popover initialization
	initializePopovers();

	// Create progress bar and loading spinner indicators
	var progressBar = $('<div id="loading-progress" class="progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="position: fixed; top: 0; left: 0; width: 100%; height: 3px; z-index: 9999; display: none;"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div></div>');
	var loadingSpinner = $('<div class="spinner-border spinner-border-sm text-primary" id="loading-spinner" role="status" style="position: fixed; top: 10px; right: 10px; z-index: 9999; display: none;"><span class="visually-hidden">Loading...</span></div>');
	$('body').append(progressBar).append(loadingSpinner);

	$(document)
		.pjax('a', '#pjax-container')
		.on('pjax:start', function () { 
			$('#loading-progress').show().find('.progress-bar').css('width', '30%');
			$('#loading-spinner').show();
		})
		.on('pjax:end', function () { 
			$('#loading-progress').find('.progress-bar').css('width', '100%').delay(500).fadeOut(300, function() {
				$(this).css('width', '0%');
				$('#loading-progress').hide();
			});
			$('#loading-spinner').hide(); 
			initializePopovers();
		})
		.on('pjax:clicked', function (ev) {
			$('.function.active').removeClass('active');
			$(ev.target).parent().addClass('active');
		});

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

		functions.push([$this.data('title'), $this.data('content')]);
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

				$.each(functions, function (i, str) {
					if (substrRegex.test(str)) {
						matches.push({ value: str[0] });
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
			// Check if this is a constant (has includeName but not from functions source)
			if (b.includeName) {
				// Direct navigation to constants page for this file
				var baseUrl = $('body').data('baseurl');
				var url = baseUrl + b.includeName;
				window.location.href = url;

				return;
			}

			// Handle functions selection
			var func = $('[data-title="' + b.value + '"]');

			$('.nav-functions.show').removeClass('show');

			func
				.parent().addClass('show')
				.end()
				.find('a').click();
		});
}());

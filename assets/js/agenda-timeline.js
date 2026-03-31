(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.pa-timeline').forEach(function (timeline) {
			var nav = timeline.querySelector('.pa-years-nav');
			if (nav) {
				var links = [].slice.call(nav.querySelectorAll('a'));
				var years = [].slice.call(timeline.querySelectorAll('.pa-year-block'));

				links.forEach(function (link) {
					link.addEventListener('click', function (event) {
						var target = document.querySelector(link.getAttribute('href'));
						if (!target) {
							return;
						}
						event.preventDefault();
						var offset = (nav.getBoundingClientRect().height || 0) + 16;
						window.scrollTo({
							top: target.getBoundingClientRect().top + window.scrollY - offset,
							behavior: 'smooth'
						});
					});
				});

				if (typeof IntersectionObserver !== 'undefined' && years.length > 0) {
					var setActive = function (year) {
						links.forEach(function (link) {
							link.classList.toggle('is-active', link.textContent.trim() === year);
						});
					};

					years.forEach(function (block) {
						new IntersectionObserver(function (entries) {
							entries.forEach(function (entry) {
								if (!entry.isIntersecting) {
									return;
								}
								setActive(entry.target.dataset.year);
							});
						}, {
							rootMargin: '-20% 0px -70% 0px',
							threshold: 0
						}).observe(block);
					});
				}
			}

			var downloadButton = timeline.querySelector('.pa-timeline-download');
			if (downloadButton) {
				downloadButton.addEventListener('click', function () {
					window.print();
				});
			}
		});
	});
})();

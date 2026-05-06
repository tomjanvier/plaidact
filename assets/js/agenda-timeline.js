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

			var nextBtn = timeline.querySelector('.pa-timeline-next-event');
			if (nextBtn) {
				nextBtn.addEventListener('click', function () {
					var nowTs = Math.floor(Date.now() / 1000);
					var events = [].slice.call(timeline.querySelectorAll('.pa-event[data-event-ts]'));
					var nearest = null;
					var minDelta = Number.POSITIVE_INFINITY;

					events.forEach(function (evt) {
						var ts = parseInt(evt.getAttribute('data-event-ts') || '0', 10);
						if (!ts) {
							return;
						}
						var delta = Math.abs(ts - nowTs);
						if (delta < minDelta) {
							minDelta = delta;
							nearest = evt;
						}
					});

					if (nearest) {
						nearest.scrollIntoView({ behavior: 'smooth', block: 'center' });
						nearest.classList.add('is-highlighted');
						setTimeout(function () { nearest.classList.remove('is-highlighted'); }, 1600);
					}
				});
			}

		});
	});
})();

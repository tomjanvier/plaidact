(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.plaidact-breves-list').forEach(function (list) {
			if (list.scrollWidth <= list.clientWidth) {
				return;
			}

			var speed = 0.55;
			var rafId = 0;
			var paused = false;

			var tick = function () {
				if (!paused) {
					list.scrollLeft += speed;
					if (list.scrollLeft >= list.scrollWidth - list.clientWidth - 1) {
						list.scrollLeft = 0;
					}
				}
				rafId = window.requestAnimationFrame(tick);
			};

			tick();

			var pause = function () {
				paused = true;
			};
			var resume = function () {
				paused = false;
			};

			list.addEventListener('mouseenter', pause);
			list.addEventListener('mouseleave', resume);
			list.addEventListener('focusin', pause);
			list.addEventListener('focusout', resume);

			window.addEventListener('beforeunload', function () {
				if (rafId) {
					window.cancelAnimationFrame(rafId);
				}
			});
		});

		document.querySelectorAll('.plaidact-breves-ticker').forEach(function (ticker) {
			var track = ticker.querySelector('.plaidact-breves-ticker__track');
			if (!track) {
				return;
			}

			var groups = track.querySelectorAll('.plaidact-breves-ticker__group');
			if (groups.length < 2) {
				return;
			}

			var firstGroup = groups[0];
			var secondGroup = groups[1];
			while (track.scrollWidth < ticker.clientWidth * 2) {
				secondGroup.insertAdjacentHTML('beforeend', firstGroup.innerHTML);
			}

			track.style.animation = 'none';

			var translateX = 0;
			var rafId = 0;
			var paused = false;
			var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			if (prefersReducedMotion) {
				return;
			}

			var speed = 0.7;
			var firstWidth = firstGroup.getBoundingClientRect().width;
			if (!firstWidth) {
				return;
			}

			var tickTicker = function () {
				if (!paused) {
					translateX += speed;
					if (translateX >= firstWidth) {
						translateX = 0;
					}
					track.style.transform = 'translate3d(' + (-translateX) + 'px,0,0)';
				}
				rafId = window.requestAnimationFrame(tickTicker);
			};

			tickTicker();

			var pause = function () {
				paused = true;
			};
			var resume = function () {
				paused = false;
			};

			ticker.addEventListener('mouseenter', pause);
			ticker.addEventListener('mouseleave', resume);
			ticker.addEventListener('focusin', pause);
			ticker.addEventListener('focusout', resume);

			window.addEventListener('resize', function () {
				firstWidth = firstGroup.getBoundingClientRect().width;
			});

			window.addEventListener('beforeunload', function () {
				if (rafId) {
					window.cancelAnimationFrame(rafId);
				}
			});
		});
	});
})();

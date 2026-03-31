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

			var applyDuration = function () {
				var pixelsPerSecond = 95;
				var duration = Math.max(18, Math.round(firstGroup.scrollWidth / pixelsPerSecond));
				track.style.animationDuration = duration + 's';
			};

			applyDuration();
			window.addEventListener('resize', applyDuration, { passive: true });
		});
	});
})();

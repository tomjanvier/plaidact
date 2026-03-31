(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
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
		});
	});
})();

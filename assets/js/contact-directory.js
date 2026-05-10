(function () {
	document.addEventListener('input', function (event) {
		if (!event.target.classList.contains('plaidact-fcd-search')) {
			return;
		}

		var root = event.target.closest('.plaidact-fcd');
		if (!root) {
			return;
		}

		var query = (event.target.value || '').toLowerCase();
		root.querySelectorAll('tbody tr').forEach(function (row) {
			row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
		});
	});
})();

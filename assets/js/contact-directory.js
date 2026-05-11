(function () {
	function filterRows(root) {
		var search = (root.querySelector('.plaidact-fcd-search')?.value || '').toLowerCase();
		var custom = (root.querySelector('.plaidact-fcd-filter-custom')?.value || '').toLowerCase();
		var meta = (root.querySelector('.plaidact-fcd-filter-meta')?.value || '').toLowerCase();

		root.querySelectorAll('tbody tr').forEach(function (row) {
			var matchesSearch = row.innerText.toLowerCase().includes(search);
			var matchesCustom = !custom || (row.dataset.custom || '').includes(custom);
			var matchesMeta = !meta || (row.dataset.meta || '').includes(meta);
			row.style.display = matchesSearch && matchesCustom && matchesMeta ? '' : 'none';
		});
	}

	document.addEventListener('input', function (event) {
		if (!event.target.closest('.plaidact-fcd-filters')) {
			return;
		}
		var root = event.target.closest('.plaidact-fcd');
		if (root) {
			filterRows(root);
		}
	});
})();

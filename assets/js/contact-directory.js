(function () {
	function filterRows(root) {
		var search = (root.querySelector('.plaidact-fcd-search')?.value || '').toLowerCase();
		var custom = root.querySelector('.plaidact-fcd-select-filter[data-filter="custom"]')?.value || '';
		var commission = root.querySelector('.plaidact-fcd-select-filter[data-filter="commission"]')?.value || '';
		var groupe = root.querySelector('.plaidact-fcd-select-filter[data-filter="groupe"]')?.value || '';

		root.querySelectorAll('tbody tr').forEach(function (row) {
			var matchesSearch = row.innerText.toLowerCase().includes(search);
			var matchesCustom = !custom || (row.dataset.custom || '') === custom;
			var matchesCommission = !commission || (row.dataset.commission || '') === commission;
			var matchesGroupe = !groupe || (row.dataset.groupe || '') === groupe;
			row.style.display = matchesSearch && matchesCustom && matchesCommission && matchesGroupe ? '' : 'none';
		});
	}

	function toggleColumns(root) {
		var indexByColumn = { groupe: 4, commission: 5, custom: 6, social: 7 };
		Object.keys(indexByColumn).forEach(function (column) {
			var checked = root.querySelector('.plaidact-fcd-column-toggle[data-column="' + column + '"]')?.checked;
			var visible = checked !== false;
			root.querySelectorAll('table tr').forEach(function (row) {
				if (row.children[indexByColumn[column]]) {
					row.children[indexByColumn[column]].style.display = visible ? '' : 'none';
				}
			});
		});
	}

	document.addEventListener('input', function (event) {
		var root = event.target.closest('.plaidact-fcd');
		if (!root) return;
		if (event.target.matches('.plaidact-fcd-search, .plaidact-fcd-select-filter')) filterRows(root);
	});

	document.addEventListener('change', function (event) {
		var root = event.target.closest('.plaidact-fcd');
		if (!root) return;
		if (event.target.matches('.plaidact-fcd-column-toggle')) toggleColumns(root);
		if (event.target.matches('.plaidact-fcd-select-filter')) filterRows(root);
	});

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.plaidact-fcd').forEach(function (root) {
			filterRows(root);
			toggleColumns(root);
		});
	});
})();

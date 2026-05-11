(function () {
	function filterRows(root) {
		var search = (root.querySelector('.plaidact-fcd-search')?.value || '').toLowerCase();
		var custom = (root.querySelector('.plaidact-fcd-filter-custom')?.value || '').toLowerCase();
		var institution = (root.querySelector('.plaidact-fcd-filter-institution')?.value || '').toLowerCase();
		var groupe = (root.querySelector('.plaidact-fcd-filter-groupe')?.value || '').toLowerCase();
		var commission = (root.querySelector('.plaidact-fcd-filter-commission')?.value || '').toLowerCase();

		root.querySelectorAll('tbody tr').forEach(function (row) {
			var matchesSearch = row.innerText.toLowerCase().includes(search);
			var matchesCustom = !custom || (row.dataset.custom || '').includes(custom);
			var matchesInstitution = !institution || (row.dataset.institution || '').includes(institution);
			var matchesGroupe = !groupe || (row.dataset.groupe || '').includes(groupe);
			var matchesCommission = !commission || (row.dataset.commission || '').includes(commission);
			row.style.display = matchesSearch && matchesCustom && matchesInstitution && matchesGroupe && matchesCommission ? '' : 'none';
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

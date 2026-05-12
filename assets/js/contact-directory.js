(function () {
	function filterRows(root) {
		var search = (root.querySelector('.plaidact-fcd-search')?.value || '').toLowerCase();
		var activeCustom = root.querySelector('.plaidact-fcd-filter-buttons[data-filter="custom"] .plaidact-fcd-filter-btn.is-active')?.dataset.value || '';
		var activeCommission = root.querySelector('.plaidact-fcd-filter-buttons[data-filter="commission"] .plaidact-fcd-filter-btn.is-active')?.dataset.value || '';
		var activeGroupe = root.querySelector('.plaidact-fcd-filter-buttons[data-filter="groupe"] .plaidact-fcd-filter-btn.is-active')?.dataset.value || '';

		root.querySelectorAll('tbody tr').forEach(function (row) {
			var matchesSearch = row.innerText.toLowerCase().includes(search);
			var matchesCustom = !activeCustom || (row.dataset.custom || '').includes(activeCustom);
			var matchesCommission = !activeCommission || (row.dataset.commission || '').includes(activeCommission);
			var matchesGroupe = !activeGroupe || (row.dataset.groupe || '').includes(activeGroupe);
			row.style.display = matchesSearch && matchesCustom && matchesCommission && matchesGroupe ? '' : 'none';
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

	document.addEventListener('click', function (event) {
		var button = event.target.closest('.plaidact-fcd-filter-btn');
		if (!button) {
			return;
		}
		var group = button.closest('.plaidact-fcd-filter-buttons');
		if (!group) {
			return;
		}
		group.querySelectorAll('.plaidact-fcd-filter-btn').forEach(function (btn) {
			btn.classList.remove('is-active');
		});
		button.classList.add('is-active');

		var root = button.closest('.plaidact-fcd');
		if (root) {
			filterRows(root);
		}
	});
})();

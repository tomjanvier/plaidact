(function () {
	const modal = document.querySelector('[data-fcd-modal]');
	const form = document.querySelector('[data-fcd-form]');
	const downloadButton = document.querySelector('.plaidact-fcd-download-btn');
	const closeButton = document.querySelector('[data-fcd-close]');
	const message = document.querySelector('[data-fcd-message]');

	if (!modal || !form || !downloadButton || typeof PlaidactFluentcrmDirectory === 'undefined') {
		return;
	}

	downloadButton.addEventListener('click', function () {
		modal.hidden = false;
	});

	if (closeButton) {
		closeButton.addEventListener('click', function () {
			modal.hidden = true;
		});
	}

	form.addEventListener('submit', function (event) {
		event.preventDefault();
		const formData = new FormData(form);
		formData.append('action', 'plaidact_fluentcrm_subscribe');
		formData.append('nonce', PlaidactFluentcrmDirectory.subscribeNonce);

		fetch(PlaidactFluentcrmDirectory.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		})
			.then((response) => response.json())
			.then((data) => {
				if (!data.success) {
					message.textContent = data?.data?.message || 'Erreur.';
					return;
				}
				message.textContent = data?.data?.message || 'Inscription réussie.';
				modal.hidden = true;
				window.location.href = downloadButton.dataset.downloadUrl || PlaidactFluentcrmDirectory.downloadUrl;
			})
			.catch(() => {
				message.textContent = 'Erreur réseau.';
			});
	});
})();

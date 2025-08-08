document.addEventListener('DOMContentLoaded', () => {
	const hasLocalStorage =
		typeof window !== 'undefined' && 'localStorage' in window;

	const setDismissed = (id) => {
		if (hasLocalStorage) {
			window.localStorage.setItem(`dismissed-${id}`, 'true');
		}
	};

	const isDismissed = (id) =>
		hasLocalStorage && window.localStorage.getItem(`dismissed-${id}`);

	const dismissWithAnimation = (el, alertId) => {
		setDismissed(alertId);

		// Trigger exit animation
		el.classList.add('is-exiting');

		const onEnd = () => {
			el.style.display = 'none'; // or el.remove();
			el.removeEventListener('transitionend', onEnd);
		};

		el.addEventListener('transitionend', onEnd);
		// Fallback in case the transitionend doesn't fire
		setTimeout(onEnd, 400);
	};

	document.querySelectorAll('[data-alert-id]').forEach((el) => {
		// Ensure a consistent class on the wrapper for styling
		el.classList.add('alert-block');

		const alertId = el.dataset.alertId;

		// If previously dismissed, hide immediately and skip fetching
		if (isDismissed(alertId)) {
			el.style.display = 'none';
			return;
		}

		// Fetch and render alert
		fetch('/wp-json/bna/v1/alerts')
			.then((res) => res.json())
			.then((alerts) => {
				const alert = alerts.find((a) => a.id === alertId);
				if (alert) {
					const bodyEl = el.querySelector('.alert-body');
					if (bodyEl) {
						bodyEl.textContent = alert.body;
					}
					el.classList.add(`alert-${alert.type || 'info'}`);
				}
			})
			.catch(() => {
				const bodyEl = el.querySelector('.alert-body');
				if (bodyEl) {
					bodyEl.textContent = 'Unable to load alert.';
				}
				el.classList.add('alert-error');
			})
			.finally(() => {
				// Fade/slide in after layout is ready
				window.requestAnimationFrame(() =>
					el.classList.add('is-mounted')
				);
			});

		// Bind dismiss button inside this alert
		const btn = el.querySelector('.alert-dismiss');
		if (btn) {
			btn.addEventListener('click', () =>
				dismissWithAnimation(el, alertId)
			);
		}
	});
});

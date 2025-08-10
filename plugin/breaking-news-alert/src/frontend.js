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

		// Bind dismiss button inside this alert
		const btn = el.querySelector('.alert-dismiss');
		if (btn) {
			btn.addEventListener('click', () =>
				dismissWithAnimation(el, alertId)
			);
		}
	});

	const slides = document.querySelectorAll('.bna-alert-slide');
	let current = 0;

	function rotateAlerts() {
		slides.forEach((slide, i) => {
			slide.style.display = i === current ? 'block' : 'none';
		});
		current = (current + 1) % slides.length;
	}

	setInterval(rotateAlerts, 5000); // every 5 sec
	rotateAlerts(); // init
});

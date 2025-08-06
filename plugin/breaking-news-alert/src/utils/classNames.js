export function getAlertClasses(alertType, isDismissible) {
	return `alert alert-${alertType || 'info'} ${
		isDismissible ? 'is-dismissible' : ''
	}`;
}

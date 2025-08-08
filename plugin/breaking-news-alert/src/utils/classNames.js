import classNames from 'classnames';

export function getAlertClasses(alertType, isDismissible, isMounted) {
	return classNames('wp-block-bna-alert', {
		[`alert-${alertType || 'info'}`]: true,
		'is-dismissible': isDismissible,
		'is-mounted': isMounted,
	});
}

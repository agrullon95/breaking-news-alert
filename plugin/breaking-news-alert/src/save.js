import { useBlockProps, RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { getAlertClasses } from './utils/classNames';

export default function Save({ attributes }) {
	const { message, alertType, isDismissible } = attributes;
	const blockProps = useBlockProps.save({
		className: getAlertClasses(alertType, isDismissible),
	});

	return (
		<div {...blockProps}>
			<RichText.Content
				tagName="div"
				className="alert-message"
				value={message}
			/>
			{isDismissible && (
				<button
					className="alert-close"
					type="button"
					aria-label={__('Dismiss alert', 'bna')}
				>
					close button
				</button>
			)}
		</div>
	);
}

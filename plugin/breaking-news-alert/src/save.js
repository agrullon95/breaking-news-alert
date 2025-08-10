import { useBlockProps } from '@wordpress/block-editor';
import { getAlertClasses } from './utils/classNames';

export default function Save({ attributes }) {
	const { type, isDismissible, uniqueId } = attributes;
	const blockProps = useBlockProps.save({
		className: getAlertClasses(type, isDismissible, true),
		id: `alert-${uniqueId}`,
		'data-alert-id': uniqueId,
	});

	return (
		<div {...blockProps}>
			<div className="alert-body">
				<p className="alert-message"></p>
			</div>
			{isDismissible && (
				<button
					className="alert-dismiss"
					type="button"
					aria-label="Dismiss alert"
				>
					<svg
						width="16"
						height="16"
						viewBox="0 0 16 16"
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
					>
						<path
							d="M4 4L12 12M12 4L4 12"
							stroke="currentColor"
							strokeWidth="2"
							strokeLinecap="round"
						/>
					</svg>
				</button>
			)}
		</div>
	);
}

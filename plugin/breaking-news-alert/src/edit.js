import { __ } from '@wordpress/i18n';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { getAlertClasses } from './utils/classNames';
import { useEffect } from '@wordpress/element';

export default function Edit({ attributes, setAttributes, clientId }) {
	const { type, isDismissible, uniqueId, displayGlobally } = attributes;

	const blockProps = useBlockProps({
		className: getAlertClasses(type, isDismissible, true),
		id: `alert-${uniqueId}`,
		'data-alert-id': uniqueId,
	});

	useEffect(() => {
		if (!uniqueId) {
			setAttributes({ uniqueId: clientId });
		}
	}, [uniqueId, clientId, setAttributes]);

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title={__('Alert settings', 'bna')}>
					<ToggleControl
						__nextHasNoMarginBottom
						label={__('Display as Global Banner', 'bna')}
						checked={displayGlobally}
						onChange={(newDisplayValue) =>
							setAttributes({ displayGlobally: newDisplayValue })
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={__('Dismissible', 'bna')}
						checked={isDismissible}
						onChange={(newIsDismissibleValue) =>
							setAttributes({
								isDismissible: newIsDismissibleValue,
							})
						}
						disabled={displayGlobally}
						help={
							displayGlobally
								? __(
										'Dismiss is disabled when global banner is active.',
										'bna'
									)
								: __('Enable close button on alert.', 'bna')
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div className="alert-block" data-alert-id={uniqueId}>
				<div className="alert-body">
					<p className="alert-message">
						{__('Alert message will be shown here', 'bna')}
					</p>
				</div>
				{isDismissible && (
					<button
						className="alert-dismiss"
						type="button"
						aria-label={__('Dismiss alert', 'bna')}
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
		</div>
	);
}

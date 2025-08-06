import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import { getAlertClasses } from './utils/classNames';

export default function Edit({ attributes, setAttributes }) {
	const { message, alertType, isDismissible } = attributes;

	const blockProps = useBlockProps({
		className: getAlertClasses(alertType, isDismissible),
	});

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title={__('Alert settings', 'bna')}>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={__('Alert Type', 'bna')}
						value={alertType}
						options={[
							{ label: __('Info', 'bna'), value: 'info' },
							{ label: __('Warning', 'bna'), value: 'warning' },
							{ label: __('Error', 'bna'), value: 'error' },
						]}
						onChange={(newAlertType) =>
							setAttributes({ alertType: newAlertType })
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
					/>
				</PanelBody>
			</InspectorControls>
			<RichText
				className="alert-message"
				tagName="div"
				value={message}
				onChange={(msg) => setAttributes({ message: msg })}
				placeholder={__('Enter breaking news…', 'bna')}
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

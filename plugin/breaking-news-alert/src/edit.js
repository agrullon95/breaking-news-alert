import { __ } from '@wordpress/i18n';
import { PanelBody, Spinner, ToggleControl } from '@wordpress/components';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { getAlertClasses } from './utils/classNames';
import { useState, useEffect, useRef } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export default function Edit({ attributes, setAttributes, clientId }) {
	const { alertType, isDismissible, uniqueId } = attributes;

	const blockProps = useBlockProps({
		className: getAlertClasses(alertType, isDismissible, true),
		id: `alert-${uniqueId}`,
		'data-alert-id': uniqueId,
	});

	const [showPreview, setShowPreview] = useState(false);
	const [loading, setLoading] = useState(true);
	const [alerts, setAlerts] = useState([]);
	const [currentAlert, setCurrentAlert] = useState(alerts[0]);

	const hasRun = useRef(false);
	const lastFetchTime = useRef(0);

	const RATE_LIMIT_MS = 10000; // 10 seconds

	useEffect(() => {
		const now = Date.now();

		if (
			showPreview &&
			!hasRun.current &&
			now - lastFetchTime.current > RATE_LIMIT_MS
		) {
			hasRun.current = true;
			lastFetchTime.current = now;

			apiFetch({ path: '/bna/v1/alerts' })
				.then((data) => {
					setAlerts(data);
					setCurrentAlert(data[0]);
				})
				.catch(() => {
					setAlerts([]);
					setCurrentAlert(null);
				})
				.finally(() => {
					setLoading(false);
				});
		}
	}, [showPreview]);

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
						label={__('Dismissible', 'bna')}
						checked={isDismissible}
						onChange={(newIsDismissibleValue) =>
							setAttributes({
								isDismissible: newIsDismissibleValue,
							})
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={__('Show preview', 'bna')}
						checked={showPreview}
						onChange={() => {
							setShowPreview(!showPreview);
						}}
					/>
				</PanelBody>
			</InspectorControls>

			<div className="alert-block" data-alert-id={uniqueId}>
				<div className="alert-body">
					<p className="alert-message">
						{(() => {
							if (!showPreview) {
								return __(
									'Alert message will be shown here',
									'bna'
								);
							}
							if (loading) {
								return <Spinner />;
							}
							if (alerts.length > 0 && currentAlert) {
								return currentAlert.message;
							}
							return __('No alerts available.', 'bna');
						})()}
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

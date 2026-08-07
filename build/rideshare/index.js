(() => {
	'use strict';

	const { __ } = window.wp.i18n;
	const { useBlockProps } = window.wp.blockEditor;
	const { registerBlockType } = window.wp.blocks;
	const { createElement } = window.wp.element;

	const metadata = {
		apiVersion: 3,
		name: 'create-block/rideshare',
		title: 'Rideshare Widget',
		category: 'widgets',
		icon: 'car',
		description: 'Widget for rideshare users to search or offer rides.',
		supports: {
			html: false,
		},
		textdomain: 'rideshare',
	};

	function Edit() {
		return createElement(
			'div',
			useBlockProps({ className: 'rideshare-riding-widget' }),
			createElement('h2', null, __('Ride sharing', 'rideshare')),
			createElement(
				'div',
				{ className: 'rideshare-riding-widget__preview' },
				createElement(
					'p',
					null,
					__(
						'Frontend widget for rideshare users who want to search for or offer rides.',
						'rideshare'
					)
				),
				createElement(
					'p',
					null,
					__(
						'Destinations are loaded from Stop_Model and requests are stored in Riding_Model.',
						'rideshare'
					)
				)
			)
		);
	}

	registerBlockType(metadata.name, {
		edit: Edit,
		save: () => null,
	});
})();

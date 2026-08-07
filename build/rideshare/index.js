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
			createElement('h2', null, __('Mitfahrgelegenheit', 'rideshare')),
			createElement(
				'div',
				{ className: 'rideshare-riding-widget__preview' },
				createElement(
					'p',
					null,
					__(
						'Frontend-Widget für Rideshare-Nutzer, die Mitfahrgelegenheiten suchen oder anbieten möchten.',
						'rideshare'
					)
				),
				createElement(
					'p',
					null,
					__(
						'Ziele werden aus Stop_Model geladen und Anfragen in Riding_Model gespeichert.',
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

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit() {
	return (
		<div { ...useBlockProps( { className: 'rideshare-riding-widget' } ) }>
			<h2>{ __( 'Mitfahrgelegenheit', 'rideshare' ) }</h2>
			<div className="rideshare-riding-widget__preview">
				<p>{ __( 'Frontend-Widget für Rideshare-Nutzer, die Mitfahrgelegenheiten suchen oder anbieten möchten.', 'rideshare' ) }</p>
				<p>{ __( 'Ziele werden aus Stop_Model geladen und Anfragen in Riding_Model gespeichert.', 'rideshare' ) }</p>
			</div>
		</div>
	);
}

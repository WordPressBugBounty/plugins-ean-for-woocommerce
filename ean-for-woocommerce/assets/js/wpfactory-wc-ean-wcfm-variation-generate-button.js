/**
 * EAN for WooCommerce - WCFM Variation Generate Button JS
 *
 * @version 5.5.6
 * @since   5.5.5
 *
 * @author  WPFactory
 */

jQuery( document ).ready( function () {
	jQuery( '.variation_id' ).each( function () {
		var variation_id = jQuery( this ).val();
		var input_id     = jQuery( this )
			.attr( 'id' )
			.replace(
				'variations_id_',
				'variations_wcfm_' + wpfactoryWCEANWCFMVariationGenerateButton.eanKey + '_'
			);
		jQuery( '#' + input_id ).after(
			'<p class="wpfactory_wc_ean_generate_button_wrapper">' +
				'<button' +
					' type="button"' +
					' class="button wpfactory_wc_ean_generate_ajax wcfm_ele variable variable-subscription pw-gift-card"' +
					' data-product="' + variation_id + '"' +
					' data-input="' + input_id + '"' +
				'>' +
					wpfactoryWCEANWCFMVariationGenerateButton.buttonLabel +
				'</button>' +
			'</p>'
		);
	} );
} );

/**
 * EAN for WooCommerce - Generate Button JS
 *
 * @version 5.5.9
 * @since   5.5.5
 *
 * @author  WPFactory
 *
 * @todo    (dev) recheck `return false;`
 * @todo    (dev) recheck `ajaxurl`
 */

jQuery( document ).ready( function () {
	jQuery( 'body' ).on( 'click', '.wpfactory_wc_ean_generate_ajax', function () {
		var product = jQuery( this ).data( 'product' )
		var input = jQuery( this ).data( 'input' );
		var generate_action = jQuery( this ).data( 'generate-action' );
		jQuery( '#spinner-' + input ).addClass( 'is-active' );
		var data = {
			'action': 'wpfactory_wc_ean_generate_ajax',
			'generate_action': generate_action,
			'product': product,
			'input': input,
			'nonce': wpfactoryWCEANGenerateButton.nonce,
		};
		jQuery.post( ajaxurl, data, function( response ) {
			if ( response ) {
				jQuery( '#' + data['input'] ).val( response ).trigger( 'change' );
			}
			jQuery( '#spinner-' + data['input'] ).removeClass( 'is-active' );
		} );
		return false;
	} );
} );

/**
 * EAN for WooCommerce - Variations
 *
 * @version 5.5.6
 * @since   1.0.0
 *
 * @author  WPFactory
 */

jQuery( document ).ready( function() {

	var variations_form = jQuery( wpfactory_wc_ean_variations_obj.variations_form );
	if ( jQuery( 'body' ).hasClass( 'single-product' ) && variations_form.length > 0 ) { // is single variable product page
		var ean = variations_form.closest( wpfactory_wc_ean_variations_obj.variations_form_closest ).find( '.ean' );
		if ( ean.length > 0 ) { // do ean
			var ean_reset  = ean.text();
			var ean_parent = ean.parent();
			wpfactory_wc_ean_maybe_hide();
			wpfactory_wc_ean_variations();
		}
	}

	/**
	 * wpfactory_wc_ean_variations
	 *
	 * @version 5.5.6
	 * @since   1.0.0
	 */
	function wpfactory_wc_ean_variations() {
		variations_form.on( 'found_variation', function( event, variation ) {
			if ( variation.ean ) {
				wpfactory_wc_ean_show( variation );
			} else {
				wpfactory_wc_ean_reset();
			}
		} );
		variations_form.on( 'reset_data', wpfactory_wc_ean_reset );
	}

	/**
	 * wpfactory_wc_ean_show
	 *
	 * @version 5.5.6
	 * @since   1.0.0
	 */
	function wpfactory_wc_ean_show( variation ) {
		if ( variation.ean ) {
			ean.text( variation.ean );
			ean_parent.show();
		}
	}

	/**
	 * wpfactory_wc_ean_reset
	 *
	 * @version 5.5.6
	 * @since   1.0.0
	 */
	function wpfactory_wc_ean_reset() {
		if ( ean_reset !== ean.text() ) {
			ean.text( ean_reset );
		}
		wpfactory_wc_ean_maybe_hide();
	}

	/**
	 * wpfactory_wc_ean_maybe_hide
	 *
	 * @version 5.5.6
	 * @since   1.0.0
	 *
	 * @todo    [later] `do_hide`
	 */
	function wpfactory_wc_ean_maybe_hide() {
		if ( '' == ean_reset ) {
			ean_parent.hide();
		}
	}

} );

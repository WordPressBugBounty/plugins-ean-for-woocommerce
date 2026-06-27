/**
 * EAN for WooCommerce - Quick Edit JS
 *
 * @version 5.5.6
 * @since   5.5.5
 *
 * @author  WPFactory
 */

jQuery(
	function ( $ ) {
		$( '#the-list' ).on(
			'click',
			'.editinline',
			function () {
				var post_id = $( this ).closest( 'tr' ).attr( 'id' );
				post_id = post_id.replace( 'post-', '' );
				var inline_data = $( '#wpfactory_wc_ean_inline_' + post_id );
				var ean = inline_data.find( '.wpfactory_wc_ean_quick_edit' ).text();
				$( 'input[name="_wpfactory_ean_qb"]', '.inline-edit-row' ).val( ean );
			}
		);
	}
);

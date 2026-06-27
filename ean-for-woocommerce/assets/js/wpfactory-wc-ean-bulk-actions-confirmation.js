/**
 * EAN for WooCommerce - Bulk Actions Confirmation JS
 *
 * @version 5.5.6
 * @since   5.5.5
 *
 * @author  WPFactory
 */

jQuery( '#doaction' ).on( 'click', function () {
	if ( -1 != wpfactoryWCEANBulkActionsConfirmation.confirmIDs.indexOf( jQuery( 'select[name="action"]' ).val() ) ) {
		if ( ! confirm( wpfactoryWCEANBulkActionsConfirmation.message ) ) {
			return false;
		}
	}
} );

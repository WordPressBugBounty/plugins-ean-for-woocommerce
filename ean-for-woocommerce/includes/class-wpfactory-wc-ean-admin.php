<?php
/**
 * EAN for WooCommerce - Admin Class
 *
 * @version 5.5.6
 * @since   3.6.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_EAN_Admin' ) ) :

class WPFactory_WC_EAN_Admin {

	/**
	 * Constructor.
	 *
	 * @version 3.6.0
	 * @since   3.6.0
	 */
	function __construct() {
		if ( 'no' === get_option( 'alg_wc_ean_duplicate_product', 'yes' ) ) {
			add_filter( 'woocommerce_duplicate_product_exclude_meta', array( $this, 'duplicate_product_exclude_meta' ) );
		}
	}

	/**
	 * duplicate_product_exclude_meta.
	 *
	 * @version 5.5.6
	 * @since   3.6.0
	 */
	function duplicate_product_exclude_meta( $meta ) {
		$meta[] = wpfactory_wc_ean()->core->ean_key;
		return $meta;
	}

}

endif;

return new WPFactory_WC_EAN_Admin();

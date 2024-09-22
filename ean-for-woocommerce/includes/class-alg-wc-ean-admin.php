<?php
/**
 * EAN for WooCommerce - Admin Class
 *
 * @version 3.6.0
 * @since   3.6.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Admin' ) ) :

class Alg_WC_EAN_Admin {

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
	 * @version 3.6.0
	 * @since   3.6.0
	 */
	function duplicate_product_exclude_meta( $meta ) {
		$meta[] = alg_wc_ean()->core->ean_key;
		return $meta;
	}

}

endif;

return new Alg_WC_EAN_Admin();

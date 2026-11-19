<?php
/**
 * EAN for WooCommerce - Admin Class
 *
 * @version 5.5.6
 * @since   3.6.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_EAN
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_EAN_Admin' ) ) :

	/**
	 * WPFactory_WC_EAN_Admin class.
	 *
	 * @version 5.5.6
	 * @since   3.6.0
	 */
	class WPFactory_WC_EAN_Admin {

		/**
		 * Constructor.
		 *
		 * @version 3.6.0
		 * @since   3.6.0
		 */
		public function __construct() {
			if ( 'no' === get_option( 'alg_wc_ean_duplicate_product', 'yes' ) ) {
				add_filter(
					'woocommerce_duplicate_product_exclude_meta',
					array( $this, 'duplicate_product_exclude_meta' )
				);
			}
		}

		/**
		 * Duplicate product exclude meta.
		 *
		 * @version 5.5.6
		 * @since   3.6.0
		 *
		 * @param array $meta Meta keys.
		 *
		 * @return array Modified meta keys.
		 */
		public function duplicate_product_exclude_meta( $meta ) {
			$meta[] = wpfactory_wc_ean()->core->ean_key;
			return $meta;
		}
	}

endif;

return new WPFactory_WC_EAN_Admin();

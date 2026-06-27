<?php
/*
Plugin Name: EAN Barcode Generator for WooCommerce: UPC, ISBN & GTIN Inventory
Plugin URI: https://wpfactory.com/item/ean-for-woocommerce/
Description: Manage product GTIN (EAN, UPC, ISBN, etc.) in WooCommerce. Beautifully.
Version: 5.5.7
Author: WPFactory
Author URI: https://wpfactory.com
Requires at least: 5.7
Text Domain: ean-for-woocommerce
Domain Path: /langs
WC tested up to: 10.9
Requires Plugins: woocommerce
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) || exit;

if ( 'ean-for-woocommerce.php' === basename( __FILE__ ) ) {
	if ( ! function_exists( 'wpfactory_wc_ean_is_pro_activated' ) ) {
		/**
		 * Check if Pro plugin version is activated.
		 *
		 * @version 5.5.6
		 * @since   2.2.0
		 */
		function wpfactory_wc_ean_is_pro_activated() {
			$plugin = 'ean-for-woocommerce-pro/ean-for-woocommerce-pro.php';
			return (
				in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
				(
					is_multisite() &&
					array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) )
				)
			);
		}
	}
	if ( wpfactory_wc_ean_is_pro_activated() ) {
		defined( 'WPFACTORY_WC_EAN_FILE_FREE' ) || define( 'WPFACTORY_WC_EAN_FILE_FREE', __FILE__ );
		return;
	}
}

/**
 * WPFACTORY_WC_EAN_VERSION.
 *
 * @version 5.5.6
 * @since   1.0.0
 */
defined( 'WPFACTORY_WC_EAN_VERSION' ) || define( 'WPFACTORY_WC_EAN_VERSION', '5.5.7' );

/**
 * WPFACTORY_WC_EAN_FILE.
 *
 * @version 5.5.6
 * @since   1.0.0
 */
defined( 'WPFACTORY_WC_EAN_FILE' ) || define( 'WPFACTORY_WC_EAN_FILE', __FILE__ );

/**
 * Main class.
 *
 * @version 5.5.6
 * @since   1.0.0
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpfactory-wc-ean.php';

if ( ! function_exists( 'wpfactory_wc_ean' ) ) {
	/**
	 * Returns the main instance of WPFactory_WC_EAN to prevent the need to use globals.
	 *
	 * @version 5.5.6
	 * @since   1.0.0
	 */
	function wpfactory_wc_ean() {
		return WPFactory_WC_EAN::instance();
	}
}

/**
 * plugins_loaded.
 *
 * @version 5.5.6
 * @since   1.0.0
 */
add_action( 'plugins_loaded', 'wpfactory_wc_ean' );

if ( 'ean-for-woocommerce-pro.php' === basename( WPFACTORY_WC_EAN_FILE ) ) {
	/**
	 * Pro Init.
	 *
	 * @version 5.5.6
	 * @since   5.4.5
	 */
	require_once plugin_dir_path( __FILE__ ) . 'includes/pro/wpfactory-wc-ean-init-pro.php';
}

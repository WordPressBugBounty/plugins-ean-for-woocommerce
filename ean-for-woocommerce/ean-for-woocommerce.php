<?php
/*
Plugin Name: EAN Barcode Generator for WooCommerce: UPC, ISBN & GTIN Inventory
Plugin URI: https://wpfactory.com/item/ean-for-woocommerce/
Description: Manage product GTIN (EAN, UPC, ISBN, etc.) in WooCommerce. Beautifully.
Version: 5.5.0
Author: WPFactory
Author URI: https://wpfactory.com
Requires at least: 4.4
Text Domain: ean-for-woocommerce
Domain Path: /langs
WC tested up to: 9.9
Requires Plugins: woocommerce
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) || exit;

if ( 'ean-for-woocommerce.php' === basename( __FILE__ ) ) {
	/**
	 * Check if Pro plugin version is activated.
	 *
	 * @version 4.7.3
	 * @since   2.2.0
	 */
	$plugin = 'ean-for-woocommerce-pro/ean-for-woocommerce-pro.php';
	if (
		in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
		(
			is_multisite() &&
			array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) )
		)
	) {
		defined( 'ALG_WC_EAN_FILE_FREE' ) || define( 'ALG_WC_EAN_FILE_FREE', __FILE__ );
		return;
	}
}

defined( 'ALG_WC_EAN_VERSION' ) || define( 'ALG_WC_EAN_VERSION', '5.5.0' );

defined( 'ALG_WC_EAN_FILE' ) || define( 'ALG_WC_EAN_FILE', __FILE__ );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-alg-wc-ean.php';

if ( ! function_exists( 'alg_wc_ean' ) ) {
	/**
	 * Returns the main instance of Alg_WC_EAN to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_ean() {
		return Alg_WC_EAN::instance();
	}
}

add_action( 'plugins_loaded', 'alg_wc_ean' );

require_once plugin_dir_path( __FILE__ ) . 'includes/alg-wc-ean-init.php';

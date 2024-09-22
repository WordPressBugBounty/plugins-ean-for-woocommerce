<?php
/**
 * EAN for WooCommerce - Settings Import/Export Class
 *
 * @version 3.9.0
 * @since   3.9.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Settings_Import_Export' ) ) :

class Alg_WC_EAN_Settings_Import_Export {

	/**
	 * Constructor.
	 *
	 * @version 3.9.0
	 * @since   3.9.0
	 */
	function __construct() {
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'import_settings' ) );
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'export_settings' ) );
	}

	/**
	 * import_settings.
	 *
	 * @version 3.8.0
	 * @since   3.1.0
	 *
	 * @todo    [now] [!!] (feature) separate "Reset all settings" tool?
	 * @todo    [maybe] (dev) better data validation?
	 */
	function import_settings() {
		if ( ! empty( $_FILES['alg_wc_ean_import_settings']['tmp_name'] ) ) {
			$content = file_get_contents( $_FILES['alg_wc_ean_import_settings']['tmp_name'] );
			$content = json_decode( $content, true );
			if ( JSON_ERROR_NONE === json_last_error() ) {
				// Reset
				global $wpdb;
				$deleted = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'alg_wc_ean%'" );
				// Import
				$counter = 0;
				foreach ( $content as $row ) {
					if ( 'alg_wc_ean_version' !== $row['option_name'] ) {
						if ( update_option( $row['option_name'], $row['option_value'] ) ) {
							$counter++;
						}
					}
				}
				if ( is_callable( array( 'WC_Admin_Settings', 'add_message' ) ) ) {
					WC_Admin_Settings::add_message( sprintf( __( 'Settings imported (%d option(s) deleted, %d option(s) updated).', 'ean-for-woocommerce' ), $deleted, $counter ) );
				}
			} elseif ( is_callable( array( 'WC_Admin_Settings', 'add_message' ) ) ) {
				WC_Admin_Settings::add_message( sprintf( __( 'Import file error: %s', 'ean-for-woocommerce' ), json_last_error_msg() ) );
			}
		}
	}

	/**
	 * export_settings.
	 *
	 * @version 3.8.0
	 * @since   3.1.0
	 *
	 * @todo    [now] [!] (dev) remove `length`?
	 * @todo    [now] [!] (dev) recheck headers?
	 * @todo    [now] (dev) redirect page?
	 */
	function export_settings() {
		if ( 'yes' === get_option( 'alg_wc_ean_export_settings', 'no' ) ) {
			update_option( 'alg_wc_ean_export_settings', 'no' );
			global $wpdb;
			$content = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'alg_wc_ean%'" );
			foreach ( $content as &$row ) {
				$row->option_value = maybe_unserialize( $row->option_value );
			}
			$content = json_encode( $content );
			$length  = strlen( $content );
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: text/plain' );
			header( 'Content-Disposition: attachment; filename=alg-wc-ean-settings.txt' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Content-Length: ' . $length );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Expires: 0' );
			header( 'Pragma: public' );
			echo $content;
			exit;
		}
	}

}

endif;

return new Alg_WC_EAN_Settings_Import_Export();

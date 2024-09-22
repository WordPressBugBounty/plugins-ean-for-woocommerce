<?php
/**
 * EAN for WooCommerce - Settings Import/Export/Reset Class
 *
 * @version 4.9.0
 * @since   3.9.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Manage_Settings' ) ) :

class Alg_WC_EAN_Manage_Settings {

	/**
	 * Constructor.
	 *
	 * @version 4.0.0
	 * @since   3.9.0
	 */
	function __construct() {
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'export_settings' ) );
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'import_settings' ) );
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'reset_settings' ) );
	}

	/**
	 * reset_settings.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function reset_settings() {
		if ( 'yes' === get_option( 'alg_wc_ean_reset_settings', 'no' ) ) {
			update_option( 'alg_wc_ean_reset_settings', 'no' );
			global $wpdb;
			$deleted = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'alg_wc_ean%'" );
			if ( is_callable( array( 'WC_Admin_Settings', 'add_message' ) ) ) {
				WC_Admin_Settings::add_message( sprintf( __( 'Settings reset: %d option(s) deleted.', 'ean-for-woocommerce' ), $deleted ) );
			}
		}
	}

	/**
	 * import_settings.
	 *
	 * @version 4.9.0
	 * @since   3.1.0
	 *
	 * @todo    (dev) better data validation?
	 */
	function import_settings() {
		if ( ! empty( $_FILES['alg_wc_ean_import_settings']['tmp_name'] ) ) {
			$content = file_get_contents( $_FILES['alg_wc_ean_import_settings']['tmp_name'] );
			$content = json_decode( $content, true );
			if ( JSON_ERROR_NONE === json_last_error() ) {
				$counter = 0;
				foreach ( $content as $row ) {
					if (
						'alg_wc_ean' === substr( $row['option_name'], 0, 10 ) &&
						! in_array( $row['option_name'], array( 'alg_wc_ean_version', 'alg_wc_ean_export_settings', 'alg_wc_ean_reset_settings' ) )
					) {
						if ( update_option( $row['option_name'], $row['option_value'] ) ) {
							$counter++;
						}
					}
				}
				if ( is_callable( array( 'WC_Admin_Settings', 'add_message' ) ) ) {
					WC_Admin_Settings::add_message( sprintf( __( 'Settings imported: %d option(s) updated.', 'ean-for-woocommerce' ), $counter ) );
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
	 * @todo    (dev) remove `length`?
	 * @todo    (dev) recheck headers?
	 * @todo    (dev) redirect page?
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

return new Alg_WC_EAN_Manage_Settings();

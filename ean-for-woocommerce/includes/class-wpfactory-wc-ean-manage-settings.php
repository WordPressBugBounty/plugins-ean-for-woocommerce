<?php
/**
 * EAN for WooCommerce - Settings Import/Export/Reset Class
 *
 * @version 5.6.1
 * @since   3.9.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_EAN
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_EAN_Manage_Settings' ) ) :

	/**
	 * WPFactory_WC_EAN_Manage_Settings class.
	 *
	 * @version 5.6.1
	 * @since   3.9.0
	 */
	class WPFactory_WC_EAN_Manage_Settings {

		/**
		 * Constructor.
		 *
		 * @version 4.0.0
		 * @since   3.9.0
		 */
		public function __construct() {
			add_action( 'alg_wc_ean_settings_saved', array( $this, 'export_settings' ) );
			add_action( 'alg_wc_ean_settings_saved', array( $this, 'import_settings' ) );
			add_action( 'alg_wc_ean_settings_saved', array( $this, 'reset_settings' ) );
		}

		/**
		 * Reset settings.
		 *
		 * @version 4.0.0
		 * @since   4.0.0
		 */
		public function reset_settings() {
			if ( 'yes' === get_option( 'alg_wc_ean_reset_settings', 'no' ) ) {
				update_option( 'alg_wc_ean_reset_settings', 'no' );
				global $wpdb;
				$deleted = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'alg_wc_ean%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				if ( is_callable( array( 'WC_Admin_Settings', 'add_message' ) ) ) {
					WC_Admin_Settings::add_message(
						sprintf(
							/* Translators: %s: Number of options. */
							__( 'Settings reset: %d option(s) deleted.', 'ean-for-woocommerce' ),
							$deleted
						)
					);
				}
			}
		}

		/**
		 * Import settings.
		 *
		 * @version 5.6.1
		 * @since   3.1.0
		 *
		 * @todo (dev) Better data validation?
		 */
		public function import_settings() {
			if ( empty( $_FILES['alg_wc_ean_import_settings']['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				return;
			}

			$tmp_name = sanitize_text_field( $_FILES['alg_wc_ean_import_settings']['tmp_name'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( ! is_uploaded_file( $tmp_name ) ) {
				return;
			}

			$content = file_get_contents( $tmp_name ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = json_decode( $content, true );

			if ( JSON_ERROR_NONE === json_last_error() ) {
				$counter = 0;
				foreach ( $content as $row ) {
					if (
						'alg_wc_ean' === substr( $row['option_name'], 0, 10 ) &&
						! in_array(
							$row['option_name'],
							array(
								'alg_wc_ean_version',
								'alg_wc_ean_export_settings',
								'alg_wc_ean_reset_settings',
							),
							true
						)
					) {
						if ( update_option( $row['option_name'], $row['option_value'] ) ) {
							++$counter;
						}
					}
				}
				if ( is_callable( array( 'WC_Admin_Settings', 'add_message' ) ) ) {
					WC_Admin_Settings::add_message(
						sprintf(
							/* Translators: %s: Number of options. */
							__( 'Settings imported: %d option(s) updated.', 'ean-for-woocommerce' ),
							$counter
						)
					);
				}
			} elseif ( is_callable( array( 'WC_Admin_Settings', 'add_message' ) ) ) {
				WC_Admin_Settings::add_message(
					sprintf(
						/* Translators: %s: Error message. */
						__( 'Import file error: %s', 'ean-for-woocommerce' ),
						json_last_error_msg()
					)
				);
			}
		}

		/**
		 * Export settings.
		 *
		 * @version 5.6.1
		 * @since   3.1.0
		 *
		 * @todo (dev) Remove `length`?
		 * @todo (dev) Recheck headers?
		 * @todo (dev) Redirect page?
		 */
		public function export_settings() {
			if ( 'yes' === get_option( 'alg_wc_ean_export_settings', 'no' ) ) {
				update_option( 'alg_wc_ean_export_settings', 'no' );
				global $wpdb;
				$content = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'alg_wc_ean%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				foreach ( $content as &$row ) {
					$row->option_value = maybe_unserialize( $row->option_value );
				}
				$content = wp_json_encode( $content );
				$length  = strlen( $content );
				header( 'Content-Description: File Transfer' );
				header( 'Content-Type: text/plain' );
				header( 'Content-Disposition: attachment; filename=wpfactory-wc-ean-settings.txt' );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Content-Length: ' . $length );
				header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
				header( 'Expires: 0' );
				header( 'Pragma: public' );
				echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				exit;
			}
		}
	}

endif;

return new WPFactory_WC_EAN_Manage_Settings();

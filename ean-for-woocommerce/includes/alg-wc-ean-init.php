<?php
/**
 * EAN for WooCommerce - Init
 *
 * @version 5.4.5
 * @since   5.4.5
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

/**
 * QRcode class.
 *
 * @version 5.4.5
 * @since   5.4.5
 *
 * @todo    (v5.4.5) check if the "2D Barcodes" section is enabled; if the "Barcode type" is set to one of the "QR code" options
 */
if (
	'ean-for-woocommerce-pro.php' === basename( ALG_WC_EAN_FILE ) &&
	'yes' === get_option( 'alg_wc_ean_qrcode_early_load', 'no' )
) {
	$do_load = false;

	// Params
	$params = get_option( 'alg_wc_ean_qrcode_early_load_params', '' );
	if ( '' === $params ) {
		$do_load = true;
	} else {
		$params = array_map( 'trim', explode( PHP_EOL, $params ) );
		foreach ( $params as $param ) {
			$param = array_map( 'trim', explode( '=', $param, 2 ) );
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if (
				2 === count( $param ) &&
				isset( $_GET[ $param[0] ] ) && // e.g., `action` or `section`
				$param[1] === sanitize_text_field( wp_unslash( $_GET[ $param[0] ] ) ) // e.g., `alg_wc_ean_print_barcodes_to_pdf` or `print`
			) {
				$do_load = true;
				break;
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}
	}

	if ( $do_load ) {
		require_once plugin_dir_path( __FILE__ ) . 'pro/assets/tcpdf/include/barcodes/qrcode.php';
	}
}

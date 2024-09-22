<?php
/**
 * EAN for WooCommerce - Orders Class
 *
 * @version 4.8.9
 * @since   2.1.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Orders' ) ) :

class Alg_WC_EAN_Orders {

	/**
	 * Constructor.
	 *
	 * @version 4.8.9
	 * @since   2.1.0
	 *
	 * @todo    (feature) option to hide the field (i.e., add it to `woocommerce_hidden_order_itemmeta`)?
	 */
	function __construct() {

		// Order item meta
		if ( 'yes' === get_option( 'alg_wc_ean_order_items_meta', 'no' ) ) {
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'add_ean_to_order_items_meta' ), PHP_INT_MAX );
		}

		// Admin new order (AJAX)
		if ( 'yes' === get_option( 'alg_wc_ean_order_items_meta_admin', 'no' ) ) {
			add_action( 'woocommerce_new_order_item', array( $this, 'new_order_item_ajax' ), PHP_INT_MAX, 2 );
		}

		// Order item meta label
		add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'set_order_item_meta_display_key' ), PHP_INT_MAX, 2 );

	}

	/**
	 * set_order_item_meta_display_key.
	 *
	 * @version 4.8.9
	 * @since   4.8.9
	 *
	 * @todo    (dev) make this optional?
	 */
	function set_order_item_meta_display_key( $display_key, $meta ) {
		return ( alg_wc_ean()->core->ean_key === $meta->key ? get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) : $display_key );
	}

	/**
	 * new_order_item_ajax.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 *
	 * @todo    (fix) EAN meta is not displayed until order page is reloaded
	 */
	function new_order_item_ajax( $item_id, $item ) {
		if (
			defined( 'DOING_AJAX' ) && DOING_AJAX &&
			'WC_Order_Item_Product' === get_class( $item ) &&
			'' === wc_get_order_item_meta( $item_id, alg_wc_ean()->core->ean_key ) &&
			( $product_id = ( ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'] ) ) &&
			'' !== ( $ean = alg_wc_ean()->core->get_ean( $product_id, true ) )
		) {
			wc_update_order_item_meta( $item_id, alg_wc_ean()->core->ean_key, $ean );
		}
	}

	/**
	 * add_ean_to_order_items_meta.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 *
	 * @todo    (feature) editable field?
	 * @todo    (dev) `( $do_overwrite || '' === wc_get_order_item_meta( $item_id, alg_wc_ean()->core->ean_key, true )`
	 */
	function add_ean_to_order_items_meta( $order_id ) {
		$count = 0;
		$order = wc_get_order( $order_id );
		if ( $order ) {
			foreach ( $order->get_items() as $item_id => $item ) {
				if (
					0 != ( $product_id = ( ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'] ) ) &&
					'' !== ( $ean = alg_wc_ean()->core->get_ean( $product_id, true ) )
				) {
					wc_update_order_item_meta( $item_id, alg_wc_ean()->core->ean_key, $ean );
					$count++;
				}
			}
		}
		return $count;
	}

}

endif;

return new Alg_WC_EAN_Orders();

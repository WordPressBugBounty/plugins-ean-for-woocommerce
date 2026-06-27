<?php
/**
 * EAN for WooCommerce - Order Tools Section Settings
 *
 * @version 5.5.6
 * @since   4.9.1
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_EAN_Settings_Order_Tools' ) ) :

class WPFactory_WC_EAN_Settings_Order_Tools extends WPFactory_WC_EAN_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 4.9.1
	 * @since   4.9.1
	 */
	function __construct() {
		$this->id   = 'order_tools';
		$this->desc = __( 'Order Tools', 'ean-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 5.5.6
	 * @since   4.9.1
	 *
	 * @todo    (desc) add info about "General > Orders" options (i.e., "Add EAN to new order items meta", etc.)
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Order Tools', 'ean-for-woocommerce' ),
				'desc'     => sprintf(
					/* Translators: %s: Dashicon. */
					__( 'Check the %s box and "Save changes" to run the tool. Please note that there is no undo for these tools.', 'ean-for-woocommerce' ),
					'<span class="dashicons dashicons-admin-generic"></span>'
				),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_tools_orders',
			),
			array(
				'title'    => __( 'Add', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' .
					__( 'Add EANs to all order items', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_orders_add',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Delete', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' .
					__( 'Delete EANs from all order items', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_orders_delete',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_tools_orders',
			),
			array(
				'title'    => __( 'Order Search', 'ean-for-woocommerce' ),
				'desc'     => __( 'Searches orders by EAN (item meta).', 'ean-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_tools_orders_search',
			),
			array(
				'title'    => __( 'Search', 'ean-for-woocommerce' ),
				'desc'     => (
					isset( $_REQUEST['alg_wc_ean_order_items_search'], wpfactory_wc_ean()->core->order_tools ) ? // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					wpfactory_wc_ean()->core->order_tools->get_order_items_search(
						sanitize_text_field( wp_unslash( $_REQUEST['alg_wc_ean_order_items_search'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					) :
					''
				),
				'id'       => 'alg_wc_ean_order_items_search',
				'default'  => (
					isset( $_REQUEST['alg_wc_ean_order_items_search'] ) ? // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					sanitize_text_field( wp_unslash( $_REQUEST['alg_wc_ean_order_items_search'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					''
				),
				'type'     => 'text',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_tools_orders_search',
			),
		);
	}

}

endif;

return new WPFactory_WC_EAN_Settings_Order_Tools();

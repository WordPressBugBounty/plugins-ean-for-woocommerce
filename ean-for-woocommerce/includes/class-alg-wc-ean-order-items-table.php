<?php
/**
 * EAN for WooCommerce - Order Items Table Class
 *
 * @version 4.8.7
 * @since   3.7.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Order_Items_Table' ) ) :

class Alg_WC_EAN_Order_Items_Table {

	/**
	 * email.
	 *
	 * @version 4.8.7
	 */
	public $email;

	/**
	 * Constructor.
	 *
	 * @version 3.7.0
	 * @since   3.7.0
	 */
	function __construct() {
		if (
			'yes' === get_option( 'alg_wc_ean_order_items_table', 'no' ) ||
			'yes' === get_option( 'alg_wc_ean_order_items_table_emails', get_option( 'alg_wc_ean_order_items_table', 'no' ) )
		) {
			add_action( 'woocommerce_order_item_meta_end',      array( $this, 'add_to_order_item_meta_ean' ), 10, 4 );
			add_action( 'woocommerce_email_before_order_table', array( $this, 'save_email_data' ), 10, 4 );
			add_action( 'woocommerce_email_after_order_table',  array( $this, 'reset_email_data' ), 10, 0 );
		}
	}

	/**
	 * save_email_data.
	 *
	 * @version 3.7.0
	 * @since   3.1.0
	 */
	function save_email_data( $order, $sent_to_admin, $plain_text, $email ) {
		$this->email = $email;
	}

	/**
	 * reset_email_data.
	 *
	 * @version 3.7.0
	 * @since   3.1.0
	 */
	function reset_email_data() {
		$this->email = false;
	}

	/**
	 * add_to_order_item_meta_ean.
	 *
	 * @version 3.7.0
	 * @since   1.2.0
	 */
	function add_to_order_item_meta_ean( $item_id, $item, $order, $plain_text ) {
		if ( false !== ( $ean = alg_wc_ean()->core->get_ean_from_order_item( $item ) ) ) {
			// Do we need to display?
			$do_display_in_emails = ( 'yes' === get_option( 'alg_wc_ean_order_items_table_emails', get_option( 'alg_wc_ean_order_items_table', 'no' ) ) );
			$emails               = get_option( 'alg_wc_ean_order_items_table_emails_list', array() );
			$do_display_on_pages  = ( 'yes' === get_option( 'alg_wc_ean_order_items_table', 'no' ) );
			if ( $do_display_in_emails && empty( $emails ) && $do_display_on_pages ) {
				// Display everywhere, no need to check further...
				$do_display = true;
			} else {
				if ( empty( $this->email ) ) {
					// "WooCommerce Email Customizer with Drag and Drop Email Builder" plugin by "Flycart Technologies LLP"
					global $woo_email_arguments;
					if ( ! empty( $woo_email_arguments['email'] ) ) {
						$this->email = $woo_email_arguments['email'];
					}
				}
				if ( ! empty( $this->email ) ) {
					// It's an email...
					$do_display = ( $do_display_in_emails ? ( empty( $emails ) || in_array( $this->email->id, $emails ) ) : false );
				} else {
					// It's a page, e.g., "Thank you"
					$do_display = $do_display_on_pages;
				}
			}
			// Display
			if ( $do_display ) {
				$templates = array_replace( array(
						'html'       => '<ul class="wc-item-meta"><li><span class="sku_wrapper ean_wrapper">EAN: <span class="ean">%ean%</span></span></li></ul>',
						'plain_text' => '%new_line%- EAN: %ean%',
					), get_option( 'alg_wc_ean_order_items_table_templates', array() ) );
				echo str_replace( array( '%new_line%', '%ean%' ), array( "\n", $ean ), $templates[ ( ! $plain_text ? 'html' : 'plain_text' ) ] );
			}
		}
	}

}

endif;

return new Alg_WC_EAN_Order_Items_Table();

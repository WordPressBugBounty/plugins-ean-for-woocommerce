<?php
/**
 * EAN for WooCommerce - Order Items Table Class
 *
 * @version 5.6.1
 * @since   3.7.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_EAN
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_EAN_Order_Items_Table' ) ) :

	/**
	 * WPFactory_WC_EAN_Order_Items_Table class.
	 *
	 * @version 5.6.1
	 * @since   3.7.0
	 */
	class WPFactory_WC_EAN_Order_Items_Table {

		/**
		 * Email.
		 *
		 * @version 4.8.7
		 *
		 * @var object|false
		 */
		public $email;

		/**
		 * Constructor.
		 *
		 * @version 3.7.0
		 * @since   3.7.0
		 */
		public function __construct() {
			if (
				'yes' === get_option( 'alg_wc_ean_order_items_table', 'no' ) ||
				'yes' === get_option( 'alg_wc_ean_order_items_table_emails', get_option( 'alg_wc_ean_order_items_table', 'no' ) )
			) {
				add_action( 'woocommerce_order_item_meta_end', array( $this, 'add_to_order_item_meta_ean' ), 10, 4 );
				add_action( 'woocommerce_email_before_order_table', array( $this, 'save_email_data' ), 10, 4 );
				add_action( 'woocommerce_email_after_order_table', array( $this, 'reset_email_data' ), 10, 0 );
			}
		}

		/**
		 * Save email data.
		 *
		 * @version 3.7.0
		 * @since   3.1.0
		 *
		 * @param object $order         Order object.
		 * @param bool   $sent_to_admin Whether sent to admin.
		 * @param bool   $plain_text    Whether plain text.
		 * @param object $email         Email object.
		 *
		 * @return void
		 */
		public function save_email_data( $order, $sent_to_admin, $plain_text, $email ) {
			$this->email = $email;
		}

		/**
		 * Reset email data.
		 *
		 * @version 3.7.0
		 * @since   3.1.0
		 */
		public function reset_email_data() {
			$this->email = false;
		}

		/**
		 * Add to order item meta EAN.
		 *
		 * @version 5.6.1
		 * @since   1.2.0
		 *
		 * @param int    $item_id    The item ID.
		 * @param object $item       The item.
		 * @param object $order      The order.
		 * @param bool   $plain_text Whether plain text.
		 *
		 * @return void
		 */
		public function add_to_order_item_meta_ean( $item_id, $item, $order, $plain_text ) {
			$ean = wpfactory_wc_ean()->core->get_ean_from_order_item( $item );
			if ( false !== $ean ) {
				// Do we need to display?
				$do_display_in_emails = ( 'yes' === get_option( 'alg_wc_ean_order_items_table_emails', get_option( 'alg_wc_ean_order_items_table', 'no' ) ) );
				$emails               = get_option( 'alg_wc_ean_order_items_table_emails_list', array() );
				$do_display_on_pages  = ( 'yes' === get_option( 'alg_wc_ean_order_items_table', 'no' ) );
				if ( $do_display_in_emails && empty( $emails ) && $do_display_on_pages ) {
					// Display everywhere, no need to check further...
					$do_display = true;
				} else {
					if ( empty( $this->email ) ) {
						// "WooCommerce Email Customizer with Drag and Drop Email Builder" plugin by "Flycart Technologies LLP".
						global $woo_email_arguments;
						if ( ! empty( $woo_email_arguments['email'] ) ) {
							$this->email = $woo_email_arguments['email'];
						}
					}
					if ( ! empty( $this->email ) ) {
						// It's an email...
						$do_display = ( $do_display_in_emails ? ( empty( $emails ) || in_array( $this->email->id, $emails, true ) ) : false );
					} else {
						// It's a page, e.g., "Thank you".
						$do_display = $do_display_on_pages;
					}
				}
				// Display.
				if ( $do_display ) {
					$templates = array_replace(
						array(
							'html'       => '<ul class="wc-item-meta"><li><span class="sku_wrapper ean_wrapper">EAN: <span class="ean">%ean%</span></span></li></ul>',
							'plain_text' => '%new_line%- EAN: %ean%',
						),
						get_option( 'alg_wc_ean_order_items_table_templates', array() )
					);
					echo wp_kses_post(
						str_replace(
							array( '%new_line%', '%ean%' ),
							array( "\n", $ean ),
							$templates[ ( ! $plain_text ? 'html' : 'plain_text' ) ]
						)
					);
				}
			}
		}
	}

endif;

return new WPFactory_WC_EAN_Order_Items_Table();

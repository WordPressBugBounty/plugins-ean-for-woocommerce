<?php
/**
 * EAN for WooCommerce - Order Tools Class
 *
 * @version 4.9.1
 * @since   3.9.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Order_Tools' ) ) :

class Alg_WC_EAN_Order_Tools {

	/**
	 * Constructor.
	 *
	 * @version 4.9.1
	 * @since   3.9.0
	 */
	function __construct() {
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'orders_add' ) );
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'orders_delete' ) );
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'orders_search' ) );
	}

	/**
	 * orders_search.
	 *
	 * @version 4.9.1
	 * @since   4.9.1
	 *
	 * @todo    (dev) better solution?
	 */
	function orders_search() {
		if ( '' !== get_option( 'alg_wc_ean_order_items_search', '' ) ) {
			delete_option( 'alg_wc_ean_order_items_search' );
		}
	}

	/**
	 * get_order_items_search.
	 *
	 * @version 4.9.1
	 * @since   4.9.1
	 */
	function get_order_items_search( $ean ) {

		// Get orders
		global $wpdb;
		$order_ids = $wpdb->get_col( $wpdb->prepare( "
			SELECT DISTINCT items.order_id
			FROM {$wpdb->prefix}woocommerce_order_items AS items
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS itemmeta ON items.order_item_id = itemmeta.order_item_id
			WHERE meta_key LIKE '%s'
			AND meta_value = %s
		", alg_wc_ean()->core->ean_key, $ean ) );

		// Output
		ob_start();
		?>
		<div class="alg-wc-ean-order-items-search-wrap">
			<p><?php printf( esc_html__( 'Search results for: %s', 'ean-for-woocommerce' ), '<strong>' . esc_html( $ean ) . '</strong>' ); ?></p>
			<table class="widefat striped">
				<thead>
					<tr>
						<td><?php echo esc_html__( 'Order', 'ean-for-woocommerce' ); ?></td>
						<td><?php echo esc_html__( 'Date', 'ean-for-woocommerce' ); ?></td>
						<td><?php echo esc_html__( 'Status', 'ean-for-woocommerce' ); ?></td>
						<td><?php echo esc_html__( 'Total', 'ean-for-woocommerce' ); ?></td>
					</tr>
				</thead>
				<tbody>
				<?php if ( ! empty( $order_ids ) ) {
					foreach ( $order_ids as $order_id ) {
						$order = wc_get_order( $order_id );
						?>
						<tr>
						<?php if ( $order ) { ?>
							<td><strong><a href="<?php echo esc_url( admin_url( "post.php?post={$order_id}&action=edit" ) ); ?>">#<?php echo $order->get_order_number(); ?></a></strong></td>
							<td><?php echo wc_format_datetime( $order->get_date_created() ); ?></td>
							<td><mark class="order-status status-<?php echo esc_attr( $order->get_status() ); ?> tips"><span><?php echo wc_get_order_status_name( $order->get_status() ); ?></span></mark></td>
							<td><?php echo wc_price( $order->get_total() ); ?></td>
						<?php } else { ?>
							<td colspan="4"><strong>#<?php echo $order_id; ?></strong></td>
						<?php } ?>
						</tr>
						<?php
					}
				} else { ?>
					<tr><td colspan="4"><?php echo esc_html__( 'No orders found', 'ean-for-woocommerce' ); ?></td></tr>
				<?php } ?>
				</tbody>
				<tfoot>
					<tr>
						<td><?php echo esc_html__( 'Order', 'ean-for-woocommerce' ); ?></td>
						<td><?php echo esc_html__( 'Date', 'ean-for-woocommerce' ); ?></td>
						<td><?php echo esc_html__( 'Status', 'ean-for-woocommerce' ); ?></td>
						<td><?php echo esc_html__( 'Total', 'ean-for-woocommerce' ); ?></td>
					</tr>
				</tfoot>
			</table>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * get_orders.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query
	 */
	function get_orders() {
		return wc_get_orders( array( 'limit' => -1, 'return' => 'ids' ) );
	}

	/**
	 * orders_delete.
	 *
	 * @version 2.9.0
	 * @since   2.1.0
	 */
	function orders_delete() {
		if ( 'yes' === get_option( 'alg_wc_ean_tool_orders_delete', 'no' ) ) {
			update_option( 'alg_wc_ean_tool_orders_delete', 'no' );
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$count = 0;
				foreach ( $this->get_orders() as $order_id ) {
					$order = wc_get_order( $order_id );
					if ( ! $order ) {
						continue;
					}
					foreach ( $order->get_items() as $item_id => $item ) {
						if ( wc_delete_order_item_meta( $item_id, alg_wc_ean()->core->ean_key ) ) {
							$count++;
						}
					}
				}
				if ( method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
					WC_Admin_Settings::add_message( sprintf( __( 'EAN deleted for %s order items.', 'ean-for-woocommerce' ), $count ) );
				}
			}
		}
	}

	/**
	 * orders_add.
	 *
	 * @version 2.9.0
	 * @since   2.1.0
	 */
	function orders_add() {
		if ( 'yes' === get_option( 'alg_wc_ean_tool_orders_add', 'no' ) ) {
			update_option( 'alg_wc_ean_tool_orders_add', 'no' );
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$count = 0;
				foreach ( $this->get_orders() as $order_id ) {
					$count += alg_wc_ean()->core->orders->add_ean_to_order_items_meta( $order_id );
				}
				if ( method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
					WC_Admin_Settings::add_message( sprintf( __( 'EAN added for %s order items.', 'ean-for-woocommerce' ), $count ) );
				}
			}
		}
	}

}

endif;

return new Alg_WC_EAN_Order_Tools();

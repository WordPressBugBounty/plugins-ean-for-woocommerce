<?php
/**
 * EAN for WooCommerce - Print Products Section Settings
 *
 * @version 4.3.0
 * @since   4.3.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Settings_Print_Products' ) ) :

class Alg_WC_EAN_Settings_Print_Products extends Alg_WC_EAN_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 4.3.0
	 * @since   4.3.0
	 */
	function __construct() {

		$this->id   = 'print_products';
		$this->desc = __( 'Print Products', 'ean-for-woocommerce' );

		parent::__construct();

		add_action( 'woocommerce_settings_' . 'alg_wc_ean', array( $this, 'before_table' ), 9 );
		add_action( 'woocommerce_settings_' . 'alg_wc_ean', array( $this, 'after_table' ), 11 );

	}

	/**
	 * print_button.
	 *
	 * @version 4.3.0
	 * @since   4.3.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/6.7.0/plugins/woocommerce/includes/admin/views/html-admin-settings.php#L40
	 */
	function print_button() {
		?>
		<p class="submit">
			<button name="save" class="button-primary woocommerce-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>"><?php esc_html_e( 'Print', 'ean-for-woocommerce' ); ?></button>
		</p>
		<?php
	}

	/**
	 * before_table.
	 *
	 * @version 4.3.0
	 * @since   4.3.0
	 */
	function before_table() {
		global $current_section;
		if ( 'print_products' === $current_section ) {
			echo '<h2>' . esc_html__( 'Print Products Tool', 'ean-for-woocommerce' ) . '</h2>';
			$this->print_button();
		}
	}

	/**
	 * after_table.
	 *
	 * @version 4.3.0
	 * @since   4.3.0
	 */
	function after_table() {
		global $current_section;
		if ( 'print_products' === $current_section ) {
			$this->print_button();
			$GLOBALS['hide_save_button'] = true;
		}
	}

	/**
	 * style.
	 *
	 * @version 4.3.0
	 * @since   4.3.0
	 */
	function style() {
		?>
		<style>

			.form-table,
			.form-table th,
			.form-table td {
				padding: 5px;
				width: auto;
				border: 1px solid #ddd;
			}

			.woocommerce table.form-table input[type=number] {
				width: 4em;
			}

		</style>
		<?php
	}

	/**
	 * get_settings.
	 *
	 * @version 4.3.0
	 * @since   4.3.0
	 *
	 * @todo    (dev) pagination
	 * @todo    (dev) `set_transient( 'alg_wc_ean_print_products_list', $products, HOUR_IN_SECONDS )`, `get_transient( 'alg_wc_ean_print_products_list' )`?
	 */
	function get_settings() {
		$settings = array();

		add_action( 'admin_footer', array( $this, 'style' ), PHP_INT_MAX );

		$products = wc_get_products( array(
			'limit'   => -1,
			'return'  => 'ids',
			'orderby' => 'title',
			'order'   => 'ASC',
			'type'    => array_merge( array_keys( wc_get_product_types() ), array( 'variation' ) ),
		) );

		$settings = array_merge( $settings, array(
			array(
				'type'     => 'title',
				'id'       => 'alg_wc_ean_print_products_list_options',
			),
		) );
		foreach ( $products as $product_id ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => sprintf( '%s (%s)',
						get_the_title( $product_id ), ( '' !== ( $sku = get_post_meta( $product_id, '_sku', true ) ) ? $sku : '#' . $product_id ) ),
					'type'     => 'number',
					'id'       => "alg_wc_ean_print_products_list[{$product_id}]",
					'default'  => '',
					'custom_attributes' => array( 'min' => 0 ),
				),
			) );
		}
		$settings = array_merge( $settings, array(
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_print_products_list_options',
			),
		) );

		return $settings;
	}

}

endif;

return new Alg_WC_EAN_Settings_Print_Products();

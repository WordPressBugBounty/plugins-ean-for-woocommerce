<?php
/**
 * EAN for WooCommerce - Print Products Section Settings
 *
 * @version 5.5.6
 * @since   4.3.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_EAN_Settings_Print_Products' ) ) :

class WPFactory_WC_EAN_Settings_Print_Products extends WPFactory_WC_EAN_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 5.5.6
	 * @since   4.3.0
	 */
	function __construct() {

		$this->id   = 'print_products';
		$this->desc = __( 'Print Products', 'ean-for-woocommerce' );

		parent::__construct();

		add_action( 'admin_enqueue_scripts', array( $this, 'style' ), PHP_INT_MAX );

		add_action( 'woocommerce_settings_' . 'wpfactory_wc_ean', array( $this, 'before_table' ), 9 );
		add_action( 'woocommerce_settings_' . 'wpfactory_wc_ean', array( $this, 'after_table' ), 11 );

	}

	/**
	 * print_button.
	 *
	 * @version 5.4.8
	 * @since   4.3.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/6.7.0/plugins/woocommerce/includes/admin/views/html-admin-settings.php#L40
	 */
	function print_button() {
		?>
		<p class="submit">
			<button name="save" class="button-primary woocommerce-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'ean-for-woocommerce' ); ?>"><?php esc_html_e( 'Print', 'ean-for-woocommerce' ); ?></button>
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
			$GLOBALS['hide_save_button'] = true; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		}
	}

	/**
	 * style.
	 *
	 * @version 5.5.6
	 * @since   4.3.0
	 */
	function style() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}
		$screen = get_current_screen();
		if (
			! isset( $screen->id ) ||
			'woocommerce_page_wc-settings' !== $screen->id ||
			! isset( $_GET['tab'], $_GET['section'] ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'wpfactory_wc_ean' !== $_GET['tab'] || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'print_products' !== $_GET['section'] // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			return;
		}

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style(
			'wpfactory-wc-ean-settings-print-products',
			wpfactory_wc_ean()->plugin_url() . '/assets/css/wpfactory-wc-ean-settings-print-products' . $min . '.css',
			array(),
			wpfactory_wc_ean()->version
		);
	}

	/**
	 * get_settings.
	 *
	 * @version 5.5.5
	 * @since   4.3.0
	 *
	 * @todo    (dev) pagination
	 * @todo    (dev) `set_transient( 'alg_wc_ean_print_products_list', $products, HOUR_IN_SECONDS )`, `get_transient( 'alg_wc_ean_print_products_list' )`?
	 */
	function get_settings() {
		$settings = array();

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

return new WPFactory_WC_EAN_Settings_Print_Products();

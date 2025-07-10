<?php
/**
 * EAN for WooCommerce - Main Class
 *
 * @version 5.5.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN' ) ) :

final class Alg_WC_EAN {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = ALG_WC_EAN_VERSION;

	/**
	 * core.
	 *
	 * @version 4.8.7
	 */
	public $core;

	/**
	 * pro.
	 *
	 * @version 4.8.7
	 */
	public $pro;

	/**
	 * @var   Alg_WC_EAN The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_EAN Instance.
	 *
	 * Ensures only one instance of Alg_WC_EAN is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @static
	 * @return  Alg_WC_EAN - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_EAN Constructor.
	 *
	 * @version 5.3.0
	 * @since   1.0.0
	 *
	 * @access  public
	 */
	function __construct() {

		// Check for active WooCommerce plugin
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		// Load libs
		if ( is_admin() ) {
			require_once plugin_dir_path( ALG_WC_EAN_FILE ) . 'vendor/autoload.php';
		}

		// Set up localisation
		add_action( 'init', array( $this, 'localize' ) );

		// Declare compatibility with custom order tables for WooCommerce
		add_action( 'before_woocommerce_init', array( $this, 'wc_declare_compatibility' ) );

		// Pro
		if ( 'ean-for-woocommerce-pro.php' === basename( ALG_WC_EAN_FILE ) ) {
			$this->pro = require_once plugin_dir_path( __FILE__ ) . 'pro/class-alg-wc-ean-pro.php';
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}

	}

	/**
	 * localize.
	 *
	 * @version 2.2.0
	 * @since   2.0.0
	 */
	function localize() {
		load_plugin_textdomain(
			'ean-for-woocommerce',
			false,
			dirname( plugin_basename( ALG_WC_EAN_FILE ) ) . '/langs/'
		);
	}

	/**
	 * wc_declare_compatibility.
	 *
	 * @version 4.7.3
	 * @since   4.5.0
	 *
	 * @see     https://developer.woocommerce.com/docs/features/high-performance-order-storage/recipe-book/
	 */
	function wc_declare_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			$files = (
				defined( 'ALG_WC_EAN_FILE_FREE' ) ?
				array( ALG_WC_EAN_FILE, ALG_WC_EAN_FILE_FREE ) :
				array( ALG_WC_EAN_FILE )
			);
			foreach ( $files as $file ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
					'custom_order_tables',
					$file,
					true
				);
			}
		}
	}

	/**
	 * includes.
	 *
	 * @version 5.3.0
	 * @since   1.0.0
	 */
	function includes() {
		$this->core = require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-ean-core.php';
	}

	/**
	 * admin.
	 *
	 * @version 5.4.4
	 * @since   1.0.0
	 */
	function admin() {

		// Action links
		add_filter(
			'plugin_action_links_' . plugin_basename( ALG_WC_EAN_FILE ),
			array( $this, 'action_links' )
		);

		// "Recommendations" page
		add_action( 'init', array( $this, 'add_cross_selling_library' ) );

		// WC Settings tab as WPFactory submenu item
		add_action( 'init', array( $this, 'move_wc_settings_tab_to_wpfactory_menu' ) );

		// Settings
		add_filter(
			'woocommerce_get_settings_pages',
			array( $this, 'add_woocommerce_settings_tab' )
		);

		// Version update
		if ( get_option( 'alg_wc_ean_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}

	}

	/**
	 * action_links.
	 *
	 * @version 5.4.0
	 * @since   1.0.0
	 *
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();

		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_ean' ) . '">' .
			__( 'Settings', 'ean-for-woocommerce' ) .
		'</a>';

		if ( 'ean-for-woocommerce.php' === basename( ALG_WC_EAN_FILE ) ) {
			$custom_links[] = '<a' .
				' target="_blank"' .
				' style="font-weight: bold; color: green;"' .
				' href="https://wpfactory.com/item/ean-for-woocommerce/"' .
			'>' .
				__( 'Go Pro', 'ean-for-woocommerce' ) .
			'</a>';
		}

		return array_merge( $custom_links, $links );
	}

	/**
	 * add_cross_selling_library.
	 *
	 * @version 5.3.0
	 * @since   5.3.0
	 */
	function add_cross_selling_library() {

		if ( ! class_exists( '\WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling' ) ) {
			return;
		}

		$cross_selling = new \WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling();
		$cross_selling->setup( array( 'plugin_file_path' => ALG_WC_EAN_FILE ) );
		$cross_selling->init();

	}

	/**
	 * move_wc_settings_tab_to_wpfactory_menu.
	 *
	 * @version 5.5.0
	 * @since   5.3.0
	 */
	function move_wc_settings_tab_to_wpfactory_menu() {

		if ( ! class_exists( '\WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu' ) ) {
			return;
		}

		$wpfactory_admin_menu = \WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu::get_instance();

		if ( ! method_exists( $wpfactory_admin_menu, 'move_wc_settings_tab_to_wpfactory_menu' ) ) {
			return;
		}

		$wpfactory_admin_menu->move_wc_settings_tab_to_wpfactory_menu( array(
			'wc_settings_tab_id' => 'alg_wc_ean',
			'menu_title'         => apply_filters(
				'alg_wc_ean_settings_page_label',
				__( 'EAN', 'ean-for-woocommerce' )
			),
			'page_title'         => __( 'EAN Barcode Generator for WooCommerce: UPC, ISBN & GTIN Inventory', 'ean-for-woocommerce' ),
			'plugin_icon'        => array(
				'get_url_method'    => 'wporg_plugins_api',
				'wporg_plugin_slug' => 'ean-for-woocommerce',
			),
		) );

	}

	/**
	 * add_woocommerce_settings_tab.
	 *
	 * @version 5.3.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once plugin_dir_path( __FILE__ ) . 'settings/class-alg-wc-ean-settings.php';
		return $settings;
	}

	/**
	 * version_updated.
	 *
	 * @version 2.9.0
	 * @since   1.0.0
	 */
	function version_updated() {
		update_option( 'alg_wc_ean_version', $this->version );

		/**
		 * Handle deprecated options.
		 *
		 * @deprecated 2.9.0
		 */
		$deprecated_options = get_option( 'alg_wc_ean_tool_product_generate_on', false );
		if ( false !== $deprecated_options ) {
			if (
				isset( $deprecated_options['insert_product'] ) &&
				'yes' === $deprecated_options['insert_product']
			) {
				update_option( 'alg_wc_ean_tool_product_action_on_new', 'generate' );
			}
			if (
				isset( $deprecated_options['update_product'] ) &&
				'yes' === $deprecated_options['update_product']
			) {
				update_option( 'alg_wc_ean_tool_product_action_on_update', 'generate' );
			}
			delete_option( 'alg_wc_ean_tool_product_generate_on' );
		}

	}

	/**
	 * plugin_url.
	 *
	 * @version 2.2.0
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( ALG_WC_EAN_FILE ) );
	}

	/**
	 * plugin_path.
	 *
	 * @version 2.2.0
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( ALG_WC_EAN_FILE ) );
	}

}

endif;

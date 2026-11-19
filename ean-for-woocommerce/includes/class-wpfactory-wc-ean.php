<?php
/**
 * EAN for WooCommerce - Main Class
 *
 * @version 5.6.1
 * @since   1.0.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_EAN
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_EAN' ) ) :

	/**
	 * WPFactory_WC_EAN class.
	 *
	 * @version 5.6.1
	 * @since   1.0.0
	 */
	final class WPFactory_WC_EAN {

		/**
		 * Plugin version.
		 *
		 * @version 5.5.6
		 * @since   1.0.0
		 *
		 * @var string
		 */
		public $version = WPFACTORY_WC_EAN_VERSION;

		/**
		 * Core.
		 *
		 * @version 4.8.7
		 *
		 * @var WPFactory_WC_EAN_Core
		 */
		public $core;

		/**
		 * Pro.
		 *
		 * @version 4.8.7
		 *
		 * @var WPFactory_WC_EAN_Pro
		 */
		public $pro;

		/**
		 * The single instance of the class.
		 *
		 * @version 5.6.1
		 * @since   1.0.0
		 *
		 * @var WPFactory_WC_EAN
		 */
		protected static $instance = null;

		/**
		 * Main WPFactory_WC_EAN Instance.
		 *
		 * Ensures only one instance of WPFactory_WC_EAN is loaded or can be loaded.
		 *
		 * @version 5.6.1
		 * @since   1.0.0
		 *
		 * @static
		 *
		 * @return WPFactory_WC_EAN
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * WPFactory_WC_EAN Constructor.
		 *
		 * @version 5.5.6
		 * @since   1.0.0
		 *
		 * @access public
		 */
		public function __construct() {
			// Check for active WooCommerce plugin.
			if ( ! function_exists( 'WC' ) ) {
				return;
			}

			// Load libs.
			if ( is_admin() ) {
				require_once plugin_dir_path( WPFACTORY_WC_EAN_FILE ) . 'vendor/autoload.php';
			}

			// Declare compatibility with custom order tables for WooCommerce.
			add_action( 'before_woocommerce_init', array( $this, 'wc_declare_compatibility' ) );

			// Pro.
			if ( 'ean-for-woocommerce-pro.php' === basename( WPFACTORY_WC_EAN_FILE ) ) {
				$this->pro = require_once plugin_dir_path( __FILE__ ) . 'pro/class-wpfactory-wc-ean-pro.php';
			}

			// Include required files.
			$this->includes();

			// Admin.
			if ( is_admin() ) {
				$this->admin();
			}
		}

		/**
		 * WC declare compatibility.
		 *
		 * @version 5.5.6
		 * @since   4.5.0
		 *
		 * @see https://developer.woocommerce.com/docs/features/high-performance-order-storage/recipe-book/
		 */
		public function wc_declare_compatibility() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				$files = (
					defined( 'WPFACTORY_WC_EAN_FILE_FREE' ) ?
					array( WPFACTORY_WC_EAN_FILE, WPFACTORY_WC_EAN_FILE_FREE ) :
					array( WPFACTORY_WC_EAN_FILE )
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
		 * Includes.
		 *
		 * @version 5.5.6
		 * @since   1.0.0
		 */
		public function includes() {
			$this->core = require_once plugin_dir_path( __FILE__ ) . 'class-wpfactory-wc-ean-core.php';
		}

		/**
		 * Admin.
		 *
		 * @version 5.5.6
		 * @since   1.0.0
		 */
		public function admin() {
			// Action links.
			add_filter(
				'plugin_action_links_' . plugin_basename( WPFACTORY_WC_EAN_FILE ),
				array( $this, 'action_links' )
			);

			// "Recommendations" page.
			add_action( 'init', array( $this, 'add_cross_selling_library' ) );

			// WC Settings tab as WPFactory submenu item.
			add_action( 'init', array( $this, 'move_wc_settings_tab_to_wpfactory_menu' ) );

			// Settings.
			add_filter(
				'woocommerce_get_settings_pages',
				array( $this, 'add_woocommerce_settings_tab' )
			);

			// Version update.
			if ( get_option( 'alg_wc_ean_version', '' ) !== $this->version ) {
				add_action( 'admin_init', array( $this, 'version_updated' ) );
			}
		}

		/**
		 * Action links.
		 *
		 * @version 5.5.6
		 * @since   1.0.0
		 *
		 * @param mixed $links Action links.
		 *
		 * @return array
		 */
		public function action_links( $links ) {
			$custom_links = array();

			$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=wpfactory_wc_ean' ) . '">' .
				__( 'Settings', 'ean-for-woocommerce' ) .
			'</a>';

			if ( 'ean-for-woocommerce.php' === basename( WPFACTORY_WC_EAN_FILE ) ) {
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
		 * Add cross selling library.
		 *
		 * @version 5.5.6
		 * @since   5.3.0
		 */
		public function add_cross_selling_library() {
			if ( ! class_exists( '\WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling' ) ) {
				return;
			}

			$cross_selling = new \WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling();
			$cross_selling->setup( array( 'plugin_file_path' => WPFACTORY_WC_EAN_FILE ) );
			$cross_selling->init();
		}

		/**
		 * Move WC settings tab to WPFactory menu.
		 *
		 * @version 5.5.6
		 * @since   5.3.0
		 */
		public function move_wc_settings_tab_to_wpfactory_menu() {
			if ( ! class_exists( '\WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu' ) ) {
				return;
			}

			$wpfactory_admin_menu = \WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu::get_instance();

			if ( ! method_exists( $wpfactory_admin_menu, 'move_wc_settings_tab_to_wpfactory_menu' ) ) {
				return;
			}

			$wpfactory_admin_menu->move_wc_settings_tab_to_wpfactory_menu(
				array(
					'wc_settings_tab_id' => 'wpfactory_wc_ean',
					'menu_title'         => apply_filters(
						'alg_wc_ean_settings_page_label',
						__( 'EAN', 'ean-for-woocommerce' )
					),
					'page_title'         => __( 'EAN Barcode Generator for WooCommerce: UPC, ISBN & GTIN Inventory', 'ean-for-woocommerce' ),
					'plugin_icon'        => array(
						'get_url_method'    => 'wporg_plugins_api',
						'wporg_plugin_slug' => 'ean-for-woocommerce',
					),
				)
			);
		}

		/**
		 * Add WooCommerce settings tab.
		 *
		 * @version 5.5.6
		 * @since   1.0.0
		 *
		 * @param array $settings WooCommerce settings.
		 *
		 * @return array Modified WooCommerce settings.
		 */
		public function add_woocommerce_settings_tab( $settings ) {
			$settings[] = require_once plugin_dir_path( __FILE__ ) . 'settings/class-wpfactory-wc-ean-settings.php';
			return $settings;
		}

		/**
		 * Version updated.
		 *
		 * @version 2.9.0
		 * @since   1.0.0
		 */
		public function version_updated() {
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
		 * Plugin URL.
		 *
		 * @version 5.5.6
		 * @since   1.0.0
		 *
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugin_dir_url( WPFACTORY_WC_EAN_FILE ) );
		}

		/**
		 * Plugin path.
		 *
		 * @version 5.5.6
		 * @since   1.0.0
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( WPFACTORY_WC_EAN_FILE ) );
		}
	}

endif;

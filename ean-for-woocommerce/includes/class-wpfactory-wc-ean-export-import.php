<?php
/**
 * EAN for WooCommerce - Export Import Class
 *
 * @version 5.6.1
 * @since   2.0.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_EAN
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_EAN_Export_Import' ) ) :

	/**
	 * WPFactory_WC_EAN_Export_Import class.
	 *
	 * @version 5.6.1
	 * @since   2.0.0
	 */
	class WPFactory_WC_EAN_Export_Import {

		/**
		 * Constructor.
		 *
		 * @version 2.0.0
		 * @since   2.0.0
		 */
		public function __construct() {
			// Export.
			add_filter( 'woocommerce_product_export_column_names', array( $this, 'add_export_column' ) );
			add_filter( 'woocommerce_product_export_product_default_columns', array( $this, 'add_export_column' ) );
			add_filter( 'woocommerce_product_export_product_column_alg_ean', array( $this, 'add_export_data' ), 10, 2 );

			// Import.
			add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'add_import_mapping_option' ) );
			add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( $this, 'set_import_mapping_option_default' ) );
			add_filter( 'woocommerce_product_importer_parsed_data', array( $this, 'parse_import_data' ) );
		}

		/**
		 * Parse import data.
		 *
		 * @version 5.6.1
		 * @since   1.5.0
		 *
		 * @param array $data Data.
		 *
		 * @return array Modified data.
		 */
		public function parse_import_data( $data ) {
			if ( isset( $data['alg_ean'] ) ) {
				if ( ! isset( $data['meta_data'] ) ) {
					$data['meta_data'] = array();
				}
				$data['meta_data'][] = array(
					'key'   => wpfactory_wc_ean()->core->ean_key,
					'value' => $data['alg_ean'],
				);
				unset( $data['alg_ean'] );
			}
			return $data;
		}

		/**
		 * Set import mapping option default.
		 *
		 * @version 1.5.0
		 * @since   1.5.0
		 *
		 * @param array $columns Columns.
		 *
		 * @return array Modified columns.
		 *
		 * @todo (dev) Use `alg_wc_ean_title` option value instead of 'EAN' (same in `add_import_mapping_option()` and `add_export_column()`)?
		 */
		public function set_import_mapping_option_default( $columns ) {
			$columns['EAN'] = 'alg_ean';
			return $columns;
		}

		/**
		 * Add import mapping option.
		 *
		 * @version 1.5.0
		 * @since   1.5.0
		 *
		 * @param array $options Options.
		 *
		 * @return array Modified options.
		 */
		public function add_import_mapping_option( $options ) {
			$options['alg_ean'] = 'EAN';
			return $options;
		}

		/**
		 * Add export column.
		 *
		 * @version 1.5.0
		 * @since   1.5.0
		 *
		 * @param array $columns Columns.
		 *
		 * @return array Modified columns.
		 */
		public function add_export_column( $columns ) {
			$columns['alg_ean'] = 'EAN';
			return $columns;
		}

		/**
		 * Add export data.
		 *
		 * @version 5.5.6
		 * @since   1.5.0
		 *
		 * @param mixed      $value   Value.
		 * @param WC_Product $product Product.
		 *
		 * @return mixed Modified value.
		 */
		public function add_export_data( $value, $product ) {
			return wpfactory_wc_ean()->core->get_ean( $product->get_id() );
		}
	}

endif;

return new WPFactory_WC_EAN_Export_Import();

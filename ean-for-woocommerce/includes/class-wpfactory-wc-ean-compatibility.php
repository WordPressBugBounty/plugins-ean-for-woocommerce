<?php
/**
 * EAN for WooCommerce - Compatibility Class
 *
 * @version 5.6.1
 * @since   2.2.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_EAN
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_EAN_Compatibility' ) ) :

	/**
	 * WPFactory_WC_EAN_Compatibility class.
	 *
	 * @version 5.6.1
	 * @since   2.2.0
	 */
	class WPFactory_WC_EAN_Compatibility {

		/**
		 * Options for the "WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels" plugin.
		 *
		 * @version 4.8.7
		 *
		 * @var array
		 */
		public $wt_pklist_options;

		/**
		 * Constructor.
		 *
		 * @version 5.6.1
		 * @since   2.2.0
		 *
		 * @todo (dev) MultiVendorX: Generate button.
		 * @todo (dev) MultiVendorX: Customizable template.
		 * @todo (dev) MultiVendorX: Barcodes?
		 * @todo (dev) "Point of Sale for WooCommerce": Add `( 'yes' === get_option( 'alg_wc_ean_wc_pos', 'yes' ) )` / "This will add EAN field to the "Register > Scanning Fields" option of the %s plugin." / Point of Sale for WooCommerce / https://woocommerce.com/products/point-of-sale-for-woocommerce/
		 * @todo (feature) WCFM: Customizable position, i.e., instead of right below the "SKU" field in "Inventory" tab.
		 * @todo (feature) Dokan: Customizable position, i.e., instead of `dokan_new_product_after_product_tags` and `dokan_product_edit_after_product_tags`.
		 * @todo (feature) https://wordpress.org/plugins/woocommerce-xml-csv-product-import/ (WooCommerce add-on for "WP All Import").
		 */
		public function __construct() {
			// Google Listings & Ads.
			if ( 'yes' === get_option( 'alg_wc_ean_gla', 'no' ) ) {
				add_filter(
					'woocommerce_gla_attribute_mapping_sources_custom_attributes',
					array( $this, 'gla_add_ean' ),
					PHP_INT_MAX
				);
			}

			// MultiVendorX.
			if ( 'yes' === get_option( 'alg_wc_ean_mvx', 'no' ) ) {
				add_action(
					'mvx_process_product_object',
					array( $this, 'mvx_save_ean_field' ),
					10,
					2
				);
				add_action(
					'mvx_frontend_dashboard_after_product_excerpt_metabox_panel',
					array( $this, 'mvx_add_ean_field' )
				);
			}

			// "Point of Sale for WooCommerce" plugin.
			add_filter(
				'wc_pos_scanning_fields',
				array( $this, 'wc_pos_scanning_fields' ),
				PHP_INT_MAX
			);
			if ( 'yes' === get_option( 'alg_wc_ean_wc_pos_search', 'no' ) ) {
				add_filter(
					'woocommerce_rest_prepare_product_object',
					array( $this, 'wc_pos_add_ean_to_product_name' ),
					PHP_INT_MAX,
					3
				);
			}

			// "Woocommerce OpenPos" plugin.
			if ( 'yes' === get_option( 'alg_wc_ean_op', 'yes' ) ) {
				add_filter( 'op_barcode_key_setting', array( $this, 'op_barcode_key_setting' ), PHP_INT_MAX );
			}

			// "Dokan – Best WooCommerce Multivendor Marketplace Solution – Build Your Own Amazon, eBay, Etsy" plugin.
			if ( 'yes' === get_option( 'alg_wc_ean_dokan', 'no' ) ) {
				add_action(
					'dokan_new_product_after_product_tags',
					array( $this, 'dokan_add_ean_field' )
				);
				add_action(
					'dokan_product_edit_after_product_tags',
					array( $this, 'dokan_add_ean_field' ),
					10,
					2
				);
				add_action(
					'dokan_new_product_added',
					array( $this, 'dokan_save_ean_field' ),
					10,
					2
				);
				add_action(
					'dokan_product_updated',
					array( $this, 'dokan_save_ean_field' ),
					10,
					2
				);
				add_action(
					'dokan_product_after_variation_pricing',
					array( $this, 'dokan_add_ean_field_variation' ),
					10,
					3
				);
			}

			// WCFM.
			if ( 'yes' === get_option( 'alg_wc_ean_wcfm', 'no' ) ) {
				// Field.
				add_filter(
					'wcfm_product_fields_stock',
					array( $this, 'wcfm_add_ean_field' ),
					10,
					3
				);
				add_action(
					'after_wcfm_products_manage_meta_save',
					array( $this, 'wcfm_save_ean_field' ),
					10,
					2
				);

				// "Generate EAN" button.
				add_action(
					'wp_enqueue_scripts',
					array( $this, 'wcfm_generate_button_script_and_style' )
				);
				add_action(
					'wp_ajax_wpfactory_wc_ean_generate_ajax',
					array( $this, 'wcfm_generate_button_ajax' )
				);

				// Variations.
				add_filter(
					'wcfm_variation_edit_data',
					array( $this, 'wcfm_variation_edit_data' ),
					10,
					3
				);
				add_filter(
					'wcfm_product_manage_fields_variations',
					array( $this, 'wcfm_variation_add_ean_field' )
				);
				add_action(
					'after_wcfm_product_variation_meta_save',
					array( $this, 'wcfm_variation_save_ean_field' ),
					10,
					3
				);

				// Variations: "Generate EAN" button.
				add_action(
					'wp_enqueue_scripts',
					array( $this, 'wcfm_variation_generate_button_script' )
				);
			}

			// "Print Invoice & Delivery Notes for WooCommerce" plugin.
			if ( 'yes' === get_option( 'alg_wc_ean_wcdn', 'no' ) ) {
				add_action(
					'wcdn_order_item_after',
					array( $this, 'add_to_wcdn_ean' ),
					10,
					3
				);
			}

			// "WooCommerce PDF Invoices & Packing Slips" plugin.
			if ( 'yes' === get_option( 'alg_wc_ean_wpo_wcpdf', 'no' ) ) {
				add_action(
					get_option( 'alg_wc_ean_wpo_wcpdf_position', 'wpo_wcpdf_after_item_meta' ),
					array( $this, 'add_to_wpo_wcpdf_ean' ),
					10,
					3
				);
			}

			// "WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels" plugin.
			if ( 'yes' === get_option( 'alg_wc_ean_wt_pklist', 'no' ) ) {
				// Options (position).
				$_wt_pklist_options = array_replace(
					array( 'position' => 'after_product_meta' ),
					get_option( 'alg_wc_ean_wt_pklist_options', array() )
				);
				// Hooks.
				switch ( $_wt_pklist_options['position'] ) {
					case 'column':
						add_filter(
							'wf_pklist_package_product_table_additional_column_val',
							array( $this, 'add_to_wt_pklist_column_ean' ),
							10,
							6
						);
						add_filter(
							'wf_pklist_product_table_additional_column_val',
							array( $this, 'add_to_wt_pklist_column_ean' ),
							10,
							6
						);
						add_filter(
							'wf_pklist_alter_product_table_head',
							array( $this, 'add_to_wt_pklist_column_head_ean' ),
							10,
							2
						);
						break;
					case 'before_product_meta':
					case 'after_product_meta':
						add_filter(
							'wf_pklist_add_product_meta',
							array( $this, 'add_to_wt_pklist_ean' ),
							10,
							5
						);
						add_filter(
							'wf_pklist_add_package_product_meta',
							array( $this, 'add_to_wt_pklist_ean' ),
							10,
							5
						);
						break;
					default: // i.e., 'after_product_name', 'before_product_name'.
						add_filter(
							'wf_pklist_alter_product_name',
							array( $this, 'add_to_wt_pklist_ean' ),
							10,
							5
						);
						add_filter(
							'wf_pklist_alter_package_product_name',
							array( $this, 'add_to_wt_pklist_ean' ),
							10,
							5
						);
				}
			}

			// "WooCommerce Google Product Feed".
			if ( 'yes' === get_option( 'alg_wc_ean_gpf', 'yes' ) ) {
				add_filter(
					'woocommerce_gpf_custom_field_list',
					array( $this, 'add_to_woocommerce_gpf_custom_field_list' ),
					PHP_INT_MAX
				);
			}

			// "WooCommerce Customer / Order / Coupon Export".
			if ( 'yes' === get_option( 'alg_wc_ean_wc_customer_order_export', 'no' ) ) {
				add_filter(
					'wc_customer_order_export_format_data_sources',
					array( $this, 'wc_customer_order_export_add_column' ),
					10,
					3
				);
				add_filter(
					'wc_customer_order_export_csv_order_row_one_row_per_item',
					array( $this, 'wc_customer_order_export_render_column' ),
					10,
					2
				);
			}

			// WC Vendors.
			if ( 'yes' === get_option( 'alg_wc_ean_wc_vendors', 'yes' ) ) {
				add_filter(
					'alg_wc_ean_search',
					array( $this, 'wc_vendors_products_fix' )
				);
			}
		}

		/**
		 * WC Vendors: Products fix.
		 *
		 * @version 5.3.1
		 * @since   5.3.1
		 *
		 * @param bool $do_search The EAN search status.
		 */
		public function wc_vendors_products_fix( $do_search ) {
			// Proceed only when search is enabled.
			if ( ! $do_search ) {
				return $do_search;
			}

			// Check for the `WCV_Vendors` class.
			if (
				! class_exists( 'WCV_Vendors' ) ||
				! is_callable( array( 'WCV_Vendors', 'is_vendor_page' ) )
			) {
				return $do_search;
			}

			// Vendor page.
			if ( WCV_Vendors::is_vendor_page() ) {
				return false;
			}

			// Dashboard shortcodes.
			global $post;
			if (
				is_a( $post, 'WP_Post' ) &&
				(
					has_shortcode( $post->post_content, 'wcv_dashboard_nav' ) ||
					has_shortcode( $post->post_content, 'wcv_vendor_dashboard' ) ||
					has_shortcode( $post->post_content, 'wcv_pro_dashboard_nav' ) ||
					has_shortcode( $post->post_content, 'wcv_pro_dashboard' )
				)
			) {
				return false;
			}

			// No changes.
			return $do_search;
		}

		/**
		 * Google Listings & Ads: Add EAN.
		 *
		 * @version 5.5.6
		 * @since   4.7.5
		 *
		 * @param array $values The existing values.
		 *
		 * @return array The modified values.
		 */
		public function gla_add_ean( $values ) {
			$values[] = wpfactory_wc_ean()->core->ean_key;
			return $values;
		}

		/**
		 * MultiVendorX: Save EAN field.
		 *
		 * @version 5.5.6
		 * @since   4.7.3
		 *
		 * @param WC_Product $product The product object.
		 * @param array      $_post   The data.
		 *
		 * @return void
		 */
		public function mvx_save_ean_field( $product, $_post ) {
			if ( isset( $_post['alg_wc_ean_mvx'] ) ) {
				$product->update_meta_data( wpfactory_wc_ean()->core->ean_key, wc_clean( $_post['alg_wc_ean_mvx'] ) );
			}
		}

		/**
		 * MultiVendorX: Add EAN field.
		 *
		 * @version 5.5.6
		 * @since   4.7.3
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return void
		 */
		public function mvx_add_ean_field( $post_id ) {
			$title       = get_option( 'alg_wc_ean_mvx_title', __( 'EAN:', 'ean-for-woocommerce' ) );
			$placeholder = get_option( 'alg_wc_ean_mvx_placeholder', '' );
			$value       = wpfactory_wc_ean()->core->get_ean( $post_id );
			?>
			<div class="add-product-info-holder row-padding">
				<label for="alg_wc_ean_mvx"><?php echo esc_html( $title ); ?></label>
				<input id="alg_wc_ean_mvx" name="alg_wc_ean_mvx" class="form-control inline-input" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>">
			</div>
			<?php
		}

		/**
		 * Woocommerce OpenPos: Barcode key setting.
		 *
		 * @version 5.5.6
		 * @since   4.2.0
		 *
		 * @param array $keys The existing keys.
		 *
		 * @return array The modified keys.
		 */
		public function op_barcode_key_setting( $keys ) {
			$keys[ wpfactory_wc_ean()->core->ean_key ] = get_option( 'alg_wc_ean_title', esc_html__( 'EAN', 'ean-for-woocommerce' ) );
			return $keys;
		}

		/**
		 * WooCommerce Customer / Order / Coupon Export: Add column.
		 *
		 * @version 3.8.0
		 * @since   3.8.0
		 *
		 * @param array  $data_sources The existing data sources.
		 * @param string $export_type  The export type.
		 * @param string $output_type  The output type.
		 *
		 * @return array The modified data sources.
		 *
		 * @todo (dev) `item_ean` to `item_alg_wc_ean`?
		 * @todo (dev) XML (at least in `OrderLineItems`)?
		 */
		public function wc_customer_order_export_add_column( $data_sources, $export_type, $output_type ) {
			if ( 'orders' === $export_type && 'csv' === $output_type ) {
				$data_sources[] = 'item_ean';
			}
			return $data_sources;
		}

		/**
		 * WooCommerce Customer / Order / Coupon Export: Render column.
		 *
		 * @version 5.6.1
		 * @since   3.8.0
		 *
		 * @param array $data The data.
		 * @param array $item The item.
		 */
		public function wc_customer_order_export_render_column( $data, $item ) {
			$data['item_ean'] = (
				! empty( $item['id'] ) && ( $_item = new WC_Order_Item_Product( $item['id'] ) ) ? // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found, WordPress.CodeAnalysis.AssignmentInTernaryCondition.FoundInTernaryCondition
				wpfactory_wc_ean()->core->get_ean_from_order_item( $_item ) :
				''
			);
			return $data;
		}

		/**
		 * WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels: Initialize `wt_pklist_options`.
		 *
		 * @version 5.4.6
		 * @since   5.4.6
		 */
		public function wt_pklist_options_init() {
			if ( ! isset( $this->wt_pklist_options ) ) {
				$this->wt_pklist_options = array_replace(
					array(
						'content'      => '<p>EAN: [alg_wc_ean]</p>',
						'position'     => 'after_product_meta',
						'documents'    => '',
						'column_title' => __( 'EAN', 'ean-for-woocommerce' ),
						'column_class' => 'wfte_product_table_head_ean wfte_text_center',
						'column_style' => '',
					),
					get_option( 'alg_wc_ean_wt_pklist_options', array() )
				);
			}
		}

		/**
		 * WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels: Check template type.
		 *
		 * @version 5.6.1
		 * @since   3.5.1
		 *
		 * @param string $template_type The template type.
		 *
		 * @return bool
		 */
		public function wt_pklist_check_template_type( $template_type ) {
			$this->wt_pklist_options_init();
			if ( ! empty( $this->wt_pklist_options['documents'] ) ) {
				if ( ! is_array( $this->wt_pklist_options['documents'] ) ) {
					$this->wt_pklist_options['documents'] = array_map(
						'trim',
						explode( ',', $this->wt_pklist_options['documents'] )
					);
				}
				return ( in_array( $template_type, $this->wt_pklist_options['documents'], true ) );
			}
			return true;
		}

		/**
		 * WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels: Column head EAN.
		 *
		 * @version 5.6.1
		 * @since   3.5.1
		 *
		 * @param array  $columns       The existing columns.
		 * @param string $template_type The template type.
		 *
		 * @return array The modified columns.
		 */
		public function add_to_wt_pklist_column_head_ean( $columns, $template_type ) {
			if ( $this->wt_pklist_check_template_type( $template_type ) ) {
				$columns['ean'] = '<th' .
					' class="' . $this->wt_pklist_options['column_class'] . '"' .
					' style="' . $this->wt_pklist_options['column_style'] . '"' .
					' col-type="' . sanitize_key( $this->wt_pklist_options['column_title'] ) . '"' .
				'>' . $this->wt_pklist_options['column_title'] . '</th>';
			}
			return $columns;
		}

		/**
		 * WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels: Column EAN.
		 *
		 * @version 3.5.1
		 * @since   3.5.1
		 *
		 * @param string $value         The existing value.
		 * @param string $template_type The template type.
		 * @param string $column_key    The column key.
		 * @param object $product       The product.
		 * @param array  $order_item    The order item.
		 * @param object $order         The order.
		 *
		 * @return string The modified value.
		 */
		public function add_to_wt_pklist_column_ean( $value, $template_type, $column_key, $product, $order_item, $order ) {
			if ( 'ean' === $column_key ) {
				$value = $this->add_to_wt_pklist_ean( $value, $template_type, $product, $order_item, $order );
			}
			return $value;
		}

		/**
		 * WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels: EAN.
		 *
		 * @version 5.6.1
		 * @since   3.5.0
		 *
		 * @param string $value         The existing value.
		 * @param string $template_type The template type.
		 * @param object $product       The product.
		 * @param array  $order_item    The order item.
		 * @param object $order         The order.
		 *
		 * @return string The modified value.
		 *
		 * @see https://wordpress.org/plugins/print-invoices-packing-slip-labels-for-woocommerce/
		 */
		public function add_to_wt_pklist_ean( $value, $template_type, $product, $order_item, $order ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			if ( $this->wt_pklist_check_template_type( $template_type ) ) {
				$result = wpfactory_wc_ean()->core->shortcodes->do_shortcode(
					$this->wt_pklist_options['content'],
					array( 'product_id' => $product->get_id() )
				);
				$value  = (
					in_array(
						$this->wt_pklist_options['position'],
						array( 'after_product_meta', 'after_product_name' ),
						true
					) ?
					$value . $result :
					$result . $value
				);
			}
			return $value;
		}

		/**
		 * WooCommerce Google Product Feed: Add to custom field list.
		 *
		 * @version 5.5.6
		 * @since   3.0.0
		 *
		 * @param array $fields The fields.
		 *
		 * @return array The modified fields.
		 */
		public function add_to_woocommerce_gpf_custom_field_list( $fields ) {
			$fields[ 'meta:' . wpfactory_wc_ean()->core->ean_key ] = __( 'EAN', 'ean-for-woocommerce' );
			return $fields;
		}

		/**
		 * Print Invoice & Delivery Notes for WooCommerce: Add EAN.
		 *
		 * @version 5.6.1
		 * @since   1.4.0
		 *
		 * @param object $product The product.
		 * @param object $order   The order.
		 * @param array  $item    The item.
		 *
		 * @return void
		 *
		 * @todo (feature) Customizable wrapper.
		 * @todo (dev) Check if valid?
		 */
		public function add_to_wcdn_ean( $product, $order, $item ) {
			$ean = wpfactory_wc_ean()->core->get_ean_from_order_item( $item );
			if ( false !== $ean ) {
				echo '<small class="ean_wrapper">' .
					esc_html( get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) ) . ' ' .
					'<span class="ean">' . wp_kses_post( $ean ) . '</span>' .
				'</small>';
			}
		}

		/**
		 * WooCommerce PDF Invoices & Packing Slips: Add_EAN.
		 *
		 * @version 5.5.6
		 * @since   2.6.0
		 *
		 * @param string $type  The type.
		 * @param array  $item  The item.
		 * @param object $order The order.
		 *
		 * @return void
		 *
		 * @todo (dev) Check if valid?
		 */
		public function add_to_wpo_wcpdf_ean( $type, $item, $order ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			if (
				! empty( $item['item_id'] ) &&
				( $item = new WC_Order_Item_Product( $item['item_id'] ) ) && // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure, Generic.CodeAnalysis.AssignmentInCondition.Found
				false !== ( $ean = wpfactory_wc_ean()->core->get_ean_from_order_item( $item ) ) // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure, Generic.CodeAnalysis.AssignmentInCondition.Found
			) {
				$options  = get_option( 'alg_wc_ean_wpo_wcpdf_options', array() );
				$template = (
					isset( $options['content'] ) ?
					$options['content'] :
					'<dl class="meta">' .
						'<dt class="ean">' .
							esc_html( get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) ) . ':' .
						'</dt>' .
						'<dd class="ean">' .
							'%ean%' .
						'</dd>' .
					'</dl>'
				);
				echo wp_kses_post( str_replace( '%ean%', $ean, $template ) );
			}
		}

		/**
		 * WCFM: Save EAN field.
		 *
		 * @version 5.5.6
		 * @since   2.2.2
		 *
		 * @param int   $new_product_id                 The new product ID.
		 * @param array $wcfm_products_manage_form_data The form data.
		 *
		 * @return void
		 *
		 * @see https://plugins.svn.wordpress.org/wc-frontend-manager/tags/6.5.10/controllers/products-manager/wcfm-controller-products-manage.php
		 */
		public function wcfm_save_ean_field( $new_product_id, $wcfm_products_manage_form_data ) {
			$id = 'wcfm_' . wpfactory_wc_ean()->core->ean_key;
			if ( isset( $wcfm_products_manage_form_data[ $id ] ) ) {
				wpfactory_wc_ean()->core->set_ean( $new_product_id, wc_clean( $wcfm_products_manage_form_data[ $id ] ) );
			}
		}

		/**
		 * WCFM: Add EAN field.
		 *
		 * @version 5.5.6
		 * @since   2.2.2
		 *
		 * @param array  $fields       The fields.
		 * @param int    $product_id   The product ID.
		 * @param string $product_type The product type.
		 *
		 * @return array The updated fields.
		 *
		 * @see https://plugins.svn.wordpress.org/wc-frontend-manager/tags/6.5.10/views/products-manager/wcfm-view-products-manage-tabs.php
		 *
		 * @todo (dev) Do we need `esc_html` everywhere, e.g., in `hints` (same for `dokan_add_ean_field()`)?
		 * @todo (feature) Optional EAN validation.
		 */
		public function wcfm_add_ean_field( $fields, $product_id, $product_type ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			$_key = 'wcfm_' . wpfactory_wc_ean()->core->ean_key;

			// "Generate" button.
			$do_add_generate_button = ( 'yes' === get_option( 'alg_wc_ean_wcfm_add_generate_button', 'no' ) && $product_id );

			// Field data.
			$_field = array(
				'label'       => esc_html( get_option( 'alg_wc_ean_wcfm_title', __( 'EAN', 'ean-for-woocommerce' ) ) ),
				'desc'        => (
					$do_add_generate_button ?
					'<p class="wpfactory_wc_ean_generate_button_wrapper">' .
						WPFactory_WC_EAN_Edit::get_generate_button( $product_id, $_key ) .
					'</p>' :
					''
				),
				'type'        => 'text',
				'class'       => 'wcfm-text',
				'label_class' => 'wcfm_title',
				'value'       => wpfactory_wc_ean()->core->get_ean( $product_id ),
				'hints'       => esc_html(
					get_option(
						'alg_wc_ean_wcfm_hints',
						__( 'The International Article Number (also known as European Article Number or EAN) is a standard describing a barcode symbology and numbering system used in global trade to identify a specific retail product type, in a specific packaging configuration, from a specific manufacturer.', 'ean-for-woocommerce' )
					)
				),
				'placeholder' => esc_html(
					get_option(
						'alg_wc_ean_wcfm_placeholder',
						__( 'Product EAN...', 'ean-for-woocommerce' )
					)
				),
			);

			// Add field.
			$_fields  = array();
			$is_added = false;
			foreach ( $fields as $key => $field ) {
				$_fields[ $key ] = $field;
				if ( 'sku' === $key ) {
					$_fields[ $_key ] = $_field;
					$is_added         = true;
				}
			}
			if ( ! $is_added ) {
				$_fields[ $_key ] = $_field; // Fallback.
			}

			return $_fields;
		}

		/**
		 * WCFM: Generate button AJAX.
		 *
		 * @version 5.5.9
		 * @since   5.5.5
		 *
		 * @todo (v5.5.5) Check for `'yes' === get_option( 'alg_wc_ean_wcfm_add_generate_button', 'no' )`?
		 */
		public function wcfm_generate_button_ajax() {
			if (
				! isset( $_POST['generate_action'] ) || // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in `WPFactory_WC_EAN_Edit::generate_button_ajax()`.
				'generate' !== sanitize_text_field( wp_unslash( $_POST['generate_action'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in `WPFactory_WC_EAN_Edit::generate_button_ajax()`.
			) {
				die();
			}

			WPFactory_WC_EAN_Edit::generate_button_ajax();
		}

		/**
		 * WCFM: Generate button script and style.
		 *
		 * @version 5.5.7
		 * @since   5.5.5
		 *
		 * @todo (v5.5.5) Check if it's a WCFM page(s).
		 */
		public function wcfm_generate_button_script_and_style() {
			if ( 'no' === get_option( 'alg_wc_ean_wcfm_add_generate_button', 'no' ) ) {
				return;
			}

			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script(
				'wpfactory-wc-ean-generate-button',
				wpfactory_wc_ean()->plugin_url() . '/assets/js/wpfactory-wc-ean-generate-button' . $min . '.js',
				array( 'jquery' ),
				wpfactory_wc_ean()->version,
				true
			);

			wp_localize_script(
				'wpfactory-wc-ean-generate-button',
				'wpfactoryWCEANGenerateButton',
				array(
					'nonce' => wp_create_nonce( 'wpfactory_wc_ean_generate_ean' ),
				),
			);

			wp_enqueue_style(
				'wpfactory-wc-ean-wcfm-generate-button',
				wpfactory_wc_ean()->plugin_url() . '/assets/css/wpfactory-wc-ean-wcfm-generate-button' . $min . '.css',
				array(),
				wpfactory_wc_ean()->version
			);
		}

		/**
		 * WCFM: Variation save EAN field.
		 *
		 * @version 5.5.6
		 * @since   4.0.0
		 *
		 * @param int   $product_id   The product ID.
		 * @param int   $variation_id The variation ID.
		 * @param array $data         The data.
		 *
		 * @return void
		 *
		 * @todo (dev) Merge with `wcfm_save_ean_field()`?
		 */
		public function wcfm_variation_save_ean_field( $product_id, $variation_id, $data ) {
			$id = 'wcfm_' . wpfactory_wc_ean()->core->ean_key;
			if ( isset( $data[ $id ] ) ) {
				wpfactory_wc_ean()->core->set_ean( $variation_id, wc_clean( $data[ $id ] ) );
			}
		}

		/**
		 * WCFM: Variation add EAN field.
		 *
		 * @version 5.5.6
		 * @since   4.0.0
		 *
		 * @param array $fields The fields.
		 *
		 * @return array The updated fields.
		 *
		 * @todo (dev) Placeholder: parent product's EAN?
		 * @todo (dev) `wcfm_half_ele`?
		 * @todo (dev) Merge with `wcfm_add_ean_field()`?
		 */
		public function wcfm_variation_add_ean_field( $fields ) {
			// Field data.
			$_field = array(
				'label'       => esc_html(
					get_option(
						'alg_wc_ean_wcfm_title',
						__( 'EAN', 'ean-for-woocommerce' )
					)
				),
				'type'        => 'text',
				'class'       => 'wcfm-text wcfm_ele wcfm_half_ele variable variable-subscription pw-gift-card',
				'label_class' => 'wcfm_title wcfm_half_ele_title',
				'hints'       => esc_html(
					get_option(
						'alg_wc_ean_wcfm_hints',
						__( 'The International Article Number (also known as European Article Number or EAN) is a standard describing a barcode symbology and numbering system used in global trade to identify a specific retail product type, in a specific packaging configuration, from a specific manufacturer.', 'ean-for-woocommerce' )
					)
				),
				'placeholder' => esc_html(
					get_option(
						'alg_wc_ean_wcfm_placeholder',
						__( 'Product EAN...', 'ean-for-woocommerce' )
					)
				),
			);

			// Add field.
			$_fields  = array();
			$_key     = 'wcfm_' . wpfactory_wc_ean()->core->ean_key;
			$is_added = false;
			foreach ( $fields as $key => $field ) {
				$_fields[ $key ] = $field;
				if ( 'sku' === $key ) {
					$_fields[ $_key ] = $_field;
					$is_added         = true;
				}
			}
			if ( ! $is_added ) {
				$_fields[ $_key ] = $_field; // Fallback.
			}

			return $_fields;
		}

		/**
		 * WCFM: Variation edit data.
		 *
		 * @version 5.5.6
		 * @since   4.0.0
		 *
		 * @param array  $variations       The variations.
		 * @param int    $variation_id     The variation ID.
		 * @param string $variation_id_key The variation ID key.
		 *
		 * @return array The updated variations.
		 */
		public function wcfm_variation_edit_data( $variations, $variation_id, $variation_id_key ) {
			if ( $variation_id ) {
				$variations[ $variation_id_key ][ 'wcfm_' . wpfactory_wc_ean()->core->ean_key ] = wpfactory_wc_ean()->core->get_ean( $variation_id );
			}
			return $variations;
		}

		/**
		 * WCFM: Variation generate button script.
		 *
		 * @version 5.5.6
		 * @since   4.0.0
		 *
		 * @todo (v5.5.5) Check if it's a WCFM page(s).
		 * @todo (dev) Merge with `WPFactory_WC_EAN_Edit::get_generate_button()`?
		 */
		public function wcfm_variation_generate_button_script() {
			if ( 'no' === get_option( 'alg_wc_ean_wcfm_add_generate_button', 'no' ) ) {
				return;
			}

			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script(
				'wpfactory-wc-ean-wcfm-variation-generate-button',
				wpfactory_wc_ean()->plugin_url() . '/assets/js/wpfactory-wc-ean-wcfm-variation-generate-button' . $min . '.js',
				array( 'jquery' ),
				wpfactory_wc_ean()->version,
				true
			);

			$button_label = sprintf(
				/* Translators: %s: EAN title. */
				esc_html__( 'Generate %s', 'ean-for-woocommerce' ),
				get_option( 'alg_wc_ean_title', esc_html__( 'EAN', 'ean-for-woocommerce' ) )
			);
			wp_localize_script(
				'wpfactory-wc-ean-wcfm-variation-generate-button',
				'wpfactoryWCEANWCFMVariationGenerateButton',
				array(
					'eanKey'      => wpfactory_wc_ean()->core->ean_key,
					'buttonLabel' => $button_label,
				),
			);
		}

		/**
		 * Dokan:_Add EAN field variation.
		 *
		 * @version 5.5.6
		 * @since   3.1.2
		 *
		 * @param int    $loop           The loop index.
		 * @param array  $variation_data The variation data.
		 * @param object $variation      The variation object.
		 *
		 * @return void
		 */
		public function dokan_add_ean_field_variation( $loop, $variation_data, $variation ) {
			$key           = wpfactory_wc_ean()->core->ean_key;
			$id            = "variable{$key}_{$loop}";
			$name          = "variable{$key}[{$loop}]";
			$value         = wpfactory_wc_ean()->core->get_ean( $variation->ID );
			$title         = esc_html( get_option( 'alg_wc_ean_dokan_title', __( 'EAN', 'ean-for-woocommerce' ) ) );
			$placeholder   = wpfactory_wc_ean()->core->get_ean( $variation->post_parent );
			$required      = ( 'yes' === get_option( 'alg_wc_ean_dokan_required', 'no' ) ? ' required' : '' );
			$required_html = ( 'yes' === get_option( 'alg_wc_ean_dokan_required', 'no' ) ? get_option( 'alg_wc_ean_dokan_required_html', '' ) : '' );
			$desc          = (
				'' !== get_option( 'alg_wc_ean_dokan_desc', '' ) ?
				wpfactory_wc_ean()->core->shortcodes->do_shortcode(
					get_option( 'alg_wc_ean_dokan_desc', '' ),
					array(
						'ean'        => $value,
						'product_id' => $variation->ID,
					)
				) :
				''
			);

			echo '<div class="dokan-form-group">' .
				'<label for="' . esc_attr( $id ) . '" class="form-label">' . wp_kses_post( $title . $required_html ) . '</label>' .
				'<input type="text" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" class="dokan-form-control wpfactory-wc-ean" placeholder="' . esc_attr( $placeholder ) . '" value="' . esc_attr( $value ) . '"' . esc_attr( $required ) . '>' .
				wp_kses_post( $desc ) .
			'</div>';
		}

		/**
		 * Dokan: Save EAN field.
		 *
		 * @version 5.5.6
		 * @since   2.2.2
		 *
		 * @param int   $product_id The product ID.
		 * @param array $data       The data.
		 *
		 * @return void
		 *
		 * @see https://github.com/weDevsOfficial/dokan/blob/v3.2.8/includes/Dashboard/Templates/Products.php#L353
		 * @see https://github.com/weDevsOfficial/dokan/blob/v3.2.8/includes/Dashboard/Templates/Products.php#L482
		 * @see https://github.com/weDevsOfficial/dokan/blob/v3.2.8/includes/Product/functions.php#L127
		 * @see https://github.com/weDevsOfficial/dokan/blob/v3.2.8/includes/Product/functions.php#L129
		 * @see https://github.com/weDevsOfficial/dokan/blob/v3.2.8/includes/REST/Manager.php#L172
		 *
		 * @todo (dev) `alg_wc_ean_dokan_required`: Add server-side validation.
		 */
		public function dokan_save_ean_field( $product_id, $data ) {
			$id = 'dokan_' . wpfactory_wc_ean()->core->ean_key;
			if ( isset( $data[ $id ] ) ) {
				wpfactory_wc_ean()->core->set_ean( $product_id, wc_clean( $data[ $id ] ) );
			}
		}

		/**
		 * Dokan: Add EAN field.
		 *
		 * @version 5.5.6
		 * @since   2.2.2
		 *
		 * @param object|false $post    The post object or false.
		 * @param int|false    $post_id The post ID or false.
		 *
		 * @return void
		 *
		 * @see https://github.com/weDevsOfficial/dokan/blob/v3.2.8/templates/products/new-product.php#L257
		 * @see https://github.com/weDevsOfficial/dokan/blob/v3.2.8/templates/products/tmpl-add-product-popup.php#L148
		 * @see https://github.com/weDevsOfficial/dokan/blob/v3.2.8/templates/products/new-product-single.php#L338
		 *
		 * @todo (feature) JS EAN validation?
		 */
		public function dokan_add_ean_field( $post = false, $post_id = false ) {
			$id            = 'dokan_' . wpfactory_wc_ean()->core->ean_key;
			$value         = ( // Edit product vs Add product.
				! empty( $post_id ) ?
				wpfactory_wc_ean()->core->get_ean( $post_id ) :
				(
					isset( $_REQUEST[ $id ] ) ? // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					sanitize_text_field( wp_unslash( $_REQUEST[ $id ] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					''
				)
			);
			$title         = get_option(
				'alg_wc_ean_dokan_title',
				__( 'EAN', 'ean-for-woocommerce' )
			);
			$placeholder   = get_option(
				'alg_wc_ean_dokan_placeholder',
				__( 'Product EAN...', 'ean-for-woocommerce' )
			);
			$required      = (
				'yes' === get_option( 'alg_wc_ean_dokan_required', 'no' ) ?
				' required' :
				''
			);
			$required_html = (
				'yes' === get_option( 'alg_wc_ean_dokan_required', 'no' ) ?
				get_option( 'alg_wc_ean_dokan_required_html', '' ) :
				''
			);
			$desc          = (
				'' !== get_option( 'alg_wc_ean_dokan_desc', '' ) ?
				wpfactory_wc_ean()->core->shortcodes->do_shortcode(
					get_option( 'alg_wc_ean_dokan_desc', '' ),
					array(
						'ean'        => $value,
						'product_id' => $post_id,
					)
				) :
				''
			);

			?>
			<div class="dokan-form-group">
				<label
					for="<?php echo esc_attr( $id ); ?>"
					class="form-label"
				>
					<?php echo esc_html( $title ); ?><?php echo wp_kses_post( $required_html ); ?>
				</label>
				<input
					type="text"
					name="<?php echo esc_attr( $id ); ?>"
					id="<?php echo esc_attr( $id ); ?>"
					class="dokan-form-control wpfactory-wc-ean"
					placeholder="<?php echo esc_attr( $placeholder ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					<?php echo esc_attr( $required ); ?>
				>
				<?php echo wp_kses_post( $desc ); ?>
			</div>
			<?php
		}

		/**
		 * Point of Sale for WooCommerce: Scanning fields.
		 *
		 * Adds "EAN" field to the "Scanning Fields" option in "Point of Sale > Settings > Register".
		 *
		 * @version 5.5.6
		 * @since   2.2.0
		 *
		 * @param array $fields The existing fields.
		 *
		 * @return array The modified fields.
		 *
		 * @see https://woocommerce.com/products/point-of-sale-for-woocommerce/
		 */
		public function wc_pos_scanning_fields( $fields ) {
			$fields[ wpfactory_wc_ean()->core->ean_key ] = __( 'EAN', 'ean-for-woocommerce' );
			return $fields;
		}

		/**
		 * Point of Sale for WooCommerce: Add EAN to product name.
		 *
		 * @version 5.5.6
		 * @since   3.8.0
		 *
		 * @param object $response The response object.
		 * @param object $product  The product object.
		 * @param object $request  The request object.
		 *
		 * @return object The modified response object.
		 *
		 * @see https://woocommerce.com/products/point-of-sale-for-woocommerce/
		 *
		 * @todo (dev) `get_route()`: Better solution, e.g., exact match with `/wc-pos/products`?
		 * @todo (dev) Find better solution, e.g., add elsewhere, not to the name?
		 */
		public function wc_pos_add_ean_to_product_name( $response, $product, $request ) {
			if (
				( false !== strpos( $request->get_route(), '/wc-pos/' ) ) &&
				'' !== ( $ean = wpfactory_wc_ean()->core->get_ean( $product->get_id() ) ) // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure, Generic.CodeAnalysis.AssignmentInCondition.Found
			) {
				$response->data['name'] .= ' (' . sprintf(
					/* Translators: %s: EAN. */
					__( 'EAN: %s', 'ean-for-woocommerce' ),
					$ean
				) . ')';
			}
			return $response;
		}
	}

endif;

return new WPFactory_WC_EAN_Compatibility();

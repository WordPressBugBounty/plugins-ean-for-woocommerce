<?php
/**
 * EAN for WooCommerce - Tools Section Settings
 *
 * @version 4.9.1
 * @since   2.2.5
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Settings_Tools' ) ) :

class Alg_WC_EAN_Settings_Tools extends Alg_WC_EAN_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.2.5
	 * @since   2.2.5
	 *
	 * @todo    (dev) rename to "Product Tools"?
	 */
	function __construct() {
		$this->id   = 'tools';
		$this->desc = __( 'Tools', 'ean-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_product_actions_list.
	 *
	 * @version 4.4.2
	 * @since   2.9.0
	 */
	function get_product_actions_list() {
		return array(
			''             => __( 'Disabled', 'ean-for-woocommerce' ),
			'generate'     => __( 'Generate', 'ean-for-woocommerce' ),
			'copy_sku'     => __( 'Copy from product SKU', 'ean-for-woocommerce' ),
			'copy_id'      => __( 'Copy from product ID', 'ean-for-woocommerce' ),
			'copy_meta'    => __( 'Copy from product meta', 'ean-for-woocommerce' ),
			'copy_attr'    => __( 'Copy from product attribute', 'ean-for-woocommerce' ),
			'assign_list'  => __( 'Assign from the list', 'ean-for-woocommerce' ),
			'copy_to_sku'  => __( 'Copy to product SKU', 'ean-for-woocommerce' ),
			'copy_to_meta' => __( 'Copy to product meta', 'ean-for-woocommerce' ),
			'copy_to_attr' => __( 'Copy to product attribute', 'ean-for-woocommerce' ),
		);
	}

	/**
	 * get_missing_product_cat_title.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function get_missing_product_cat_title( $term_id ) {
		return sprintf( __( 'Product category #%s', 'ean-for-woocommerce' ), $term_id );
	}

	/**
	 * get_terms.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @todo    (desc) add term ID?
	 */
	function get_terms( $taxonomy, $option ) {
		$terms  = get_option( $option, array() );
		$terms  = ( ! empty( $terms[ $taxonomy ] ) ? array_combine( $terms[ $taxonomy ], array_map( array( $this, "get_missing_{$taxonomy}_title" ), $terms[ $taxonomy ] ) ) : array() );
		$_terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		$_terms = ( ! empty( $_terms ) && ! is_wp_error( $_terms ) ? array_combine( wp_list_pluck( $_terms, 'term_id' ), wp_list_pluck( $_terms, 'name' ) ) : array() );
		return array_replace( $terms, $_terms );
	}

	/**
	 * get_product_attributes.
	 *
	 * @version 4.6.0
	 * @since   3.7.2
	 */
	function get_product_attributes( $none_title = false, $do_add_custom = false ) {
		$options     = array();
		$options[''] = ( $none_title ? $none_title : esc_html__( 'Disable', 'ean-for-woocommerce' ) );
		if ( $do_add_custom ) {
			$options['alg_wc_ean_product_attribute_custom'] = '(' . esc_html__( 'Custom', 'ean-for-woocommerce' ) . ')';
		}
		$taxonomies  = wc_get_attribute_taxonomies();
		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $tax ) {
				$id             = esc_attr( wc_attribute_taxonomy_name( $tax->attribute_name ) );
				$label          = esc_html( $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name );
				$options[ $id ] = $label;
			}
		}
		return $options;
	}

	/**
	 * get_settings.
	 *
	 * @version 4.9.1
	 * @since   2.2.5
	 *
	 * @todo    (dev) Generate: Type: `EAN14`
	 * @todo    (dev) `alg_wc_ean_product_bulk_actions_confirm`: better default value?
	 * @todo    (dev) "Product tags" (similar to "Product categories")
	 * @todo    (dev) add "Product categories" (and "Product tags") to all tools (now only in "Assign from the list")
	 * @todo    (desc) add subsections, e.g.: "Automatic and Periodic Actions", "Bulk Actions"
	 * @todo    (desc) Periodic action: rename?
	 * @todo    (desc) `seed_prefix`
	 * @todo    (desc) better desc for all tools?
	 */
	function get_settings() {
		$settings = array(
			array(
				'title'    => __( 'Product Tools', 'ean-for-woocommerce' ),
				'desc'     => sprintf( __( 'Check the %s box and "Save changes" to run the tool. Please note that there is no undo for these tools.', 'ean-for-woocommerce' ),
						'<span class="dashicons dashicons-admin-generic"></span>' ) . '<br><br>' .
					sprintf( __( 'Please note that %s, %s and %s tools will <strong>not</strong> overwrite EANs for products with existing EANs.', 'ean-for-woocommerce' ),
						'<strong>' . __( 'Generate', 'ean-for-woocommerce' ) . '</strong>',
						'<strong>' . __( 'Copy', 'ean-for-woocommerce' ) . '</strong>',
						'<strong>' . __( 'Assign', 'ean-for-woocommerce' ) . '</strong>' ) . ' ' .
					sprintf( __( 'You can use the %s tool to clear the existing EANs before generating or copying.', 'ean-for-woocommerce' ),
						'<strong>' . __( 'Delete', 'ean-for-woocommerce' ) . '</strong>' ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_tools_products',
			),
			array(
				'title'    => __( 'Generate', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Generate EAN for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[generate]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Type', 'ean-for-woocommerce' ),
				'desc_tip' =>
					'* ' . __( 'To generate ISBN-13, set this option to "EAN-13", and then set the "Country prefix" option to 978 or 979.', 'ean-for-woocommerce' ) . '<br><br>' .
					'* ' . __( 'To generate JAN, set this option to "EAN-13", and then set the "Country prefix" option to a number in the 450-459 range or in the 490-499 range.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_generate[type]',
				'default'  => 'EAN13',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'EAN8'  => 'EAN-8',
					'UPCA'  => 'UPC-A',
					'EAN13' => 'EAN-13',
				),
			),
			array(
				'desc'     => __( 'Country prefix (from)', 'ean-for-woocommerce' ) . ' ' .
					sprintf( '<a target="_blank" title="%s" style="text-decoration:none;" href="%s">%s</a>',
						__( 'List of GS1 country codes.', 'ean-for-woocommerce' ),
						'https://en.wikipedia.org/wiki/List_of_GS1_country_codes',
						'<span class="dashicons dashicons-external"></span>' ),
				'id'       => 'alg_wc_ean_tool_product_generate[prefix]',
				'default'  => 200,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'max' => 999 ),
			),
			array(
				'desc'     => __( 'County prefix (to)', 'ean-for-woocommerce' ) . ' (' . __( 'optional', 'ean-for-woocommerce' ) . ')',
				'desc_tip' => sprintf( __( 'If set, prefix will be generated randomly between "%s" and "%s" values.', 'ean-for-woocommerce' ),
					__( 'Prefix from', 'ean-for-woocommerce' ), __( 'Prefix to', 'ean-for-woocommerce' ) ),
				'id'       => 'alg_wc_ean_tool_product_generate[prefix_to]',
				'default'  => '',
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'max' => 999 ),
			),
			array(
				'desc'     => __( 'County prefix length', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'For EAN-8 type only. County prefix length for other types will always be 3.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_generate[prefix_length]',
				'default'  => 3,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 2, 'max' => 3 ),
			),
			array(
				'desc'     => __( 'Seed prefix', 'ean-for-woocommerce' ) . ' (' . __( 'optional', 'ean-for-woocommerce' ) . ')',
				'desc_tip' => __( 'Or manufacturer code.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_generate[seed_prefix]',
				'default'  => '',
				'type'     => 'text',
				'custom_attributes' => array( 'pattern' => '[0-9]+' ),
			),
			array(
				'desc'     => __( 'Seed', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Or product code.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_generate[seed_method]',
				'default'  => 'product_id',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'product_id' => __( 'Product ID', 'ean-for-woocommerce' ),
					'counter'    => __( 'Counter', 'ean-for-woocommerce' ),
				),
			),
			array(
				'desc'     => __( 'Seed counter', 'ean-for-woocommerce' ) . ' (' . __( 'ignored unless "Seed" option is set to "Counter"', 'ean-for-woocommerce' ) . ')',
				'id'       => 'alg_wc_ean_tool_product_generate_seed_counter',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0 ),
			),
			array(
				'desc'     => __( 'Product attribute', 'ean-for-woocommerce' ) . ' (' . __( 'optional', 'ean-for-woocommerce' ) . ')',
				'desc_tip' => __( 'If enabled, will copy the generated EAN to the selected product attribute as well.', 'ean-for-woocommerce' ) . ' (' . __( 'optional', 'ean-for-woocommerce' ) . ')',
				'id'       => 'alg_wc_ean_tool_product_generate[product_attribute]',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => $this->get_product_attributes(),
			),
			array(
				'title'    => __( 'Copy from product SKU', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Copy EAN from product SKU for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[copy_sku]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Copy from product ID', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Copy EAN from product ID for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[copy_id]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Copy from product meta', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Copy EAN from product meta for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[copy_meta]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => sprintf( __( 'Meta key, e.g., %s', 'ean-for-woocommerce' ), '<code>_gtin</code>' ),
				'desc_tip' => __( 'Product meta key to copy from.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_copy_meta[key]',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'desc'     => __( 'Meta sub key', 'ean-for-woocommerce' ) . ' (' . __( 'optional', 'ean-for-woocommerce' ) . ')',
				'desc_tip' => __( 'Optional sub key. This is used when meta was saved in an array.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_copy_meta[sub_key]',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Copy from product attribute', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Copy EAN from product attribute for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[copy_attr]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Product attribute', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_copy_attr[product_attribute]',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => $this->get_product_attributes( __( 'Select attribute...', 'ean-for-woocommerce' ), true ),
			),
			array(
				'desc'     => __( 'Custom product attribute', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Ignored, unless the "Product attribute" option is set to "Custom".', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_copy_attr[product_attribute_custom]',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Assign from the list', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Assign EAN from the list for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[assign_list]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Product categories', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Sets which product categories to include when assigning the EANs (i.e., all other categories will be skipped).', 'ean-for-woocommerce' ) . ' ' .
					__( 'Leave blank to use all product categories.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_assign_list_settings[product_cat]',
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => $this->get_terms( 'product_cat', 'alg_wc_ean_tool_product_assign_list_settings' ),
			),
			array(
				'desc'     => __( 'Reuse deleted', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Will add EANs from the deleted products to the "List" option.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_assign_list_settings[reuse_deleted]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'List', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'EAN list to assign from.', 'ean-for-woocommerce' ) . ' ' .
					__( 'One EAN per line.', 'ean-for-woocommerce' ) . ' ' .
					__( 'Used (i.e., assigned) EANs will be automatically removed from the list.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_assign_list',
				'default'  => '',
				'type'     => 'textarea',
				'css'      => 'height:100px;',
			),
			array(
				'title'    => __( 'Delete', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Delete all EANs for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_delete_product_meta',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Copy to product SKU', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Copy EAN to the product SKU for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[copy_to_sku]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Copy to product meta', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Copy EAN to the product meta for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[copy_to_meta]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => sprintf( __( 'Meta key, e.g.: %s, or comma-separated list of keys, e.g.: %s', 'ean-for-woocommerce' ), '<code>_gtin</code>', '<code>_gtin,_ean13</code>' ),
				'desc_tip' => __( 'Product meta key to copy to.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_copy_to_meta[key]',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'desc'     => __( 'Meta sub key', 'ean-for-woocommerce' ) . ' (' . __( 'optional', 'ean-for-woocommerce' ) . ')',
				'desc_tip' => __( 'Optional sub key. This is used when meta is saved in an array.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_copy_to_meta[sub_key]',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Copy to product attribute', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Copy EAN to the product attribute for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[copy_to_attr]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Product attribute', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_copy_to_attr[product_attribute]',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => $this->get_product_attributes( __( 'Select attribute...', 'ean-for-woocommerce' ) ),
			),
			array(
				'title'    => __( 'Get stats', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'This tool will not assign/delete any EANs, instead it will count how many products in your shop do not have EAN.', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Get stats', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[get_stats]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Variable products', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Sets how variable products should be handled in all product tools.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_variable',
				'default'  => 'all',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'all'             => __( 'All (variable and variations)', 'ean-for-woocommerce' ),
					'variations_only' => __( 'Variations only', 'ean-for-woocommerce' ),
					'variable_only'   => __( 'Variable only', 'ean-for-woocommerce' ),
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_tools_products',
			),
			array(
				'title'    => __( 'Product Actions', 'ean-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_tools_products_actions',
			),
			array(
				'title'    => __( 'Automatic actions', 'ean-for-woocommerce' ),
				'desc'     => __( 'New product', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Sets actions to be automatically performed when new product is added.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_action_on_new',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => $this->get_product_actions_list(),
			),
			array(
				'desc'     => __( 'Update product', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Sets actions to be automatically performed when product is updated.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_action_on_update',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => $this->get_product_actions_list(),
			),
			array(
				'title'    => __( 'Periodic action', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Sets action to be automatically performed on a periodic basis (for all products).', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_products_periodic_action',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => $this->get_product_actions_list(),
			),
			array(
				'desc'     => __( 'Periodic action interval in seconds', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_products_periodic_action_interval',
				'default'  => 3600,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 1 ),
			),
			array(
				'title'    => __( '"Products > Bulk actions"', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Sets actions to be added to the "Products > Bulk actions" dropdown.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_product_bulk_actions',
				'default'  => array( 'alg_wc_ean_delete', 'alg_wc_ean_generate' ),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => array(
					'alg_wc_ean_generate' => __( 'Generate EAN', 'ean-for-woocommerce' ),
					'alg_wc_ean_delete'   => __( 'Delete EAN', 'ean-for-woocommerce' ),
				),
			),
			array(
				'desc'     => __( 'Require confirmation?', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_product_bulk_actions_confirm',
				'default'  => array( 'alg_wc_ean_delete' ),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => array(
					'alg_wc_ean_generate' => __( 'Generate EAN', 'ean-for-woocommerce' ),
					'alg_wc_ean_delete'   => __( 'Delete EAN', 'ean-for-woocommerce' ),
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_tools_products_actions',
			),
		);
		return $settings;
	}

}

endif;

return new Alg_WC_EAN_Settings_Tools();

<?php
/**
 * EAN for WooCommerce - General Section Settings
 *
 * @version 5.5.2
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Settings_General' ) ) :

class Alg_WC_EAN_Settings_General extends Alg_WC_EAN_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id   = '';
		$this->desc = __( 'General', 'ean-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_types_desc.
	 *
	 * @version 4.7.0
	 * @since   3.3.0
	 *
	 * @see     https://en.wikipedia.org/wiki/Global_Trade_Item_Number
	 * @see     https://en.wikipedia.org/wiki/EAN-8
	 * @see     https://en.wikipedia.org/wiki/Universal_Product_Code
	 * @see     https://en.wikipedia.org/wiki/International_Article_Number
	 * @see     https://en.wikipedia.org/wiki/International_Standard_Book_Number
	 * @see     https://en.wikipedia.org/wiki/International_Article_Number#jan
	 * @see     https://www.barcode.graphics/gtin-14-encoding/
	 */
	function get_types_desc() {
		$types = array(
			'EAN-8'    => array(
				'desc'   => __( 'Less commonly used EAN standard. An EAN-8 number includes a 2- or 3-digit GS1 prefix, 5- or 4-digit item reference element (depending on the length of the GS1 prefix), and a checksum digit.', 'ean-for-woocommerce' ),
				'length' => 8,
			),
			'UPC-A'    => array(
				'desc'   => __( 'The UPC-A barcode is the most common type in the United States. UPC (technically refers to UPC-A) consists of 12 digits. It begins with a single digit number system character, which designates how the code should be classified: as a regular product, a weighted item, pharmaceuticals, coupons, etc. After that is a five digit manufacturer\'s number, followed by a five digit product number, and finally a checksum digit.', 'ean-for-woocommerce' ),
				'length' => 12,
			),
			'EAN-13'   => array(
				'desc'   => __( 'This is the most commonly used EAN standard. An EAN-13 number includes a 3-digit GS1 prefix, 9-digit manufacturer and product code, and a checksum digit.', 'ean-for-woocommerce' ),
				'length' => 13,
			),
			'ISBN-13'  => array(
				'desc'   => __( 'The International Standard Book Number (ISBN) is a numeric commercial book identifier. It\'s a subset of EAN-13 - with <code>978</code> or <code>979</code> prefix.', 'ean-for-woocommerce' ),
				'length' => 13,
			),
			'JAN'      => array(
				'desc'   => __( 'Japanese Article Number (JAN) is a subset of EAN-13 - with <code>45</code> or <code>49</code> prefix.', 'ean-for-woocommerce' ),
				'length' => 13,
			),
			'EAN14'    => array(
				'desc'   => __( 'EAN-14 is a 14 digit number used to identify trade items at various packaging levels. The first digit denotes the level of packaging.', 'ean-for-woocommerce' ),
				'length' => 14,
			),
			__( 'Custom', 'ean-for-woocommerce' ) => array(
				'desc'   => __( 'Custom can represent all 128 ASCII code characters (numbers, upper case/lower case letters, symbols and control codes).', 'ean-for-woocommerce' ),
				'length' => __( 'Any', 'ean-for-woocommerce' ),
			),
		);
		$result  = '';
		$result .= '<table class="widefat striped">' .
			'<tr>' .
				'<td><strong>' . __( 'Type', 'ean-for-woocommerce' ) . '</td>' .
				'<td><strong>' . __( 'Length', 'ean-for-woocommerce' ) . '</td>' .
				'<td><strong>' . __( 'Description', 'ean-for-woocommerce' ) . '</td>' .
			'</tr>';
		foreach ( $types as $title => $data ) {
			$result .= '<tr>' .
				"<td><pre><strong>{$title}</strong></pre></td>" .
				"<td><code>{$data['length']}</code></td>" .
				"<td>{$data['desc']}</td>" .
			'</tr>';
		}
		$result .= '</table>';
		return '<details style="cursor:pointer;">' .
			'<summary>' . __( 'Type details', 'ean-for-woocommerce' ) . '</summary>' .
			$result .
		'</details>';
	}

	/**
	 * get_settings.
	 *
	 * @version 5.5.2
	 * @since   1.0.0
	 *
	 * @see     https://www.keyence.com/ss/products/auto_id/barcode_lecture/basic/barcode-types/
	 *
	 * @todo    (desc) `alg_wc_ean_backend_add_generate_button`: better desc?
	 * @todo    (dev) `alg_wc_ean_order_items_table_templates`: translate?
	 * @todo    (dev) deprecate placeholders
	 * @todo    (dev) `alg_wc_ean_type`: rename `C128` to `CUSTOM`
	 * @todo    (desc) `alg_wc_ean_order_items_meta_rest`: "... tries order item meta, then uses product as a fallback..."
	 * @todo    (desc) add subsections, e.g., "General", "Display", etc., or "Products", "Orders", etc.?
	 * @todo    (desc) "REST API" as a separate *section*?
	 * @todo    (desc) remove "This will" everywhere
	 * @todo    (dev) `alg_wc_ean_order_items_meta_admin`: default to `yes` || merge with `alg_wc_ean_order_items_meta`
	 * @todo    (dev) `alg_wc_ean_order_items_meta`: default to `yes`
	 * @todo    (desc) `alg_wc_ean_order_items_meta_admin`: better desc
	 * @todo    (desc) `alg_wc_ean_order_items_meta`: better desc
	 * @todo    (dev) `$single_product_page_positions`: add more options, and maybe add `custom` hook option?
	 * @todo    (desc) `alg_wc_ean_frontend_positions_priorities`: better desc, e.g., add "known priorities"
	 * @todo    (desc) `alg_wc_ean_frontend_search_ajax_flatsome`: add link to the theme?
	 * @todo    (desc) Type: add more info (and maybe links) about all types
	 * @todo    (desc) add shortcode examples
	 * @todo    (desc) Type: rename to "Standard"?
	 * @todo    (desc) Shop pages: better title/desc?
	 * @todo    (desc) Cart: better desc?
	 * @todo    (desc) `$wcdn_settings`: better desc?
	 * @todo    (dev) `alg_wc_ean_backend_position`: add more positions?
	 * @todo    (desc) `alg_wc_ean_backend_position`: better names, e.g., "Inventory: SKU" to "Inventory: After SKU", etc.
	 * @todo    (dev) `alg_wc_ean_backend_search_ajax`: remove (i.e., always `yes`)?
	 */
	function get_settings() {

		$single_product_page_positions = array(
			'woocommerce_product_meta_start'             => __( 'Product meta start', 'ean-for-woocommerce' ),
			'woocommerce_product_meta_end'               => __( 'Product meta end', 'ean-for-woocommerce' ),
			'woocommerce_before_single_product'          => __( 'Before single product', 'ean-for-woocommerce' ),
			'woocommerce_before_single_product_summary'  => __( 'Before single product summary', 'ean-for-woocommerce' ),
			'woocommerce_single_product_summary'         => __( 'Single product summary', 'ean-for-woocommerce' ),
			'woocommerce_after_single_product_summary'   => __( 'After single product summary', 'ean-for-woocommerce' ),
			'woocommerce_after_single_product'           => __( 'After single product', 'ean-for-woocommerce' ),
			'woocommerce_product_additional_information' => __( 'Product additional information tab', 'ean-for-woocommerce' ),
		);

		$settings = array(
			array(
				'title'             => __( 'EAN Options', 'ean-for-woocommerce' ),
				'type'              => 'title',
				'id'                => 'alg_wc_ean_plugin_options',
			),
			array(
				'title'             => __( 'EAN', 'ean-for-woocommerce' ),
				'desc'              => '<strong>' . __( 'Enable plugin', 'ean-for-woocommerce' ) . '</strong>',
				'id'                => 'alg_wc_ean_plugin_enabled',
				'default'           => 'yes',
				'type'              => 'checkbox',
			),
			array(
				'title'             => __( 'Type', 'ean-for-woocommerce' ),
				'desc'              => $this->get_types_desc(),
				'desc_tip'          => sprintf(
					/* Translators: %s: List. */
					__( 'The "Type" will be used for: %s', 'ean-for-woocommerce' ),
					'<br><br>' . implode( ',<br><br>', array(
						'* ' . __( 'EAN validation (on the admin product edit pages, and in the admin products column)', 'ean-for-woocommerce' ),
						'* ' . __( 'EAN input pattern (on the admin product edit pages)', 'ean-for-woocommerce' ),
						'* ' . __( 'product structured data (e.g., for Google Search Console)', 'ean-for-woocommerce' ),
						'* ' . __( 'outputting 1D barcodes', 'ean-for-woocommerce' ),
					) ) . '.'
				),
				'id'                => 'alg_wc_ean_type',
				'default'           => 'EAN13',
				'type'              => 'select',
				'class'             => 'chosen_select',
				'options'           => array(
					'AUTO'   => (
						__( 'Automatic', 'ean-for-woocommerce' ) .
						' (' . implode( ', ', array( 'EAN-13', 'UPC-A', 'EAN-8', 'ISBN-13', 'JAN', 'EAN-14' ) ) . ')'
					),
					'EAN8'   => 'EAN-8',
					'UPCA'   => 'UPC-A',
					'EAN13'  => 'EAN-13',
					'ISBN13' => 'ISBN-13',
					'JAN'    => 'JAN',
					'EAN14'  => 'EAN-14',
					'C128'   => __( 'Custom', 'ean-for-woocommerce' ), // mislabeled, should be `CUSTOM`
				),
			),
			array(
				'title'             => __( 'Title', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This title will be used for the EAN input fields on admin product edit pages, in admin products list column, etc.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_title',
				'default'           => __( 'EAN', 'ean-for-woocommerce' ),
				'type'              => 'text',
			),
			array(
				'title'             => __( 'Admin product edit page', 'ean-for-woocommerce' ),
				'desc'              => __( 'Position', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'Set to which product data tab EAN field should be added.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_backend_position',
				'default'           => 'woocommerce_product_options_sku',
				'type'              => 'select',
				'class'             => 'chosen_select',
				'options'           => array(
					'woocommerce_product_options_general_product_data'   => __( 'General', 'ean-for-woocommerce' ),
					'woocommerce_product_options_inventory_product_data' => __( 'Inventory', 'ean-for-woocommerce' ),
					'woocommerce_product_options_sku'                    => __( 'Inventory: SKU', 'ean-for-woocommerce' ),
					'woocommerce_product_options_advanced'               => __( 'Advanced', 'ean-for-woocommerce' ),
				),
			),
			array(
				'desc'              => __( 'Position (variation product)', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_backend_position_variation',
				'default'           => 'woocommerce_variation_options_pricing',
				'type'              => 'select',
				'class'             => 'chosen_select',
				'options'           => array(
					'woocommerce_variation_options_pricing'         => __( 'Variations: After pricing', 'ean-for-woocommerce' ),
					'woocommerce_variation_options_dimensions'      => __( 'Variations: After dimensions', 'ean-for-woocommerce' ),
					'woocommerce_product_after_variable_attributes' => __( 'Variations: After all', 'ean-for-woocommerce' ),
				),
			),
			array(
				'desc'              => __( 'Check if valid', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This will check if product EAN is valid.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_backend_is_valid',
				'default'           => 'yes',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'start',
			),
			array(
				'desc'              => __( 'Check if unique', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This will check if product EAN is unique in your shop.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_backend_is_unique',
				'default'           => 'no',
				'type'              => 'checkbox',
				'checkboxgroup'     => '',
			),
			array(
				'desc'              => __( 'Require', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This will make the EAN field required on the admin product edit pages.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_required',
				'default'           => 'no',
				'type'              => 'checkbox',
				'checkboxgroup'     => '',
			),
			array(
				'desc'              => __( 'Add pattern', 'ean-for-woocommerce' ),
				'desc_tip'          => (
					__( 'This will specify a maximum length and pattern (digits only) for the EAN input field to be checked against on the admin product edit pages.', 'ean-for-woocommerce' ) . ' ' .
					__( 'Ignored if "Type" is set to "Custom".', 'ean-for-woocommerce' )
				),
				'id'                => 'alg_wc_ean_add_pattern',
				'default'           => 'no',
				'type'              => 'checkbox',
				'checkboxgroup'     => '',
			),
			array(
				'desc'              => __( 'Add "Generate" button', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf(
					/* Translators: %s: EAN title. */
					__( 'This will add "Generate %s" button to the admin product edit pages.', 'ean-for-woocommerce' ),
					get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) )
				),
				'id'                => 'alg_wc_ean_backend_add_generate_button',
				'default'           => 'no',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'end',
			),
			array(
				'title'             => __( 'Admin product block editor', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This will add the EAN field to the WooCommerce product block editor.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_product_block_editor',
				'default'           => 'yes',
				'type'              => 'checkbox',
			),
			array(
				'title'             => __( 'Admin product search', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This will enable searching by EAN in admin area.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_backend_search',
				'default'           => 'yes',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'start',
			),
			array(
				'desc'              => __( 'AJAX search', 'ean-for-woocommerce' ),
				'desc_tip'          => (
					__( 'This will enable searching by EAN in AJAX.', 'ean-for-woocommerce' ) . ' ' .
					__( 'E.g., when searching for a product when creating new order in admin area.', 'ean-for-woocommerce' )
				),
				'id'                => 'alg_wc_ean_backend_search_ajax',
				'default'           => 'yes',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'end',
			),
			array(
				'title'             => __( 'Admin products list column', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf(
					/* Translators: %1$s: Column title, %2$s: Admin product list link. */
					__( 'This will add "%1$s" column to %2$s.', 'ean-for-woocommerce' ),
					get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ),
					'<a href="' . admin_url( 'edit.php?post_type=product' ) . '">' .
						__( 'admin products list', 'ean-for-woocommerce' ) .
					'</a>'
				),
				'id'                => 'alg_wc_ean_backend_column',
				'default'           => 'yes',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'start',
			),
			array(
				'desc'              => __( 'Validate', 'ean-for-woocommerce' ),
				'desc_tip'          => (
					__( 'Validate EAN in column.', 'ean-for-woocommerce' ) . ' ' .
					__( 'Invalid EANs will be marked red.', 'ean-for-woocommerce' )
				),
				'id'                => 'alg_wc_ean_backend_column_validate',
				'default'           => 'no',
				'type'              => 'checkbox',
				'checkboxgroup'     => '',
			),
			array(
				'desc'              => __( 'Show duplicates', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'Show duplicate variation EANs for variable products.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_backend_column_show_duplicates',
				'default'           => 'yes',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'end',
			),
			array(
				'title'             => __( 'Admin product duplicate', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This will copy EAN on admin "Duplicate" product action.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_duplicate_product',
				'default'           => 'yes',
				'type'              => 'checkbox',
			),
			array(
				'title'             => __( 'Single product page', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This will show EAN on single product page on frontend.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_frontend',
				'default'           => 'yes',
				'type'              => 'checkbox',
			),
			array(
				'desc'              => __( 'Template', 'ean-for-woocommerce' ),
				'desc_tip'          => (
					sprintf(
						/* Translators: %s: Placeholder name. */
						__( 'Available placeholder: %s.', 'ean-for-woocommerce' ),
						'%ean%'
					) .
					'<br><br>' .
					__( 'You can also use shortcodes here.', 'ean-for-woocommerce' )
				),
				'id'                => 'alg_wc_ean_template',
				'default'           => alg_wc_ean()->core->get_default_template(),
				'type'              => 'textarea',
			),
			array(
				'desc'              => __( 'Positions', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'You can select multiple positions at once.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_frontend_positions',
				'default'           => array( 'woocommerce_product_meta_start' ),
				'type'              => 'multiselect',
				'class'             => 'chosen_select',
				'options'           => $single_product_page_positions,
			),
		);
		foreach ( get_option( 'alg_wc_ean_frontend_positions', array( 'woocommerce_product_meta_start' ) ) as $position ) {
			$position_title = ( $single_product_page_positions[ $position ] ?? $position );
			$settings = array_merge( $settings, array(
				array(
					'desc'          => sprintf(
						/* Translators: %s: Position title. */
						'- ' . __( 'Position priority: "%s"', 'ean-for-woocommerce' ),
						$position_title
					),
					'desc_tip'      => __( 'Fine-tune the position.', 'ean-for-woocommerce' ),
					'id'            => "alg_wc_ean_frontend_positions_priorities[{$position}]",
					'default'       => 10,
					'type'          => 'number',
				),
			) );
		}
		$settings = array_merge( $settings, array(
			array(
				'desc'              => __( 'Variable products: Position in variation', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_frontend_variation_position',
				'default'           => 'product_meta',
				'type'              => 'select',
				'class'             => 'chosen_select',
				'options'           => array(
					'product_meta'          => __( 'Product meta', 'ean-for-woocommerce' ),
					'variation_description' => __( 'Description', 'ean-for-woocommerce' ),
				),
			),
			array(
				'title'             => __( 'Shop pages', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => (
					__( 'This will show EAN on shop (e.g., category) pages on frontend.', 'ean-for-woocommerce' ) . ' ' .
					$this->variable_products_note()
				),
				'id'                => 'alg_wc_ean_frontend_loop',
				'default'           => 'no',
				'type'              => 'checkbox',
			),
			array(
				'desc'              => __( 'Template', 'ean-for-woocommerce' ),
				'desc_tip'          => (
					sprintf(
						/* Translators: %s: Placeholder name. */
						__( 'Available placeholder: %s.', 'ean-for-woocommerce' ),
						'%ean%'
					) .
					'<br><br>' .
					__( 'You can also use shortcodes here.', 'ean-for-woocommerce' )
				),
				'id'                => 'alg_wc_ean_template_loop',
				'default'           => alg_wc_ean()->core->get_default_template(),
				'type'              => 'textarea',
			),
			array(
				'title'             => __( 'Cart', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This will show EAN on the cart page.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_frontend_cart',
				'default'           => 'no',
				'type'              => 'checkbox',
			),
			array(
				'desc'              => __( 'Template', 'ean-for-woocommerce' ),
				'desc_tip'          => (
					sprintf(
						/* Translators: %s: Placeholder name. */
						__( 'Available placeholder: %s.', 'ean-for-woocommerce' ),
						'%ean%'
					) .
					'<br><br>' .
					__( 'You can also use shortcodes here.', 'ean-for-woocommerce' )
				),
				'id'                => 'alg_wc_ean_frontend_cart_template',
				'default'           => alg_wc_ean()->core->get_default_template(),
				'type'              => 'textarea',
			),
			array(
				'title'             => __( 'Checkout', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This will show EAN on the checkout page.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_frontend_checkout',
				'default'           => 'no',
				'type'              => 'checkbox',
			),
			array(
				'title'             => __( 'Search', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This will enable searching by EAN on frontend.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_frontend_search',
				'default'           => 'yes',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'start',
			),
			array(
				'desc'              => __( '"Flatsome" theme', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This will enable searching by EAN in "Flatsome" theme\'s "LIVE SEARCH".', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_frontend_search_ajax_flatsome',
				'default'           => 'no',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'end',
			),
			array(
				'title'             => __( 'Product structured data', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_frontend_product_structured_data',
				'desc_tip'          => __( 'This will add EAN to the product structured data, e.g., for Google Search Console.', 'ean-for-woocommerce' ),
				'default'           => 'yes',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'start',
			),
			array(
				'desc'              => sprintf(
					/* Translators: %s: Plugin name. */
					__( '"%s" plugin', 'ean-for-woocommerce' ),
					__( 'Rank Math SEO', 'ean-for-woocommerce' )
				),
				'desc_tip'          => sprintf(
					/* Translators: %s: Plugin link. */
					__( 'This will add EAN to the product structured data generated by the %s plugin.', 'ean-for-woocommerce' ),
					'<a href="https://wordpress.org/plugins/seo-by-rank-math/" target="_blank">' .
						__( 'Rank Math SEO', 'ean-for-woocommerce' ) .
					'</a>'
				),
				'id'                => 'alg_wc_ean_frontend_product_structured_data_rank_math_seo',
				'default'           => 'no',
				'type'              => 'checkbox',
				'checkboxgroup'     => '',
			),
			array(
				'desc'              => __( 'Automatic key', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf(
					/* Translators: %1$s-%5$s: Key. */
					__( 'If enabled, will use the key based on EAN type, i.e., %1$s for EAN-8, %2$s for UPC-A, %3$s for EAN-13, ISBN-13 and JAN, %4$s for EAN-14, and %5$s for all other types.', 'ean-for-woocommerce' ),
					'<code>gtin8</code>',
					'<code>gtin12</code>',
					'<code>gtin13</code>',
					'<code>gtin14</code>',
					'<code>gtin</code>'
				),
				'id'                => 'alg_wc_ean_frontend_product_structured_data_key_auto',
				'default'           => 'yes',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'end',
			),
			array(
				'desc'              => __( 'Custom key', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'Ignored, unless the "Automatic key" option above is disabled.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_frontend_product_structured_data_key',
				'default'           => 'gtin',
				'type'              => 'text',
			),
			array(
				'type'              => 'sectionend',
				'id'                => 'alg_wc_ean_plugin_options',
			),
			array(
				'title'             => __( 'Orders & Emails', 'ean-for-woocommerce' ),
				'type'              => 'title',
				'id'                => 'alg_wc_ean_orders_options',
			),
			array(
				'title'             => __( 'Orders', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'Add EAN to new order items meta.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_order_items_meta',
				'default'           => 'no',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'start',
			),
			array(
				'desc'              => __( 'Admin order', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'Add EAN to new order items meta for orders created by admin.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_order_items_meta_admin',
				'default'           => 'no',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'end',
			),
			array(
				'title'             => __( 'Order items table', 'ean-for-woocommerce' ),
				'desc'              => __( 'Pages', 'ean-for-woocommerce' ),
				'desc_tip'          => (
					__( 'This will show EAN in order items table on <strong>pages</strong>.', 'ean-for-woocommerce' ) . ' ' .
					__( 'E.g.: "thank you" (i.e., "order received") page, "view order" page (in "my account").', 'ean-for-woocommerce' )
				),
				'id'                => 'alg_wc_ean_order_items_table',
				'default'           => 'no',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'start',
			),
			array(
				'desc'              => __( 'Emails', 'ean-for-woocommerce' ),
				'desc_tip'          => (
					__( 'This will show EAN in order items table in <strong>emails</strong>.', 'ean-for-woocommerce' ) . ' ' .
					__( 'You can limit it to the specific emails in the "Emails list" option below.', 'ean-for-woocommerce' )
				),
				'id'                => 'alg_wc_ean_order_items_table_emails',
				'default'           => get_option( 'alg_wc_ean_order_items_table', 'no' ), // for the backward compatibility
				'type'              => 'checkbox',
				'checkboxgroup'     => 'end',
			),
			array(
				'desc'              => __( 'Emails list', 'ean-for-woocommerce' ),
				'desc_tip'          => (
					__( 'Leave empty to add to all emails.', 'ean-for-woocommerce' ) . ' ' .
					__( 'Ignored unless the "Emails" option above is enabled.', 'ean-for-woocommerce' )
				),
				'id'                => 'alg_wc_ean_order_items_table_emails_list',
				'default'           => array(),
				'type'              => 'multiselect',
				'class'             => 'chosen_select',
				'options'           => $this->get_wc_emails(),
			),
			array(
				'desc'              => (
					__( 'Template (HTML)', 'ean-for-woocommerce' ) . '<br>' .
					sprintf(
						/* Translators: %s: Placeholder list. */
						__( 'Available placeholder(s): %s', 'ean-for-woocommerce' ),
						'<code>%ean%</code>'
					)
				),
				'id'                => 'alg_wc_ean_order_items_table_templates[html]',
				'default'           => '<ul class="wc-item-meta"><li><span class="sku_wrapper ean_wrapper">EAN: <span class="ean">%ean%</span></span></li></ul>',
				'type'              => 'textarea',
				'css'               => 'height:100px;'
			),
			array(
				'desc'              => (
					__( 'Template (Plain text (emails only))', 'ean-for-woocommerce' ) . '<br>' .
					sprintf(
						/* Translators: %s: Placeholder list. */
						__( 'Available placeholder(s): %s', 'ean-for-woocommerce' ),
						'<code>%ean%</code>, <code>%new_line%</code>'
					)
				),
				'id'                => 'alg_wc_ean_order_items_table_templates[plain_text]',
				'default'           => '%new_line%- EAN: %ean%',
				'type'              => 'textarea',
			),
			array(
				'type'              => 'sectionend',
				'id'                => 'alg_wc_ean_orders_options',
			),
			array(
				'title'             => __( 'REST API', 'ean-for-woocommerce' ),
				'type'              => 'title',
				'id'                => 'alg_wc_ean_rest_api_options',
			),
			array(
				'title'             => __( 'Products', 'ean-for-woocommerce' ),
				'desc'              => __( 'Add EAN to each product object in REST API responses', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf(
					/* Translators: %s: URL. */
					__( 'E.g.: %s', 'ean-for-woocommerce' ),
					'<code>https://example.com/wp-json/wc/v3/products/123</code>'
				),
				'id'                => 'alg_wc_ean_product_rest',
				'default'           => 'no',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'start',
			),
			array(
				'desc'              => __( 'Search by EAN', 'ean-for-woocommerce' ),
				'desc_tip'          => (
					sprintf(
						/* Translators: %s: URL. */
						__( 'E.g.: %s', 'ean-for-woocommerce' ),
						'<code>https://example.com/wp-json/wc/v3/products?ean=7980441417892</code>'
					) .
					'<br>* ' .
					sprintf(
						/* Translators: %s: Parameter name. */
						__( 'Alternatively, you can enable the "Search > This will enable searching by EAN on frontend" option, and then search using the standard %s parameter:', 'ean-for-woocommerce' ),
						'<code>search</code>'
					) .
					'<br>' .
					sprintf(
						/* Translators: %s: URL. */
						__( 'E.g.: %s', 'ean-for-woocommerce' ),
						'<code>https://example.com/wp-json/wc/v3/products?search=7980441417892</code>'
					)
				),
				'id'                => 'alg_wc_ean_product_search_rest',
				'default'           => 'no',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'end',
			),
			array(
				'title'             => __( 'Orders', 'ean-for-woocommerce' ),
				'desc'              => __( 'Add EAN to each order object in REST API responses', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf(
					/* Translators: %s: URL. */
					__( 'E.g.: %s', 'ean-for-woocommerce' ),
					'<code>https://example.com/wp-json/wc/v3/orders/465</code>'
				),
				'id'                => 'alg_wc_ean_order_items_meta_rest',
				'default'           => 'no',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'start',
			),
			array(
				'desc'              => __( 'Search by EAN', 'ean-for-woocommerce' ),
				'desc_tip'          => (
					sprintf(
						/* Translators: %s: URL. */
						__( 'E.g.: %s', 'ean-for-woocommerce' ),
						'<code>https://example.com/wp-json/wc/v3/orders?ean=7980441417892</code>'
					) .
					'<br>* ' . __( 'Please note that the "Orders > Add EAN to new order items meta" option must be enabled.', 'ean-for-woocommerce' )
				),
				'id'                => 'alg_wc_ean_order_items_meta_search_rest',
				'default'           => 'no',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'end',
			),
			array(
				'type'              => 'sectionend',
				'id'                => 'alg_wc_ean_rest_api_options',
			),
			array(
				'title'             => __( 'Notes', 'ean-for-woocommerce' ),
				'desc'              => implode( '<br>', array(
					'<span class="dashicons dashicons-info"></span> ' . sprintf(
						/* Translators: %s: Shortcode name. */
						__( 'You can also output EAN with %s shortcode.', 'ean-for-woocommerce' ),
						'<code>[alg_wc_ean]</code>'
					),
					'<span class="dashicons dashicons-info"></span> ' . sprintf(
						/* Translators: %s: Meta key. */
						__( 'EAN is stored in product meta with %s key. You may need this for some third-party plugins, e.g., for product import.', 'ean-for-woocommerce' ),
						'<code>' . alg_wc_ean()->core->ean_key . '</code>'
					),
				) ),
				'type'              => 'title',
				'id'                => 'alg_wc_ean_notes',
			),
			array(
				'type'              => 'sectionend',
				'id'                => 'alg_wc_ean_notes',
			),
		) );

		return $settings;
	}

}

endif;

return new Alg_WC_EAN_Settings_General();

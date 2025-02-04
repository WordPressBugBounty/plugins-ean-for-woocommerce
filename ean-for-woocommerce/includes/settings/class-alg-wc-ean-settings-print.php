<?php
/**
 * EAN for WooCommerce - Print Section Settings
 *
 * @version 5.4.2
 * @since   2.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Settings_Print' ) ) :

class Alg_WC_EAN_Settings_Print extends Alg_WC_EAN_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function __construct() {
		$this->id   = 'print';
		$this->desc = __( 'Print', 'ean-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 5.4.2
	 * @since   2.0.0
	 *
	 * @see     https://www.avery.com/templates/6879 (default margins etc.)
	 *
	 * @todo    (feature) predefined templates, e.g., with `<table>`
	 * @todo    (feature) Cell border: add customization options
	 * @todo    (desc) "Deprecated placeholders" desc tip: remove?
	 * @todo    (feature) `wpautop()`?
	 * @todo    (desc) `children`: better desc?
	 * @todo    (desc) better section desc?
	 * @todo    (desc) Page format: better desc?
	 */
	function get_settings() {

		$options = get_option( 'alg_wc_ean_print_barcodes_to_pdf_settings', array() );

		$unit       = ( isset( $options['unit'] ) ? $options['unit'] : 'in' );
		$units      = array(
			'mm' => __( 'millimeters', 'ean-for-woocommerce' ),
			'cm' => __( 'centimeters', 'ean-for-woocommerce' ),
			'in' => __( 'inches', 'ean-for-woocommerce' ),
			'pt' => __( 'points', 'ean-for-woocommerce' ),
		);
		$unit_title = $units[ $unit ];

		$point_desc = __( 'A point equals 1/72 of inch, that is to say about 0.35 mm (an inch being 2.54 cm). This is a very common unit in typography; font sizes are expressed in that unit.', 'ean-for-woocommerce' );

		$pdf_page_formats = require( 'alg-wc-ean-pdf-page-formats.php' );
		foreach ( $pdf_page_formats as $format_id => $format_dim ) {
			// Converting from points to `$unit`
			switch ( $unit ) {
				case 'mm':
					$format_dim_w = round( $format_dim[0] / 2.835 );
					$format_dim_h = round( $format_dim[1] / 2.835 );
					break;
				case 'cm':
					$format_dim_w = round( $format_dim[0] / 28.35, 1 );
					$format_dim_h = round( $format_dim[1] / 28.35, 1 );
					break;
				default: // 'in'
					$format_dim_w = round( $format_dim[0] / 72, 1 );
					$format_dim_h = round( $format_dim[1] / 72, 1 );
			}
			$pdf_page_formats[ $format_id ] = "{$format_id} ({$format_dim_w} x {$format_dim_h} {$unit_title})";
		}

		$settings = array(
			array(
				'title'             => __( 'Print Barcodes (PDF)', 'ean-for-woocommerce' ),
				'type'              => 'title',
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_options',
			),
			array(
				'title'             => __( 'Print barcodes (PDF)', 'ean-for-woocommerce' ),
				'desc'              => '<strong>' . __( 'Enable section', 'ean-for-woocommerce' ) . '</strong>',
				'desc_tip'          => sprintf( __( 'This will add "Print barcodes" to the "Bulk actions" in %s.', 'ean-for-woocommerce' ),
					'<a href="' . admin_url( 'edit.php?post_type=product' ) . '">' . __( 'admin products list', 'ean-for-woocommerce' ) . '</a>' ) . $this->pro_msg(),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf',
				'default'           => 'no',
				'type'              => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_ean_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'type'              => 'sectionend',
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_options',
			),
			array(
				'title'             => __( 'General Options', 'ean-for-woocommerce' ),
				'type'              => 'title',
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_general_options',
			),
			array(
				'title'             => __( 'Page orientation', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[page_orientation]',
				'default'           => 'P',
				'type'              => 'select',
				'class'             => 'chosen_select',
				'options'           => array(
					'P' => __( 'Portrait', 'ean-for-woocommerce' ),
					'L' => __( 'Landscape', 'ean-for-woocommerce' ),
				),
			),
			array(
				'title'             => __( 'Unit', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'User measure unit.', 'ean-for-woocommerce' ) . '<br><br>' .
					sprintf( __( 'Used in %s options.', 'ean-for-woocommerce' ), '"' . implode( '", "', array(
							__( 'Page format: Custom: Width', 'ean-for-woocommerce' ),
							__( 'Page format: Custom: Height', 'ean-for-woocommerce' ),
							__( 'Cell width', 'ean-for-woocommerce' ),
							__( 'Cell height', 'ean-for-woocommerce' ),
							__( 'Top margin', 'ean-for-woocommerce' ),
							__( 'Left margin', 'ean-for-woocommerce' ),
							__( 'Right margin', 'ean-for-woocommerce' ),
							__( 'Page break margin', 'ean-for-woocommerce' ),
						) ) . '"' ) . '<br><br>' .
					'* ' . $point_desc,
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[unit]',
				'default'           => 'in',
				'type'              => 'select',
				'class'             => 'chosen_select',
				'options'           => $units,
			),
			array(
				'title'             => __( 'Page format', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[page_format]',
				'default'           => 'LETTER',
				'type'              => 'select',
				'class'             => 'chosen_select',
				'options'           => array_merge( array( 'custom' => __( 'Custom', 'ean-for-woocommerce' ) ), $pdf_page_formats ),
			),
			array(
				'desc'              => __( 'Page format: Custom: Width', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ) . '<br><br>' .
					sprintf( __( 'Ignored unless "%s" option is set to "%s".', 'ean-for-woocommerce' ),
						__( 'Page format', 'ean-for-woocommerce' ), __( 'Custom', 'ean-for-woocommerce' ) ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[page_format_custom_width]',
				'default'           => 0,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'desc'              => __( 'Page format: Custom: Height', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ) . '<br><br>' .
					sprintf( __( 'Ignored unless "%s" option is set to "%s".', 'ean-for-woocommerce' ),
						__( 'Page format', 'ean-for-woocommerce' ), __( 'Custom', 'ean-for-woocommerce' ) ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[page_format_custom_height]',
				'default'           => 0,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'title'             => __( 'Max barcodes per page', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[per_page]',
				'default'           => 12,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 1 ),
			),
			array(
				'title'             => __( 'Columns', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[columns]',
				'default'           => 2,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 1 ),
			),
			array(
				'title'             => __( 'Cell', 'ean-for-woocommerce' ),
				'desc'              => __( 'Cell width', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[cell_width]',
				'default'           => 4,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0.000001, 'step' => 0.000001 ),
			),
			array(
				'desc'              => __( 'Cell height', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[cell_height]',
				'default'           => 1.5,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0.000001, 'step' => 0.000001 ),
			),
			array(
				'desc'              => __( 'Cell border', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[cell_border]',
				'default'           => 0,
				'type'              => 'select',
				'class'             => 'chosen_select',
				'options'           => array(
					0 => __( 'No', 'ean-for-woocommerce' ),
					1 => __( 'Yes', 'ean-for-woocommerce' ),
				),
			),
			array(
				'desc'              => __( 'Cell top margin', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[cell_margin_top]',
				'default'           => 0,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'desc'              => __( 'Cell left margin', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[cell_margin_left]',
				'default'           => 0,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'desc'              => __( 'Cell right margin', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[cell_margin_right]',
				'default'           => 0,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'desc'              => __( 'Cell bottom margin', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[cell_margin_bottom]',
				'default'           => 0,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'desc'              => __( 'Cell content alignment', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[cell_align]',
				'default'           => '',
				'type'              => 'select',
				'class'             => 'chosen_select',
				'options'           => array(
					''  => __( 'Default', 'ean-for-woocommerce' ),
					'L' => __( 'Left', 'ean-for-woocommerce' ),
					'C' => __( 'Center', 'ean-for-woocommerce' ),
					'R' => __( 'Right', 'ean-for-woocommerce' ),
				),
			),
			array(
				'title'             => __( 'Page margins', 'ean-for-woocommerce' ),
				'desc'              => __( 'Top margin', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[margin_top]',
				'default'           => 1.13,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'desc'              => __( 'Left margin', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[margin_left]',
				'default'           => 0.46,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'desc'              => __( 'Right margin', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[margin_right]',
				'default'           => 0.31,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'desc'              => __( 'Page break (i.e., bottom) margin', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ) . '<br><br>' .
					__( 'Distance from the bottom of the page that defines the automatic page breaking triggering limit.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[page_break_margin]',
				'default'           => 0.79,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'title'             => __( 'Font', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'If you are having issues displaying your language specific letters, select "%s" font.', 'ean-for-woocommerce' ),
					'DejaVu Sans (Unicode)' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[font_family]',
				'default'           => 'dejavusans',
				'type'              => 'select',
				'class'             => 'chosen_select',
				'options'           => array(
					'dejavusans' => 'DejaVu Sans (Unicode)',
					'times'      => 'Times New Roman',
					'helvetica'  => 'Helvetica',
					'courier'    => 'Courier',
				),
			),
			array(
				'title'             => __( 'Font size', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[font_size]',
				'default'           => 11,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 1 ),
			),
			array(
				'title'             => __( 'Template', 'ean-for-woocommerce' ),
				'desc'              =>  sprintf( '<details><summary style="%s">%s</summary>%s</details>',
					'cursor: pointer; color: #2271b1;',
					__( 'Available shortcodes', 'ean-for-woocommerce' ),
					'<ul><li><code>' . implode( '</code></li><li><code>', array(
						'[alg_wc_ean]',
						'[alg_wc_ean_barcode]',
						'[alg_wc_ean_barcode_2d]',
						'[alg_wc_ean_product_image]',
						'[alg_wc_ean_product_name]',
						'[alg_wc_ean_product_price]',
						'[alg_wc_ean_product_sku]',
						'[alg_wc_ean_product_attr]',
						'[alg_wc_ean_product_id]',
						'[alg_wc_ean_product_meta]',
						'[alg_wc_ean_product_function]',
					) ) . '</code></li></ul>' ),
				'desc_tip'          => apply_filters( 'alg_wc_ean_print_barcodes_to_pdf_template_settings_desc', '' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[template]',
				'default'           => '[alg_wc_ean_barcode]<br>[alg_wc_ean]',
				'type'              => 'textarea',
				'css'               => 'width:100%;height:200px;',
			),
			array(
				'title'             => __( 'Style', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'Defines style information (CSS) for the labels.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[style]',
				'default'           => '',
				'type'              => 'textarea',
				'css'               => 'width:100%;height:100px;',
			),
			array(
				'title'             => __( 'Variations', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[children]',
				'default'           => 'no',
				'type'              => 'select',
				'class'             => 'chosen_select',
				'options'           => array(
					'no'      => __( 'Do not include', 'ean-for-woocommerce' ),
					'yes'     => __( 'Add', 'ean-for-woocommerce' ),
					'replace' => __( 'Replace', 'ean-for-woocommerce' ),
				),
			),
			array(
				'title'             => __( 'Use stock quantity', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'Print separate label for each product inventory item.', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[use_qty]',
				'default'           => 'no',
				'type'              => 'checkbox',
			),
			array(
				'type'              => 'sectionend',
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_general_options',
			),
		);

		$settings = apply_filters( 'alg_wc_ean_print_barcodes_to_pdf_general_settings', $settings, $options );

		$settings = array_merge( $settings, array(
			array(
				'title'             => __( 'Admin Options', 'ean-for-woocommerce' ),
				'type'              => 'title',
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_admin_options',
			),
			array(
				'title'             => __( 'Print buttons', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings_print_buttons',
				'default'           => array( 'bulk_actions' ),
				'type'              => 'multiselect',
				'class'             => 'chosen_select',
				'options'           => array(
					'bulk_actions'   => __( 'Products > Bulk actions', 'ean-for-woocommerce' ),
					'single_product' => __( 'Single product', 'ean-for-woocommerce' ),
					'single_order'   => __( 'Single order', 'ean-for-woocommerce' ),
				),
			),
			array(
				'desc'              => sprintf( __( 'Print buttons style, e.g.: %s', 'ean-for-woocommerce' ), '<code>font-size: 40px; width: 40px; height: 40px;</code>' ),
				'desc_tip'          => sprintf( __( 'Applied to the "%s" and "%s" print buttons.', 'ean-for-woocommerce' ),
					__( 'Single product', 'ean-for-woocommerce' ), __( 'Single order', 'ean-for-woocommerce' ) ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings_print_buttons_style',
				'default'           => '',
				'type'              => 'text',
			),
			array(
				'desc'              => __( 'Variations print buttons', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'Applied to the "%s" print buttons.', 'ean-for-woocommerce' ),
					__( 'Single product', 'ean-for-woocommerce' ) ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings_buttons_variations',
				'default'           => array( 'variations_tab' ),
				'type'              => 'multiselect',
				'class'             => 'chosen_select',
				'options'           => array(
					'variations_tab' => __( 'Variations tab', 'ean-for-woocommerce' ),
					'meta_box'       => __( 'Meta box', 'ean-for-woocommerce' ),
				),
			),
			array(
				'desc'              => __( 'Quantity input', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'Adds quantity input field for the print buttons.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings_print_qty',
				'default'           => array(),
				'type'              => 'multiselect',
				'class'             => 'chosen_select',
				'options'           => array(
					'bulk_actions'              => __( 'Products > Bulk actions', 'ean-for-woocommerce' ),
					'bulk_actions_each_product' => __( 'Products > Bulk actions > Each product', 'ean-for-woocommerce' ),
				),
			),
			array(
				'type'              => 'sectionend',
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_admin_options',
			),
			array(
				'title'             => __( 'Advanced Print Options', 'ean-for-woocommerce' ),
				'type'              => 'title',
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_advanced_options',
			),
			array(
				'title'             => __( 'Skip products without EAN', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'Skip products without EAN when generating PDF.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings_skip_empty_ean',
				'default'           => 'yes',
				'type'              => 'checkbox',
			),
			array(
				'title'             => __( 'Use Print.js', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => sprintf( __( 'Use %s library for printing PDFs.', 'ean-for-woocommerce' ),
					'<a href="https://printjs.crabbly.com/" target="_blank">Print.js</a>' ),
				'id'                => 'alg_wc_ean_print_use_print_js',
				'default'           => 'yes',
				'type'              => 'checkbox',
			),
			array(
				'title'             => __( 'Suppress errors', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ) . ' (' . __( 'recommended', 'ean-for-woocommerce' ) . ')',
				'desc_tip'          => __( 'Suppress PHP errors when generating PDF.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[suppress_errors]',
				'default'           => 'yes',
				'type'              => 'checkbox',
			),
			array(
				'title'             => __( 'Define K_PATH_CACHE', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => (
					__( 'Sets TCPDF cache directory for temporary files.', 'ean-for-woocommerce' ) . ' ' .
					__( 'Enable this if barcode images are empty.', 'ean-for-woocommerce' )
				),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_settings[define_k_path_cache]',
				'default'           => 'no',
				'type'              => 'checkbox',
			),
			array(
				'type'              => 'sectionend',
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_advanced_options',
			),
			array(
				'title'             => __( 'Print Tools', 'ean-for-woocommerce' ),
				'type'              => 'title',
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_tools',
			),
			array(
				'title'             => __( 'Print', 'ean-for-woocommerce' ),
				'desc'              => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Print', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'Check the box and "Save changes" to run the tool.', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_tool',
				'default'           => 'no',
				'type'              => 'checkbox',
			),
			array(
				'desc'              => __( 'Product(s)', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_tool_data[products]',
				'default'           => array(),
				'type'              => 'multiselect',
				'class'             => 'wc-product-search',
				'options'           => array(),
				'custom_attributes' => array(
					'data-placeholder' => esc_attr__( 'Search for a product&hellip;', 'woocommerce' ),
					'data-action'      => 'woocommerce_json_search_products_and_variations',
					'data-allow_clear' => true,
				),
			),
			array(
				'desc'              => __( 'Quantity', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_tool_data[qty]',
				'default'           => 1,
				'type'              => 'number',
				'placeholder'       => '1',
				'custom_attributes' => array( 'min' => 1 ),
			),
			array(
				'title'             => __( 'Products List', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This will add new "Print Products" section to "WooCommerce > Settings > EAN".', 'ean-for-woocommerce' ),
				'id'                => 'alg_wc_ean_print_products_list_section',
				'default'           => 'no',
				'type'              => 'checkbox',
			),
			array(
				'type'              => 'sectionend',
				'id'                => 'alg_wc_ean_print_barcodes_to_pdf_tools',
			),
		) );

		return $settings;
	}

}

endif;

return new Alg_WC_EAN_Settings_Print();

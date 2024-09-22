<?php
/**
 * EAN for WooCommerce - Barcodes - Compatibility Section Settings
 *
 * @version 4.2.0
 * @since   3.8.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Settings_Barcodes_Compatibility' ) ) :

class Alg_WC_EAN_Settings_Barcodes_Compatibility extends Alg_WC_EAN_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.8.0
	 * @since   3.8.0
	 */
	function __construct( $dim ) {
		$this->dim        = $dim;
		$this->dim_suffix = ( '1d' === $this->dim ? ''                                      : '_2d' );
		$this->id         = ( '1d' === $this->dim ? 'barcodes'                              : 'barcodes_2d' ) . '_compatibility';
		$this->desc       = ( '1d' === $this->dim ? __( 'Barcodes', 'ean-for-woocommerce' ) : __( '2D Barcodes', 'ean-for-woocommerce' ) ) . ' > ' . __( 'Compatibility', 'ean-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 4.2.0
	 * @since   3.8.0
	 */
	function get_settings() {
		$settings = array(
			array(
				'title'    => __( 'Plugin Compatibility Options', 'ean-for-woocommerce' ),
				'desc'     => sprintf( __( '"%s" option must be enabled.', 'ean-for-woocommerce' ),
					'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_ean&section=' . ( '1d' === $this->dim ? 'barcodes' : 'barcodes_2d' ) ) . '">' .
						( '1d' === $this->dim ? __( 'Barcodes', 'ean-for-woocommerce' ) : __( '2D Barcodes', 'ean-for-woocommerce' ) ) . ' > ' . __( 'Enable section', 'ean-for-woocommerce' ) .
					'</a>' ),
				'type'     => 'title',
				'id'       => "alg_wc_ean_barcode{$this->dim_suffix}_compatibility_options",
			),
			array(
				'title'    => __( 'Print Invoice & Delivery Notes for WooCommerce', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Show barcode image in PDF documents of the %s plugin.', 'ean-for-woocommerce' ),
					'<a target="_blank" href="' . 'https://wordpress.org/plugins/woocommerce-delivery-notes/' . '">' .
						__( 'Print Invoice & Delivery Notes for WooCommerce', 'ean-for-woocommerce' ) . '</a>' ),
				'id'       => "alg_wc_ean_wcdn_barcode{$this->dim_suffix}",
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'WooCommerce PDF Invoices & Packing Slips', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Show barcode image in PDF documents of the %s plugin.', 'ean-for-woocommerce' ),
					'<a target="_blank" href="' . 'https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/' . '">' .
						__( 'WooCommerce PDF Invoices & Packing Slips', 'ean-for-woocommerce' ) . '</a>' ),
				'id'       => "alg_wc_ean_wpo_wcpdf_barcode{$this->dim_suffix}",
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Content', 'ean-for-woocommerce' ),
				'id'       => "alg_wc_ean_wpo_wcpdf_barcode_options{$this->dim_suffix}[content]",
				'default'  => '<div class="ean_wrapper' . $this->dim_suffix . '">[alg_wc_ean_barcode' . $this->dim_suffix . ']</div>',
				'type'     => 'textarea',
			),
			array(
				'desc'     => __( 'Position', 'ean-for-woocommerce' ),
				'id'       => "alg_wc_ean_wpo_wcpdf_barcode_position{$this->dim_suffix}",
				'default'  => 'wpo_wcpdf_after_item_meta',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'wpo_wcpdf_before_item_meta' => __( 'Before item meta', 'ean-for-woocommerce' ),
					'wpo_wcpdf_after_item_meta'  => __( 'After item meta', 'ean-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Show barcode image in PDF documents of the %s plugin.', 'ean-for-woocommerce' ),
					'<a target="_blank" href="' . 'https://wordpress.org/plugins/print-invoices-packing-slip-labels-for-woocommerce/' . '">' .
						__( 'WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels', 'ean-for-woocommerce' ) . '</a>' ),
				'id'       => "alg_wc_ean_wt_pklist_barcode{$this->dim_suffix}",
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Content', 'ean-for-woocommerce' ),
				'id'       => "alg_wc_ean_wt_pklist_barcode_options{$this->dim_suffix}[content]",
				'default'  => "<p>[alg_wc_ean_barcode{$this->dim_suffix}]</p>",
				'type'     => 'textarea',
			),
			array(
				'desc'     => __( 'Position', 'ean-for-woocommerce' ),
				'id'       => "alg_wc_ean_wt_pklist_barcode_options{$this->dim_suffix}[position]",
				'default'  => 'after_product_meta',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'before_product_name' => __( 'Before item name', 'ean-for-woocommerce' ),
					'after_product_name'  => __( 'After item name', 'ean-for-woocommerce' ),
					'before_product_meta' => __( 'Before item meta', 'ean-for-woocommerce' ),
					'after_product_meta'  => __( 'After item meta', 'ean-for-woocommerce' ),
					'column'              => __( 'As a separate column', 'ean-for-woocommerce' ),
				),
			),
			array(
				'desc'     => __( 'Documents', 'ean-for-woocommerce' ) . '<br>' .
					sprintf( __( 'Can be a comma-separated list, e.g.: %s.', 'ean-for-woocommerce' ),
						'<code>' . implode( ',', array( 'invoice', 'packinglist', 'deliverynote', 'dispatchlabel' ) ) . '</code>' ),
				'desc_tip' => __( 'Leave empty to include in all documents.', 'ean-for-woocommerce' ),
				'id'       => "alg_wc_ean_wt_pklist_barcode_options{$this->dim_suffix}[documents]",
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'desc'     => __( 'Column title', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Used only if the "Position" option is set to the "As a separate column".', 'ean-for-woocommerce' ),
				'id'       => "alg_wc_ean_wt_pklist_barcode_options{$this->dim_suffix}[column_title]",
				'default'  => __( 'Barcode', 'ean-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'desc'     => sprintf( __( 'Column HTML %s', 'ean-for-woocommerce' ), '<code>class</code>' ),
				'desc_tip' => __( 'Used only if the "Position" option is set to the "As a separate column".', 'ean-for-woocommerce' ),
				'id'       => "alg_wc_ean_wt_pklist_barcode_options{$this->dim_suffix}[column_class]",
				'default'  => 'wfte_product_table_head_ean wfte_text_center',
				'type'     => 'text',
			),
			array(
				'desc'     => sprintf( __( 'Column HTML %s', 'ean-for-woocommerce' ), '<code>style</code>' ),
				'desc_tip' => __( 'Used only if the "Position" option is set to the "As a separate column".', 'ean-for-woocommerce' ),
				'id'       => "alg_wc_ean_wt_pklist_barcode_options{$this->dim_suffix}[column_style]",
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'WooCommerce Customer / Order / Coupon Export', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will add %s column to the order items export of the %s plugin.', 'ean-for-woocommerce' ),
					"<code>item_barcode{$this->dim_suffix}</code>", '<a target="_blank" href="https://woocommerce.com/products/ordercustomer-csv-export/">' . __( 'WooCommerce Customer / Order / Coupon Export', 'ean-for-woocommerce' ) . '</a>' ),
				'id'       => "alg_wc_ean_wc_customer_order_export_barcode{$this->dim_suffix}",
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Content', 'ean-for-woocommerce' ),
				'id'       => "alg_wc_ean_wc_customer_order_export_content_barcode{$this->dim_suffix}",
				'default'  => '[alg_wc_ean_barcode' . $this->dim_suffix . '_base64 before="data:image/png;base64,"]',
				'type'     => 'textarea',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Dokan', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will show barcode in vendor product form of the %s plugin.', 'ean-for-woocommerce' ),
					'<a target="_blank" href="https://wordpress.org/plugins/dokan-lite/">' . __( 'Dokan', 'ean-for-woocommerce' ) . '</a>' ),
				'id'       => "alg_wc_ean_dokan_barcode{$this->dim_suffix}",
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Title', 'ean-for-woocommerce' ),
				'id'       => "alg_wc_ean_dokan_title_barcode{$this->dim_suffix}",
				'default'  => __( 'Barcode', 'ean-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'desc'     => __( 'Content', 'ean-for-woocommerce' ),
				'id'       => "alg_wc_ean_dokan_content_barcode{$this->dim_suffix}",
				'default'  => "[alg_wc_ean_barcode{$this->dim_suffix}]",
				'type'     => 'textarea',
			),
			array(
				'type'     => 'sectionend',
				'id'       => "alg_wc_ean_barcode{$this->dim_suffix}_compatibility_options",
			),
		);
		return $settings;
	}

}

endif;

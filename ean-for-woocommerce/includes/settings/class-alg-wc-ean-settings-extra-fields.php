<?php
/**
 * EAN for WooCommerce - Extra Fields Section Settings
 *
 * @version 4.7.6
 * @since   4.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Settings_Extra_Fields' ) ) :

class Alg_WC_EAN_Settings_Extra_Fields extends Alg_WC_EAN_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function __construct() {
		$this->id   = 'extra_fields';
		$this->desc = __( 'Extra Fields', 'ean-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 4.7.6
	 * @since   4.0.0
	 *
	 * @todo    (dev) `alg_wc_ean_extra_fields_num_total`: JS?
	 * @todo    (dev) add "Meta key"?
	 * @todo    (desc) better section desc?
	 * @todo    (desc) better `pro_msg`?
	 */
	function get_settings() {

		$settings = array(
			array(
				'title'    => __( 'Extra Fields Options', 'ean-for-woocommerce' ),
				'desc'     => $this->pro_msg( 'use this section' ) .
					__( 'This section allows you to add multiple extra fields per product, e.g., EAN and MPN simultaneously.', 'ean-for-woocommerce' ) . '<br>' .
					__( 'Please note that extra fields have less features compared to the main field.', 'ean-for-woocommerce' ) . ' ' .
					sprintf( __( 'Currently supported features are: %s.', 'ean-for-woocommerce' ), implode( ', ', array(
						__( 'Title', 'ean-for-woocommerce' ),
						__( 'Meta key', 'ean-for-woocommerce' ),
						__( 'Admin product search', 'ean-for-woocommerce' ),
						__( 'Single product page display (including variations)', 'ean-for-woocommerce' ),
						__( 'Search (frontend)', 'ean-for-woocommerce' ),
						__( 'Product structured data', 'ean-for-woocommerce' ),
					) ) ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_extra_fields_options',
			),
			array(
				'title'    => __( 'Total extra fields', 'ean-for-woocommerce' ),
				'type'     => 'number',
				'id'       => 'alg_wc_ean_extra_fields_num_total',
				'default'  => 0,
				'custom_attributes' => array( 'min' => 0 ),
			),
		);

		for ( $i = 1; $i <= get_option( 'alg_wc_ean_extra_fields_num_total', 0 ); $i++ ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => sprintf( __( 'Extra field #%d', 'ean-for-woocommerce' ), $i ),
					'type'     => 'text',
					'id'       => "alg_wc_ean_extra_field_name[{$i}]",
					'default'  => sprintf( __( 'Extra field #%d', 'ean-for-woocommerce' ), $i ),
					'custom_attributes' => array( 'required' => 'required' ),
				),
			) );
		}

		$settings = array_merge( $settings, array(
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_extra_fields_options',
			),
		) );

		return $settings;

	}

}

endif;

return new Alg_WC_EAN_Settings_Extra_Fields();

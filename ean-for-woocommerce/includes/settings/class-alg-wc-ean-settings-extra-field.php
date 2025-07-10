<?php
/**
 * EAN for WooCommerce - Extra Field Section Settings
 *
 * @version 4.7.6
 * @since   4.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Settings_Extra_Field' ) ) :

class Alg_WC_EAN_Settings_Extra_Field extends Alg_WC_EAN_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function __construct( $num ) {
		$this->num  = $num;
		$this->id   = 'extra_field_' . $this->num;
		$this->desc = $this->get_desc();
		parent::__construct();
	}

	/**
	 * get_desc.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 *
	 * @todo    (dev) this is (almost) duplicated in `Alg_WC_EAN_Extra_Field::get_name()`
	 */
	function get_desc() {
		$name = get_option( 'alg_wc_ean_extra_field_name', array() );
		return (
			isset( $name[ $this->num ] ) && '' !== $name[ $this->num ] ?
			$name[ $this->num ] :
			sprintf(
				/* Translators: %d: Field ID. */
				__( 'Extra field #%d', 'ean-for-woocommerce' ),
				$this->num
			)
		);
	}

	/**
	 * get_settings.
	 *
	 * @version 4.7.6
	 * @since   4.0.0
	 */
	function get_settings() {

		$settings = array(
			array(
				'title'             => sprintf(
					/* Translators: %s: Field title. */
					__( '%s Options', 'ean-for-woocommerce' ),
					$this->desc
				),
				'type'              => 'title',
				'id'                => 'alg_wc_ean_extra_field_' . $this->num . '_options',
			),
			array(
				'title'             => $this->get_desc(),
				'desc'              => '<strong>' .
					__( 'Enable field', 'ean-for-woocommerce' ) .
				'</strong>',
				'desc_tip'          => $this->pro_msg( 'enable this field' ),
				'type'              => 'checkbox',
				'id'                => "alg_wc_ean_extra_field_enabled[{$this->num}]",
				'default'           => 'no',
				'custom_attributes' => apply_filters(
					'alg_wc_ean_settings',
					array( 'disabled' => 'disabled' )
				),
			),
			array(
				'title'             => __( 'Title', 'ean-for-woocommerce' ),
				'desc'              => sprintf(
					/* Translators: %s: Example. */
					__( 'For example: %s', 'ean-for-woocommerce' ),
					'<code>MPN</code>'
				),
				'type'              => 'text',
				'id'                => "alg_wc_ean_extra_field_name[{$this->num}]",
				'default'           => sprintf(
					/* Translators: %d: Field ID. */
					__( 'Extra field #%d', 'ean-for-woocommerce' ),
					$this->num
				),
				'custom_attributes' => array( 'required' => 'required' ),
			),
			array(
				'title'             => __( 'Meta key', 'ean-for-woocommerce' ),
				'desc'              => sprintf(
					/* Translators: %s: Example. */
					__( 'For example: %s', 'ean-for-woocommerce' ),
					'<code>mpn</code>'
				),
				'type'              => 'text',
				'id'                => "alg_wc_ean_extra_field_key[{$this->num}]",
				'default'           => sprintf(
					'extra_field_%d',
					$this->num
				),
				'custom_attributes' => array( 'required' => 'required' ),
			),
			array(
				'title'             => __( 'Admin product search', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'id'                => "alg_wc_ean_extra_field_backend_search[{$this->num}]",
				'desc_tip'          => __( 'This will enable searching by the field in admin area.', 'ean-for-woocommerce' ),
				'default'           => 'no',
				'type'              => 'checkbox',
			),
			array(
				'title'             => __( 'Single product page', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This will show the field on single product page on frontend.', 'ean-for-woocommerce' ),
				'id'                => "alg_wc_ean_extra_field_frontend[{$this->num}]",
				'default'           => 'yes',
				'type'              => 'checkbox',
			),
			array(
				'title'             => __( 'Search', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip'          => __( 'This will enable searching by the field on the frontend.', 'ean-for-woocommerce' ),
				'id'                => "alg_wc_ean_extra_field_frontend_search[{$this->num}]",
				'default'           => 'no',
				'type'              => 'checkbox',
			),
			array(
				'title'             => __( 'Product structured data', 'ean-for-woocommerce' ),
				'desc'              => __( 'Enable', 'ean-for-woocommerce' ),
				'id'                => "alg_wc_ean_extra_field_structured_data_product[{$this->num}]",
				'desc_tip'          => __( 'This will add the field to the product structured data, e.g., for Google Search Console.', 'ean-for-woocommerce' ),
				'default'           => 'yes',
				'type'              => 'checkbox',
			),
			array(
				'type'              => 'sectionend',
				'id'                => 'alg_wc_ean_extra_field_' . $this->num . '_options',
			),
		);

		return $settings;

	}

}

endif;

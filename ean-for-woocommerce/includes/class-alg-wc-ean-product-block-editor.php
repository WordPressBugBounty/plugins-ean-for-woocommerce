<?php
/**
 * EAN for WooCommerce - Product Block Editor Class
 *
 * @version 5.4.3
 * @since   5.2.1
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Product_Block_Editor' ) ) :

class Alg_WC_EAN_Product_Block_Editor {

	/**
	 * Constructor.
	 *
	 * @version 5.4.3
	 * @since   5.2.1
	 */
	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * init.
	 *
	 * @version 5.4.3
	 * @since   5.4.3
	 */
	function init() {

		// Check option and "New product editor" feature
		if (
			'yes' !== get_option( 'alg_wc_ean_product_block_editor', 'yes' ) ||
			! class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ||
			! \Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled( 'product_block_editor' )
		) {
			return;
		}

		// Add block
		add_action( 'woocommerce_layout_template_after_instantiation', array( $this, 'add_block' ), 10, 3 );

		// Save in REST
		add_filter( 'woocommerce_rest_pre_insert_product_object',           array( $this, 'save_ean' ), 10, 3 );
		add_filter( 'woocommerce_rest_pre_insert_product_variation_object', array( $this, 'save_ean' ), 10, 3 );

		// Add in REST
		add_filter( 'woocommerce_rest_prepare_product_object',           array( $this, 'get_ean' ), 10, 3 );
		add_filter( 'woocommerce_rest_prepare_product_variation_object', array( $this, 'get_ean' ), 10, 3 );

	}

	/**
	 * add_block.
	 *
	 * @version 5.2.1
	 * @since   5.2.1
	 */
	function add_block( $template_id, $template_area, $template ) {
		if (
			! ( $section = $template->get_section_by_id( 'product-inventory-section' ) ) &&
			! ( $section = $template->get_section_by_id( 'product-variation-inventory-section' ) )
		) {
			return;
		}
		$section->add_block(
			array(
				'id'         => 'alg-wc-ean-product-block-editor-field',
				'blockName'  => 'woocommerce/product-text-field',
				'order'      => 10,
				'attributes' => array(
					'property' => 'alg_wc_ean_product_block_editor',
					'label'    => esc_html( get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) ),
				),
			)
		);
	}

	/**
	 * save_ean.
	 *
	 * @version 5.2.1
	 * @since   5.2.1
	 */
	function save_ean( $product, $request, $creating ) {
		if ( isset( $request['alg_wc_ean_product_block_editor'] ) ) {
			$product->update_meta_data( alg_wc_ean()->core->ean_key, wc_clean( $request['alg_wc_ean_product_block_editor'] ) );
		}
		return $product;
	}

	/**
	 * get_ean.
	 *
	 * @version 5.2.1
	 * @since   5.2.1
	 *
	 * @todo    (dev) merge this with the "REST API > Products > Add EAN to each product object in REST API responses" option?
	 */
	function get_ean( $response, $product, $request ) {
		$response->data['alg_wc_ean_product_block_editor'] = $product->get_meta( alg_wc_ean()->core->ean_key );
		return $response;
	}

}

endif;

return new Alg_WC_EAN_Product_Block_Editor();

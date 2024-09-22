<?php
/**
 * EAN for WooCommerce - REST API Class
 *
 * @version 4.8.2
 * @since   3.7.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_REST_API' ) ) :

class Alg_WC_EAN_REST_API {

	/**
	 * Constructor.
	 *
	 * @version 4.3.2
	 * @since   3.7.0
	 *
	 * @see     https://woocommerce.github.io/woocommerce-rest-api-docs/
	 *
	 * @todo    (feature) `create_product`
	 * @todo    (feature) `create_order`
	 */
	function __construct() {

		// Products
		if ( 'yes' === get_option( 'alg_wc_ean_product_rest', 'no' ) ) {
			add_filter( 'woocommerce_rest_prepare_product_object', array( $this, 'product_add_ean' ), PHP_INT_MAX, 3 );
			add_filter( 'woocommerce_rest_prepare_product_variation_object', array( $this, 'product_add_ean' ), PHP_INT_MAX, 3 );
		}
		if ( 'yes' === get_option( 'alg_wc_ean_product_search_rest', 'no' ) ) {
			add_filter( 'woocommerce_rest_product_object_query', array( $this, 'product_search' ), 10, 2 );
			add_action( 'pre_get_posts', array( $this, 'product_variation_search' ) );
			add_filter( 'woocommerce_rest_query_vars', array( $this, 'product_variation_search_var' ) );
		}

		// Orders
		if ( 'yes' === get_option( 'alg_wc_ean_order_items_meta_rest', 'no' ) ) {
			add_filter( 'woocommerce_rest_prepare_shop_order_object', array( $this, 'order_add_ean' ), PHP_INT_MAX, 3 );
		}
		if ( 'yes' === get_option( 'alg_wc_ean_order_items_meta_search_rest', 'no' ) ) {
			add_filter( 'woocommerce_rest_orders_prepare_object_query', array( $this, 'order_search' ), 10, 2 );
		}

	}

	/**
	 * product_variation_search_var.
	 *
	 * @version 4.3.0
	 * @since   4.3.0
	 */
	function product_variation_search_var( $vars ) {
		$vars[] = 'alg_wc_ean_rest_api';
		return $vars;
	}

	/**
	 * product_variation_search.
	 *
	 * @version 4.3.0
	 * @since   4.3.0
	 */
	function product_variation_search( $query ) {
		if ( 'product_search' === $query->get( 'alg_wc_ean_rest_api' ) ) {
			$query->set( 'post_type', array( 'product', 'product_variation' ) );
		}
		return $query;
	}

	/**
	 * product_search.
	 *
	 * @version 4.3.0
	 * @since   3.7.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/6.2.1/plugins/woocommerce/includes/rest-api/Controllers/Version3/class-wc-rest-crud-controller.php#L340
	 *
	 * @todo    (dev) use `meta_query` instead?
	 */
	function product_search( $args, $request ) {
		if ( isset( $request['ean'] ) ) {

			// Meta key/value
			$args['meta_key']   = alg_wc_ean()->core->ean_key;
			$args['meta_value'] = $request['ean'];

			// For product variations
			$args['alg_wc_ean_rest_api'] = 'product_search';

		}
		return $args;
	}

	/**
	 * product_add_ean.
	 *
	 * @version 4.4.5
	 * @since   2.9.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/6.2.1/plugins/woocommerce/includes/rest-api/Controllers/Version2/class-wc-rest-products-v2-controller.php#L190
	 */
	function product_add_ean( $response, $product, $request ) {
		$res_key = apply_filters( 'alg_wc_ean_rest_api_product_ean_key', 'ean', $response, $product, $request );
		$response->data[ $res_key ] = alg_wc_ean()->core->get_ean( $product->get_id() );
		return $response;
	}

	/**
	 * order_search.
	 *
	 * @version 3.7.0
	 * @since   3.7.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/6.2.1/plugins/woocommerce/includes/rest-api/Controllers/Version2/class-wc-rest-orders-v2-controller.php#L524
	 *
	 * @todo    (test) HPOS compatible?
	 * @todo    (dev) fallback: get product(s) ID by EAN, then search order items by product ID
	 */
	function order_search( $args, $request ) {
		if ( ! empty( $request['ean'] ) ) {
			global $wpdb;

			$order_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT order_id
					FROM {$wpdb->prefix}woocommerce_order_items
					WHERE order_item_id IN ( SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key = '%s' AND meta_value = %s )
					AND order_item_type = 'line_item'",
					alg_wc_ean()->core->ean_key,
					$request['ean']
				)
			);

			// Force WP_Query return empty if don't found any order.
			$order_ids = ! empty( $order_ids ) ? $order_ids : array( 0 );

			$args['post__in'] = $order_ids;
		}
		return $args;
	}

	/**
	 * order_add_ean.
	 *
	 * @version 4.8.2
	 * @since   2.8.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/6.2.1/plugins/woocommerce/includes/rest-api/Controllers/Version2/class-wc-rest-orders-v2-controller.php#L420
	 */
	function order_add_ean( $response, $order, $request ) {
		if ( empty( $response->data['line_items'] ) ) {
			return $response;
		}

		$res_key = apply_filters( 'alg_wc_ean_rest_api_order_ean_key', 'ean', $response, $order, $request );

		foreach ( $response->data['line_items'] as $item_key => &$item ) {

			$is_in_meta = false;

			// Item meta
			if ( ! empty( $item['meta_data'] ) ) {
				foreach ( $item['meta_data'] as $meta_data ) {
					if ( isset( $meta_data['key'], $meta_data['value'] ) && alg_wc_ean()->core->ean_key === $meta_data['key'] ) {
						$item[ $res_key ] = $meta_data['value'];
						$is_in_meta = true;
						break;
					}
				}
			}

			// Product meta (fallback)
			if ( ! $is_in_meta && isset( $item['product_id'] ) ) {
				$product_id = ( ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'] );
				if ( '' !== ( $ean = alg_wc_ean()->core->get_ean( $product_id ) ) ) {
					$item[ $res_key ] = $ean;
				}
			}

		}

		return $response;
	}

}

endif;

return new Alg_WC_EAN_REST_API();

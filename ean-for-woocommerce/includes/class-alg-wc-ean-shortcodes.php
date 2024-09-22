<?php
/**
 * EAN for WooCommerce - Shortcodes Class
 *
 * @version 4.9.9
 * @since   3.5.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Shortcodes' ) ) :

class Alg_WC_EAN_Shortcodes {

	/**
	 * data.
	 *
	 * @version 4.8.7
	 */
	public $data;

	/**
	 * Constructor.
	 *
	 * @version 4.6.0
	 * @since   3.5.0
	 *
	 * @todo    (feature) add `[alg_wc_ean_type]` shortcode?
	 */
	function __construct() {

		$this->data = array();

		// Shortcodes
		add_shortcode( 'alg_wc_ean',                   array( $this, 'ean_shortcode' ) );
		add_shortcode( 'alg_wc_ean_is_unique',         array( $this, 'ean_is_unique_shortcode' ) );
		add_shortcode( 'alg_wc_ean_is_valid',          array( $this, 'ean_is_valid_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_attr',      array( $this, 'product_attr_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_image',     array( $this, 'product_image_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_name',      array( $this, 'product_name_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_sku',       array( $this, 'product_sku_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_price',     array( $this, 'product_price_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_id',        array( $this, 'product_id_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_author_id', array( $this, 'product_author_id_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_meta',      array( $this, 'product_meta_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_function',  array( $this, 'product_function_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_terms',     array( $this, 'product_terms_shortcode' ) );
		add_shortcode( 'alg_wc_ean_if',                array( $this, 'if_shortcode' ) );
		add_shortcode( 'alg_wc_ean_if_product_cat',    array( $this, 'if_product_cat_shortcode' ) );
		add_shortcode( 'alg_wc_ean_if_product_tag',    array( $this, 'if_product_tag_shortcode' ) );
	}

	/**
	 * do_shortcode.
	 *
	 * @version 3.5.0
	 * @since   3.1.2
	 *
	 * @todo    (dev) add `set_data()`, `reset_data()` functions?
	 */
	function do_shortcode( $content, $data = array() ) {
		if ( ! empty( $data ) ) {
			$this->data = $data;
		}
		$result = do_shortcode( $content );
		if ( ! empty( $data ) ) {
			$this->data = array();
		}
		return $result;
	}

	/**
	 * output.
	 *
	 * @version 4.9.9
	 * @since   3.5.0
	 *
	 * @see     https://developer.wordpress.org/reference/functions/wp_kses_post/
	 *
	 * @todo    (dev) `max_length`: add everywhere
	 */
	function output( $result, $atts ) {
		$result = ( '' === $result ?
			$atts['on_empty'] :
			(
				$atts['before'] .
					( ! empty( $atts['max_length'] ) ? substr( $result, 0, $atts['max_length'] ) : $result ) .
				$atts['after']
			)
		);
		$sanitized_result = wp_kses( $result, 'post', array_merge( wp_allowed_protocols(), array( 'data' ) ) );
		return apply_filters( 'alg_wc_ean_shortcode_output', $sanitized_result, $result, $atts );
	}

	/**
	 * get_shortcode_att.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	function get_shortcode_att( $att, $atts, $default = '' ) {
		return ( ! empty( $atts[ $att ] ) ? $atts[ $att ] : ( ! empty( $this->data[ $att ] ) ? $this->data[ $att ] : $default ) );
	}

	/**
	 * get_products.
	 *
	 * @version 3.6.0
	 * @since   3.6.0
	 */
	function get_products( $atts, $default = false ) {
		$product_id  = $this->get_shortcode_att( 'product_id', $atts, $default );
		$product_ids = array( $product_id );
		if ( filter_var( $atts['children'], FILTER_VALIDATE_BOOLEAN ) && ( $product = wc_get_product( $product_id ) ) ) {
			$product_ids = array_merge( $product_ids, $product->get_children() );
		}
		return $product_ids;
	}

	/**
	 * if_product_term.
	 *
	 * @version 4.6.0
	 * @since   4.6.0
	 *
	 * @todo    (dev) `shortcode_atts`
	 * @todo    (dev) rewrite: use `has_term()` instead?
	 */
	function if_product_term( $taxonomy, $atts, $content = '' ) {
		$value = ( isset( $atts['in'] ) && '' !== $atts['in'] ? $atts['in'] : ( isset( $atts['not_in'] ) && '' !== $atts['not_in'] ? $atts['not_in'] : '' ) );
		if ( '' !== $value ) {
			$atts = array_replace( $atts, array(
				'value1'   => '{alg_wc_ean_product_terms taxonomy="' . $taxonomy . '" return="slug"}',
				'value2'   => $value,
				'operator' => ( isset( $atts['in'] ) && '' !== $atts['in'] ? 'intersect' : 'not_intersect' ),
			) );
			unset( $atts['in'] );
			unset( $atts['not_in'] );
			return $this->if_shortcode( $atts, $content );
		}
		return '';
	}

	/**
	 * if_product_cat_shortcode.
	 *
	 * @version 4.6.0
	 * @since   4.6.0
	 */
	function if_product_cat_shortcode( $atts, $content = '' ) {
		return $this->if_product_term( 'product_cat', $atts, $content );
	}

	/**
	 * if_product_tag_shortcode.
	 *
	 * @version 4.6.0
	 * @since   4.6.0
	 */
	function if_product_tag_shortcode( $atts, $content = '' ) {
		return $this->if_product_term( 'product_tag', $atts, $content );
	}

	/**
	 * product_terms_shortcode.
	 *
	 * @version 4.6.0
	 * @since   4.6.0
	 */
	function product_terms_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'yes',
			'taxonomy'   => 'product_cat',
			'return'     => 'name',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_terms' );

		// Result
		$result = '';
		if ( ( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) ) ) {
			if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
				$product_id = $product_parent_id;
			}
			$result = implode( ', ', wp_list_pluck( wc_get_product_terms( $product_id, $atts['taxonomy'] ), $atts['return'] ) );
		}

		return $this->output( $result, $atts );
	}

	/**
	 * if_shortcode.
	 *
	 * @version 4.6.0
	 * @since   4.6.0
	 *
	 * @todo    (dev) `shortcode_atts`
	 */
	function if_shortcode( $atts, $content = '' ) {
		if ( ! isset( $atts['value1'], $atts['operator'], $atts['value2'] ) || ( '' === $content && ! isset( $atts['then'] ) ) ) {
			return '';
		}
		$value1 =              do_shortcode( str_replace( array( '{', '}' ), array( '[', ']' ), $atts['value1'] ) );
		$value2 =              do_shortcode( str_replace( array( '{', '}' ), array( '[', ']' ), $atts['value2'] ) );
		$then   = ( '' === $content        ? str_replace( array( '{', '}' ), array( '[', ']' ), $atts['then'] ) : $content );
		$else   = ( isset( $atts['else'] ) ? str_replace( array( '{', '}' ), array( '[', ']' ), $atts['else'] ) : '' );
		return do_shortcode( ( $this->if_shortcode_operator( $value1, $atts['operator'], $value2 ) ? $then : $else ) );
	}

	/**
	 * if_shortcode_operator.
	 *
	 * @version 4.6.0
	 * @since   4.6.0
	 */
	function if_shortcode_operator( $value1, $operator, $value2 ) {
		switch ( $operator ) {
			case 'equal':
				return ( $value1 == $value2 );
			case 'not_equal':
				return ( $value1 != $value2 );
			case 'less':
				return ( $value1 <  $value2 );
			case 'less_or_equal':
				return ( $value1 <= $value2 );
			case 'greater':
				return ( $value1 >  $value2 );
			case 'greater_or_equal':
				return ( $value1 >= $value2 );
			case 'in':
				return (   in_array( $value1, array_map( 'trim', explode( ',', $value2 ) ) ) );
			case 'not_in':
				return ( ! in_array( $value1, array_map( 'trim', explode( ',', $value2 ) ) ) );
			case 'intersect':
				return ( ! empty( array_intersect( array_map( 'trim', explode( ',', $value1 ) ), array_map( 'trim', explode( ',', $value2 ) ) ) ) );
			case 'not_intersect':
				return (   empty( array_intersect( array_map( 'trim', explode( ',', $value1 ) ), array_map( 'trim', explode( ',', $value2 ) ) ) ) );
		}
		return false;
	}

	/**
	 * product_function_shortcode.
	 *
	 * @version 3.6.0
	 * @since   3.6.0
	 *
	 * @todo    (dev) `name`: sanitize?
	 * @todo    (dev) `name`: add `alg_wc_ean_product_function_shortcode_allowed_names` option?
	 */
	function product_function_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
			'name'       => '',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_function' );

		// Check the required atts
		if ( '' === $atts['name'] || ! ( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) ) ) {
			return '';
		}

		// Product ID
		if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
			$product_id = $product_parent_id;
		}

		// Check if function exists
		if ( ! ( $product = wc_get_product( $product_id ) ) || ! is_callable( array( $product, $atts['name'] ) ) ) {
			return '';
		}

		// Result
		$result = $product->{$atts['name']}();

		return $this->output( $result, $atts );
	}

	/**
	 * product_meta_shortcode.
	 *
	 * @version 4.9.3
	 * @since   3.6.0
	 *
	 * @todo    (dev) `key`: sanitize?
	 * @todo    (dev) `key`: add `alg_wc_ean_product_meta_shortcode_allowed_keys` option?
	 */
	function product_meta_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
			'key'        => '',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_meta' );

		// Check the required atts
		if ( '' === $atts['key'] || ! ( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) ) ) {
			return '';
		}

		// Product ID
		if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
			$product_id = $product_parent_id;
		}

		// Product object
		if ( ! ( $product = wc_get_product( $product_id ) ) ) {
			return '';
		}

		// Result
		$result = $product->get_meta( $atts['key'] );

		return $this->output( $result, $atts );
	}

	/**
	 * product_image_shortcode.
	 *
	 * @version 4.4.3
	 * @since   3.5.0
	 */
	function product_image_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
			'output'     => 'img',
			'size'       => 'woocommerce_thumbnail',
			'width'      => 30, // px // for `output = img`
			'height'     => 30, // px // for `output = img`
			'is_in_pdf'  => false,    // for `output = img`
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_image' );

		// Result
		$result = '';
		if (
			( 'url' === $atts['output'] || ! $this->get_shortcode_att( 'is_in_pdf', $atts, false ) || function_exists( 'curl_init' ) ) &&
			( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) )
		) {
			if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
				$product_id = $product_parent_id;
			}
			if ( $product = wc_get_product( $product_id ) ) {
				if ( ( $img_id = $product->get_image_id() ) && ( $img_url = wp_get_attachment_image_src( $img_id, $atts['size'] ) ) ) {
					$img_url = $img_url[0];
				}
				if ( ! $img_url ) {
					$img_url = wc_placeholder_img_src( $atts['size'] );
				}
				$img_url = esc_url( $img_url );
				$result = ( 'url' === $atts['output'] ? $img_url : '<img src="' . $img_url . '" width="' . esc_attr( $atts['width'] ) . '" height="' . esc_attr( $atts['height'] ) . '">' );
			}
		}

		return $this->output( $result, $atts );
	}

	/**
	 * product_name_shortcode.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	function product_name_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
			'type'       => 'name',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_name' );

		// Result
		$result = '';
		if ( ( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) ) ) {
			if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
				$product_id = $product_parent_id;
			}
			if ( 'name' === $atts['type'] ) {
				if ( ( $product = wc_get_product( $product_id ) ) ) {
					$result = $product->get_formatted_name();
				}
			} else { // 'title'
				$result = get_the_title( $product_id );
			}
		}

		return $this->output( $result, $atts );
	}

	/**
	 * product_sku_shortcode.
	 *
	 * @version 4.1.0
	 * @since   3.5.0
	 */
	function product_sku_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'max_length' => false,
			'parent'     => 'no',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_sku' );

		// Result
		$result = '';
		if ( ( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) ) ) {
			if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
				$product_id = $product_parent_id;
			}
			if ( ( $product = wc_get_product( $product_id ) ) ) {
				$result = $product->get_sku();
			}
		}

		return $this->output( $result, $atts );
	}

	/**
	 * product_price_shortcode.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 *
	 * @todo    (dev) `sale`: do we really need to use `get_regular_price()` as a fallback?
	 * @todo    (desc) `sale` and `regular` prices won't work for a) variable products, b) parent of a variation
	 */
	function product_price_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
			'raw'        => 'no',
			'type'       => 'final',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_price' );

		// Result
		$result = '';
		if ( ( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) ) ) {
			if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
				$product_id = $product_parent_id;
			}
			if ( ( $product = wc_get_product( $product_id ) ) ) {
				switch ( $atts['type'] ) {
					case 'final':
						$result = ( 'yes' === $atts['raw'] ? $product->get_price() : $product->get_price_html() );
						break;
					case 'regular':
						$result = ( 'yes' === $atts['raw'] ? $product->get_regular_price() : wc_price( $product->get_regular_price() ) );
						break;
					case 'sale':
						$price  = ( '' === ( $sale = $product->get_sale_price() ) ? $product->get_regular_price() : $sale );
						$result = ( 'yes' === $atts['raw'] ? $price : wc_price( $price ) );
						break;
				}
			}
		}

		return $this->output( $result, $atts );
	}

	/**
	 * product_id_shortcode.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	function product_id_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_id' );

		// Result
		$product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() );
		$result     = ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ? $product_parent_id : $product_id );

		return $this->output( $result, $atts );
	}

	/**
	 * product_author_id_shortcode.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function product_author_id_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'min_length' => 0,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_author_id' );

		// Product ID
		if ( ! ( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) ) ) {
			return '';
		}
		$product_id = ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ? $product_parent_id : $product_id );

		// Author ID
		$result = get_post_field( 'post_author', $product_id );
		if ( ! empty( $atts['min_length'] ) ) {
			$result = str_pad( $result, $atts['min_length'], '0', STR_PAD_LEFT );
		}

		return $this->output( $result, $atts );
	}

	/**
	 * product_attr_shortcode.
	 *
	 * @version 3.5.0
	 * @since   2.7.0
	 *
	 * @todo    (feature) multiple attributes (comma-separated)
	 * @todo    (feature) all attributes at once (maybe use `WC_Product::get_attributes()`?
	 * @todo    (feature) all attributes starting with X
	 */
	function product_attr_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
			'attr'       => '',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_attr' );

		// Result
		$result = '';
		if ( '' !== $atts['attr'] && ( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) ) ) {
			if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
				$product_id = $product_parent_id;
			}
			if ( ( $product = wc_get_product( $product_id ) ) && is_callable( array( $product, 'get_attribute' ) ) ) {
				$result = $product->get_attribute( $atts['attr'] );
			}
		}

		return $this->output( $result, $atts );
	}

	/**
	 * ean_is_unique_shortcode.
	 *
	 * @version 4.4.6
	 * @since   4.4.6
	 */
	function ean_is_unique_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id'     => false,
			'before'         => '',
			'after'          => '',
			'on_empty'       => '',
			'ean'            => '',
			'unique_text'    => '<span style="color:green;">' . esc_html__( 'Unique EAN', 'ean-for-woocommerce' )     . '</span>',
			'duplicate_text' => '<span style="color:red;">'   . esc_html__( 'Duplicated EAN', 'ean-for-woocommerce' ) . '</span>',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_is_unique' );

		$ean        = $this->get_shortcode_att( 'ean', $atts, '' );
		$product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() );

		// Result
		$result = '';
		if ( ! empty( $ean ) ) {
			$result = ( ! alg_wc_ean()->core->do_ean_exist( $ean, $product_id ) ? $atts['unique_text'] : $atts['duplicate_text'] );
		}

		return $this->output( $result, $atts );
	}

	/**
	 * ean_is_valid_shortcode.
	 *
	 * @version 4.4.6
	 * @since   4.4.6
	 */
	function ean_is_valid_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id'     => false,
			'before'         => '',
			'after'          => '',
			'on_empty'       => '',
			'ean'            => '',
			'valid_text'     => '<span style="color:green;">' . esc_html__( 'Valid EAN', 'ean-for-woocommerce' )   . '</span>',
			'invalid_text'   => '<span style="color:red;">'   . esc_html__( 'Invalid EAN', 'ean-for-woocommerce' ) . '</span>',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_is_valid' );

		$ean        = $this->get_shortcode_att( 'ean', $atts, '' );
		$product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() );

		// Result
		$result = '';
		if ( ! empty( $ean ) ) {
			$result = ( alg_wc_ean()->core->is_valid_ean( $ean, $product_id ) ? $atts['valid_text'] : $atts['invalid_text'] );
		}

		return $this->output( $result, $atts );
	}

	/**
	 * ean_shortcode.
	 *
	 * @version 3.6.0
	 * @since   1.5.1
	 *
	 * @todo    (dev) `$atts['ean']`: ` if ( false !== ( $ean = ( '' !== $atts['ean'] ? $atts['ean'] : ( ! empty( $this->data['ean'] ) ? $this->data['ean'] : false ) ) ) ) { return $this->output( $ean, $atts ); }`
	 * @todo    (feature) `parent`? (same for barcodes)
	 * @todo    (dev) check if valid?
	 * @todo    (feature) add `children` attribute to all shortcodes, e.g., `product_attr_shortcode()`
	 */
	function ean_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'children'   => 'no',
			'glue'       => ', ', // for `children = yes`
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean' );

		// Products
		$product_ids = $this->get_products( $atts );

		// Result
		$result = array();
		foreach ( $product_ids as $product_id ) {
			$result[] = alg_wc_ean()->core->get_ean( $product_id );
		}
		$result = implode( $atts['glue'], $result );

		return $this->output( $result, $atts );
	}

}

endif;

return new Alg_WC_EAN_Shortcodes();

<?php
/**
 * EAN for WooCommerce - Core Class
 *
 * @version 5.2.1
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Core' ) ) :

class Alg_WC_EAN_Core {

	/**
	 * ean_key.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	public $ean_key;

	/**
	 * edit.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	public $edit;

	/**
	 * admin.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	public $admin;

	/**
	 * search.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	public $search;

	/**
	 * display.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	public $display;

	/**
	 * order_items_table.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	public $order_items_table;

	/**
	 * import_export.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	public $import_export;

	/**
	 * orders.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	public $orders;

	/**
	 * rest_api.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	public $rest_api;

	/**
	 * product_tools.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	public $product_tools;

	/**
	 * order_tools.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	public $order_tools;

	/**
	 * settings_import_export.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	public $settings_import_export;

	/**
	 * compatibility.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	public $compatibility;

	/**
	 * shortcodes.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	public $shortcodes;

	/**
	 * product_block_editor.
	 *
	 * @version 5.2.1
	 * @since   5.2.1
	 */
	public $product_block_editor;

	/**
	 * Constructor.
	 *
	 * @version 5.2.1
	 * @since   1.0.0
	 *
	 * @todo    (dev) wpml-config.xml?
	 * @todo    (dev) `alg_wc_ean_meta_key`: search for `alg_ean`
	 * @todo    (dev) WPML/Polylang (use default language product ID)
	 */
	function __construct() {

		$this->ean_key = get_option( 'alg_wc_ean_meta_key', '_alg_ean' );

		if ( 'yes' === get_option( 'alg_wc_ean_plugin_enabled', 'yes' ) ) {
			$this->edit                   = require_once( 'class-alg-wc-ean-edit.php' );
			$this->admin                  = require_once( 'class-alg-wc-ean-admin.php' );
			$this->search                 = require_once( 'class-alg-wc-ean-search.php' );
			$this->display                = require_once( 'class-alg-wc-ean-display.php' );
			$this->order_items_table      = require_once( 'class-alg-wc-ean-order-items-table.php' );
			$this->import_export          = require_once( 'class-alg-wc-ean-export-import.php' );
			$this->orders                 = require_once( 'class-alg-wc-ean-orders.php' );
			$this->rest_api               = require_once( 'class-alg-wc-ean-rest-api.php' );
			$this->product_tools          = require_once( 'class-alg-wc-ean-product-tools.php' );
			$this->order_tools            = require_once( 'class-alg-wc-ean-order-tools.php' );
			$this->settings_import_export = require_once( 'class-alg-wc-ean-manage-settings.php' );
			$this->compatibility          = require_once( 'class-alg-wc-ean-compatibility.php' );
			$this->shortcodes             = require_once( 'class-alg-wc-ean-shortcodes.php' );
			$this->product_block_editor   = require_once( 'class-alg-wc-ean-product-block-editor.php' );
		}

		// Core loaded
		do_action( 'alg_wc_ean_core_loaded', $this );

	}

	/**
	 * get_default_template.
	 *
	 * @version 4.6.0
	 * @since   4.6.0
	 *
	 * @todo    (dev) move to another file
	 */
	function get_default_template() {
		return sprintf( '%s: %%ean%%', get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) );
	}

	/**
	 * get_type.
	 *
	 * @version 2.4.0
	 * @since   1.5.0
	 */
	function get_type( $ean, $do_match_auto_type = true, $product_id = false ) {
		$raw_type = get_option( 'alg_wc_ean_type', 'EAN13' );
		$type     = ( 'AUTO' === $raw_type && $do_match_auto_type ? $this->get_type_by_ean_length( $ean ) : $raw_type );
		return apply_filters( 'alg_wc_ean_get_type', $type, $raw_type, $ean, $product_id );
	}

	/**
	 * get_type_by_ean_length.
	 *
	 * @version 4.7.0
	 * @since   1.5.0
	 *
	 * @todo    (dev) rename function?
	 */
	function get_type_by_ean_length( $ean ) {
		$length = strlen( $ean );
		switch ( $length ) {
			case 8:
				return 'EAN8';
			case 12:
				return 'UPCA';
			case 13:
				return ( $this->is_ean_isbn( $ean ) ? 'ISBN13' : ( $this->is_ean_jan( $ean ) ? 'JAN' : 'EAN13' ) );
			case 14:
				return 'EAN14';
			default:
				return false;
		}
	}

	/**
	 * get_ean_type_length.
	 *
	 * @version 3.3.0
	 * @since   1.4.0
	 *
	 * @todo    (dev) now used only: `EAN8`, `UPCA`, `EAN13`, `ISBN13`, `JAN`, `EAN14`
	 */
	function get_ean_type_length( $type ) {
		switch ( $type ) {
			case 'GTIN8':
			case 'EAN8':
				return 8;
			case 'GTIN12':
			case 'UPCA':
				return 12;
			case 'GTIN13':
			case 'EAN13':
			case 'ISBN13':
			case 'JAN':
				return 13;
			case 'GTIN14':
			case 'EAN14':
				return 14;
			case 'GSIN':
				return 17;
			case 'SSCC':
				return 18;
		}
	}

	/**
	 * is_ean_isbn.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 */
	function is_ean_isbn( $value ) {
		return in_array( substr( $value, 0, 3 ), array( '978', '979' ) );
	}

	/**
	 * is_ean_jan.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 */
	function is_ean_jan( $value ) {
		return in_array( substr( $value, 0, 2 ), array( '45', '49' ) );
	}

	/**
	 * is_valid_ean.
	 *
	 * @version 4.9.7
	 * @since   1.0.1
	 *
	 * @see     https://stackoverflow.com/questions/19890144/generate-valid-ean13-in-php
	 * @see     https://stackoverflow.com/questions/29076255/how-do-i-validate-a-barcode-number-using-php
	 * @see     http://www.gs1.org/how-calculate-check-digit-manually
	 *
	 * @todo    (feature) add more formats/standards, e.g., ASIN, etc.; also see https://github.com/tecnickcom/TCPDF/blob/6.4.1/tcpdf_barcodes_1d.php#L70
	 */
	function is_valid_ean( $value, $product_id = false ) {

		$type = $this->get_type( $value, false, $product_id );

		switch ( $type ) {

			case 'EAN8':   // e.g.: 96385074
			case 'UPCA':   // e.g.: 042100005264
			case 'EAN13':  // e.g.: 5901234123457
			case 'ISBN13':
			case 'JAN':
			case 'EAN14':  // e.g.: 40700719670720
			case 'AUTO':

				// Get value
				$ean = ( string ) $value;

				// We accept only digits
				if ( ! preg_match( "/^[0-9]+$/", $ean ) ) {
					$result = false;
					break;
				}

				// Check valid lengths
				$l = strlen( $ean );
				if (
					( 'AUTO' == $type && ! in_array( $l, array( 8, 12, 13, 14 ) ) ) ||
					( 'AUTO' != $type && $l != $this->get_ean_type_length( $type ) )
				) {
					$result = false;
					break;
				}

				// Get check digit
				$check    = substr( $ean, -1 );
				$ean      = substr( $ean, 0, -1 );
				$sum_even = $sum_odd = 0;
				$even     = true;
				while ( strlen( $ean ) > 0 ) {
					$digit = substr( $ean, -1 );
					if ( $even ) {
						$sum_even += 3 * $digit;
					} else {
						$sum_odd  += $digit;
					}
					$even = ! $even;
					$ean  = substr( $ean, 0, -1 );
				}
				$sum            = $sum_even + $sum_odd;
				$sum_rounded_up = ceil( $sum / 10 ) * 10;
				$result         = ( $check == ( $sum_rounded_up - $sum ) );

				// Extra prefix check (ISBN13, JAN)
				if ( $result ) {
					if ( 'ISBN13' === $type ) {
						if ( ! $this->is_ean_isbn( $value ) ) {
							$result = false;
						}
					} elseif ( 'JAN' === $type ) {
						if ( ! $this->is_ean_jan( $value ) ) {
							$result = false;
						}
					}
				}

				// The end
				break;

			default:
				$result = ( 0 != strlen( $value ) );

		}

		return apply_filters( 'alg_wc_ean_is_valid', $result, $value, $type );
	}

	/**
	 * do_ean_exist.
	 *
	 * @version 3.6.0
	 * @since   3.6.0
	 */
	function do_ean_exist( $ean, $not_product_id = false ) {
		$args = array(
			'meta_key'     => $this->ean_key,
			'meta_value'   => $ean,
			'post_type'    => array( 'product', 'product_variation' ),
			'post__not_in' => ( $not_product_id ? array( $not_product_id ) : array() ),
			'fields'       => 'ids',
		);
		$query = new WP_Query( $args );
		return $query->have_posts();
	}

	/**
	 * get_ean_from_order_item.
	 *
	 * @version 2.4.0
	 * @since   1.2.0
	 *
	 * @todo    (dev) move to `Alg_WC_EAN_Orders`?
	 */
	function get_ean_from_order_item( $item ) {
		return ( is_a( $item, 'WC_Order_Item_Product' ) && (
				'' !== ( $ean = wc_get_order_item_meta( $item->get_id(), $this->ean_key ) ) ||                                     // from order item meta
				( ( $product_id = $this->get_order_item_product_id( $item ) ) && '' !== ( $ean = $this->get_ean( $product_id ) ) ) // from product directly
			) ? $ean : false );
	}

	/**
	 * get_order_item_product_id.
	 *
	 * @version 2.4.0
	 * @since   2.4.0
	 *
	 * @todo    (dev) move to `Alg_WC_EAN_Orders`?
	 * @todo    (dev) `if ( ! is_a( $item, 'WC_Order_Item_Product' ) )` try `( $item = new WC_Order_Item_Product( $item['item_id'] ) )`?
	 */
	function get_order_item_product_id( $item ) {
		return ( is_a( $item, 'WC_Order_Item_Product' ) ?
				( 0 != ( $variation_id = $item->get_variation_id() ) ? $variation_id : ( 0 != ( $_product_id = $item->get_product_id() ) ? $_product_id : false ) ) :
				false
			);
	}

	/**
	 * get_ean.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) use `$do_try_parent`?
	 * @todo    (dev) rethink `$product_id`?
	 */
	function get_ean( $product_id = false, $do_try_parent = false ) {
		if ( ! $product_id ) {
			$product_id = get_the_ID();
		}
		$ean = get_post_meta( $product_id, $this->ean_key, true );
		if ( '' === $ean && $do_try_parent && 0 != ( $parent_id = wp_get_post_parent_id( $product_id ) ) ) {
			$ean = get_post_meta( $parent_id, $this->ean_key, true );
		}
		return $ean;
	}

	/**
	 * set_ean.
	 *
	 * @version 4.5.0
	 * @since   4.5.0
	 */
	function set_ean( $product_id, $value ) {
		return update_post_meta( $product_id, $this->ean_key, $value );
	}

}

endif;

return new Alg_WC_EAN_Core();

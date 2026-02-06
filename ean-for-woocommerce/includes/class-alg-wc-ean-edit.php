<?php
/**
 * EAN for WooCommerce - Edit Class
 *
 * @version 5.5.2
 * @since   2.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Edit' ) ) :

class Alg_WC_EAN_Edit {

	/**
	 * do_add_generate_button.
	 *
	 * @version 4.8.7
	 */
	public $do_add_generate_button;

	/**
	 * Constructor.
	 *
	 * @version 5.2.0
	 * @since   2.0.0
	 *
	 * @todo    (dev) position: new tab (for both simple and variable products)
	 */
	function __construct() {
		if ( is_admin() && apply_filters( 'alg_wc_ean_edit', true ) ) {

			// Admin product edit page
			add_action(
				get_option( 'alg_wc_ean_backend_position', 'woocommerce_product_options_sku' ),
				array( $this, 'add_ean_input' )
			);
			add_action(
				'save_post_product',
				array( $this, 'save_ean_input' ),
				10,
				2
			);

			// Variations
			add_action(
				get_option( 'alg_wc_ean_backend_position_variation', 'woocommerce_variation_options_pricing' ),
				array( $this, 'add_ean_input_variation' ),
				10,
				3
			);
			add_action(
				'woocommerce_save_product_variation',
				array( $this, 'save_ean_input_variation' ),
				10,
				2
			);

			// Quick and Bulk edit
			add_action(
				'woocommerce_product_quick_edit_end',
				array( $this, 'add_bulk_and_quick_edit_fields' ),
				PHP_INT_MAX
			);
			add_action(
				'woocommerce_product_bulk_edit_end',
				array( $this, 'add_bulk_and_quick_edit_fields' ),
				PHP_INT_MAX
			);
			add_action(
				'woocommerce_product_bulk_and_quick_edit',
				array( $this, 'save_bulk_and_quick_edit_fields' ),
				PHP_INT_MAX,
				2
			);
			add_action(
				'manage_product_posts_custom_column',
				array( $this, 'add_quick_edit_inline_data' ),
				10,
				2
			);
			add_action(
				'admin_footer',
				array( $this, 'add_quick_edit_js' )
			);

			// "Generate" button
			$this->do_add_generate_button = ( 'yes' === get_option( 'alg_wc_ean_backend_add_generate_button', 'no' ) );
			if ( $this->do_add_generate_button ) {
				add_action(
					'admin_footer',
					array( $this, 'add_generate_button' )
				);
				add_action(
					'wp_ajax_alg_wc_ean_generate_ajax',
					array( $this, 'generate_button_ajax' )
				);
			}

		}
	}

	/**
	 * add_quick_edit_inline_data.
	 *
	 * @version 5.2.0
	 * @since   5.2.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/9.3.3/plugins/woocommerce/client/legacy/js/admin/quick-edit.js
	 */
	function add_quick_edit_inline_data( $column, $product_id ) {
		if ( 'name' !== $column ) {
			return;
		}
		?>
		<div class="hidden" id="alg_wc_ean_inline_<?php echo absint( $product_id ); ?>">
			<div class="alg_wc_ean_quick_edit"><?php echo esc_html( alg_wc_ean()->core->get_ean( $product_id ) ); ?></div>
		</div>
		<?php
	}

	/**
	 * add_quick_edit_js.
	 *
	 * @version 5.2.0
	 * @since   5.2.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/9.3.3/plugins/woocommerce/includes/admin/list-tables/class-wc-admin-list-table-products.php#L161
	 *
	 * @todo    (dev) move to a separate JS file?
	 */
	function add_quick_edit_js() {
		if (
			! function_exists( 'get_current_screen' ) ||
			! ( $current_screen = get_current_screen() ) ||
			! isset( $current_screen->id ) ||
			'edit-product' !== $current_screen->id
		) {
			return;
		}
		?>
		<script>
		jQuery(
			function ( $ ) {
				$( '#the-list' ).on(
					'click',
					'.editinline',
					function () {
						var post_id = $( this ).closest( 'tr' ).attr( 'id' );
						post_id = post_id.replace( 'post-', '' );
						var inline_data = $( '#alg_wc_ean_inline_' + post_id );
						var ean = inline_data.find( '.alg_wc_ean_quick_edit' ).text();
						$( 'input[name="_alg_ean_qb"]', '.inline-edit-row' ).val( ean );
					}
				);
			}
		);
		</script>
		<?php
	}

	/**
	 * add_generate_button.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 */
	function add_generate_button() {
		if (
			is_admin() &&
			function_exists( 'get_current_screen' ) &&
			( $screen = get_current_screen() ) &&
			'product' === $screen->post_type
		) {
			$this->generate_button_js();
		}
	}

	/**
	 * generate_button_js.
	 *
	 * @version 5.5.2
	 * @since   4.0.0
	 *
	 * @todo    (dev) static? (3x)
	 * @todo    (dev) recheck `return false;`
	 * @todo    (dev) recheck `ajaxurl`
	 * @todo    (dev) use `admin_enqueue_scripts`?
	 */
	static function generate_button_js() {
		?><script>
			jQuery( document ).ready( function () {
				jQuery( 'body' ).on( 'click', '.alg_wc_ean_generate_ajax', function () {
					var product = jQuery( this ).data( 'product' )
					var input   = jQuery( this ).data( 'input' );
					jQuery( '#spinner-' + input ).addClass( 'is-active' );
					var data = {
						'action':  'alg_wc_ean_generate_ajax',
						'product': product,
						'input':   input,
					};
					jQuery.post( ajaxurl, data, function( response ) {
						if ( response ) {
							jQuery( '#' + data['input'] ).val( response ).trigger( 'change' );
						}
						jQuery( '#spinner-' + data['input'] ).removeClass( 'is-active' );
					} );
					return false;
				} );
			} );
		</script><?php
	}

	/**
	 * generate_button_ajax.
	 *
	 * @version 5.3.5
	 * @since   4.0.0
	 */
	static function generate_button_ajax() {
		$ean = alg_wc_ean()->core->product_tools->generate_ean(
			intval( $_POST['product'] ),
			alg_wc_ean()->core->product_tools->get_generate_data()
		);
		echo esc_html( $ean );
		die();
	}

	/**
	 * get_generate_button.
	 *
	 * @version 5.5.2
	 * @since   4.0.0
	 *
	 * @todo    (dev) spinner: styling?
	 * @todo    (dev) spinner: `float: none;`?
	 * @todo    (dev) spinner: wcfm
	 */
	static function get_generate_button( $product_id, $input_html_id, $button_style = '' ) {
		return (
			'<button' .
				' style="' . $button_style . '"' .
				' type="button"' .
				' class="button' .
				' alg_wc_ean_generate_ajax"' .
				' data-product="' . $product_id . '"' .
				' data-input="' . $input_html_id . '"' .
			'>' .
				sprintf(
					/* Translators: %s: EAN title. */
					esc_html__( 'Generate %s', 'ean-for-woocommerce' ),
					get_option( 'alg_wc_ean_title', esc_html__( 'EAN', 'ean-for-woocommerce' ) )
				) .
			'</button>' .
			'<span class="spinner" id="spinner-' . $input_html_id . '" style="float:none;"></span>'
		);
	}

	/**
	 * add_bulk_and_quick_edit_fields.
	 *
	 * @version 5.2.0
	 * @since   1.5.0
	 *
	 * @todo    (dev) reposition this (e.g., right after the "SKU" field)?
	 */
	function add_bulk_and_quick_edit_fields() {
		if ( 'woocommerce_product_quick_edit_end' === current_filter() ) {
			$start       = '<br class="clear" />';
			$placeholder = '';
		} else {
			$start       = '';
			$placeholder = __( '- No change -', 'ean-for-woocommerce' );
		}
		echo $start .
			'<label>' .
				'<span class="title">' . esc_html( get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) ) . '</span>' .
				'<span class="input-text-wrap">' .
					'<input type="text" name="_alg_ean_qb" class="text" placeholder="' . $placeholder . '" value="">' .
				'</span>' .
			'</label>';
	}

	/**
	 * save_bulk_and_quick_edit_fields.
	 *
	 * @version 5.2.0
	 * @since   1.5.0
	 */
	function save_bulk_and_quick_edit_fields( $post_id, $post ) {
		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if (
			defined( 'DOING_AUTOSAVE' ) &&
			DOING_AUTOSAVE
		) {
			return $post_id;
		}
		// Don't save revisions and autosaves
		if (
			wp_is_post_revision( $post_id ) ||
			wp_is_post_autosave( $post_id ) ||
			'product' !== $post->post_type ||
			! current_user_can( 'edit_post', $post_id )
		) {
			return $post_id;
		}
		// Check nonce
		if (
			! isset( $_REQUEST['woocommerce_quick_edit_nonce'] ) ||
			! wp_verify_nonce( $_REQUEST['woocommerce_quick_edit_nonce'], 'woocommerce_quick_edit_nonce' )
		) {
			return $post_id;
		}
		// Save
		if (
			isset( $_REQUEST['_alg_ean_qb'] ) &&
			(
				'' !== $_REQUEST['_alg_ean_qb'] ||
				! empty( $_REQUEST['woocommerce_quick_edit'] )
			)
		) {
			alg_wc_ean()->core->set_ean( $post_id, wc_clean( $_REQUEST['_alg_ean_qb'] ) );
		}
		return $post_id;
	}

	/**
	 * get_ean_input_desc.
	 *
	 * @version 3.6.0
	 * @since   1.0.1
	 *
	 * @todo    (dev) replace `style` with `class`
	 */
	function get_ean_input_desc( $ean, $product_id = false ) {
		$desc = array();
		if ( 'yes' === get_option( 'alg_wc_ean_backend_is_valid', 'yes' ) ) {
			$desc[] = ( alg_wc_ean()->core->is_valid_ean( $ean, $product_id ) ?
				'<span style="color:green;">' . esc_html__( 'Valid EAN', 'ean-for-woocommerce' )   . '</span>' :
				'<span style="color:red;">'   . esc_html__( 'Invalid EAN', 'ean-for-woocommerce' ) . '</span>' );
		}
		if ( 'yes' === get_option( 'alg_wc_ean_backend_is_unique', 'no' ) ) {
			$desc[] = ( ! alg_wc_ean()->core->do_ean_exist( $ean, $product_id ) ?
				'<span style="color:green;">' . esc_html__( 'Unique EAN', 'ean-for-woocommerce' )     . '</span>' :
				'<span style="color:red;">'   . esc_html__( 'Duplicated EAN', 'ean-for-woocommerce' ) . '</span>' );
		}
		return implode( ' | ', $desc );
	}

	/**
	 * get_ean_input_custom_atts.
	 *
	 * @version 5.2.2
	 * @since   1.0.1
	 *
	 * @todo    (dev) `AUTO`: better maxlength (13); add minlength (8)
	 * @todo    (dev) `ean-13`: `array( 'pattern' => '.{0}|[0-9]{13}', 'maxlength' => '13' ) )`
	 * @todo    (dev) `ean-13`: `array( 'pattern' => '.{0}|[0-9]+', 'minlength' => '13', 'maxlength' => '13' )`
	 */
	function get_ean_input_custom_atts( $product_id = false, $atts = array() ) {
		$result = $atts;

		// Required
		if ( 'yes' === get_option( 'alg_wc_ean_required', 'no' ) ) {
			$result = array_merge( $result, array( 'required' => 'required' ) );
		}

		// Pattern and max length
		$type = false;
		if ( 'yes' === get_option( 'alg_wc_ean_add_pattern', 'no' ) ) {
			$type = alg_wc_ean()->core->get_type( false, false, $product_id );
			switch ( $type ) {
				case 'EAN8':
				case 'UPCA':
				case 'EAN13':
				case 'ISBN13':
				case 'JAN':
				case 'EAN14':
				case 'AUTO':
					$result = array_merge( $result, array(
						'pattern'   => '[0-9]+',
						'maxlength' => ( 'AUTO' === $type ? 14 : alg_wc_ean()->core->get_ean_type_length( $type ) ),
					) );
					break;
			}
		}

		// Deprecated filter
		$result = apply_filters( 'alg_wc_ean_input_pattern', $result, $atts, $type );

		return apply_filters( 'alg_wc_ean_input_custom_atts', $result, $product_id, $atts );
	}

	/**
	 * add_ean_input_variation.
	 *
	 * @version 4.4.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) `variable{$key}` to `variable_{$key}`?
	 */
	function add_ean_input_variation( $loop, $variation_data, $variation ) {
		$key = alg_wc_ean()->core->ean_key;
		woocommerce_wp_text_input( array(
			'id'                => "variable{$key}_{$loop}",
			'name'              => "variable{$key}[{$loop}]",
			'value'             => ( isset( $variation_data[ $key ][0] ) ? $variation_data[ $key ][0] : '' ),
			'label'             => esc_html( get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) ),
			'wrapper_class'     => 'form-row form-row-full',
			'placeholder'       => alg_wc_ean()->core->get_ean( $variation->post_parent ),
			'description'       => (
				! empty( $variation_data[ $key ][0] ) ?
				$this->get_ean_input_desc( $variation_data[ $key ][0], $variation->ID ) :
				(
					$this->do_add_generate_button ?
					'<p>' . $this->get_generate_button( $variation->ID, "variable{$key}_{$loop}" ) . '</p>' :
					''
				)
			),
			'custom_attributes' => $this->get_ean_input_custom_atts( $variation->ID ),
		) );
	}

	/**
	 * save_ean_input_variation.
	 *
	 * @version 4.5.0
	 * @since   1.0.0
	 */
	function save_ean_input_variation( $variation_id, $i ) {
		$key = alg_wc_ean()->core->ean_key;
		if ( isset( $_POST[ 'variable' . $key ][ $i ] ) ) {
			alg_wc_ean()->core->set_ean( $variation_id, wc_clean( $_POST[ 'variable' . $key ][ $i ] ) );
		}
	}

	/**
	 * add_ean_input.
	 *
	 * @version 5.5.2
	 * @since   1.0.0
	 */
	function add_ean_input() {
		$product_id = get_the_ID();
		$value      = alg_wc_ean()->core->get_ean( $product_id );
		woocommerce_wp_text_input( array(
			'id'                => alg_wc_ean()->core->ean_key,
			'value'             => $value,
			'label'             => esc_html( get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) ),
			'description'       => (
				! empty( $value ) ?
				$this->get_ean_input_desc( $value, $product_id ) :
				(
					$this->do_add_generate_button ?
					$this->get_generate_button( $product_id, alg_wc_ean()->core->ean_key, 'margin-top:5px;' ) :
					''
				)
			),
			'custom_attributes' => $this->get_ean_input_custom_atts( $product_id ),
		) );
	}

	/**
	 * save_ean_input.
	 *
	 * @version 4.5.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) save `$key . '_is_valid'` (same in `save_ean_input_variation()`)
	 */
	function save_ean_input( $post_id, $__post ) {
		if (
			isset( $_POST[ alg_wc_ean()->core->ean_key ] ) &&
			empty( $_REQUEST['woocommerce_quick_edit'] ) &&
			empty( $_REQUEST['woocommerce_bulk_edit'] )
		) {
			alg_wc_ean()->core->set_ean( $post_id, wc_clean( $_POST[ alg_wc_ean()->core->ean_key ] ) );
		}
	}

}

endif;

return new Alg_WC_EAN_Edit();

<?php
/**
 * EAN for WooCommerce - Edit Class
 *
 * @version 5.5.7
 * @since   2.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_EAN_Edit' ) ) :

class WPFactory_WC_EAN_Edit {

	/**
	 * do_add_generate_button.
	 *
	 * @version 4.8.7
	 */
	public $do_add_generate_button;

	/**
	 * Constructor.
	 *
	 * @version 5.5.6
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
				'admin_enqueue_scripts',
				array( $this, 'add_quick_edit_js' )
			);

			// "Generate" button
			$this->do_add_generate_button = ( 'yes' === get_option( 'alg_wc_ean_backend_add_generate_button', 'no' ) );
			if ( $this->do_add_generate_button ) {
				add_action(
					'admin_enqueue_scripts',
					array( $this, 'add_generate_button' )
				);
				add_action(
					'wp_ajax_wpfactory_wc_ean_generate_ajax',
					array( $this, 'generate_button_ajax' )
				);
			}

		}
	}

	/**
	 * add_quick_edit_inline_data.
	 *
	 * @version 5.5.6
	 * @since   5.2.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/9.3.3/plugins/woocommerce/client/legacy/js/admin/quick-edit.js
	 */
	function add_quick_edit_inline_data( $column, $product_id ) {
		if ( 'name' !== $column ) {
			return;
		}
		?>
		<div class="hidden" id="wpfactory_wc_ean_inline_<?php echo absint( $product_id ); ?>">
			<div class="wpfactory_wc_ean_quick_edit"><?php echo esc_html( wpfactory_wc_ean()->core->get_ean( $product_id ) ); ?></div>
		</div>
		<?php
	}

	/**
	 * add_quick_edit_js.
	 *
	 * @version 5.5.6
	 * @since   5.2.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/9.3.3/plugins/woocommerce/includes/admin/list-tables/class-wc-admin-list-table-products.php#L161
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

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script(
			'wpfactory-wc-ean-quick-edit',
			wpfactory_wc_ean()->plugin_url() . '/assets/js/wpfactory-wc-ean-quick-edit' . $min . '.js',
			array( 'jquery' ),
			wpfactory_wc_ean()->version,
			true
		);
	}

	/**
	 * add_generate_button.
	 *
	 * @version 5.5.7
	 * @since   4.0.0
	 */
	function add_generate_button() {
		if (
			! is_admin() ||
			! function_exists( 'get_current_screen' ) ||
			! ( $screen = get_current_screen() ) ||
			'product' !== $screen->post_type
		) {
			return;
		}

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script(
			'wpfactory-wc-ean-generate-button',
			wpfactory_wc_ean()->plugin_url() . '/assets/js/wpfactory-wc-ean-generate-button' . $min . '.js',
			array( 'jquery' ),
			wpfactory_wc_ean()->version,
			true
		);

		wp_localize_script(
			'wpfactory-wc-ean-generate-button',
			'wpfactoryWCEANGenerateButton',
			array(
				'nonce' => wp_create_nonce( 'wpfactory_wc_ean_generate_ean' ),
			),
		);
	}

	/**
	 * generate_button_ajax.
	 *
	 * @version 5.5.7
	 * @since   4.0.0
	 */
	static function generate_button_ajax() {
		if (
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
				'wpfactory_wc_ean_generate_ean'
			)
		) {
			die();
		}

		$ean = wpfactory_wc_ean()->core->product_tools->generate_ean(
			intval( $_POST['product'] ?? 0 ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
			wpfactory_wc_ean()->core->product_tools->get_generate_data()
		);
		echo esc_html( $ean );
		die();
	}

	/**
	 * get_generate_button.
	 *
	 * @version 5.5.6
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
				' wpfactory_wc_ean_generate_ajax"' .
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
	 * @version 5.5.6
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
		echo wp_kses_post( $start ) .
			'<label>' .
				'<span class="title">' . esc_html( get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) ) . '</span>' .
				'<span class="input-text-wrap">' .
					'<input type="text" name="_wpfactory_ean_qb" class="text" placeholder="' . esc_attr( $placeholder ) . '" value="">' .
				'</span>' .
			'</label>';
	}

	/**
	 * save_bulk_and_quick_edit_fields.
	 *
	 * @version 5.5.6
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
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['woocommerce_quick_edit_nonce'] ) ), 'woocommerce_quick_edit_nonce' )
		) {
			return $post_id;
		}
		// Save
		if (
			isset( $_REQUEST['_wpfactory_ean_qb'] ) &&
			(
				'' !== $_REQUEST['_wpfactory_ean_qb'] ||
				! empty( $_REQUEST['woocommerce_quick_edit'] )
			)
		) {
			wpfactory_wc_ean()->core->set_ean( $post_id, wc_clean( wp_unslash( $_REQUEST['_wpfactory_ean_qb'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}
		return $post_id;
	}

	/**
	 * get_ean_input_desc.
	 *
	 * @version 5.5.6
	 * @since   1.0.1
	 *
	 * @todo    (dev) replace `style` with `class`
	 */
	function get_ean_input_desc( $ean, $product_id = false ) {
		$desc = array();
		if ( 'yes' === get_option( 'alg_wc_ean_backend_is_valid', 'yes' ) ) {
			$desc[] = ( wpfactory_wc_ean()->core->is_valid_ean( $ean, $product_id ) ?
				'<span style="color:green;">' . esc_html__( 'Valid EAN', 'ean-for-woocommerce' )   . '</span>' :
				'<span style="color:red;">'   . esc_html__( 'Invalid EAN', 'ean-for-woocommerce' ) . '</span>' );
		}
		if ( 'yes' === get_option( 'alg_wc_ean_backend_is_unique', 'no' ) ) {
			$desc[] = ( ! wpfactory_wc_ean()->core->do_ean_exist( $ean, $product_id ) ?
				'<span style="color:green;">' . esc_html__( 'Unique EAN', 'ean-for-woocommerce' )     . '</span>' :
				'<span style="color:red;">'   . esc_html__( 'Duplicated EAN', 'ean-for-woocommerce' ) . '</span>' );
		}
		return implode( ' | ', $desc );
	}

	/**
	 * get_ean_input_custom_atts.
	 *
	 * @version 5.5.6
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
			$type = wpfactory_wc_ean()->core->get_type( false, false, $product_id );
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
						'maxlength' => ( 'AUTO' === $type ? 14 : wpfactory_wc_ean()->core->get_ean_type_length( $type ) ),
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
	 * @version 5.5.6
	 * @since   1.0.0
	 *
	 * @todo    (dev) `variable{$key}` to `variable_{$key}`?
	 */
	function add_ean_input_variation( $loop, $variation_data, $variation ) {
		$key = wpfactory_wc_ean()->core->ean_key;
		woocommerce_wp_text_input( array(
			'id'                => "variable{$key}_{$loop}",
			'name'              => "variable{$key}[{$loop}]",
			'value'             => ( isset( $variation_data[ $key ][0] ) ? $variation_data[ $key ][0] : '' ),
			'label'             => esc_html( get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) ),
			'wrapper_class'     => 'form-row form-row-full',
			'placeholder'       => wpfactory_wc_ean()->core->get_ean( $variation->post_parent ),
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
		wp_nonce_field(
			'alg_wc_ean_save_input_variation',
			'_alg_wc_ean_save_input_variation_nonce_' . $variation->ID
		);
	}

	/**
	 * save_ean_input_variation.
	 *
	 * @version 5.5.6
	 * @since   1.0.0
	 */
	function save_ean_input_variation( $variation_id, $i ) {
		$key = wpfactory_wc_ean()->core->ean_key;
		if (
			isset(
				$_POST[ 'variable' . $key ][ $i ],
				$_POST[ '_alg_wc_ean_save_input_variation_nonce_' . $variation_id ]
			)
		) {
			if (
				! wp_verify_nonce(
					sanitize_text_field(
						wp_unslash(
							$_POST[ '_alg_wc_ean_save_input_variation_nonce_' . $variation_id ]
						)
					),
					'alg_wc_ean_save_input_variation'
				)
			) {
				wp_die( esc_html__( 'Nonce verification failed.', 'ean-for-woocommerce' ) );
			}
			wpfactory_wc_ean()->core->set_ean(
				$variation_id,
				wc_clean( wp_unslash( $_POST[ 'variable' . $key ][ $i ] ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			);
		}
	}

	/**
	 * add_ean_input.
	 *
	 * @version 5.5.6
	 * @since   1.0.0
	 *
	 * @todo    (v5.5.5) generate button: `empty( $value )` (also for variations)?
	 */
	function add_ean_input() {
		$product_id = get_the_ID();
		$value      = wpfactory_wc_ean()->core->get_ean( $product_id );
		woocommerce_wp_text_input( array(
			'id'                => wpfactory_wc_ean()->core->ean_key,
			'value'             => $value,
			'label'             => esc_html( get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) ),
			'description'       => (
				! empty( $value ) ?
				$this->get_ean_input_desc( $value, $product_id ) :
				(
					$this->do_add_generate_button ?
					$this->get_generate_button( $product_id, wpfactory_wc_ean()->core->ean_key, 'margin-top:5px;' ) :
					''
				)
			),
			'custom_attributes' => $this->get_ean_input_custom_atts( $product_id ),
		) );
		wp_nonce_field(
			'alg_wc_ean_save_input',
			'_alg_wc_ean_save_input_nonce'
		);
	}

	/**
	 * save_ean_input.
	 *
	 * @version 5.5.6
	 * @since   1.0.0
	 *
	 * @todo    (dev) save `$key . '_is_valid'` (same in `save_ean_input_variation()`)
	 */
	function save_ean_input( $post_id, $__post ) {
		if (
			isset(
				$_POST[ wpfactory_wc_ean()->core->ean_key ],
				$_POST['_alg_wc_ean_save_input_nonce']
			) &&
			empty( $_REQUEST['woocommerce_quick_edit'] ) &&
			empty( $_REQUEST['woocommerce_bulk_edit'] )
		) {
			if (
				! wp_verify_nonce(
					sanitize_text_field(
						wp_unslash(
							$_POST['_alg_wc_ean_save_input_nonce']
						)
					),
					'alg_wc_ean_save_input'
				)
			) {
				wp_die( esc_html__( 'Nonce verification failed.', 'ean-for-woocommerce' ) );
			}
			wpfactory_wc_ean()->core->set_ean(
				$post_id,
				wc_clean( wp_unslash( $_POST[ wpfactory_wc_ean()->core->ean_key ] ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			);
		}
	}

}

endif;

return new WPFactory_WC_EAN_Edit();

<?php
/**
 * EAN for WooCommerce - Product Tools Class
 *
 * @version 4.8.3
 * @since   2.1.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Product_Tools' ) ) :

class Alg_WC_EAN_Product_Tools {

	/**
	 * Constructor.
	 *
	 * @version 4.1.2
	 * @since   2.1.0
	 *
	 * @todo    (dev) split into more files/classes, e.g., `class-alg-wc-ean-crons.php`?
	 * @todo    (feature) Automatic actions: `updated_postmeta`?
	 * @todo    (dev) Automatic actions: `woocommerce_after_product_object_save`?
	 */
	function __construct() {

		// Products Tools
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'products_delete' ) );
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'products_create' ) );

		// Automatic actions
		add_action( 'wp_insert_post', array( $this, 'product_on_insert_post' ), PHP_INT_MAX, 3 );

		// Periodic action
		if ( '' !== get_option( 'alg_wc_ean_products_periodic_action', '' ) ) {
			add_action( 'init', array( $this, 'schedule_products_periodic_action' ) );
			add_action( 'alg_wc_ean_products_periodic_action', array( $this, 'process_products_periodic_action' ) );
		} else {
			add_action( 'init', array( $this, 'unschedule_products_periodic_action' ) );
		}

		// "Products > Bulk actions"
		add_filter( 'bulk_actions-edit-product', array( $this, 'add_product_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-product', array( $this, 'handle_product_bulk_actions' ), 10, 3 );
		add_action( 'admin_footer', array( $this, 'bulk_actions_confirmation_js' ) );

		// Assign from the list: Reuse deleted
		add_action( 'before_delete_post', array( $this, 'reuse_deleted' ), 10, 2 );

	}

	/**
	 * bulk_actions_confirmation_js.
	 *
	 * @version 4.1.2
	 * @since   4.1.2
	 *
	 * @todo    (dev) load only when needed
	 * @todo    (dev) use `admin_enqueue_scripts`
	 */
	function bulk_actions_confirmation_js() {
		$actions     = get_option( 'alg_wc_ean_product_bulk_actions',         array( 'alg_wc_ean_delete', 'alg_wc_ean_generate' ) );
		$confirm     = get_option( 'alg_wc_ean_product_bulk_actions_confirm', array( 'alg_wc_ean_delete' ) );
		$confirm_ids = array_intersect( $actions, $confirm );
		if ( ! empty( $confirm_ids ) ) {
			?><script>
				const confirm_ids = <?php echo "['" . implode( "','", $confirm_ids ) . "']"; ?>;
				jQuery( '#doaction' ).on( 'click', function () {
					if ( -1 != confirm_ids.indexOf( jQuery( 'select[name="action"]' ).val() ) ) {
						if ( ! confirm( "<?php echo esc_html__( 'Are you sure?', 'ean-for-woocommerce' ); ?>" ) ) {
							return false;
						}
					}
				} );
			</script><?php
		}
	}

	/**
	 * reuse_deleted.
	 *
	 * @version 3.7.0
	 * @since   3.7.0
	 *
	 * @todo    (dev) `product_variation` + `alg_wc_ean_tool_product_variable`?
	 * @todo    (feature) add option to add to the end of the list (i.e., not to the beginning of the list, as it is now)
	 */
	function reuse_deleted( $postid, $post ) {
		$assign_list_settings = get_option( 'alg_wc_ean_tool_product_assign_list_settings', array() );
		if (
			! empty( $assign_list_settings['reuse_deleted'] ) && 'yes' === $assign_list_settings['reuse_deleted'] &&
			in_array( $post->post_type, array( 'product', 'product_variation' ) ) &&
			'' !== ( $ean = get_post_meta( $postid, alg_wc_ean()->core->ean_key, true ) )
		) {
			$data = ( '' !== ( $data = get_option( 'alg_wc_ean_tool_product_assign_list', '' ) ) ? array_map( 'trim', explode( PHP_EOL, $data ) ) : array() );
			update_option( 'alg_wc_ean_tool_product_assign_list', implode( PHP_EOL, array_merge( array( $ean ), $data ) ) );
		}
	}

	/**
	 * process_products_periodic_action.
	 *
	 * @version 2.9.0
	 * @since   2.9.0
	 *
	 * @todo    (dev) log
	 */
	function process_products_periodic_action( $args ) {
		if ( '' !== ( $action = get_option( 'alg_wc_ean_products_periodic_action', '' ) ) ) {
			$this->process_action_for_all_products( $action );
		}
	}

	/**
	 * unschedule_products_periodic_action.
	 *
	 * @version 2.9.0
	 * @since   2.9.0
	 */
	function unschedule_products_periodic_action() {
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( 'alg_wc_ean_products_periodic_action' );
		}
	}

	/**
	 * schedule_products_periodic_action.
	 *
	 * @version 2.9.0
	 * @since   2.9.0
	 */
	function schedule_products_periodic_action() {
		if (
			function_exists( 'as_has_scheduled_action' ) &&
			( $interval_in_seconds = get_option( 'alg_wc_ean_products_periodic_action_interval', 3600 ) ) &&
			false === as_has_scheduled_action( 'alg_wc_ean_products_periodic_action', array( $interval_in_seconds ) )
		) {
			as_unschedule_all_actions( 'alg_wc_ean_products_periodic_action' );
			as_schedule_recurring_action( time(), $interval_in_seconds, 'alg_wc_ean_products_periodic_action', array( $interval_in_seconds ) );
		}
	}

	/**
	 * handle_product_bulk_actions.
	 *
	 * @version 4.5.0
	 * @since   2.7.0
	 *
	 * @todo    (feature) all other actions, e.g., "Copy EAN from SKU", etc.
	 * @todo    (dev) notices
	 * @todo    (dev) merge with `products_create()`?
	 */
	function handle_product_bulk_actions( $redirect_to, $action, $post_ids ) {
		if ( in_array( $action, array( 'alg_wc_ean_generate', 'alg_wc_ean_delete' ) ) ) {
			$data  = ( 'alg_wc_ean_generate' === $action ? $this->get_generate_data() : false );
			$count = 0;
			foreach ( $post_ids as $post_id ) {
				$product = wc_get_product( $post_id );
				if ( $product->is_type( 'variable' ) ) {
					$variations = array_keys( get_children( array( 'post_parent' => $post_id, 'posts_per_page' => -1, 'post_type' => 'product_variation' ), 'ARRAY_N' ) );
					switch ( get_option( 'alg_wc_ean_tool_product_variable', 'all' ) ) {
						case 'variations_only':
							$product_ids = $variations;
							break;
						case 'variable_only':
							$product_ids = array( $post_id );
							break;
						default: // 'all'
							$product_ids = array_merge( array( $post_id ), $variations );
							break;
					}
				} else {
					$product_ids = array( $post_id );
				}
				foreach ( $product_ids as $product_id ) {
					switch ( $action ) {
						case 'alg_wc_ean_generate':
							$result = (
									'' === get_post_meta( $product_id, alg_wc_ean()->core->ean_key, true ) &&
									'' !== ( $ean = $this->generate_ean( $product_id, $data ) ) &&
									alg_wc_ean()->core->set_ean( $product_id, $ean )
								);
							if ( $result && '' !== $data['product_attribute'] ) {
								$this->add_product_attribute( $product_id, $ean, $data['product_attribute'] );
							}
							break;
						case 'alg_wc_ean_delete':
							$result = delete_post_meta( $product_id, alg_wc_ean()->core->ean_key );
							break;
					}
					if ( $result ) {
						$count++;
					}
				}
			}
		}
		return $redirect_to;
	}

	/**
	 * add_product_bulk_actions.
	 *
	 * @version 2.9.0
	 * @since   2.7.0
	 */
	function add_product_bulk_actions( $actions ) {
		return array_merge( $actions, array_intersect_key( array(
				'alg_wc_ean_generate' => __( 'Generate EAN', 'ean-for-woocommerce' ),
				'alg_wc_ean_delete'   => __( 'Delete EAN', 'ean-for-woocommerce' ),
			), array_flip( get_option( 'alg_wc_ean_product_bulk_actions', array( 'alg_wc_ean_delete', 'alg_wc_ean_generate' ) ) ) ) );
	}

	/**
	 * get_products.
	 *
	 * @version 4.7.2
	 * @since   2.1.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/wiki/wc_get_products-and-WC_Product_Query
	 *
	 * @todo    (dev) `meta_query`: `ean` not empty
	 * @todo    (feature) optional: `meta_query`: `ean` not empty
	 */
	function get_products( $action = false ) {

		// Get product types
		$product_types = array_merge( array_keys( wc_get_product_types() ), array( 'variation' ) );
		if ( 'all' !== ( $variable_products = get_option( 'alg_wc_ean_tool_product_variable', 'all' ) ) ) {
			$unset = ( 'variations_only' === $variable_products ? 'variable' : 'variation' );
			if ( false !== ( $key = array_search( $unset, $product_types ) ) ) {
				unset( $product_types[ $key ] );
			}
		}

		// Query args
		$args = array(
			'limit'   => -1,
			'return'  => 'ids',
			'orderby' => 'ID',
			'order'   => 'ASC',
			'type'    => $product_types,
		);

		// Product options
		$product_options = false;
		switch ( $action ) {
			case 'assign_list':
				$product_options = get_option( 'alg_wc_ean_tool_product_assign_list_settings', array() );
				break;
		}
		if ( $product_options ) {
			foreach ( array( 'product_cat' => 'category' ) as $taxonomy => $arg ) {
				$terms = ( isset( $product_options[ $taxonomy ] ) ? $product_options[ $taxonomy ] : array() );
				if ( ! empty( $terms ) ) {
					$args[ $arg ] = array_map( array( $this, "get_{$taxonomy}_slug" ), $terms );
				}
			}
		}

		// Fix variations `category`
		if ( ( $do_fix_variations = ( 'variable_only' !== $variable_products && ! empty( $args['category'] ) ) ) ) {
			$do_remove_variable = false;
			if ( 'variations_only' === $variable_products ) {
				// Temporarily get `variable` products
				$args['type'][] = 'variable';
				$do_remove_variable = true;
			}
		}

		// Query
		$products = wc_get_products( $args );

		// Fix variations `category`: Add variations
		if ( $do_fix_variations ) {
			$_products = array();
			foreach ( $products as $product_id ) {
				$children = ( 'variable' !== WC_Product_Factory::get_product_type( $product_id ) ? false :
					array_keys( get_children( array(
						'post_parent'    => $product_id,
						'posts_per_page' => -1,
						'post_type'      => 'product_variation',
					), 'ARRAY_N' ) ) );
				if ( ! empty( $children ) ) {
					// Variable products
					if ( ! $do_remove_variable ) {
						$_products[] = $product_id; // only when `$variable_products` is set to `all` (i.e., not to `variations_only`)
					}
					$_products = array_merge( $_products, $children );
				} else {
					// Other (e.g., simple) products
					$_products[] = $product_id;
				}
			}
			$products = $_products;
			sort( $products );
		}

		return $products;
	}

	/**
	 * get_product_cat_slug.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function get_product_cat_slug( $term_id ) {
		$term = get_term( $term_id, 'product_cat' );
		return ( $term && ! is_wp_error( $term ) ? $term->slug : '' );
	}

	/**
	 * products_delete.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 *
	 * @todo    (dev) delete `product_attribute` as well
	 * @todo    (dev) delete directly with SQL from the `meta` table: `$counter = $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = '" . alg_wc_ean()->core->ean_key . "'" );`
	 * @todo    (dev) better notice(s)?
	 */
	function products_delete() {
		if ( 'yes' === get_option( 'alg_wc_ean_tool_delete_product_meta', 'no' ) ) {
			update_option( 'alg_wc_ean_tool_delete_product_meta', 'no' );
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$count = 0;
				foreach ( $this->get_products() as $product_id ) {
					if ( delete_post_meta( $product_id, alg_wc_ean()->core->ean_key ) ) {
						$count++;
					}
				}
				if ( method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
					WC_Admin_Settings::add_message( sprintf( __( 'EAN deleted for %s products.', 'ean-for-woocommerce' ), $count ) );
				}
			}
		}
	}

	/**
	 * is_valid_product.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function is_valid_product( $post_id, $action ) {
		$product_options = false;
		switch ( $action ) {
			case 'assign_list':
				$product_options = get_option( 'alg_wc_ean_tool_product_assign_list_settings', array() );
				break;
		}
		if ( $product_options ) {
			foreach ( array( 'product_cat' => 'category' ) as $taxonomy => $arg ) {
				$terms = ( isset( $product_options[ $taxonomy ] ) ? $product_options[ $taxonomy ] : array() );
				if ( ! empty( $terms ) && ! has_term( $terms, $taxonomy, $post_id ) ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * product_on_insert_post.
	 *
	 * @version 4.8.3
	 * @since   2.2.8
	 *
	 * @todo    (dev) merge with `products_create()`?
	 * @todo    (dev) stop on first `update_post_meta`?
	 */
	function product_on_insert_post( $post_id, $post, $update ) {

		$action = get_option( ( $update ? 'alg_wc_ean_tool_product_action_on_update' : 'alg_wc_ean_tool_product_action_on_new' ), '' );

		if (
			! empty( $action ) &&
			in_array( $post->post_type, array( 'product', 'product_variation' ) ) &&
			$this->is_valid_product( $post_id, $action )
		) {

			$product = wc_get_product( $post_id );

			if ( 'all' !== ( $variable_products = get_option( 'alg_wc_ean_tool_product_variable', 'all' ) ) ) {
				if (
					( $product->is_type( 'variable' )  && 'variations_only' === $variable_products ) ||
					( $product->is_type( 'variation' ) && 'variable_only'   === $variable_products )
				) {
					return;
				}
			}

			if ( '' === ( $current_ean = get_post_meta( $post_id, alg_wc_ean()->core->ean_key, true ) ) ) {

				// Action: Generate, Copy from SKU/ID/meta, Assign from the list
				$ean = '';
				switch ( $action ) {

					case 'generate':
						$data = $this->get_generate_data();
						$ean  = $this->generate_ean( $post_id, $data );
						break;

					case 'copy_sku':
						$ean = ( $product ? $product->get_sku() : get_post_meta( $post_id, '_sku', true ) );
						break;

					case 'copy_id':
						$ean = $post_id;
						break;

					case 'copy_meta':
						$data = array_replace( array( 'key' => '', 'sub_key' => '' ), get_option( 'alg_wc_ean_tool_product_copy_meta', array() ) );
						if ( '' !== $data['key'] ) {
							$ean = get_post_meta( $post_id, $data['key'], true );
							if ( '' !== $data['sub_key'] ) {
								$ean = ( isset( $ean[ $data['sub_key'] ] ) ? $ean[ $data['sub_key'] ] : '' );
							}
						}
						break;

					case 'copy_attr':
						$data = get_option( 'alg_wc_ean_tool_product_copy_attr', array() );
						if ( isset( $data['product_attribute'] ) && 'alg_wc_ean_product_attribute_custom' === $data['product_attribute'] ) {
							$data['product_attribute'] = ( isset( $data['product_attribute_custom'] ) ? $data['product_attribute_custom'] : '' );
						}
						if ( isset( $data['product_attribute'] ) && '' !== $data['product_attribute'] ) {
							$ean = ( $product ? $product->get_attribute( $data['product_attribute'] ) : get_post_meta( $post_id, 'attribute_' . $data['product_attribute'], true ) );
						}
						break;

					case 'assign_list':
						$data = get_option( 'alg_wc_ean_tool_product_assign_list', '' );
						if ( '' !== $data ) {
							$data = array_map( 'trim', explode( PHP_EOL, $data ) );
							$ean  = array_shift( $data );
							update_option( 'alg_wc_ean_tool_product_assign_list', ( empty( $data ) ? '' : implode( PHP_EOL, $data ) ) );
						}
						break;

				}

				if ( '' !== $ean ) {

					alg_wc_ean()->core->set_ean( $post_id, $ean );

					if ( 'generate' === $action && '' !== $data['product_attribute'] ) {
						$this->add_product_attribute( $post_id, $ean, $data['product_attribute'] );
					}

					// Prevent the new EAN from being overwritten by the variation's `input` field on the product edit page
					if ( 'product_variation' === $post->post_type && isset( alg_wc_ean()->core->edit ) ) {
						remove_action( 'woocommerce_save_product_variation', array( alg_wc_ean()->core->edit, 'save_ean_input_variation' ), 10 );
					}

				}

			} else {

				// Action: Copy to product SKU/attribute
				switch ( $action ) {

					case 'copy_to_sku':
						update_post_meta( $post_id, '_sku', $current_ean );
						break;

					case 'copy_to_meta':
						$data             = get_option( 'alg_wc_ean_tool_product_copy_to_meta', array() );
						$data['keys']     = ( isset( $data['key'] )     && '' !== $data['key']     ? explode( ',', $data['key'] )     : array() );
						$data['sub_keys'] = ( isset( $data['sub_key'] ) && '' !== $data['sub_key'] ? explode( ',', $data['sub_key'] ) : array() );
						foreach ( $data['keys'] as $i => $key ) {
							if ( '' === $key ) {
								continue;
							}
							if ( isset( $data['sub_keys'][ $i ] ) && '' !== $data['sub_keys'][ $i ] ) {
								if ( '' === ( $_value = get_post_meta( $post_id, $key, true ) ) ) {
									$_value = array();
								} elseif ( ! is_array( $_value ) ) {
									continue;
								}
								$_value[ $data['sub_keys'][ $i ] ] = $current_ean;
								update_post_meta( $post_id, $key, $_value );
							} else {
								update_post_meta( $post_id, $key, $current_ean );
							}
						}
						break;

					case 'copy_to_attr':
						$data = get_option( 'alg_wc_ean_tool_product_copy_to_attr', array() );
						if ( isset( $data['product_attribute'] ) && '' !== $data['product_attribute'] ) {
							$this->add_product_attribute( $post_id, $current_ean, $data['product_attribute'] );
						}
						break;

				}

			}

		}

	}

	/**
	 * process_action_for_all_products.
	 *
	 * @version 4.8.3
	 * @since   2.9.0
	 *
	 * @todo    (dev) Copy to: do NOT overwrite?
	 * @todo    (dev) `array_shift()` vs `array_reverse()` + `array_pop()`?
	 */
	function process_action_for_all_products( $action ) {

		// Prepare (and validate) data
		switch ( $action ) {

			case 'generate':
				$data = $this->get_generate_data();
				break;

			case 'copy_meta':
				$data = array_replace( array( 'key' => '', 'sub_key' => '' ), get_option( 'alg_wc_ean_tool_product_copy_meta', array() ) );
				if ( '' === $data['key'] ) {
					return array( 'result' => false, 'message' => __( 'Please set the "Meta key" option.', 'ean-for-woocommerce' ) );
				}
				break;

			case 'copy_attr':
				$data = get_option( 'alg_wc_ean_tool_product_copy_attr', array() );
				if ( ! isset( $data['product_attribute'] ) || '' === $data['product_attribute'] ) {
					return array( 'result' => false, 'message' => __( 'Please set the "Product attribute" option.', 'ean-for-woocommerce' ) );
				}
				if ( 'alg_wc_ean_product_attribute_custom' === $data['product_attribute'] ) {
					if ( ! isset( $data['product_attribute_custom'] ) || '' === $data['product_attribute_custom'] ) {
						return array( 'result' => false, 'message' => __( 'Please set the "Custom product attribute" option.', 'ean-for-woocommerce' ) );
					}
					$data['product_attribute'] = $data['product_attribute_custom'];
				}
				break;

			case 'assign_list':
				$data = get_option( 'alg_wc_ean_tool_product_assign_list', '' );
				if ( '' === $data ) {
					return array( 'result' => false, 'message' => __( 'Please set the "List" option.', 'ean-for-woocommerce' ) );
				} else {
					$data = array_map( 'trim', explode( PHP_EOL, $data ) );
				}
				break;

			case 'copy_to_meta':
				$data             = get_option( 'alg_wc_ean_tool_product_copy_to_meta', array() );
				$data['keys']     = ( isset( $data['key'] )     && '' !== $data['key']     ? explode( ',', $data['key'] )     : array() );
				$data['sub_keys'] = ( isset( $data['sub_key'] ) && '' !== $data['sub_key'] ? explode( ',', $data['sub_key'] ) : array() );
				if ( empty( $data['keys'] ) ) {
					return array( 'result' => false, 'message' => __( 'Please set the "Meta key" option.', 'ean-for-woocommerce' ) );
				}
				break;

			case 'copy_to_attr':
				$data = get_option( 'alg_wc_ean_tool_product_copy_to_attr', array() );
				$data['product_attribute'] = ( isset( $data['product_attribute'] ) ? $data['product_attribute'] : '' );
				break;

		}

		// Product loop
		$products = $this->get_products( $action );
		$total    = count( $products );
		$count    = 0;
		foreach ( $products as $product_id ) {
			$current_ean = get_post_meta( $product_id, alg_wc_ean()->core->ean_key, true );

			// Action: Get stats
			if ( 'get_stats' === $action ) {
				if ( '' === $current_ean ) {
					$count++;
				}
				continue;
			}

			// Action: Copy to product SKU/meta/attribute
			if ( 'copy_to_sku' === $action ) {
				if ( '' !== $current_ean ) {
					if ( update_post_meta( $product_id, '_sku', $current_ean ) ) {
						$count++;
					}
				}
				continue;
			}
			if ( 'copy_to_meta' === $action ) {
				if ( '' !== $current_ean ) {
					foreach ( $data['keys'] as $i => $key ) {
						if ( '' === $key ) {
							continue;
						}
						if ( isset( $data['sub_keys'][ $i ] ) && '' !== $data['sub_keys'][ $i ] ) {
							if ( '' === ( $_value = get_post_meta( $product_id, $key, true ) ) ) {
								$_value = array();
							} elseif ( ! is_array( $_value ) ) {
								continue;
							}
							$_value[ $data['sub_keys'][ $i ] ] = $current_ean;
							if ( update_post_meta( $product_id, $key, $_value ) ) {
								$count++;
							}
						} else {
							if ( update_post_meta( $product_id, $key, $current_ean ) ) {
								$count++;
							}
						}
					}
				}
				continue;
			}
			if ( 'copy_to_attr' === $action && '' !== $data['product_attribute'] ) {
				if ( '' !== $current_ean ) {
					if ( $this->add_product_attribute( $product_id, $current_ean, $data['product_attribute'] ) ) {
						$count++;
					}
				}
				continue;
			}

			// Action: Generate, Copy from SKU/ID/meta, Assign from the list
			if ( '' !== $current_ean ) {
				continue;
			}
			$ean = '';
			switch ( $action ) {
				case 'generate':
					$ean = $this->generate_ean( $product_id, $data );
					break;
				case 'copy_sku':
					$ean = ( ( $product = wc_get_product( $product_id ) ) ? $product->get_sku() : get_post_meta( $product_id, '_sku', true ) );
					break;
				case 'copy_id':
					$ean = $product_id;
					break;
				case 'copy_meta':
					$ean = get_post_meta( $product_id, $data['key'], true );
					if ( '' !== $data['sub_key'] ) {
						$ean = ( isset( $ean[ $data['sub_key'] ] ) ? $ean[ $data['sub_key'] ] : '' );
					}
					break;
				case 'copy_attr':
					$ean = ( ( $product = wc_get_product( $product_id ) ) ? $product->get_attribute( $data['product_attribute'] ) : get_post_meta( $product_id, 'attribute_' . $data['product_attribute'], true ) );
					break;
				case 'assign_list':
					$ean = array_shift( $data );
					break;
			}
			if ( '' !== $ean && alg_wc_ean()->core->set_ean( $product_id, $ean ) ) {
				if ( 'generate' === $action && '' !== $data['product_attribute'] ) {
					$this->add_product_attribute( $product_id, $ean, $data['product_attribute'] );
				}
				$count++;
			}
			if ( 'assign_list' === $action && empty( $data ) ) {
				break;
			}

		}

		if ( 'assign_list' === $action ) {
			update_option( 'alg_wc_ean_tool_product_assign_list', ( empty( $data ) ? '' : implode( PHP_EOL, $data ) ) );
		}

		switch ( $action ) {
			case 'generate':
				$message = __( 'EAN generated for %s products (%s total products).', 'ean-for-woocommerce' );
				break;
			case 'assign_list':
				$message = __( 'EAN assigned for %s products (%s total products).', 'ean-for-woocommerce' );
				break;
			case 'get_stats':
				$message = __( '%s products without EAN (%s total products).', 'ean-for-woocommerce' );
				break;
			default:
				$message = __( 'EAN copied for %s products (%s total products).', 'ean-for-woocommerce' );
		}

		return array( 'result' => true, 'message' => sprintf( $message, $count, $total ) );

	}

	/**
	 * products_create.
	 *
	 * @version 4.4.2
	 * @since   2.1.0
	 *
	 * @todo    (dev) message: "success/error" (i.e., check `$response['result']`)
	 * @todo    (feature) per individual product (JS or AJAX?)
	 * @todo    (dev) better notice(s)?
	 */
	function products_create() {
		$tools = array_replace( array(
			'generate'     => 'no',
			'copy_sku'     => 'no',
			'copy_id'      => 'no',
			'copy_meta'    => 'no',
			'copy_attr'    => 'no',
			'assign_list'  => 'no',
			'get_stats'    => 'no',
			'copy_to_sku'  => 'no',
			'copy_to_meta' => 'no',
			'copy_to_attr' => 'no',
		), get_option( 'alg_wc_ean_tool_product', array() ) );
		if ( in_array( 'yes', $tools ) ) {
			delete_option( 'alg_wc_ean_tool_product' );
			if ( current_user_can( 'manage_woocommerce' ) ) {
				foreach ( $tools as $tool => $is_enabled ) {
					if ( 'yes' === $is_enabled ) {
						$action = $tool;
						break;
					}
				}
				$response = $this->process_action_for_all_products( $action );
				if ( ! empty( $response['message'] ) && method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
					WC_Admin_Settings::add_message( $response['message'] );
				}
			}
		}
	}

	/**
	 * add_product_attribute.
	 *
	 * @version 3.9.0
	 * @since   3.7.2
	 *
	 * @todo    (feature) local (i.e., per product; non-taxonomy) product attribute
	 * @todo    (dev) use `$product->set_attributes()` instead of `update_post_meta( $product_id, '_product_attributes', $product_attributes )`
	 */
	function add_product_attribute( $product_id, $ean, $taxonomy ) {
		wp_set_object_terms( $product_id, $ean, $taxonomy );
		$product_attributes = get_post_meta( $product_id, '_product_attributes', true );
		if ( empty( $product_attributes ) ) {
			$product_attributes = array();
		}
		$product_attributes[ $taxonomy ] = array(
			'name'         => $taxonomy,
			'value'        => $ean,
			'is_visible'   => 1,
			'is_variation' => 0,
			'is_taxonomy'  => 1,
		);
		return update_post_meta( $product_id, '_product_attributes', $product_attributes );
	}

	/**
	 * get_generate_data.
	 *
	 * @version 3.9.0
	 * @since   2.2.8
	 *
	 * @todo    (dev) `ISBN13`, `JAN`
	 * @todo    (fix) `UPCA`: 1+5+5+1 (https://www.cognex.com/resources/symbologies/1-d-linear-barcodes/upc-a-barcodes)
	 * @todo    (dev) move to a separate class/file, e.g., `class-alg-wc-ean-generator.php`?
	 */
	function get_generate_data() {
		$res = array();
		$default_data = array(
			'type'              => 'EAN13',
			'prefix'            => 200,
			'prefix_to'         => '',
			'prefix_length'     => 3,
			'seed_prefix'       => '',
			'seed_method'       => 'product_id',
			'product_attribute' => '',
		);
		$data = array_replace( $default_data, get_option( 'alg_wc_ean_tool_product_generate', array() ) );

		// Seed length
		switch ( $data['type'] ) {
			case 'EAN8':
				$length = 8;
				$res['prefix_length'] = $data['prefix_length'];
				break;
			case 'UPCA':
				$length = 12;
				$res['prefix_length'] = 3;
				break;
			default: // 'EAN13'
				$length = 13;
				$res['prefix_length'] = 3;
		}
		$res['seed_length'] = ( $length - $res['prefix_length'] - 1 );

		// Seed prefix, i.e., "Manufacturer code"
		$seed_prefix         = ( strlen( $data['seed_prefix'] ) > $res['seed_length'] ? substr( $data['seed_prefix'], 0, $res['seed_length'] ) : $data['seed_prefix'] );
		$res['seed_length'] -= strlen( $seed_prefix );
		$res['seed_prefix']  = $seed_prefix;

		// Prefix
		if ( '' === $data['prefix'] ) {
			$data['prefix'] = 0;
		}
		$res['is_rand_prefix'] = ( '' !== $data['prefix_to'] && $data['prefix'] != $data['prefix_to'] );
		$res['prefix'] = ( $res['is_rand_prefix'] ?
			array(
				'from' => ( $data['prefix_to'] > $data['prefix'] ? $data['prefix'] : $data['prefix_to'] ),
				'to'   => ( $data['prefix_to'] > $data['prefix'] ? $data['prefix_to'] : $data['prefix'] ),
			) :
			str_pad( substr( $data['prefix'], 0, $res['prefix_length'] ), $res['prefix_length'], '0', STR_PAD_LEFT )
		);

		// Seed method, i.e., "Product code"
		$res['seed_method'] = $data['seed_method'];

		// Product attribute
		$res['product_attribute'] = $data['product_attribute'];

		return $res;
	}

	/**
	 * get_rand_prefix.
	 *
	 * @version 3.9.0
	 * @since   3.9.0
	 */
	function get_rand_prefix( $from, $to, $length ) {
		return str_pad( substr( rand( $from, $to ), 0, $length ), $length, '0', STR_PAD_LEFT );
	}

	/**
	 * get_seed.
	 *
	 * @version 3.9.0
	 * @since   3.9.0
	 *
	 * @todo    (dev) `counter`: (optional) max value?
	 */
	function get_seed( $method, $length, $args ) {
		switch ( $method ) {

			case 'product_id':
				$seed = $args['product_id'];
				break;

			case 'counter':
				global $wpdb;
				$wpdb->query( 'START TRANSACTION' );
				$seed = get_option( 'alg_wc_ean_tool_product_generate_seed_counter', 0 );
				update_option( 'alg_wc_ean_tool_product_generate_seed_counter', ( $seed + 1 ) );
				$wpdb->query( 'COMMIT' );
				break;

		}
		return str_pad( substr( $seed, 0, $length ), $length, '0', STR_PAD_LEFT );
	}

	/**
	 * generate_ean.
	 *
	 * @version 4.0.0
	 * @since   2.2.8
	 */
	function generate_ean( $product_id, $data ) {

		$prefix      = ( $data['is_rand_prefix'] ? $this->get_rand_prefix( $data['prefix']['from'], $data['prefix']['to'], $data['prefix_length'] ) : $data['prefix'] );
		$prefix      = apply_filters( 'alg_wc_ean_product_tools_generate_ean_country_prefix', $prefix, $product_id );

		$seed_prefix = apply_filters( 'alg_wc_ean_product_tools_generate_ean_seed_prefix', $data['seed_prefix'], $product_id );

		$seed        = $this->get_seed( $data['seed_method'], $data['seed_length'], array( 'product_id' => $product_id ) );
		$seed        = apply_filters( 'alg_wc_ean_product_tools_generate_ean_seed', $seed, $product_id );

		$ean         = $prefix . $seed_prefix . $seed;

		return $ean . $this->get_checksum( $ean );
	}

	/**
	 * get_checksum.
	 *
	 * @version 2.2.7
	 * @since   2.2.5
	 *
	 * @see     https://stackoverflow.com/questions/19890144/generate-valid-ean13-in-php
	 */
	function get_checksum( $code ) {
		$flag = true;
		$sum  = 0;
		for ( $i = strlen( $code ) - 1; $i >= 0; $i-- ) {
			$sum += (int) $code[ $i ] * ( $flag ? 3 : 1 );
			$flag = ! $flag;
		}
		return ( 10 - ( $sum % 10 ) ) % 10;
	}

}

endif;

return new Alg_WC_EAN_Product_Tools();

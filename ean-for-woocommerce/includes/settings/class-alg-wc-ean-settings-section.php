<?php
/**
 * EAN for WooCommerce - Section Settings
 *
 * @version 4.8.7
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Settings_Section' ) ) :

class Alg_WC_EAN_Settings_Section {

	/**
	 * id.
	 *
	 * @version 4.8.7
	 */
	public $id;

	/**
	 * desc.
	 *
	 * @version 4.8.7
	 */
	public $desc;

	/**
	 * dim.
	 *
	 * @version 4.8.7
	 */
	public $dim;

	/**
	 * dim_suffix.
	 *
	 * @version 4.8.7
	 */
	public $dim_suffix;

	/**
	 * num.
	 *
	 * @version 4.8.7
	 */
	public $num;

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		add_filter( 'woocommerce_get_sections_alg_wc_ean', array( $this, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_alg_wc_ean_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
	}

	/**
	 * settings_section.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

	/**
	 * pro_msg.
	 *
	 * @version 4.0.0
	 * @since   2.0.0
	 */
	function pro_msg( $msg = 'enable this section' ) {
		return apply_filters( 'alg_wc_ean_settings', '<p style="padding:15px;color:black;background-color:white;font-weight:bold;">' .
			sprintf( 'You will need <a target="_blank" href="https://wpfactory.com/item/ean-for-woocommerce/">EAN for WooCommerce Pro</a> plugin version to %s.', $msg ) .
		'</p>' );
	}

	/**
	 * variable_products_note.
	 *
	 * @version 2.4.0
	 * @since   2.0.0
	 *
	 * @todo    (desc) better desc?
	 */
	function variable_products_note() {
		return __( 'Please note that for the <strong>variable</strong> products, <strong>main</strong> product\'s EAN must be set.', 'ean-for-woocommerce' );
	}

	/**
	 * get_wc_emails.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function get_wc_emails() {
		$emails    = array();
		$wc_emails = WC_Emails::instance();
		foreach ( $wc_emails->get_emails() as $email_id => $email ) {
			$emails[ $email->id ] = $email->get_title();
		}
		return $emails;
	}

}

endif;

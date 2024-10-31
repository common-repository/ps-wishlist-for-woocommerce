<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.storeprose.com
 * @since      1.0.0
 *
 * @package    Pswish_Woocommerce_Wishlist
 * @subpackage Pswish_Woocommerce_Wishlist/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Pswish_Woocommerce_Wishlist
 * @subpackage Pswish_Woocommerce_Wishlist/includes
 * @author     Store Prose <hello@storeprose.com>
 */
class Pswish_Woocommerce_Wishlist_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ps-wishlist-for-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}

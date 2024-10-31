<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.storeprose.com
 * @since             1.0.0
 * @package           Pswish_Woocommerce_Wishlist
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Wishlist
 * Plugin URI:        https://www.storeprose.com
 * Description:       WooCommerce Wishlist is a lightweight plugin that can boost your sales. Allow your customers to add products to their Wishlist and buy them later.
 * Version:           1.1.1
 * Author:            Store Prose
 * Author URI:        https://www.storeprose.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ps-wishlist-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PSWISH_WOOCOMMERCE_WISHLIST_VERSION', '1.1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pswish-woocommerce-wishlist-activator.php
 */
function pswish_activate_woocommerce_wishlist() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pswish-woocommerce-wishlist-activator.php';
	Pswish_Woocommerce_Wishlist_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pswish-woocommerce-wishlist-deactivator.php
 */
function pswish_deactivate_woocommerce_wishlist() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pswish-woocommerce-wishlist-deactivator.php';
	Pswish_Woocommerce_Wishlist_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'pswish_activate_woocommerce_wishlist' );
register_deactivation_hook( __FILE__, 'pswish_deactivate_woocommerce_wishlist' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pswish-woocommerce-wishlist.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function pswish_run_woocommerce_wishlist() {
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
		$plugin = new Pswish_Woocommerce_Wishlist();
		$plugin->run();
	}
}
pswish_run_woocommerce_wishlist();

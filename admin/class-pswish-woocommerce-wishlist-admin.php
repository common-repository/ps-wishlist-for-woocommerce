<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.storeprose.com
 * @since      1.0.0
 *
 * @package    Pswish_Woocommerce_Wishlist
 * @subpackage Pswish_Woocommerce_Wishlist/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pswish_Woocommerce_Wishlist
 * @subpackage Pswish_Woocommerce_Wishlist/admin
 * @author     Store Prose <hello@storeprose.com>
 */
class Pswish_Woocommerce_Wishlist_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Method pswish_init
	 *
	 * @return void
	 */
	public function pswish_init() {
		$checked_value = 'on';
		add_filter( 'query_vars', array( $this, 'pswish_add_custom_query_vars' ), 0 );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'pswish_add_custom_menu_item_my_account' ) );
		add_action( 'woocommerce_account_pswishlist_endpoint', array( $this, 'pswish_pswishlist_content_my_account' ) );
		$options = get_option( 'pswish' );
		$icon    = ( isset( $options['icon'] ) && $options['icon'] === $checked_value ) ? true : false;
		if ( $icon ) {
			add_action( 'wp_head', array( $this, 'pswish_wish_style' ) );
		}
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pswishlistadmin.css', array(), $this->version, 'all' );
	}

	/**
	 * Method pswish_wish_style
	 *
	 * @return void
	 */
	public function pswish_wish_style() {
		?>
		<style>
			.woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--pswishlist a:before {
	font-family: pswishlist;
	content: "\e801";
}
		</style>
		<?php
	}

	/**
	 * Method pswish_add_custom_query_vars
	 *
	 * @param $vars $vars array.
	 *
	 * @return array
	 */
	public function pswish_add_custom_query_vars( $vars ) {
		$vars[] = 'pswishlist';
		return $vars;
	}

	/**
	 * Method pswish_add_custom_menu_item_my_account
	 *
	 * @param $items $items array.
	 *
	 * @return array
	 */
	public function pswish_add_custom_menu_item_my_account( $items ) {
		$options = get_option( 'pswish' );
		$tabname = ( isset( $options['prompt'] ) && strlen( $options['prompt'] ) > 0 ) ? $options['prompt'] : __( 'My Wishlist', 'ps-wishlist-for-woocommerce' );
		$items   = array_slice( $items, 0, 5, true )
		+ array( 'pswishlist' => $tabname )
		+ array_slice( $items, 5, null, true );
		return $items;
	}

	/**
	 * Method pswish_pswishlist_content_my_account
	 *
	 * @return void
	 */
	public function pswish_pswishlist_content_my_account() {
		if ( is_user_logged_in() ) {
			$options        = get_option( 'pswish' );
			$customer_id    = get_current_user_id();
			$user_meta_name = '_pswish_default';
			$user_options   = get_user_meta( $customer_id, $user_meta_name, true );
			$wishlist_count = ( $user_options ? count( $user_options ) : 0 );
			$site_url       = get_site_url();
			$page_title     = ( isset( $options['prompt'] ) && strlen( $options['prompt'] ) > 0 ) ? $options['prompt'] : __( 'My Wishlist', 'ps-wishlist-for-woocommerce' );
			$added_on       = ( isset( $options['added_on'] ) && strlen( $options['added_on'] ) > 0 ) ? $options['added_on'] : __( 'Added to Wishlist on:', 'ps-wishlist-for-woocommerce' );
			$price_added    = ( isset( $options['price_added'] ) && strlen( $options['price_added'] ) > 0 ) ? $options['price_added'] : __( 'Price when added:', 'ps-wishlist-for-woocommerce' );
			?>
			<h2><?php echo esc_html( $page_title ); ?>(<?php echo esc_html( $wishlist_count ); ?>)</h2>
			<?php
			if ( $wishlist_count > 0 ) {
				$product_ids = array_keys( $user_options );
				foreach ( $product_ids as $product_id ) {
					$variation_id = $user_options[ $product_id ]['variation'];
					$product      = ( $variation_id ) ? wc_get_product( $variation_id ) : wc_get_product( $product_id );

					if ( $product ) {
						$image           = wp_get_attachment_image( $product->get_image_id() );
						$thumbnail       = $product->get_image( array( 200, 200 ) );
						$product_name    = $product->get_title();
						$date_added      = $user_options[ $product_id ]['date'];
						$currency        = $user_options[ $product_id ]['currency'];
						$price           = $user_options[ $product_id ]['price'];
						$url             = ( $variation_id ) ? get_permalink( $variation_id ) : get_permalink( $product_id );
						$minimum_quanity = $product->get_min_purchase_quantity();
						?>
						<div class="pswish-grid-container">
							<div class="pswishimg"><a href='<?php echo wp_kses_post( $url ); ?>'><?php echo wp_kses_post( $image ); ?></a></div>
							<div class="pswishtitle"><a href='<?php echo wp_kses_post( $url ); ?>'><?php echo wp_kses_post( $product_name ); ?></a></div>  
							<div class="pswishdate"><?php echo wp_kses_post( html_entity_decode( $added_on ) ); ?> <?php echo wp_kses_post( $date_added ); ?></div>
							<div class="pswishprice"><?php echo wp_kses_post( html_entity_decode( $price_added ) ); ?> <?php echo wp_kses_post( $currency . $price ); ?> </div >
							<button class = 'pswishcartbtn' remove_only = 'N' variation_id = '<?php echo wp_kses_post( $variation_id ); ?>' title = 'Add to Cart' product_id = '<?php echo wp_kses_post( $product_id ); ?>' customer_id='<?php echo wp_kses_post( $customer_id ); ?>'><span class='pswishlist icon-basket'/></button>
							<button class="pswishremove" remove_only= 'Y' variation_id='<?php echo wp_kses_post( $variation_id ); ?>'  product_id='<?php echo wp_kses_post( $product_id ); ?>' customer_id='<?php echo wp_kses_post( $customer_id ); ?>' title='Remove from Wishlist'><span class='pswishlist icon-trash-empty'/></button>
						</div>
						<?php
					}
				}
			} else {

				$empty_text = ( isset( $options['empty_text'] ) && strlen( $options['empty_text'] ) > 0 ) ? $options['empty_text'] : __( 'There is nothing here. Explore the products on our shop and add products that you wish to buy later.', 'ps-wishlist-for-woocommerce' );
				echo wp_kses_post( html_entity_decode( $empty_text ) );
			}

			?>
			<?php
		}
	}
}

<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.storeprose.com
 * @since      1.0.0
 *
 * @package    Pswish_Woocommerce_Wishlist
 * @subpackage Pswish_Woocommerce_Wishlist/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Pswish_Woocommerce_Wishlist
 * @subpackage Pswish_Woocommerce_Wishlist/public
 * @author     Store Prose <hello@storeprose.com>
 */
class Pswish_Woocommerce_Wishlist_Public {

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
	 * @param      string $plugin_name       The name of the plugin.
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
		add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'pswish_add_icon' ) );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pswish-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_style( $this->plugin_name . '_public', plugin_dir_url( __FILE__ ) . 'css/pswishlist.css', array(), $this->version, 'all' );

		wp_localize_script(
			$this->plugin_name,
			'pswish_wish_ajax',
			array(
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'pswish_wish_ajax' ),
			)
		);
		add_action( 'wp_ajax_pswish_wish_update_meta', array( $this, 'pswish_wish_update_meta' ) );
		add_action( 'wp_ajax_nopriv_pswish_wish_update_meta', array( $this, 'pswish_wish_update_meta' ) );
		add_action( 'wp_ajax_pswish_wish_add_to_cart', array( $this, 'pswish_wish_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_pswish_wish_add_to_cart', array( $this, 'pswish_wish_add_to_cart' ) );
	}

	/**
	 * Method pswish_wish_add_to_cart
	 *
	 * @return void
	 */
	public function pswish_wish_add_to_cart() {
		header( 'Access-Control-Allow-Origin: *' );
		$set_value      = 'Y';
		$checked_value  = 'on';
		$respond        = false;
		$user_meta_name = '_pswish_default';
		$response       = array(
			'refresh' => 'Y',
		);
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'pswish_wish_ajax' ) ) {

			$product_id   = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : $set_value;
			$customer_id  = isset( $_POST['customer_id'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_id'] ) ) : $set_value;
			$variation_id = isset( $_POST['variation_id'] ) ? sanitize_text_field( wp_unslash( $_POST['variation_id'] ) ) : false;
			$remove_only  = isset( $_POST['remove_only'] ) ? sanitize_text_field( wp_unslash( $_POST['remove_only'] ) ) : $set_value;
			if ( $product_id !== $set_value && $customer_id !== $set_value ) {
				if ( $remove_only !== $set_value ) {
					$quantity = ( $variation_id ) ? wc_get_product( $variation_id )->get_min_purchase_quantity() : wc_get_product( $product_id )->get_min_purchase_quantity();

					$attributes = array();
					if ( $variation_id ) {
						$user_options = get_user_meta( $customer_id, $user_meta_name, true );
						$attributes   = $user_options[ $product_id ]['attributes'];
					}
					$variation_id = ( $variation_id ) ? $variation_id : 0;

					WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $attributes );
					wc_add_to_cart_message( $product_id );
					$options = get_option( 'pswish' );
					$remove  = ( isset( $options['remove_after_add'] ) && $options['remove_after_add'] === $checked_value ) ? true : false;

					if ( $remove ) {
						$this->pswish_remove_from_product_list( $product_id, $variation_id, $customer_id );
						$this->pswish_remove_from_user_list( $product_id, $variation_id, $customer_id );
					}
				} else {
					$this->pswish_remove_from_product_list( $product_id, $variation_id, $customer_id );
					$this->pswish_remove_from_user_list( $product_id, $variation_id, $customer_id );
				}

				$respond = true;
			}
			if ( $respond ) {
				wp_send_json( $response );
			}
		}
		die();
	}

	/**
	 * Method pswish_remove_from_product_list
	 *
	 * @param product_id  $product_id product id.
	 * @param customer_id $variation_id variation id.
	 * @param customer_id $customer_id customer id.
	 *
	 * @return void
	 */
	public function pswish_remove_from_product_list( $product_id, $variation_id, $customer_id ) {
		$product         = wc_get_product( $product_id );
		$product_options = $product->get_meta( '_pswish' );
		if ( $product_options ) {
			$options = explode( '/', $product_options );
			$key     = array_search( $customer_id, $options, true );
			unset( $options[ $key ] );
			$product_options = implode( '/', $options );
		}
		$product->update_meta_data( '_pswish', $product_options );
		$product->save_meta_data();
	}

	/**
	 * Method pswish_remove_from_user_list
	 *
	 * @param product_id  $product_id product id.
	 * @param customer_id $variation_id variation id.
	 * @param $customer_id $customer_id customer id.
	 *
	 * @return void
	 */
	public function pswish_remove_from_user_list( $product_id, $variation_id, $customer_id ) {
		$user_meta_name = '_pswish_default';
		$user_options   = get_user_meta( $customer_id, $user_meta_name, true );
		if ( $user_options && isset( $user_options[ $product_id ] ) ) {
			unset( $user_options[ $product_id ] );
			update_user_meta( $customer_id, $user_meta_name, $user_options );
		}
	}

	/**
	 * Method pswish_add_to_product_list
	 *
	 * @param product_id  $product_id product id.
	 * @param customer_id $variation_id variation id.
	 * @param customer_id $customer_id customer id.
	 *
	 * @return void
	 */
	public function pswish_add_to_product_list( $product_id, $variation_id, $customer_id ) {
		$product         = wc_get_product( $product_id );
		$product_options = $product->get_meta( '_pswish' );
		if ( $product_options ) {
			$product_options .= $customer_id . '/';
		} else {
			$product_options = $customer_id . '/';
		}
		$product->update_meta_data( '_pswish', $product_options );
		$product->save_meta_data();
	}

	/**
	 * Method pswish_add_to_user_list
	 *
	 * @param product_id     $product_id product id.
	 * @param customer_id    $variation_id variation id.
	 * @param customer_id    $customer_id customer id.
	 * @param var_attributes $var_attributes attribute names.
	 * @param var_options    $var_options attribute options.
	 *
	 * @return void
	 */
	public function pswish_add_to_user_list( $product_id, $variation_id, $customer_id, $var_attributes, $var_options ) {
		$user_meta_name = '_pswish_default';
		$user_options   = get_user_meta( $customer_id, $user_meta_name, true );

		$price = ( $variation_id ) ? wc_get_product( $variation_id )->get_price() : wc_get_product( $product_id )->get_price();

		$attributes = array();

		$var_count = count( $var_attributes );
		for ( $index = 0;$index < $var_count;$index++ ) {
			$var_index                = $var_attributes[ $index ];
			$var_option               = $var_options[ $index ];
			$attributes[ $var_index ] = $var_option;
		}

		$currency     = get_woocommerce_currency_symbol();
		$this_date    = gmdate( 'Y-m-d' );
		$options_data = array(
			'price'      => $price,
			'date'       => $this_date,
			'variation'  => $variation_id,
			'attributes' => $attributes,
			'currency'   => $currency,
		);

		if ( $user_options ) {
			$user_options[ $product_id ] = $options_data;
		} else {
			$user_options = array( $product_id => $options_data );
		}
		update_user_meta( $customer_id, $user_meta_name, $user_options );
	}

	/**
	 * Method pswish_hpacb_toggle
	 *
	 * @return void
	 */
	public function pswish_wish_update_meta() {
		header( 'Access-Control-Allow-Origin: *' );
		$on_choice = 'on';
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'pswish_wish_ajax' ) ) {

			$term_id        = isset( $_POST['term_id'] ) ? sanitize_text_field( wp_unslash( $_POST['term_id'] ) ) : 'X';
			$term_type      = isset( $_POST['term_type'] ) ? sanitize_text_field( wp_unslash( $_POST['term_type'] ) ) : 'X';
			$variation_id   = isset( $_POST['variation_id'] ) ? sanitize_text_field( wp_unslash( $_POST['variation_id'] ) ) : 0;
			$var_attributes = isset( $_POST['attributes'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['attributes'] ) ) : array();
			$var_options    = isset( $_POST['options'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['options'] ) ) : array();
			$respond        = false;
			$options        = get_option( 'pswish' );
			$p_text         = ( isset( $options['add_to_list'] ) && strlen( $options['add_to_list'] ) > 0 ) ? $options['add_to_list'] : __( 'Add to Wishlist', 'ps-wishlist-for-woocommerce' );

			if ( is_user_logged_in() ) {

				$product_ids = explode( 'pswish', $term_id );
				$product_id  = $product_ids[1];
				$customer_id = get_current_user_id();

				if ( $term_type === $on_choice ) {
					$this->pswish_remove_from_product_list( $product_id, $variation_id, $customer_id );
					$this->pswish_remove_from_user_list( $product_id, $variation_id, $customer_id );
				} else {
					$this->pswish_add_to_product_list( $product_id, $variation_id, $customer_id );
					$this->pswish_add_to_user_list( $product_id, $variation_id, $customer_id, $var_attributes, $var_options );

					if ( isset( $options['added_to_list'] ) && strlen( $options['added_to_list'] ) > 0 ) {
						$p_text = $options['added_to_list'];
					} else {
						$url    = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '/pswishlist';
						$p_text = 'Product added to your <a href="' . $url . '">Wishlist!</a>';
					}
				}
				$response = array(
					'id'        => esc_html( $term_id ),
					'text'      => html_entity_decode( $p_text ),
					'do_action' => $term_type,
				);
				$respond  = true;
			}

			if ( $respond ) {
				wp_send_json( $response );
			}
		}
		die();
	}

	/**
	 * Method pswish_add_icon
	 *
	 * @return void
	 */
	public function pswish_add_icon() {

		if ( is_user_logged_in() ) {
			$options                = get_option( 'pswish' );
			$is_product_on_wishlist = false;
			global $product;
			if ( ! is_object( $product ) ) {
				$product = wc_get_product( get_the_ID() );
			}
			$product_id     = $product->get_id();
			$class          = 'pswishlistbtn pswishicon ';
			$user_meta_name = '_pswish_default';
			$customer_id    = get_current_user_id();
			$user_options   = get_user_meta( $customer_id, $user_meta_name, true );

			if ( $user_options && isset( $user_options[ $product_id ] ) ) {
				$is_product_on_wishlist = true;
			}
			$url             = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '/pswishlist';
			$already_in_list = 'The product is already in your <a href="' . $url . '">Wishlist!</a>';

			$add_to_list = ( isset( $options['add_to_list'] ) && strlen( $options['add_to_list'] ) > 0 ) ? $options['add_to_list'] : __( 'Add to Wishlist', 'ps-wishlist-for-woocommerce' );

			if ( isset( $options['already_in_list'] ) && strlen( $options['already_in_list'] ) > 0 ) {
				$already_in_list = $options['already_in_list'];
			}

			$already_in_list = html_entity_decode( $already_in_list );
			$add_to_list     = html_entity_decode( $add_to_list );

			$wish_text    = ( $is_product_on_wishlist ? $already_in_list : $add_to_list );
			$type         = ( $is_product_on_wishlist ? 'on' : 'off' );
			$tag_id       = 'pswish' . $product_id;
			$pswsih_title = ( $is_product_on_wishlist ) ? $already_in_list : $add_to_list;
			$class       .= ( $is_product_on_wishlist ) ? 'icon-heart-filled' : 'icon-heart';

			echo '<div style="display:flex;align-items:baseline;"><div class="' . esc_html( $class ) . '" style="font-size:2em;color:#dd3333;cursor: pointer;" title ="' . esc_html( $pswsih_title ) . '" id="' . esc_html( $tag_id ) . '" pswish_type="' . esc_html( $type ) . '"></div><div class="pswishlisttext"><p id ="pswishlisttext">' . wp_kses_post( $wish_text ) . '</p></div></div><br/>';

		}
	}
}

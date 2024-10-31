/**
 * This is the main JS file to
 * have the plugin functionality.
 *
 * @link       https://www.storeprose.com
 * @since      1.0.0
 *
 * @package    Pswish_Woocommerce_Wishlist
 * @subpackage Pswish_Woocommerce_Wishlist/public/js
 */

(function ($) {
	'use strict';

	$( document ).on(
		'click',
		'.pswishlistbtn',
		function (e) {
			e.preventDefault();
			var term_type    = $( this ).attr( 'pswish_type' );
			var term_id      = $( this ).attr( 'id' );
			var variation_id = $( 'input.variation_id' ).val();
			var attributes   = [];
			var var_options  = [];
			if (variation_id != 0) {

				$( 'table.variations tbody tr' ).each(
					function () {
						var option    = $( this ).find( 'td.value select' ).val();
						var attribute = $( this ).find( 'td.value select' ).attr( 'data-attribute_name' );
						attributes.push( attribute );
						var_options.push( option );
					}
				);
			}

			($).ajax(
				{
					type: "POST",
					url: pswish_wish_ajax.url,
					async: false,
					data: {
						action: "pswish_wish_update_meta",
						nonce: pswish_wish_ajax.nonce,
						term_type: term_type,
						term_id: term_id,
						variation_id: variation_id,
						attributes: attributes,
						options: var_options,
					},
					success: function (data) {
						var object_id = '#' + data.id;
						var text      = data.text;
						if (data.do_action == "on") {
							$( object_id ).attr( "title", "Add to Wishlist" );
							$( '#pswishlisttext' ).html( text )
							$( object_id ).attr( "class", "pswishlistbtn pswishlist icon-heart" );
							$( object_id ).attr( "pswish_type", "off" );
						} else if (data.do_action == "off") {
							$( object_id ).attr( "title", "Product Added to your wishlist." );
							$( '#pswishlisttext' ).html( text );
							$( object_id ).attr( "class", "pswishlistbtn pswishlist icon-heart-filled" );
							$( object_id ).attr( "pswish_type", "on" );
						}
					}
				}
			);
		}
	);
	$( document ).on(
		'click',
		'.pswishcartbtn, .pswishremove',
		function (e) {
			e.preventDefault();
			var product_id   = $( this ).attr( 'product_id' );
			var customer_id  = $( this ).attr( 'customer_id' );
			var remove_only  = $( this ).attr( 'remove_only' );
			var variation_id = $( this ).attr( 'variation_id' );
			($).ajax(
				{
					type: "POST",
					url: pswish_wish_ajax.url,
					async: false,
					data: {
						action: "pswish_wish_add_to_cart",
						nonce: pswish_wish_ajax.nonce,
						product_id: product_id,
						customer_id: customer_id,
						remove_only: remove_only,
						variation_id: variation_id,
					},
					success: function (data) {
						if (data.refresh == 'Y') {
							window.location.reload();
						}
					}
				}
			);
		}
	);
})( jQuery );
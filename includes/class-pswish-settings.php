<?php
/**
 * Plugin settings Class
 *
 * @link       https://www.storeprose.com
 * @since      1.0.0
 *
 * @package    Pswish_Woocommerce_Wishlist
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Pswish_Settings
 */
class Pswish_Settings {
	/**
	 * Dir
	 *
	 * @var mixed
	 */
	private $dir;
	/**
	 * File
	 *
	 * @var mixed
	 */
	private $file;
	/**
	 * Plugin_name
	 *
	 * @var mixed
	 */
	private $plugin_name;
	/**
	 * Plugin_slug
	 *
	 * @var mixed
	 */
	private $plugin_slug;
	/**
	 * Textdomain
	 *
	 * @var mixed
	 */
	private $textdomain;
	/**
	 * Options
	 *
	 * @var mixed
	 */
	private $options;
	/**
	 * Settings
	 *
	 * @var mixed
	 */
	private $settings;

	/**
	 * Method __construct
	 *
	 * @param $plugin_name $plugin_name passed as parameter.
	 * @param $plugin_slug $plugin_slug passed as parameter.
	 * @param file        $file passed as parameter.
	 *
	 * @return void
	 */
	public function __construct( $plugin_name, $plugin_slug, $file ) {
		$this->file        = $file;
		$this->plugin_slug = $plugin_slug;
		$this->plugin_name = $plugin_name;
		$this->textdomain  = str_replace( '_', '-', $plugin_slug );

		// Initialise settings.
		add_action( 'admin_init', array( $this, 'pswish_init' ) );

		// Add settings page to menu.
		add_action( 'admin_menu', array( $this, 'pswish_add_menu_item' ) );

		// Add settings link to plugins page.
		$plugin_link_name = 'plugin_action_links_ps-wishlist-for-woocommerce/pswish-woocommerce-wishlist.php';
		add_filter( $plugin_link_name, array( $this, 'pswish_add_settings_link' ) );
		add_filter( 'plugin_row_meta', array( $this, 'pswish_wish_add_plugin_description' ), 10, 2 );
	}

	/**
	 * Initialise settings
	 *
	 * @return void
	 */
	public function pswish_init() {
		$this->settings = $this->settings_fields();
		$this->options  = $this->get_options();
		$this->register_settings();
	}

	/**
	 * Add settings page to admin menu
	 *
	 * @return void
	 */
	public function pswish_add_menu_item() {
		$page = add_submenu_page( 'woocommerce', $this->plugin_name, $this->plugin_name, 'manage_options', $this->plugin_slug, array( $this, 'settings_page' ) );
	}

	/**
	 * Method pswish_hpacb_add_plugin_description
	 *
	 * @param $links $links text.
	 * @param file  $file text.
	 *
	 * @return array
	 */
	public function pswish_wish_add_plugin_description( $links, $file ) {

		if ( strpos( $file, 'pswish-woocommerce-wishlist.php' ) !== false ) {
			$review_link   = '<a href="https://wordpress.org/support/plugin/ps-wishlist-for-woocommerce/reviews/#new-post" target="_blank"><span class="dashicons dashicons-welcome-write-blog"></span>Write a Review</a>';
			$donation_link = '<a href="' . esc_url( 'https://ko-fi.com/storeprose' ) . '" style="color:#e76f51;font-weight:bold" target="_blank"><span class="dashicons dashicons-heart"></span>' . __( 'Donate', 'ps-wishlist-for-woocommerce' ) . '</a>';
			array_push( $links, $review_link );
			array_push( $links, $donation_link );
		}

		return $links;
	}

	/**
	 * Add settings link to plugin list table
	 *
	 * @param  array $links Existing links.
	 * @return array        Modified links.
	 */
	public function pswish_add_settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=' . $this->plugin_slug . '">' . __( 'Settings', 'ps-wishlist-for-woocommerce' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Build settings fields
	 *
	 * @return array Fields to be displayed on settings page.
	 */
	private function settings_fields() {

		$settings['my_account'] = array(
			'title'       => __( 'My Account', 'ps-wishlist-for-woocommerce' ),
			'description' => __( 'Personalize My Account page.', 'ps-wishlist-for-woocommerce' ),
			'fields'      => array(
				array(
					'id'          => 'prompt',
					'label'       => __( 'Rename Wishlist tab', 'ps-wishlist-for-woocommerce' ),
					'description' => __( 'Use if you want to override the default Tab Name.', 'ps-wishlist-for-woocommerce' ),
					'type'        => 'text',
					'default'     => 'My Wishlist',
				),
				array(
					'id'          => 'icon',
					'label'       => __( 'Use Icon with tab name', 'ps-wishlist-for-woocommerce' ),
					'description' => __( 'Uncheck if you do not want to show the icon.', 'ps-wishlist-for-woocommerce' ),
					'type'        => 'checkbox',
					'default'     => 'on',
				),
				array(
					'id'          => 'remove_after_add',
					'label'       => __( 'Remove product from Wishlist after adding to cart', 'ps-wishlist-for-woocommerce' ),
					'description' => __( 'Remove product from Wishlist after adding to cart', 'ps-wishlist-for-woocommerce' ),
					'type'        => 'checkbox',
					'default'     => 'off',
				),
			),
		);
		$settings['translate']  = array(
			'title'       => __( 'Change Texts', 'ps-wishlist-for-woocommerce' ),
			'description' => __( 'Change the texts used by the plugin. You can type in any language. You can also use HTML.', 'ps-wishlist-for-woocommerce' ),
			'fields'      => array(
				array(
					'id'          => 'add_to_list',
					'label'       => __( 'Add to Wishlist', 'ps-wishlist-for-woocommerce' ),
					'description' => __( 'Default: Add to Wishlist', 'ps-wishlist-for-woocommerce' ),
					'type'        => 'text',
					'default'     => 'Add to Wishlist',
				),
				array(
					'id'          => 'added_to_list',
					'label'       => __( 'Product added to your wishlist', 'ps-wishlist-for-woocommerce' ),
					'description' => __( 'Default: Product added to your wishlist', 'ps-wishlist-for-woocommerce' ),
					'type'        => 'text',
					'default'     => 'Product added to your wishlist!',
				),
				array(
					'id'          => 'already_in_list',
					'label'       => __( 'The product is already in your wishlist', 'ps-wishlist-for-woocommerce' ),
					'description' => __( 'Default: The product is already in your Wishlist', 'ps-wishlist-for-woocommerce' ),
					'type'        => 'text',
					'default'     => 'The product is already in your Wishlist!',
				),
				array(
					'id'          => 'added_on',
					'label'       => __( 'Added to Wishlist on:', 'ps-wishlist-for-woocommerce' ),
					'description' => __( 'Default: Added to wishlist on', 'ps-wishlist-for-woocommerce' ),
					'type'        => 'text',
					'default'     => 'Added to wishlist on:',
				),
				array(
					'id'          => 'price_added',
					'label'       => __( 'Price when added:', 'ps-wishlist-for-woocommerce' ),
					'description' => __( 'Default: Price when added', 'ps-wishlist-for-woocommerce' ),
					'type'        => 'text',
					'default'     => 'Price when added:',
				),
				array(
					'id'          => 'empty_text',
					'label'       => __( 'Text to display on empty wishlists', 'ps-wishlist-for-woocommerce' ),
					'description' => __( 'Default: There is nothing here. Explore the products on our shop and add products that you wish to buy later.', 'ps-wishlist-for-woocommerce' ),
					'type'        => 'textarea',
					'default'     => 'There is nothing here. Explore the products on our shop and add products that you wish to buy later.',
				),

			),
		);
		return $settings;
	}


	/**
	 * Options getter
	 *
	 * @return array Options, either saved or default ones.
	 */
	public function get_options() {
		$options = get_option( $this->plugin_slug );

		if ( ! $options && is_array( $this->settings ) ) {
			$options = array();
			foreach ( $this->settings as $section => $data ) {
				foreach ( $data['fields'] as $field ) {
					$options[ $field['id'] ] = $field['default'];
				}
			}

			add_option( $this->plugin_slug, $options );
		}

		return $options;
	}

	/**
	 * Register plugin settings
	 *
	 * @return void
	 */
	public function register_settings() {
		if ( is_array( $this->settings ) ) {

			register_setting( $this->plugin_slug, $this->plugin_slug, array( $this, 'validate_fields' ) );

			foreach ( $this->settings as $section => $data ) {

				// Add section to page.
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->plugin_slug );

				foreach ( $data['fields'] as $field ) {

					// Add field to page.
					add_settings_field( $field['id'], $field['label'], array( $this, 'display_field' ), $this->plugin_slug, $section, array( 'field' => $field ) );
				}
			}
		}
	}

	/**
	 * Method settings_section
	 *
	 * @param $section $section render sections.
	 *
	 * @return void
	 */
	public function settings_section( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo wp_kses_post( $html );
	}

	/**
	 * Method safe_tags
	 *
	 * @return array.
	 */
	public function safe_tags() {

		$allowed_tags = array(
			'input'    => array(
				'id'          => array(),
				'type'        => array(),
				'name'        => array(),
				'placeholder' => array(),
				'value'       => array(),
				'checked'     => array(),
				'size'        => array(),
			),
			'textarea' => array(
				'textarea'    => array(),
				'rows'        => array(),
				'cols'        => array(),
				'placeholder' => array(),
				'id'          => array(),
				'name'        => array(),
			),
			'label'    => array(
				'class' => array(),
			),
			'span'     => array(
				'class' => array(),
			),

		);

		return $allowed_tags;
	}

	/**
	 * Generate HTML for displaying fields
	 *
	 * @param  array $args Field data.
	 * @return void
	 */
	public function display_field( $args ) {

		$field = $args['field'];

		$html = '';

		$option_name = $this->plugin_slug . '[' . $field['id'] . ']';

		$data = ( isset( $this->options[ $field['id'] ] ) ) ? $this->options[ $field['id'] ] : '';

		switch ( $field['type'] ) {

			case 'text':
			case 'password':
			case 'number':
				$html .= '<input size="50" id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['default'] ) . '" value="' . $data . '"/>' . "\n";
				break;

			case 'text_secret':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value=""/>' . "\n";
				break;

			case 'textarea':
				$html .= '<textarea id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['default'] ) . '">' . $data . '</textarea><br/>' . "\n";
				break;

			case 'checkbox':
				$checked = '';
				if ( $data && 'on' === $data ) {
					$checked = 'checked="checked"';
				}
				$html .= '<label class="switch"><input id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $option_name ) . '" ' . $checked . '/><span class="slider round"></span></label>' . "\n";
				break;

			case 'checkbox_multi':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( is_array( $data ) && in_array( $k, $data, true ) ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
				break;

			case 'radio':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( $k === $data ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
				break;

			case 'select':
				$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( $k === $data ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'select_multi':
				$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, $data, true ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '" />' . $v . '</label> ';
				}
				$html .= '</select> ';
				break;

		}

		$allowed_html = $this->safe_tags();

		echo wp_kses( $html, $allowed_html );
	}


	/**
	 * Method sanitize_checkbox
	 *
	 * @param $input $input input.
	 *
	 * @return number
	 */
	public function sanitize_checkbox( $input ) {
		return ( 'on' === $input ) ? 'on' : '';
	}
	/**
	 * Validate individual settings field
	 *
	 * @param  array $data Inputted value.
	 * @return array       Validated value.
	 */
	public function validate_fields( $data ) {

		if ( isset( $data['icon'] ) ) {
			$data['icon'] = $this->sanitize_checkbox( $data['icon'] );
		}

		if ( isset( $data['add_to_list'] ) ) {
			$data['add_to_list'] = htmlentities( wp_kses_post( $data['add_to_list'] ) );
		}
		if ( isset( $data['added_to_list'] ) ) {
			$data['added_to_list'] = htmlentities( wp_kses_post( $data['added_to_list'] ) );
		}
		if ( isset( $data['already_in_list'] ) ) {
			$data['already_in_list'] = htmlentities( wp_kses_post( $data['already_in_list'] ) );
		}
		if ( isset( $data['price_added'] ) ) {
			$data['price_added'] = htmlentities( wp_kses_post( $data['price_added'] ) );
		}
		if ( isset( $data['added_on'] ) ) {
			$data['added_on'] = htmlentities( wp_kses_post( $data['added_on'] ) );
		}

		if ( isset( $data['prompt'] ) ) {
			$data['prompt'] = wp_kses_post( $data['prompt'] );
		}

		if ( isset( $data['empty_text'] ) ) {
			$data['empty_text'] = htmlentities( wp_kses_post( $data['empty_text'] ) );
		}

		return $data;
	}

	/**
	 * Load settings page content
	 *
	 * @return void
	 */
	public function settings_page() {
		// Build page HTML output
		// If you don't need tabbed navigation just strip out everything between the <!-- Tab navigation --> tags.
		?>
		<div class="wrap" id="<?php echo wp_kses_post( $this->plugin_slug ); ?>">
			<h2><?php esc_html_e( 'Wishlist for WooCommerce', 'ps-wishlist-for-woocommerce' ); ?></h2>
			<p><?php esc_html_e( 'Configure the plugin functionality using these options.', 'ps-wishlist-for-woocommerce' ); ?></p>

		<!-- Tab navigation starts -->
		<h2 class="nav-tab-wrapper settings-tabs hide-if-no-js">
			<?php
			foreach ( $this->settings as $section => $data ) {
				echo wp_kses_post( '<a href="#' . $section . '" class="nav-tab">' . $data['title'] . '</a>' );
			}
			?>
		</h2>
		<?php $this->do_script_for_tabbed_nav(); ?>
		<!-- Tab navigation ends -->

		<form action="options.php" method="POST">
			<?php settings_fields( $this->plugin_slug ); ?>
			<div class="settings-container">
			<?php do_settings_sections( $this->plugin_slug ); ?>
			</div>
			<?php submit_button(); ?>
		</form>
	</div>
		<?php
	}

	/**
	 * Print jQuery script for tabbed navigation
	 *
	 * @return void
	 */
	private function do_script_for_tabbed_nav() {
		// Very simple jQuery logic for the tabbed navigation.
		// Delete this function if you don't need it.
		// If you have other JS assets you may merge this there.
		?>
		<script>
		jQuery(document).ready(function($) {
			var headings = jQuery('.settings-container > h2, .settings-container > h3');
			var paragraphs  = jQuery('.settings-container > p');
			var tables = jQuery('.settings-container > table');
			var triggers = jQuery('.settings-tabs a');

			triggers.each(function(i){
				triggers.eq(i).on('click', function(e){
					e.preventDefault();
					triggers.removeClass('nav-tab-active');
					headings.hide();
					paragraphs.hide();
					tables.hide();

					triggers.eq(i).addClass('nav-tab-active');
					headings.eq(i).show();
					paragraphs.eq(i).show();
					tables.eq(i).show();
				});
			})

			triggers.eq(0).click();
		});
		</script>
		<?php
	}
}

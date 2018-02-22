<?php
/**
 * WooCommerce URL Coupons
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce URL Coupons to newer
 * versions in the future. If you wish to customize WooCommerce URL Coupons for your
 * needs please refer to http://docs.woocommerce.com/document/url-coupons/ for more information.
 *
 * @package     WC-URL-Coupons/Admin
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;


/**
 * Admin class
 *
 * @since 2.0.0
 */
class WC_URL_Coupons_Admin {


	/**
	 * Setup admin class
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		// add per-coupon options
		add_action( 'woocommerce_coupon_options', array( $this, 'add_coupon_options' ) );
		add_action( 'woocommerce_coupon_options', array( $this, 'render_coupon_option_js' ), 11 );

		// save per-coupon options
		add_action( 'woocommerce_process_shop_coupon_meta', array( $this, 'save_coupon_options' ) );

		// purge unique URL from active list when parent coupon is trashed or deleted
		add_action( 'wp_trash_post', array( $this, 'purge_coupon_url' ) );

		// add settings to hide coupon code field
		add_filter( 'woocommerce_payment_gateways_settings', array( $this, 'admin_settings' ) );

		// add a 'URL slug' column to the coupon list table
		add_filter( 'manage_edit-shop_coupon_columns',        array( $this, 'add_url_slug_column_header' ), 20 );
		add_action( 'manage_shop_coupon_posts_custom_column', array( $this, 'add_url_slug_column' ) );
	}


	/**
	 * Add coupon options to the Coupon edit page.
	 *
	 * @internal
	 *
	 * @since 1.0
	 */
	public function add_coupon_options() {
		global $post;

		$coupon = SV_WC_Coupon_Compatibility::get_coupon( $post->ID );

		?>
		<div class="options_group">
			<?php

			/**
			 * Unique URL
			 *
			 * @since 2.2.1
			 * @param string $unique_url The unique URL for the coupon (defaults to empty string).
			 * @param int $coupon_id The shop coupon ID.
			 */
			$unique_url = apply_filters( 'wc_url_coupons_unique_url', SV_WC_Coupon_Compatibility::get_meta( $coupon, '_wc_url_coupons_unique_url', true ), $post->ID );

			// Unique URL field.
			woocommerce_wp_text_input( array(
				'id'          => '_wc_url_coupons_unique_url',
				'label'       => __( 'Unique URL', 'ultimatewoo-pro' ),
				'description' => __( 'The URL that a customer can visit to have this coupon / product added to their cart.', 'ultimatewoo-pro' ),
				'desc_tip'    => true,
				'value'       => $unique_url,
			) );

			// Dropdown for product(s) to add to cart. ?>
			<p class="form-field _wc_url_coupons_product_ids_field">
				<label for="_wc_url_coupons_product_ids"><?php esc_html_e( 'Products to Add to Cart', 'ultimatewoo-pro' ); ?></label>
				<?php

				/**
				 * Products to add to cart
				 *
				 * @since 2.2.1
				 * @param false|array $product_ids The product ids to add to cart
				 * @param int $coupon_id The shop coupon id
				 */
				$url_coupon_product_ids = apply_filters( 'wc_url_coupons_product_ids', SV_WC_Coupon_Compatibility::get_meta( $coupon, '_wc_url_coupons_product_ids', true ), $post->ID );
				$url_coupon_product_ids = ! empty( $url_coupon_product_ids ) && is_array( $url_coupon_product_ids ) ? array_filter( array_map( 'absint', $url_coupon_product_ids ) ) : array();

				?>

				<?php if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) : ?>

					<select
						name="_wc_url_coupons_product_ids[]"
						class="wc-product-search"
						style="width: 50%;"
						multiple="multiple"
						data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'ultimatewoo-pro' ); ?>"
						data-action="woocommerce_json_search_products_and_variations">
						<?php foreach( $url_coupon_product_ids as $product_id ) : ?>
							<?php if ( $product = wc_get_product( $product_id ) ) : ?>
								<option value="<?php echo esc_attr( $product_id ); ?>" selected="selected"><?php echo esc_html( $product->get_formatted_name() ); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>

				<?php else : ?>

					<input
						type="hidden"
						name="_wc_url_coupons_product_ids"
						class="wc-product-search"
						style="width: 50%;"
						data-multiple="true"
						data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'ultimatewoo-pro' ); ?>"
						data-action="woocommerce_json_search_products_and_variations"
						data-selected="<?php $json_ids = array();
						foreach ( $url_coupon_product_ids as $product_id ) {
							$product = wc_get_product( $product_id );
							$json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() );
						}
						echo esc_attr( json_encode( $json_ids ) ); ?>"
						value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>"
					/>

				<?php endif; ?>

				<?php echo wc_help_tip( __( 'Add these products to the customers cart when they visit the URL.', 'ultimatewoo-pro' ) ); ?>
			</p>
			<?php

			/**
			 * Redirect target ID
			 *
			 * @since 2.2.1
			 * @param false|int $redirect_content_id The content object ID (or false if none set).
			 * @param int $coupon_id The shop coupon ID.
			 */
			$selected_page_id    = apply_filters( 'wc_url_coupons_redirect_page_id', SV_WC_Coupon_Compatibility::get_meta( $coupon, '_wc_url_coupons_redirect_page', true ), $post->ID );

			/**
			 * Redirect target type
			 *
			 * @since 2.2.1
			 * @param false|string $redirect_content_type The content type (or false if none set).
			 * @param int $coupon_id The shop coupon ID.
			 */
			$selected_page_type  = apply_filters( 'wc_url_coupons_redirect_page_type', SV_WC_Coupon_Compatibility::get_meta( $coupon, '_wc_url_coupons_redirect_page_type', true ), $post->ID );

			// Redirect target selection formatted for enhanced input.
			$selected_page_title = $this->get_selected_redirect_page_title( $selected_page_id, $selected_page_type );

			// Enhanced select value.
			$selected_value      = ! empty( $selected_page_title ) ? array( $selected_page_type . '|' . $selected_page_id => esc_html( $selected_page_title ) ) : array();

			// Redirect to page dropdown field. ?>
			<p class="form-field _wc_url_coupons_redirect_page_field">
				<label for="_wc_url_coupons_redirect_page"><?php esc_html_e( 'Page Redirect', 'ultimatewoo-pro' ); ?></label>

				<?php if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) : ?>

					<select
						name="_wc_url_coupons_redirect_page"
						id="_wc_url_coupons_redirect_page"
						class="sv-wc-enhanced-search"
						style="min-width: 300px;"
						data-action="wc_url_coupons_json_search_page_redirects"
						data-nonce="<?php echo wp_create_nonce( 'search-page-redirects' ); ?>"
						data-placeholder="<?php esc_attr_e( 'Select a page to redirect to&hellip;', 'ultimatewoo-pro' ); ?>"
						data-allow_clear="true">
						<?php if ( ! empty( $selected_value ) ) : ?>
							<option value="<?php echo esc_attr( key( $selected_value ) ); ?>" selected><?php echo esc_html( $selected_page_title ); ?></option>
						<?php endif; ?>
					</select>

				<?php else : ?>

					<input
						type="hidden"
						name="_wc_url_coupons_redirect_page"
						id="_wc_url_coupons_redirect_page"
						class="sv-wc-enhanced-search"
						style="min-width: 300px;"
						data-multiple="false"
						data-action="wc_url_coupons_json_search_page_redirects"
						data-nonce="<?php echo wp_create_nonce( 'search-page-redirects' ); ?>"
						data-placeholder="<?php esc_attr_e( 'Select a page to redirect to&hellip;', 'ultimatewoo-pro' ); ?>"
						data-allow_clear="true"
						data-selected="<?php echo esc_attr( current( $selected_value ) );  ?>"
						value="<?php echo esc_attr( key( $selected_value ) ); ?>"
					/>

				<?php endif; ?>

				<?php echo wc_help_tip( __( 'Select the page the customer will be redirected to after visiting the URL. Leave blank to disable redirect.', 'ultimatewoo-pro' ) ); ?>

				<input
					type="hidden"
					name="_wc_url_coupons_redirect_page_type"
					value=""
					id="_wc_url_coupons_redirect_page_type"
				/>
			</p>

			<?php SV_WC_Helper::render_select2_ajax(); ?>

			<?php

			/**
			 * Defer coupon application
			 *
			 * @since 2.2.1
			 * @param false|string $defer_apply Checkbox option: 'yes', 'no' or false if not set
			 * @param int $coupon_id The shop coupon id
			 */
			$defer_apply = apply_filters( 'wc_url_coupons_defer_apply', SV_WC_Coupon_Compatibility::get_meta( $coupon, '_wc_url_coupons_defer_apply', true ), $post->ID );

			// defer apply option
			woocommerce_wp_checkbox( array(
				'id'          => '_wc_url_coupons_defer_apply',
				'label'       => __( 'Defer Apply', 'ultimatewoo-pro' ),
				'description' => __( "Check this box to defer applying the coupon until the customer's cart meets the coupon's requirements.", 'ultimatewoo-pro' ),
				'value'       => $defer_apply,
			) );

			?>
		</div>
		<?php
	}


	/**
	 * Get selected redirect page title
	 *
	 * @since 2.1.5
	 * @param int $page_id Object id
	 * @param string $page_type Object type
	 * @return string Term name or post type title
	 */
	private function get_selected_redirect_page_title( $page_id, $page_type ) {

		if ( -1 === (int) $page_id ) {

			$page_title = __( 'Homepage', 'ultimatewoo-pro' );

		} else {

			switch ( $page_type ) {

				case 'page':
				case 'pages':
				case 'post':
				case 'posts':
					$page_title = get_the_title( $page_id );
				break;

				case 'product':
				case 'products':

					$product = wc_get_product( $page_id );
					$page_title = $product ? $product->get_title() : '';

				break;

				case 'category':
				case 'tag':
				case 'post_tag':
				case 'product_cat':
				case 'product_tag':

					$taxonomy   = 'post_tag' === $page_type ? 'tag' : $page_type;
					$term       = get_term_by( 'id', $page_id, $taxonomy );

					$page_title = isset( $term->name ) ? $term->name : '';

				break;

				default:
					$page_title = '';
				break;
			}
		}

		return $page_title;
	}


	/**
	 * Render JS to add live preview.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 */
	public function render_coupon_option_js() {

		$home_url = home_url( '/' );

		wc_enqueue_js( "
			$( 'p._wc_url_coupons_unique_url_field' ).append( \"<span id='_wc_url_coupons_url_preview' class='description'>{$home_url}</span>\" );

			$( 'input[id=_wc_url_coupons_unique_url]' ).on( 'keyup change input', function() {
				$( 'span#_wc_url_coupons_url_preview' ).text( '{$home_url}' + $( this ).val() );
			} ).change();

			$( '#_wc_url_coupons_redirect_page' ).change( function() {
				var page      = $( this ).val(),
					page_type = '';
				if ( page ) {
					page_type = page.substr( 0, page.indexOf( '|' ) );
				}
				$( '#_wc_url_coupons_redirect_page_type' ).val( page_type );
			} ).change();
		" );
	}


	/**
	 * Get the redirect page data, used for the redirect page select.
	 *
	 * @since 2.0.0
	 * @param string $search Optional search keyword.
	 * @return array Associative array by content type and ID and content title as values.
	 */
	public function get_redirect_pages( $search = '' ) {

		$pages = array(
			'pages'       => array(),
			'posts'       => array(),
			'products'    => array(),
			'category'    => array(),
			'post_tag'    => array(),
			'product_cat' => array(),
			'product_tag' => array(),
		);

		// add homepage
		$pages['pages'][-1] = array( 'type' => 'page', 'title' => __( 'Homepage', 'ultimatewoo-pro' ) );

		// add pages
		foreach ( get_pages( array( 'sort_column' => 'menu_order' ) ) as $page ) {

			// indent child page titles
			$title = ( 0 === $page->post_parent ) ? $page->post_title : '&nbsp;&nbsp;&nbsp;' . $page->post_title;

			$pages['pages'][ $page->ID ] = array( 'type' => 'page', 'title' => $title );
		}

		// add posts
		$args = array(
			'fields'      => 'ids',
			'post_status' => 'publish',
			'orderby'     => 'title',
			'order'       => 'ASC',
			'nopaging'    => true,
		);

		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		foreach ( get_posts( $args ) as $post_id ) {

			$pages['posts'][ $post_id ] = array(
				'type'  => 'post',
				'title' => get_the_title( $post_id ),
			);
		}

		// add products
		$args = array(
			'fields'      => 'ids',
			'post_type'   => array( 'product', 'product_variation' ),
			'post_status' => 'publish',
			'orderby'     => 'title',
			'order'       => 'ASC',
			'nopaging'    => true,
		);

		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		$products = get_posts( $args );

		foreach ( $products as $product_id ) {

			if ( $product = wc_get_product( $product_id ) ) {
				$pages['products'][ $product_id ] = array( 'type' => 'product', 'title' => $product->get_formatted_name() );
			}
		}

		// Add taxonomies.
		foreach ( $pages as $page_group => $_ ) {

			// Bail for invalid or non-taxonomies (pages, products).
			if ( ! taxonomy_exists( $page_group ) || in_array( $page_group, array( 'pages', 'products' ), true ) ) {
				continue;
			}

			$terms = get_terms( $page_group, array(
				'hide_empty' => false,
				'number' => 250
			) );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {

				foreach ( $terms as $term ) {
					$pages[ $page_group ][ $term->term_id ] = array(
						'type'  => $page_group,
						'title' => $term->name
					);
				}
			}
		}

		$groups = array(
			'pages'       => __( 'Pages', 'ultimatewoo-pro' ),
			'posts'       => __( 'Posts', 'ultimatewoo-pro' ),
			'products'    => __( 'Products', 'ultimatewoo-pro' ),
			'category'    => __( 'Categories', 'ultimatewoo-pro' ),
			'post_tag'    => __( 'Tags', 'ultimatewoo-pro' ),
			'product_cat' => __( 'Product Categories', 'ultimatewoo-pro' ),
			'product_tag' => __( 'Product Tags', 'ultimatewoo-pro' ),
		);

		// Set translated group titles, this is done here,
		// in order to allow  simplify the taxonomy handling code.
		foreach ( $groups as $group => $title ) {
			if ( isset( $pages[ $group ] ) ) {
				$pages[ $title ] = $pages[ $group ];
				unset( $pages[ $group ] );
			}
		}

		/**
		 * Get redirect pages.
		 *
		 * @since 2.0.0
		 * @param array $pages Associative array.
		 * @param \WC_URL_Coupons_Admin $url_coupons Instance of this class.
		 */
		return apply_filters( 'wc_url_coupons_redirect_pages', $pages, $this );
	}


	/**
	 * Save coupon options on Coupon edit page.
	 *
	 * @internal
	 *
	 * @since 1.0
	 * @param int $post_id Coupon ID.
	 */
	public function save_coupon_options( $post_id ) {

		$coupon             = SV_WC_Coupon_Compatibility::get_coupon( $post_id );
		$unique_url         = ! empty( $_POST['_wc_url_coupons_unique_url'] )         ?  $_POST['_wc_url_coupons_unique_url']         : '';
		$redirect_page      = ! empty( $_POST['_wc_url_coupons_redirect_page'] )      ?  $_POST['_wc_url_coupons_redirect_page']      : '';
		$redirect_page_type = ! empty( $_POST['_wc_url_coupons_redirect_page_type'] ) ?  $_POST['_wc_url_coupons_redirect_page_type'] : 'page';
		$page               = explode( '|', $redirect_page );
		$redirect_page_id   = isset( $page[1] ) ? $page[1] : '';

		// Unique URL.
		if ( empty( $unique_url ) ) {
			SV_WC_Coupon_Compatibility::delete_meta_data( $coupon, '_wc_url_coupons_unique_url' );
		} else {
			SV_WC_Coupon_Compatibility::update_meta_data( $coupon, '_wc_url_coupons_unique_url', sanitize_text_field( $unique_url ) );
		}

		// Redirect.
		if ( empty( $redirect_page ) ) {
			SV_WC_Coupon_Compatibility::update_meta_data( $coupon, '_wc_url_coupons_redirect_page', 0 ); // 0 is checked in maybe_apply_coupons() to redirect to shop page since redirect is empty.
			SV_WC_Coupon_Compatibility::delete_meta_data( $coupon, '_wc_url_coupons_redirect_page_type' );
		} elseif ( $redirect_page_id && $redirect_page_type ) {
			SV_WC_Order_Compatibility::update_meta_data( $coupon, '_wc_url_coupons_redirect_page', (int) $redirect_page_id );
			SV_WC_Order_Compatibility::update_meta_data( $coupon, '_wc_url_coupons_redirect_page_type', sanitize_key( $redirect_page_type ) );
		}

		// Products to add to cart.
		$product_ids = isset( $_POST['_wc_url_coupons_product_ids'] ) ? $_POST['_wc_url_coupons_product_ids'] : array();

		// TODO remove this when WC 3.0 and Select2 v4.0.3 is the minimum requirement {FN 2017-02-17}
		// Select2 v3.5.3 saves values as a comma-separated string.
		if ( is_string( $product_ids ) ) {
			$product_ids = explode( ',', $product_ids );
		}

		if ( ! empty( $product_ids ) && is_array( $product_ids ) ) {
			SV_WC_Coupon_Compatibility::update_meta_data( $coupon, '_wc_url_coupons_product_ids', array_map( 'absint', $product_ids ) );
		} else {
			SV_WC_Coupon_Compatibility::delete_meta_data( $coupon, '_wc_url_coupons_product_ids' );
		}

		// Defer apply.
		$defer_apply = isset( $_POST['_wc_url_coupons_defer_apply'] ) ? $_POST['_wc_url_coupons_defer_apply'] : '';

		if ( ! empty( $defer_apply ) ) {
			SV_WC_Coupon_Compatibility::update_meta_data( $coupon, '_wc_url_coupons_defer_apply', sanitize_text_field( $defer_apply ) );
		} else {
			SV_WC_Coupon_Compatibility::delete_meta_data( $coupon, '_wc_url_coupons_defer_apply' );
		}

		$options = array(
			'coupon_id'          => $post_id,
			'unique_url'         => $unique_url,
			'redirect_page'      => $redirect_page_id,
			'redirect_page_type' => $redirect_page_type,
			'product_ids'        => $product_ids,
			'defer_apply'        => $defer_apply,
		);

		// Update active coupon array option.
		$this->update_coupons( $options );
	}


	/**
	 * Helper function to update the active coupon option array.
	 *
	 * @since 1.0
	 * @param array $options coupon options
	 */
	public function update_coupons( $options ) {

		// load existing coupon urls
		$coupons = get_option( 'wc_url_coupons_active_urls', array() );

		// add coupon URL & Redirect page ID
		$coupons[ $options['coupon_id'] ] = array(
			'url'                => strtolower( $options['unique_url'] ),
			'redirect'           => (int) $options['redirect_page'],
			'redirect_page_type' => $options['redirect_page_type'],
			'products'           => ! empty( $options['product_ids'] ) && is_array( $options['product_ids'] ) ? array_map( 'absint', (array) $options['product_ids'] ) : array(),
			'defer'              => $options['defer_apply'],
		);

		// remove coupon URL if blank
		if ( ! $options['unique_url'] ) {
			unset( $coupons[ $options['coupon_id'] ] );
		}

		// update the array
		update_option( 'wc_url_coupons_active_urls', $coupons );

		// clear the transient
		delete_transient( 'wc_url_coupons_active_urls' );
	}


	/**
	 * Remove the unique URL associated with a coupon when the coupon is trashed. This prevents a "coupon does not exist"
	 * error message when the unique URL is visited but the coupon is trashed
	 *
	 * @internal
	 *
	 * @since 1.0.2
	 * @param int $coupon_id Coupon ID.
	 */
	public function purge_coupon_url( $coupon_id ) {

		// only purge for coupons
		if ( 'shop_coupon' !== get_post_type( $coupon_id ) ) {
			return;
		}

		$coupons = get_option( 'wc_url_coupons_active_urls' );

		// remove from active list
		if ( isset( $coupons[ $coupon_id ] ) ) {
			unset( $coupons[ $coupon_id ] );
		}

		// update active list
		update_option( 'wc_url_coupons_active_urls', $coupons );

		// clear transient
		delete_transient( 'wc_url_coupons_active_urls' );
	}


	/**
	 * Inject our admin settings into the Settings > Checkout page
	 *
	 * @internal
	 *
	 * @since 1.2
	 * @param array $settings WooCommerce settings.
	 * @return array
	 */
	public function admin_settings( $settings ) {

		$updated_settings = array();

		foreach ( $settings as $section ) {

			$updated_settings[] = $section;

			$section_id = 'woocommerce_calc_discounts_sequentially';

			// New section after the "General Options" section
			if ( isset( $section['id'] ) && $section_id === $section['id'] ) {

				$updated_settings[] = array(
					'title'         => __( 'Hide Coupon Code Field', 'ultimatewoo-pro' ),
					'desc'          => __( 'Hide on cart page.', 'ultimatewoo-pro' ),
					'desc_tip'      => __( 'Enable to hide the coupon code field on the cart page.', 'ultimatewoo-pro' ),
					'id'            => 'wc_url_coupons_hide_coupon_field_cart',
					'type'          => 'checkbox',
					'default'       => 'no',
					'checkboxgroup' => 'start'
				);

				$updated_settings[] = array(
					'desc'          => __( 'Hide on checkout page.', 'ultimatewoo-pro' ),
					'desc_tip'      => __( 'Enable to hide the coupon code field on the checkout page.', 'ultimatewoo-pro' ),
					'id'            => 'wc_url_coupons_hide_coupon_field_checkout',
					'type'          => 'checkbox',
					'default'       => 'no',
					'checkboxgroup' => 'end'
				);

			}
		}

		return $updated_settings;
	}


	/**
	 * Add 'URL Slug' column header to the Coupons list table
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 * @param array $column_headers
	 * @return array
	 */
	public function add_url_slug_column_header( $column_headers ) {

		$column_headers['url_slug'] = __( 'URL Slug', 'ultimatewoo-pro' );

		return $column_headers;
	}


	/**
	 * Add 'URL Slug' column content to the Coupons list table.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 * @param array $column
	 */
	public function add_url_slug_column( $column ) {

		if ( 'url_slug' === $column ) {

			$coupon = isset( $GLOBALS['post']->ID ) ? SV_WC_Coupon_Compatibility::get_coupon( $GLOBALS['post']->ID ) : null;
			$slug   = $coupon ? SV_WC_Coupon_Compatibility::get_meta( $coupon, '_wc_url_coupons_unique_url', true )  : null;

			echo $slug ? esc_html( $slug ) : '&ndash;';
		}
	}


}

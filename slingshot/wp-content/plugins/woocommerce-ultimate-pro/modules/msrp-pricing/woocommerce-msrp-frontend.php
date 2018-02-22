<?php

if ( ! class_exists( 'woocommerce_msrp_frontend' ) ) {

	class woocommerce_msrp_frontend {

		/**
		 * Add hooks for the default services.
		 */
		function __construct() {

			if ( ! is_admin() ) {
				// Add hooks for JS and CSS
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			}

			// Make sure the information is available to JS.
			add_action( 'woocommerce_available_variation', array( $this, 'add_msrp_to_js' ), 10, 3 );

			// Single Product Page.
			add_action( 'woocommerce_single_product_summary', array( $this, 'show_msrp' ), 7 );

			// Loop
			add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'show_msrp' ), 9 );

			 // Composite products extension.
			add_action( 'woocommerce_composite_before_price', array( $this, 'show_msrp' ), 7 );

			// Grouped product table
			add_action( 'woocommerce_grouped_product_list_before_price', array( $this, 'show_grouped_msrp' ), 9 );

			// REST API support for WooCommerce 2.6+
			add_filter( 'woocommerce_rest_prepare_product', array( $this, 'rest_api_price_output' ), 10, 2 );
			add_action( 'woocommerce_rest_insert_product', array( $this, 'rest_api_maybe_update_msrp' ), 10, 2 );
			add_filter( 'woocommerce_rest_product_schema', array( $this, 'rest_api_product_schema' ), 10 );

			// Add support for product add-ons extension.
			add_filter( 'woocommerce_product_addons_option_price', array( $this, 'product_addons_show_msrp' ), 10, 4 );
		}


		/**
		 * add_msrp_to_js function.
		 *
		 * @access public
		 * @param mixed $variation_data
		 * @param mixed $product
		 * @param mixed $variation
		 * @return void
		 */
		function add_msrp_to_js( $variation_data, $product, $variation ) {
			if ( is_callable( array( $variation, 'get_id' ) ) ) {
				$msrp = get_post_meta( $variation->get_id(), '_msrp', true );
			} else {
				$msrp = get_post_meta( $variation->variation_id, '_msrp', true );
			}
			if ( empty( $msrp ) ) {
				return $variation_data;
			}
			$variation_data['msrp'] = $msrp;
			$variation_data['msrp_html'] = $this->wc_price( $msrp );
			if ( is_callable( array( $variation, 'get_price' ) ) ) {
				$variation_data['non_msrp_price'] = $variation->get_price();
			} else {
				$variation_data['non_msrp_price'] = $variation->price;
			}
			return $variation_data;
		}


		/**
		 * Enqueue javascript required to show MSRPs on variable products.
		 */
		function enqueue_scripts() {
			$suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';
			if ( version_compare( WOOCOMMERCE_VERSION, '2.4.0' ) >= 0 ) {
				wp_enqueue_script( 'woocommerce_msrp', ULTIMATEWOO_MODULES_URL . "/msrp-pricing/js/frontend{$suffix}.js", array( 'jquery' ) );
			} else {
				wp_enqueue_script( 'woocommerce_msrp', ULTIMATEWOO_MODULES_URL . "/msrp-pricing/js/frontend-legacy{$suffix}.js", array( 'jquery' ) );
			}
		}


		/**
		 * Enqueue frontend stylesheets
		 */
		function enqueue_styles() {
			wp_enqueue_style( 'woocommerce_msrp', ULTIMATEWOO_MODULES_URL . '/msrp-pricing/css/frontend.css' );
		}


		/**
		 * Wrapper function to add markup required when showing the MSRP in a
		 * grouped product table list
		 */
		function show_grouped_msrp( $current_product ) {
			echo '<td class="woocommerce_msrp_price">';
			$this->show_msrp( $current_product );
			echo '</td>';
		}

		/**
		 * Get the MSRP for a non-variable product
		 * @param  object $current_product The product the MSRP is required for
		 * @return string				  The MSRP, or empty string
		 */
		function get_msrp_for_single_product( $current_product ) {
			if ( is_callable( array( $current_product, 'get_id' ) ) ) {
				// WC > 2.7.0
				return get_post_meta( $current_product->get_id(), '_msrp_price', true );
			} else {
				// WC < 2.7.0
				return get_post_meta( $current_product->id, '_msrp_price', true );
			}
		}

		/**
		 * Get the MSRP for a variable product. This will be the cheapest MSRP for any
		 * variation, and does not necessarily relate to the cheapest actual priced variation.
		 * @param  object $current_product The product the MSRP is required for
		 * @return string				  The MRSP, or empty string
		 */
		function get_msrp_for_variable_product( $current_product ) {
			$children = $current_product->get_children();
			if ( ! $children ) {
				return $this->get_msrp_for_single_product( $current_product );
			}
			$lowest_msrp  = '';
			$highest_msrp = '';
			foreach ( $children as $child ) {
				$child_msrp = get_post_meta( $child, '_msrp', true );
				if ( false === $child_msrp || '' === $child_msrp ) {
					continue;
				}
				if ( empty( $lowest_msrp ) || $child_msrp < $lowest_msrp ) {
					$lowest_msrp = $child_msrp;
				}
				if ( empty( $highest_msrp ) || $child_msrp > $highest_msrp ) {
					$highest_msrp = $child_msrp;
				}
			}
			if ( '' === $lowest_msrp ) {
				return array();
			}
			return array( $lowest_msrp, $highest_msrp );
		}

		/**
		 * Show the MSRP on the frontend
		 */
		function show_msrp( $current_product = null ) {

			global $product, $woocommerce_settings;

			if ( ! $current_product ) {
				$current_product = $product;
			}

			$msrp_status = get_option( 'woocommerce_msrp_status' );
			// User has chosen not to show MSRP, don't show it
			if ( empty( $msrp_status ) || 'never' === $msrp_status ) {
				return;
			}

			$msrp_description = get_option( 'woocommerce_msrp_description' );
			echo '<script type="text/javascript">';
			echo 'var msrp_status = "' . esc_attr( $msrp_status ) . '";';
			echo 'var msrp_description= "' . esc_attr( $msrp_description ) . '";';
			echo '</script>';
			if ( is_callable( array( $current_product, 'get_type' ) ) ) {
				$product_type = $current_product->get_type();
			} else {
				$product_type = $current_product->product_type;
			}
			if ( 'variable' === $product_type ) {
				$msrp = $this->get_msrp_for_variable_product( $current_product );
				if ( empty( $msrp ) ) {
					return;
				}
				if ( 'always' === $msrp_status ||
					   ( 'different' === $msrp_status &&
						  ( $current_product->price !== $msrp[0] ||
							 $current_product->price !== $msrp[1] )
						)
					) {
					if ( apply_filters( 'woocommerce_msrp_hide_variation_price_ranges', false ) ) {
						return;
					}
					if ( $msrp[0] === $msrp[1] ) {
						$price_string = $this->wc_price( $msrp[0] );
					} else {
						$price_string = $this->wc_price( $msrp[0] ) . ' - ' . $this->wc_price( $msrp[1] );
					}
					echo '<div class="woocommerce_msrp">';
					echo esc_html( $msrp_description );
					echo ': ';
					echo '<span class="woocommerce_msrp_price">' . $price_string . '</span>';
					echo '</div>';
				}
			} else {
				$msrp = $this->get_msrp_for_single_product( $current_product );
				if ( empty( $msrp ) ) {
					return;
				}
				if ( is_callable( array( $current_product, 'get_price' ) ) ) {
					$selling_price = $current_product->get_price();
				} else {
					$selling_price = $current_product->price;
				}
				if ( 'always' === $msrp_status ||
					( $msrp !== $selling_price && 'different' === $msrp_status ) ) {
					echo '<div class="woocommerce_msrp">';
					echo esc_html( $msrp_description );
					echo ': ';
					echo '<span class="woocommerce_msrp_price">' . $this->wc_price( $msrp ) . '</span>';
					echo '</div>';
				}
			}
		}

		/**
		 * Include MSRP Prices in REST API GET responses on products.
		 */
		public function rest_api_price_output( $response, $post ) {
			$product = wc_get_product( $post );
			if ( 'variable' === $product->product_type ) {
				if ( ! count( $response->data['variations'] ) ) {
					$response->data['msrp_price'] = $this->get_msrp_for_single_product( $product );
					return $response;
				}
				foreach ( $response->data['variations'] as $idx => $variation ) {
					if ( isset( $response->data['variations'][ $idx ]['msrp_price'] ) ) {
						continue;
					}
					$product = wc_get_product( $variation['id'] );
					$response->data['variations'][ $idx ]['msrp_price'] = $this->get_msrp_for_single_product( $product );
				}
				return $response;
			} else {
				// Do nothing if we already have the data.
				if ( isset( $response->data['msrp_price'] ) ) {
					return $response;
				}
				$response->data['msrp_price'] = $this->get_msrp_for_single_product( $product );
				return $response;
			}
		}

		/**
		 * Allow MSRP prices to be updated via REST API.
		 */
		public function rest_api_maybe_update_msrp( $post, $request ) {
			if ( ! isset( $request['msrp_price'] ) ) {
				return;
			}
			$product = wc_get_product( $post );
			if ( 'variation' === $product->product_type ) {
				$key = '_msrp';
			} else {
				$key = '_msrp_price';
			}
			update_post_meta( $post->ID, $key, $request['msrp_price'] );
		}

		/**
		 * Include a description of the msrp_price element in the REST schema.
		 */
		public function rest_api_product_schema( $schema ) {
			$schema['msrp_price'] = array(
				'description' => 'The MSRP price for the product.',
				'type'        => 'string',
				'context'     => array(
					'view',
					'edit',
				),
			);
			return $schema;
		}

		public function product_addons_show_msrp( $html, $option, $idx, $type ) {

			// User has chosen not to show MSRP, don't show it
			$msrp_status = get_option( 'woocommerce_msrp_status' );
			if ( empty( $msrp_status ) || 'never' === $msrp_status ) {
				return $html;
			}

			// Get the info we need.
			$template         = __(
				'(%1$s, <span class="woocommerce_msrp">%2$s: <span class="woocommerce_msrp_price">%3$s</span></span>)',
				'woocommerce_msrp'
			);
			if ( 'select' === $type ) {
				$template         = __(
					'(%1$s, %2$s: %3$s)',
					'woocommerce_msrp'
				);
			}
			$raw_price        = isset( $option['price'] ) ? $option['price'] : null;
			$raw_msrp         = isset( $option['msrp'] ) ? $option['msrp'] : null;
			if ( empty( $raw_msrp ) ) {
				return $html;
			}
			$price_html       = $option['price'] > 0 ? wc_price( get_product_addon_price_for_display( $option['price'] ) ) : '';
			$msrp_description = get_option( 'woocommerce_msrp_description' );
			$msrp_price_html  = ! empty( $option['msrp'] ) ? wc_price( $option['msrp'] ) : '';

			// Check whether we should show the MSRP, and either return the original markup
			// if not, or the MSRP included markup.
			if ( 'different' === $msrp_status && $raw_price === $raw_msrp ) {
				return $html;
			}
			return sprintf( $template, $price_html, $msrp_description, $msrp_price_html );
		}

		private function wc_price( $price ) {
			if ( is_callable( 'wc_price' ) ) {
				return wc_price( $price );
			} else {
				return woocommerce_price( $price );
			}
		}
	}
}

$woocommerce_msrp_frontend = new woocommerce_msrp_frontend();

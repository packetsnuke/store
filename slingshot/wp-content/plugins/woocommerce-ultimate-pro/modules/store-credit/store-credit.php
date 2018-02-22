<?php
/*
 * Copyright: 2014-2017 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * 
 * Adapted from the original store credit extension created by Visser Labs (http://visser.com.au)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_woocommerce_active() && ! class_exists( 'WC_Store_Credit_Plus' ) ) {

	/**
	 * Localisation
	 */
	load_plugin_textdomain( 'woocommerce-store-credit', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	define( 'WC_STORE_CREDIT_PLUS_VERSION', '2.1.7' );

	/**
	 * WC_Store_Credit_Plus class
	 */
	class WC_Store_Credit_Plus {

		/**
		 * Constructor
		 */
		public function __construct() {
			define( 'WC_STORE_CREDIT_PLUGIN_DIR', ULTIMATEWOO_MODULES_DIR . '/store-credit' );
			define( 'WC_STORE_CREDIT_PLUGIN_URL', ULTIMATEWOO_MODULES_URL . '/store-credit' );

			add_filter( 'woocommerce_coupon_is_valid', array( $this, 'coupon_is_valid' ), 10, 2 );
			add_filter( 'woocommerce_coupon_is_valid_for_cart', array( $this, 'coupon_is_valid_for_cart' ), 10, 2 );
			add_action( 'woocommerce_new_order', array( $this, 'update_credit_amount' ), 9 );
			add_action( 'woocommerce_before_my_account', array( $this, 'display_credit' ) );
			add_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'coupon_get_discount_amount' ), 10, 5 );
			add_filter( 'woocommerce_cart_totals_coupon_label', array( $this, 'cart_totals_coupon_label' ), 10, 2 );
			add_action( 'woocommerce_applied_coupon', array( $this, 'apply_credit_last' ) );
			add_filter( 'woocommerce_coupon_discount_types', array( $this, 'add_discount_type' ) );

			// Admin
			if ( is_admin() ) {
				include_once( 'includes/class-wc-store-credit-plus-admin.php' );
			}

		}

		/**
		 * Add the coupon type to admin
		 */
		public function add_discount_type( $discount_types ) {
			$discount_types['store_credit'] = __( 'Store Credit', 'ultimatewoo-pro' );
			return $discount_types;
		}


		/**
		 * Display credit
		 */
		public function display_credit() {
			if ( $coupons = $this->get_customer_credit() ) {
				?>
					<h2><?php _e( 'Store Credit', 'ultimatewoo-pro' ); ?></h2>
					<ul class="store-credit">
						<?php
						$html = '';
						foreach ( $coupons as $code ) {
							$coupon = new WC_Coupon( $code->post_title );
							if ( 'store_credit' === self::get_coupon_prop( $coupon, 'type' ) ) {
								$html .= '<li><strong>' . self::get_coupon_prop( $coupon, 'code' ) . '</strong> &mdash;' . wc_price( self::get_coupon_prop( $coupon, 'amount' ) ) . '</li>';
							}
						}

						if ( ! empty ( $html ) ) {
							echo $html;
						} else {
							echo '<li>' . __( 'You do not have any store credit on your account yet.', 'ultimatewoo-pro' ) . '</li>';
						}
						?>
					</ul>
				<?php
			}
		}

		/**
		 * Get credit for a customer
		 */
		public function get_customer_credit() {
			if ( 'no' === get_option( 'woocommerce_store_credit_show_my_account', 'yes' ) ) {
				return;
			}

			$user = wp_get_current_user();

			if ( '' === $user->user_email ) {
				return;
			}

			$args = array(
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'customer_email',
						'value'   => $user->user_email,
						'compare' => 'LIKE'
					),
					array(
						'key'     => 'coupon_amount',
						'value'   => '0',
						'compare' => '>=',
						'type'    => 'NUMERIC'
					)
				)
			);

			return get_posts( $args );
		}

		/**
		 * Check if credit is valid
		 */
		public function coupon_is_valid( $valid, $coupon ) {
			if ( $valid && 'store_credit' === self::get_coupon_prop( $coupon, 'type' ) && self::get_coupon_prop( $coupon, 'amount' ) <= 0 ) {
				wc_add_notice( __( 'There is no credit remaining on this coupon.', 'ultimatewoo-pro' ), 'error' );
				return false;
			}
			return $valid;
		}

		/**
		 * Check if credit is valid
		 */
		public function coupon_is_valid_for_cart( $valid, $coupon ) {
			if ( 'store_credit' === self::get_coupon_prop( $coupon, 'type' ) ) {
				return true;
			}
			return $valid;
		}

		/**
		 * Update a coupon after purchase
		 */
		public function update_credit_amount() {
			if ( empty( WC()->cart ) ) {
				return;
			}

			if ( $coupons = WC()->cart->get_coupons() ) {
				$apply_before_tax = get_option( 'woocommerce_store_credit_apply_before_tax', 'no' );

				foreach ( $coupons as $code => $coupon ) {
					if ( 'store_credit' === self::get_coupon_prop( $coupon, 'type' ) ) {
						
						if ( 'yes' === $apply_before_tax ) {
							$discount_amounts = WC()->cart->coupon_discount_amounts[ $code ];
						} else {
							$discount_amounts = WC()->cart->coupon_discount_amounts[ $code ] + WC()->cart->coupon_discount_tax_amounts[ $code ];
						}

						$credit_remaining = max( 0, ( self::get_coupon_prop( $coupon, 'amount' ) - $discount_amounts ) );

						if ( $credit_remaining <= 0 && 'yes' === get_option( 'woocommerce_delete_store_credit_after_usage', 'yes' ) ) {
							wp_delete_post( self::get_coupon_prop( $coupon, 'id' ) );
						} else {
							update_post_meta( self::get_coupon_prop( $coupon, 'id' ), 'coupon_amount', wc_format_decimal( $credit_remaining, 2 ) );
						}
					}
				}
			}
		}

		/**
		 * Get coupon discount amount
		 * @param  float $discount
		 * @param  float $discounting_amount
		 * @param  object $cart_item
		 * @param  bool $single
		 * @param  WC_Coupon $coupon
		 * @return float
		 */
		public function coupon_get_discount_amount( $discount, $discounting_amount, $cart_item, $single, $coupon ) {
			if ( 'store_credit' === self::get_coupon_prop( $coupon, 'type' ) && ! is_null( $cart_item ) ) {
				/**
				 * This is the most complex discount - we need to divide the discount between rows based on their price in
				 * proportion to the subtotal. This is so rows with different tax rates get a fair discount, and so rows
				 * with no price (free) don't get discounted.
				 *
				 * Get item discount by dividing item cost by subtotal to get a %
				 */
				$discount_percent = 0;

				if ( WC()->cart->subtotal_ex_tax ) {
					$price = version_compare( WC_VERSION, '3.0', '<' ) ? $cart_item['data']->get_price_excluding_tax() : wc_get_price_to_display( $cart_item['data'], array( 'qty' => 1, 'price' => '' ) );
					$discount_percent = ( $price * $cart_item['quantity'] ) / WC()->cart->subtotal_ex_tax;
				}

				$discount = min( ( self::get_coupon_prop( $coupon, 'amount' ) * $discount_percent ) / $cart_item['quantity'], $discounting_amount );
			} elseif ( 'store_credit' === self::get_coupon_prop( $coupon, 'type' ) ) {
				$discount = min( self::get_coupon_prop( $coupon, 'amount' ), $discounting_amount );
			}
			return $discount;
		}

		/**
		 * Change label in cart
		 * @param  string $label
		 * @param  WC_Coupon $coupon
		 * @return string
		 */
		public function cart_totals_coupon_label( $label, $coupon ) {
			if ( 'store_credit' === self::get_coupon_prop( $coupon, 'type' ) ) {
				$label = __( 'Store credit:', 'ultimatewoo-pro' );
			}
			return $label;
		}

		/**
		 * If another discount is provided, the credit is moved to the "last applied" coupon
		 */
		public function apply_credit_last( $code ) {
			$coupon = new WC_Coupon( $code );

			// If the coupon we are trying to apply is a store credit, we can stop
			if ( 'store_credit' === self::get_coupon_prop( $coupon, 'discount_type' ) ) {
				return;
			}

			$applied_coupons = WC()->cart->get_applied_coupons();

			if ( empty ( $applied_coupons ) || ! is_array( $applied_coupons ) ) {
				return;
			}

			$codes_to_add_back = array();
			foreach ( $applied_coupons as $applied_coupon_index => $applied_coupon_code ) {
				$applied_coupon = new WC_Coupon( $applied_coupon_code );

				if ( 'store_credit' === self::get_coupon_prop( $applied_coupon, 'discount_type' ) ) {
					WC()->cart->remove_coupon( $applied_coupon_code );
					$codes_to_add_back[] = $applied_coupon_code;
				}
			}

			add_filter( 'woocommerce_coupon_message', array( $this, 'hide_coupon_message' ), 10, 3 );

			if ( ! empty ( $codes_to_add_back ) && is_array( $codes_to_add_back ) ) {
				foreach ( $codes_to_add_back as $code_to_add_back ) {
					WC()->cart->add_discount( $code_to_add_back );
				}
			}

			remove_filter( 'woocommerce_coupon_message', array( $this, 'hide_coupon_message' ) );
		}

		/**
		 * Makes it so we don't add a "Coupon code applied successfully" message for EVERY discount we add back
		 */
		public function hide_coupon_message( $msg, $msg_code, $coupon ) {
			return '';
		}

		/**
		 * Get coupon property with compatibility for WC lt 3.0.
		 *
		 * @since 2.1.7
		 *
		 * @param WC_Coupon $coupon Coupon object.
		 * @param string    $key    Coupon property.
		 *
		 * @return mixed Value of coupon property.
		 */
		public static function get_coupon_prop( $coupon, $key ) {
			switch ( $key ) {
				case 'type':
					$getter = array( $coupon, 'get_discount_type' );
					break;
				default:
					$getter = array( $coupon, 'get_' . $key );
					break;
			}

			return is_callable( $getter ) ? call_user_func( $getter ) : $coupon->{ $key };
		}
	}

	new WC_Store_Credit_Plus();
}

//2.1.7
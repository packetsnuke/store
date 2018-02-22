<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_COMPATIBILITY_woocommerce_dynamic_pricing_and_discounts {

	protected static $_instance = NULL;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		add_action( 'init', array( $this, 'add_compatibility' ) );
		add_filter( 'plugins_loaded', array( $this, 'add_compatibility_settings' ), 2, 1 );
	}

	public function init() {
		add_filter( 'wc_epo_autoload_path', array( $this, 'wc_epo_autoload_path' ), 10, 2 );
		add_filter( 'wc_epo_autoload_file', array( $this, 'wc_epo_autoload_file' ), 10, 2 );
	}

	public function add_compatibility_settings() {
		add_filter( 'wc_epo_get_settings', array( $this, 'wc_epo_get_settings' ), 10, 1 );
	}

	public function wp_enqueue_scripts() {
		if (
			((class_exists( 'WC_Quick_View' ) || TM_EPO()->is_supported_quick_view()) && (is_shop() || is_product_category() || is_product_tag()))
			|| TM_EPO()->is_enabled_shortcodes()
			|| is_product()
			|| is_cart()
			|| is_checkout()
			|| is_order_received_page()
			|| (TM_EPO()->tm_epo_enable_in_shop == "yes" && (is_shop() || is_product_category() || is_product_tag()))
		) {
			wp_enqueue_script( 'tc-comp-dynamic-pricing-discounts', TM_EPO_PLUGIN_URL . '/include/compatibility/assets/js/dynamic-pricing-discounts.js', array( 'jquery' ), TM_EPO()->version, TRUE );
		}
	}

	public function wc_epo_get_settings( $settings = array() ) {
		if ( class_exists( 'RP_WCDPD' ) ) {
			$settings["tm_epo_dpd_enable"] = array( "no", $this, "is_dpd_enabled" );
			$settings["tm_epo_dpd_prefix"] = array( "", $this, "is_dpd_enabled" );
			$settings["tm_epo_dpd_suffix"] = array( "", $this, "is_dpd_enabled" );
			$settings["tm_epo_dpd_original_final_total"] = array( "no", $this, "is_dpd_enabled" );
			$settings["tm_epo_dpd_enable_pricing_table"] = array( "no", $this, "is_dpd_enabled" );
		}

		return $settings;
	}

	public function is_dpd_enabled() {
		return class_exists( 'RP_WCDPD' );
	}

	public function add_compatibility() {

		/** WooCommerce Dynamic Pricing & Discounts support **/
		if ( !class_exists( 'RP_WCDPD' ) ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 4 );

		if ( TM_EPO()->tm_epo_dpd_enable == "no" ) {
			add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'cart_loaded_from_session_2' ), 2 );
			add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'cart_loaded_from_session_99999' ), 99999 );
		}
		add_filter( 'woocommerce_cart_item_price', array( $this, 'cart_item_price' ), 101, 3 );
		add_action( 'wc_epo_order_item_meta', array( $this, 'wc_epo_order_item_meta' ), 10, 2 );

		add_filter( 'wc_epo_discounted_price', array( $this, 'get_RP_WCDPD' ), 10, 3 );

		add_filter( 'tm_epo_settings_headers', array( $this, 'tm_epo_settings_headers' ), 10, 1 );
		add_filter( 'tm_epo_settings_settings', array( $this, 'tm_epo_settings_settings' ), 10, 1 );

		add_filter( 'wc_epo_product_price_rules', array( $this, 'wc_epo_product_price_rules' ), 10, 2 );
		add_filter( 'wc_epo_template_args_tm_totals', array( $this, 'wc_epo_template_args_tm_totals' ), 10, 1 );
		add_action( 'wc_epo_template_tm_totals', array( $this, 'wc_epo_template_tm_totals' ), 10, 1 );

		add_action( 'woocommerce_tm_epo_price_compatibility', array( $this, 'woocommerce_tm_epo_price_compatibility' ), 10, 2 );

	}

	/**
     * Get applicable volume rule
     *
     * Note: This feature assumes that considering all conditions only one
     * volume rule will be applicable to one product. If there are more than
     * one volume rule, the first one in a row will be selected.
     *
     * Note: Rules that contain cart related conditions are not considered
     *
     * @access public
     * @param object $product
     * @return array|bool
     */
	public static function get_applicable_volume_rule($product){
        if ($matched_rules = RP_WCDPD_Product_Pricing::get_applicable_rules_for_product($product, array('bulk', 'tiered'), true)) {
            return array_shift($matched_rules);
        }

        return false;
    }

    /**
     * Get missing quantity range
     *
     * @access public
     * @param int $from
     * @param int $to
     * @return array
     */
    public static function get_missing_range($from, $to){
        return array(
            'from'              => $from,
            'to'                => $to,
            'pricing_method'    => 'discount__amount',
            'pricing_value'     => 0,
            'is_missing_range'  => true,
        );
    }

    /**
     * Add missing quantity ranges (gaps in continuity)
     *
     * @access public
     * @param array $quantity_ranges
     * @param object $product
     * @return array
     */
    public static function add_missing_ranges($quantity_ranges, $product){
        $fixed = array();

        // Check if product uses decimal quantities
        $decimal_quantities = RP_WCDPD_Settings::get('decimal_quantities') && RightPress_Helper::wc_product_uses_decimal_quantities($product);

        // Get quantity step
        $quantity_step = $decimal_quantities ? RightPress_Helper::get_wc_product_quantity_step($product) : 1;

        $last_from = null;
        $last_to = null;

        $count = count($quantity_ranges);
        $i = 1;

        foreach ($quantity_ranges as $quantity_range) {

            // Get from and to
            $from = $quantity_range['from'];
            $to = $quantity_range['to'];

            // Maybe add first range
            if ($last_from === null && $from > $quantity_step) {
                $fixed[] = self::get_missing_range($quantity_step, ($from - $quantity_step));
            }

            // Gap between last to and current from
            if ($last_to !== null && ($from - $last_to) > $quantity_step) {
                $fixed[] = self::get_missing_range(($last_to + $quantity_step), ($from - $quantity_step));
            }

            // Add current range
            $fixed[] = $quantity_range;

            // Set last from and to
            $last_from = $from;
            $last_to = $to;

            $i++;
        }

        // Add closing range
        if ($last_to !== null) {
            $fixed[] = self::get_missing_range(($last_to + $quantity_step), null);
        }

        return $fixed;
    }

	public function woocommerce_tm_epo_price_compatibility($price, $product){
	
		if ( class_exists('RP_WCDPD_Settings') && class_exists('RP_WCDPD_Promotion_Display_Price_Override') && RP_WCDPD_Settings::get('promo_display_price_override') ){
			
			RP_WCDPD_Promotion_Display_Price_Override::get_instance()->remove_all_price_hooks();

			if ( function_exists( 'WC_CP' ) && version_compare( WC_CP()->version, "3.8", "<" ) && tc_get_product_type( $product ) == "composite" && is_callable( array( $product, 'get_base_price' ) ) ) {
				$price = $product->get_base_price();
			} else {
				$price = $product->get_price();
			}

			RP_WCDPD_Promotion_Display_Price_Override::get_instance()->add_all_price_hooks();

		}elseif( version_compare( RP_WCDPD_VERSION, '2.2', '>=' ) && class_exists('RP_WCDPD_Product_Price_Override')){

			RP_WCDPD_Product_Price_Override::get_instance()->remove_all_price_hooks();
			if ( function_exists( 'WC_CP' ) && version_compare( WC_CP()->version, "3.8", "<" ) && tc_get_product_type( $product ) == "composite" && is_callable( array( $product, 'get_base_price' ) ) ) {
				$price = $product->get_base_price();
			} else {
				$price = $product->get_price();
			}
			RP_WCDPD_Product_Price_Override::get_instance()->add_all_price_hooks();
		}

		return $price;
	}

	public function wc_epo_template_tm_totals( $args ) {
		$tm_epo_dpd_prefix = $args['tm_epo_dpd_prefix'];
		$tm_epo_dpd_suffix = $args['tm_epo_dpd_suffix'];
		$tm_epo_dpd_original_final_total = $args['tm_epo_dpd_original_final_total'];
		$tm_epo_dpd_enable_pricing_table = $args['tm_epo_dpd_enable_pricing_table'];

		echo 'data-tm-epo-dpd-enable-pricing-table="'.esc_attr( $tm_epo_dpd_enable_pricing_table ).'" data-tm-epo-dpd-original-final-total="'.esc_attr( $tm_epo_dpd_original_final_total ).'" data-tm-epo-dpd-prefix="' . esc_attr( $tm_epo_dpd_prefix ) . '" data-tm-epo-dpd-suffix="' . esc_attr( $tm_epo_dpd_suffix ) . '" ';

		if ( class_exists('RP_WCDPD_Settings') && class_exists('RP_WCDPD_Promotion_Display_Price_Override') && RP_WCDPD_Settings::get('promo_display_price_override') ){
			echo 'data-tm-epo-dpd-price-override="1" ';
		}

		if ( defined('RP_WCDPD_VERSION') && version_compare( RP_WCDPD_VERSION, '2.2', '>=' ) ){
			echo 'data-tm-epo-dpd-product-price-discounted="1" ';	
		}
	}

	public function wc_epo_template_args_tm_totals( $args ) {
		$args["tm_epo_dpd_suffix"] = TM_EPO()->tm_epo_dpd_suffix;
		$args["tm_epo_dpd_prefix"] = TM_EPO()->tm_epo_dpd_prefix;
		$args["tm_epo_dpd_original_final_total"] = TM_EPO()->tm_epo_dpd_original_final_total;
		$args["tm_epo_dpd_enable_pricing_table"] = TM_EPO()->tm_epo_dpd_enable_pricing_table;

		$args["fields_price_rules"] = (TM_EPO()->tm_epo_dpd_enable == "no") ? $args["fields_price_rules"] : 1;

		if ( $args["price_override"] == "1" ) {
			$args["fields_price_rules"] = 1;
		}

		return $args;
	}

	public function wc_epo_product_price_rules( $price = array(), $product ) {
		if ( class_exists( 'RP_WCDPD' ) ) {
			$check_price = apply_filters( 'wc_epo_discounted_price', NULL, $product, NULL );
			if ( $check_price ) {
				$price['product'] = array();
				if ( $check_price['is_multiprice'] ) {
					foreach ( $check_price['rules'] as $variation_id => $variation_rule ) {
						foreach ( $variation_rule as $rulekey => $pricerule ) {
							$price['product'][ $variation_id ][] = array(
								"min"   => $pricerule["min"],
								"max"   => $pricerule["max"],
								"value" => ($pricerule["type"] != "percentage") ? apply_filters( 'wc_epo_product_price', $pricerule["value"], "", FALSE ) : $pricerule["value"],
								"type"  => $pricerule["type"],
							);
						}
					}
				} else {
					foreach ( $check_price['rules'] as $rulekey => $pricerule ) {
						$price['product'][0][] = array(
							"min"   => $pricerule["min"],
							"max"   => $pricerule["max"],
							"value" => ($pricerule["type"] != "percentage") ? apply_filters( 'wc_epo_product_price', $pricerule["value"], "", FALSE ) : $pricerule["value"],
							"type"  => $pricerule["type"],
						);
					}
				}
			}
			$price['price'] = apply_filters( 'woocommerce_tm_epo_price_compatibility', apply_filters( 'wc_epo_product_price', $product->get_price(), "", FALSE ), $product );
		}

		return $price;
	}

	public function wc_epo_order_item_meta( $item_id, $values ) {
		if ( !empty( $values['tmcartepo'] ) ) {
			wc_add_order_item_meta( $item_id, '_tm_has_dpd', 1 );
		}
	}

	public function wc_epo_autoload_path( $path, $original_class ) {
		// Composite products sometimes do not load the Discount and Pricing classes
		if ( defined('RP_WCDPD_VERSION') && version_compare( RP_WCDPD_VERSION, '1.0.13', '<' ) && $original_class == "RP_WCDPD_Pricing" && defined( 'TM_EPO_INCLUDED' ) && defined( 'RP_WCDPD_PLUGIN_PATH' ) ) {
			$path = RP_WCDPD_PLUGIN_PATH . 'includes/classes/';
		}

		return $path;
	}

	public function wc_epo_autoload_file( $file, $original_class ) {
		// Composite products sometimes do not load the Discount and Pricing classes
		if ( defined('RP_WCDPD_VERSION') && version_compare( RP_WCDPD_VERSION, '1.0.13', '<' ) && $original_class == "RP_WCDPD_Pricing" && defined( 'TM_EPO_INCLUDED' ) && defined( 'RP_WCDPD_PLUGIN_PATH' ) ) {
			$file = 'Pricing.php';
		}

		return $file;
	}

	/** Admin settings **/
	public function tm_epo_settings_headers( $headers = array() ) {
		$headers["dpd"] = __( 'Dynamic Pricing & Discounts', 'woocommerce-tm-extra-product-options' );

		return $headers;
	}

	/** Admin settings **/
	public function tm_epo_settings_settings( $settings = array() ) {
		$label = __( 'Dynamic Pricing & Discounts', 'woocommerce-tm-extra-product-options' );;
		$settings["dpd"] = array(
			array(
				'type'  => 'tm_title',
				'id'    => 'epo_page_options',
				'title' => $label,
			),
			array(
				'title'    => __( 'Enable discounts on extra options', 'woocommerce-tm-extra-product-options' ),
				'desc'     => '<span>' . __( 'Enabling this will apply the product discounts to the extra options as well.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_dpd_enable',
				'class'    => 'chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'no',
				'type'     => 'select',
				'options'  => array(
					'no'  => __( 'Disable', 'woocommerce-tm-extra-product-options' ),
					'yes' => __( 'Enable', 'woocommerce-tm-extra-product-options' ),
				),
				'desc_tip' => FALSE,
			),
			array(
				'title'   => __( 'Enable alteration of pricing table', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'Check to enable the the inclusion of option prices to the pricing table.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_dpd_enable_pricing_table',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Enable original final total display', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'Check to enable the display of the undiscounted final total', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_dpd_original_final_total',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'    => __( 'Prefix label', 'woocommerce-tm-extra-product-options' ),
				'desc'     => '<span>' . __( 'Display a prefix label on product page.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_dpd_prefix',
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => FALSE,
			),
			array(
				'title'    => __( 'Suffix label', 'woocommerce-tm-extra-product-options' ),
				'desc'     => '<span>' . __( 'Display a suffix label on product page.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_dpd_suffix',
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => FALSE,
			),
			array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),

		);

		return $settings;
	}

	/** WooCommerce Dynamic Pricing & Discounts support **/
	public function cart_loaded_from_session_2() {

		$cart_contents = WC()->cart->cart_contents;

		if ( is_array( $cart_contents ) ) {
			foreach ( $cart_contents as $cart_item_key => $cart_item ) {
				if ( !empty( $cart_item['tmcartepo'] ) && isset( $cart_item['tm_epo_product_original_price'] ) && empty( $cart_item['epo_price_override'] ) ) {
					if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7.0', '<' ) ) {
						WC()->cart->cart_contents[ $cart_item_key ]['data']->price = $cart_item['tm_epo_product_original_price'];
					}else{
						WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $cart_item['tm_epo_product_original_price'] );
					}
					WC()->cart->cart_contents[ $cart_item_key ]['tm_epo_doing_adjustment'] = TRUE;
				}
			}
		}

	}

	/** WooCommerce Dynamic Pricing & Discounts support **/
	public function cart_loaded_from_session_99999() {

		$cart_contents = WC()->cart->cart_contents;
		if ( is_array( $cart_contents ) ) {
			foreach ( $cart_contents as $cart_item_key => $cart_item ) {
				if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7.0', '<' ) ) {
					$current_product_price = WC()->cart->cart_contents[ $cart_item_key ]['data']->price;
				}else{
					$current_product_price = WC()->cart->cart_contents[ $cart_item_key ]['data']->get_price();
				}				

				if ( !empty( $cart_item['tmcartepo'] ) && isset( $cart_item['tm_epo_options_prices'] ) && !empty( $cart_item['tm_epo_doing_adjustment'] ) && empty( $cart_item['epo_price_override'] ) ) {
					WC()->cart->cart_contents[ $cart_item_key ]['tm_epo_product_after_adjustment'] = $current_product_price;
					WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( WC()->cart->cart_contents[ $cart_item_key ]['data']->get_price() + $cart_item['tm_epo_options_prices'] );
					unset( WC()->cart->cart_contents[ $cart_item_key ]['tm_epo_doing_adjustment'] );
				}
			}
		}

	}

	/**
	 * Replace cart html prices for WooCommerce Dynamic Pricing & Discounts
	 *
	 * @access public
	 * @param string $item_price
	 * @param array $cart_item
	 * @param string $cart_item_key
	 * @return string
	 */
	public function cart_item_price( $item_price = "", $cart_item = "", $cart_item_key = "" ) {

		if ( !isset( $cart_item['tmcartepo'] ) ) {
			return $item_price;
		}
		if ( !isset( $cart_item['rp_wcdpd'] ) ) {
			if ( !isset( $cart_item['rp_wcdpd_data'] ) ) {
				return $item_price;
			}
		}

		// Get price to display
		$price = TM_EPO()->get_price_for_cart( FALSE, $cart_item, "", NULL, 0, 1 );

		// Format price to display
		$price_to_display = $price;

		$product = $cart_item['data'];
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7.0', '<' ) ) {
			if ( is_object( $product ) && property_exists( $product, "price" ) ) {
				$float_price_to_display = floatval( $cart_item['data']->price );
			} else {
				$float_price_to_display = floatval( $product->price );
			}
		} else {
			$float_price_to_display = floatval( $product->get_price() );
		}

		if ( TM_EPO()->tm_epo_cart_field_display == "advanced" ) {
			$original_price_to_display = TM_EPO()->get_price_for_cart( $cart_item['tm_epo_product_original_price'], $cart_item, "", NULL, 0, 1 );
			$float_original_price_to_display = floatval( $cart_item['tm_epo_product_original_price'] );
			if ( TM_EPO()->tm_epo_dpd_enable == "yes" ) {
				$price = $this->get_RP_WCDPD( $cart_item['tm_epo_product_original_price'], wc_get_product( tc_get_id( $cart_item['data'] ) ), $cart_item_key );
				$price_to_display = TM_EPO()->get_price_for_cart( $price, $cart_item, "", NULL, 0, 1 );
				$float_price_to_display = floatval( $price );
			} else {
				$price = $cart_item['data']->get_price();
				$price = $price - $cart_item['tm_epo_options_prices'];
				$price_to_display = TM_EPO()->get_price_for_cart( $price, $cart_item, "", NULL, 0, 1 );
				$float_price_to_display = floatval( $price );
			}
		} else {
			$original_price_to_display = TM_EPO()->get_price_for_cart( $cart_item['tm_epo_product_price_with_options'], $cart_item, "", NULL, 0, 1 );
			$float_original_price_to_display = floatval( $cart_item['tm_epo_product_price_with_options'] );
		}

		if ( isset($float_price_to_display) && isset($float_original_price_to_display) && $float_price_to_display === $float_original_price_to_display){
			return $item_price;			
		}	

		$item_price = '<span class="rp_wcdpd_cart_price"><del>' . $original_price_to_display . '</del> <ins>' . $price_to_display . '</ins></span>';		

		return $item_price;
	}

	// get WooCommerce Dynamic Pricing & Discounts price for options
	// modified from get version from Pricing class
	private function get_RP_WCDPD_single( $field_price, $cart_item_key, $pricing=NULL ) {

		if ( TM_EPO()->tm_epo_dpd_enable == 'no' ) {
			return $field_price;
		}

		$price = $field_price;
		$original_price = $price;

		if (!$pricing){
			// This runs on versions >=2
			$product_pricing = RP_WCDPD_Settings::get('product_pricing');
			$cart_item = WC()->cart->cart_contents[$cart_item_key];
			if ( !isset( $cart_item['rp_wcdpd_data'] ) ) {
				return $field_price;
			}
			$initial_product_price = $cart_item['rp_wcdpd_data']['initial_price'];
			if ((float)$initial_product_price>0){
				$product_price = RP_WCDPD_WC_Cart::get_cart_item_price_for_display($cart_item);
				$price = ($product_price/$initial_product_price)*$price;
			}

		}else{
			if ( !isset( $pricing->items[ $cart_item_key ] ) ) {
				return $field_price;
			}

			if ( in_array( $pricing->pricing_settings['apply_multiple'], array( 'all', 'first' ) ) ) {
				
				foreach ( $pricing->apply['global'] as $rule_key => $apply ) {
					if ( $deduction = $pricing->apply_rule_to_item( $rule_key, $apply, $cart_item_key, $pricing->items[ $cart_item_key ], FALSE, $price ) ) {

						if ( $apply['if_matched'] == 'other' && isset( $pricing->applied ) && isset( $pricing->applied['global'] ) ) {
							if ( count( $pricing->applied['global'] ) > 1 || !isset( $pricing->applied['global'][ $rule_key ] ) ) {
								continue;
							}
						}

						$pricing->applied['global'][ $rule_key ] = 1;
						$price = $price - $deduction;
					}
				}

			} elseif ( $pricing->pricing_settings['apply_multiple'] == 'biggest' ) {

				$price_deductions = array();

				foreach ( $pricing->apply['global'] as $rule_key => $apply ) {

					if ( $apply['if_matched'] == 'other' && isset( $pricing->applied ) && isset( $pricing->applied['global'] ) ) {
						if ( count( $pricing->applied['global'] ) > 1 || !isset( $pricing->applied['global'][ $rule_key ] ) ) {
							continue;
						}
					}

					if ( $deduction = $pricing->apply_rule_to_item( $rule_key, $apply, $cart_item_key, $pricing->items[ $cart_item_key ], FALSE ) ) {
						$price_deductions[ $rule_key ] = $deduction;
					}
				}

				if ( !empty( $price_deductions ) ) {
					$max_deduction = max( $price_deductions );
					$rule_key = array_search( $max_deduction, $price_deductions );
					$pricing->applied['global'][ $rule_key ] = 1;
					$price = $price - $max_deduction;
				}

			}
		}

		// Make sure price is not negative
		// $price = ($price < 0) ? 0 : $price;

		if ( $price != $original_price ) {
			return $price;
		} else {
			return $field_price;
		}
	}

	// get WooCommerce Dynamic Pricing & Discounts price rules
	public function get_RP_WCDPD( $field_price = NULL, $product, $cart_item_key = NULL ) {
		$price = NULL;

		if ( class_exists( 'RP_WCDPD' ) && class_exists( 'RP_WCDPD_Pricing' ) ) {

			$tm_RP_WCDPD = RP_WCDPD::get_instance();

			$selected_rule = NULL;

			$dpd_version_compare = version_compare( RP_WCDPD_VERSION, '1.0.13', '<' );
			$dpd_version_compare2 = version_compare( RP_WCDPD_VERSION, '2.0', '>=' );

			if ( $field_price !== NULL && $cart_item_key !== NULL ) {
				if ($dpd_version_compare2){
					return $this->get_RP_WCDPD_single( $field_price, $cart_item_key );
				}else{
					return $this->get_RP_WCDPD_single( $field_price, $cart_item_key, $tm_RP_WCDPD->pricing );
				}				
			}

			// Iterate over pricing rules and use the first one that has this product in conditions (or does not have if condition "not in list")
			if ( !$dpd_version_compare2 && isset( $tm_RP_WCDPD->opt['pricing']['sets'] )
				&& count( $tm_RP_WCDPD->opt['pricing']['sets'] )
			) {
				foreach ( $tm_RP_WCDPD->opt['pricing']['sets'] as $rule_key => $rule ) {
					if ( $rule['method'] == 'quantity' && $validated_rule = RP_WCDPD_Pricing::validate_rule( $rule ) ) {
						if ( $dpd_version_compare ) {
							if ( $validated_rule['selection_method'] == 'all' && $tm_RP_WCDPD->user_matches_rule( $validated_rule['user_method'], $validated_rule['roles'] ) ) {
								$selected_rule = $validated_rule;
								break;
							}
							if ( $validated_rule['selection_method'] == 'categories_include' && count( array_intersect( $tm_RP_WCDPD->get_product_categories( tc_get_id( $product ) ), $validated_rule['categories'] ) ) > 0 && $tm_RP_WCDPD->user_matches_rule( $validated_rule['user_method'], $validated_rule['roles'] ) ) {
								$selected_rule = $validated_rule;
								break;
							}
							if ( $validated_rule['selection_method'] == 'categories_exclude' && count( array_intersect( $tm_RP_WCDPD->get_product_categories( tc_get_id( $product ) ), $validated_rule['categories'] ) ) == 0 && $tm_RP_WCDPD->user_matches_rule( $validated_rule['user_method'], $validated_rule['roles'] ) ) {
								$selected_rule = $validated_rule;
								break;
							}
							if ( $validated_rule['selection_method'] == 'products_include' && in_array( tc_get_id( $product ), $validated_rule['products'] ) && $tm_RP_WCDPD->user_matches_rule( $validated_rule['user_method'], $validated_rule['roles'] ) ) {
								$selected_rule = $validated_rule;
								break;
							}
							if ( $validated_rule['selection_method'] == 'products_exclude' && !in_array( tc_get_id( $product ), $validated_rule['products'] ) && $tm_RP_WCDPD->user_matches_rule( $validated_rule['user_method'], $validated_rule['roles'] ) ) {
								$selected_rule = $validated_rule;
								break;
							}
						} else /*if( $dpd_version_compare2 )*/{
							if ( $validated_rule['selection_method'] == 'all' && $tm_RP_WCDPD->user_matches_rule( $validated_rule ) ) {
								$selected_rule = $validated_rule;
								break;
							}
							if ( $validated_rule['selection_method'] == 'categories_include' && count( array_intersect( $tm_RP_WCDPD->get_product_categories( tc_get_id( $product ) ), $validated_rule['categories'] ) ) > 0 && $tm_RP_WCDPD->user_matches_rule( $validated_rule ) ) {
								$selected_rule = $validated_rule;
								break;
							}
							if ( $validated_rule['selection_method'] == 'categories_exclude' && count( array_intersect( $tm_RP_WCDPD->get_product_categories( tc_get_id( $product ) ), $validated_rule['categories'] ) ) == 0 && $tm_RP_WCDPD->user_matches_rule( $validated_rule ) ) {
								$selected_rule = $validated_rule;
								break;
							}
							if ( $validated_rule['selection_method'] == 'products_include' && in_array( tc_get_id( $product ), $validated_rule['products'] ) && $tm_RP_WCDPD->user_matches_rule( $validated_rule ) ) {
								$selected_rule = $validated_rule;
								break;
							}
							if ( $validated_rule['selection_method'] == 'products_exclude' && !in_array( tc_get_id( $product ), $validated_rule['products'] ) && $tm_RP_WCDPD->user_matches_rule( $validated_rule ) ) {
								$selected_rule = $validated_rule;
								break;
							}
						}
					}
				}
			}

			if ($dpd_version_compare2){
				$price = array();

				$all_rules = array();
				if (!$product->is_type('variable') && !$product->is_type('variation')) {

	                $price['is_multiprice'] = FALSE;

	                $product_rules = RP_WCDPD_Product_Pricing::get_applicable_rules_for_product($product,NULL,true);
	                $all_rules = array();
	                foreach ($product_rules as $k => $rule) {
	                	$all_rules[] = array(
		                    'product'   => $product,
		                    'rule'      =>  $rule,
		                );
	                }

				}else{

					if ($product->is_type('variation')) {
		                $product = RightPress_WC_Legacy::product_variation_get_parent($product);
		            }
		            $variation_rules = array();
		            foreach ($product->get_available_variations() as $variation_data) {
		                $variation = wc_get_product($variation_data['variation_id']);
		                if ($rule = $this->get_applicable_volume_rule($variation)) {
		                    $variation_rules[] = array(
		                        'product'   => $variation,
		                        'rule'      => $rule,
		                    );
		                }
		            }

		            $all_rules = $variation_rules;
		           	$price['is_multiprice'] = FALSE;
				}
				$table_data = array();
				foreach ($all_rules as $single) {
					$_product = $single['product'];
					$_rule = $single['rule'];
					if (!$_rule){
						continue;
					}
					$original_price = $_product->get_price();
					if (isset($_rule['quantity_ranges'])){
						$quantity_ranges = $_rule['quantity_ranges'];
						if (RP_WCDPD_Settings::get('promo_volume_pricing_table_missing_ranges') === 'display') {
				            $quantity_ranges = $this->add_missing_ranges($quantity_ranges, $_product);
				        }

				        foreach ($quantity_ranges as $quantity_range) {

				        	switch ($quantity_range['pricing_method']) {
				        		case 'discount__percentage':
				        			$quantity_range['pricing_method'] = 'percentage';
				        			break;
				        		case 'discount__amount':
				        			$quantity_range['pricing_method'] = 'price';
				        			break;
				        		case 'fixed__price':
				        			$quantity_range['pricing_method'] = 'fixed';
				        			break;
				        		
				        		default:
				        			# code...
				        			break;
				        	}
							$table_data[] = array(
				        		'min' => $quantity_range['from'],
				        		'max' => $quantity_range['to'],
				        		'type' => $quantity_range['pricing_method'],
				        		'value' => $quantity_range['pricing_value'],
				        	);

				        }
					}else{
						if (isset($_rule['pricing_method']) && isset($_rule['pricing_value'])){
							$table_data[] = array(
					        	'min' => 1,
					        	'max' => '',
					       		'type' => $_rule['pricing_method'],
					       		'value' => $_rule['pricing_value'],
					       	);	
						}elseif (isset($_rule['group_pricing_method']) && isset($_rule['group_pricing_value'])){
							$table_data[] = array(
					        	'min' => 1,
					        	'max' => '',
					       		'type' => $_rule['group_pricing_method'],
					       		'value' => $_rule['group_pricing_value'],
					       	);	
						}
						
					}					
					
				}

				$price['rules'] = $table_data;

			}
			elseif ( is_array( $selected_rule ) ) {

				// Quantity
				if ( $selected_rule['method'] == 'quantity' && isset( $selected_rule['pricing'] ) && in_array( $selected_rule['quantities_based_on'], array( 'exclusive_product', 'exclusive_variation', 'exclusive_configuration' ) ) ) {

					if ( tc_get_product_type( $product ) == 'variable' || tc_get_product_type( $product ) == 'variable-subscription' ) {
						$product_variations = $product->get_available_variations();
					}

					// For variable products only - check if prices differ for different variations
					$multiprice_variable_product = FALSE;

					if ( (tc_get_product_type( $product ) == 'variable' || tc_get_product_type( $product ) == 'variable') && !empty( $product_variations ) ) {
						$last_product_variation = array_slice( $product_variations, -1 );
						if (class_exists('RightPress_WC_Legacy') && class_exists('RightPress_Helper')){
							$last_product_variation_object = RightPress_Helper::wc_get_product($last_product_variation[0]['variation_id']);
						}else{
							$last_product_variation_object = new WC_Product_Variable( $last_product_variation[0]['variation_id'] );	
						}						
						$last_product_variation_price = $last_product_variation_object->get_price();

						foreach ( $product_variations as $variation ) {
							if (class_exists('RightPress_WC_Legacy') && class_exists('RightPress_Helper')){
								$variation_object = RightPress_Helper::wc_get_product($variation['variation_id']);
							}else{
								$variation_object = new WC_Product_Variable( $variation['variation_id'] );
							}							

							if ( $variation_object->get_price() != $last_product_variation_price ) {
								$multiprice_variable_product = TRUE;
								break;
							}
						}
					}

					if ( $multiprice_variable_product ) {
						$variation_table_data = array();

						foreach ( $product_variations as $variation ) {
							if (class_exists('RightPress_WC_Legacy') && class_exists('RightPress_Helper')){
								$variation_product = RightPress_Helper::wc_get_product($variation['variation_id']);
							}else{
								$variation_product = new WC_Product_Variation( $variation['variation_id'] );
							}							
							$variation_table_data[ $variation['variation_id'] ] = $tm_RP_WCDPD->pricing_table_calculate_adjusted_prices( $selected_rule['pricing'], $variation_product->get_price() );
						}
						$price = array();
						$price['is_multiprice'] = TRUE;
						$price['rules'] = $variation_table_data;
					} else {
						if ( tc_get_product_type( $product ) == 'variable' && !empty( $product_variations ) ) {
							if (class_exists('RightPress_WC_Legacy') && class_exists('RightPress_Helper')){
								$variation_product = RightPress_Helper::wc_get_product($last_product_variation[0]['variation_id']);
							}else{
								$variation_product = new WC_Product_Variation( $last_product_variation[0]['variation_id'] );
							}							
							$table_data = $tm_RP_WCDPD->pricing_table_calculate_adjusted_prices( $selected_rule['pricing'], $variation_product->get_price() );
						} else {
							$table_data = $tm_RP_WCDPD->pricing_table_calculate_adjusted_prices( $selected_rule['pricing'], $product->get_price() );
						}
						$price = array();
						$price['is_multiprice'] = FALSE;
						$price['rules'] = $table_data;
					}
				}

			}
		}
		if ( $field_price !== NULL ) {
			$price = $field_price;
		}

		return $price;
	}

}

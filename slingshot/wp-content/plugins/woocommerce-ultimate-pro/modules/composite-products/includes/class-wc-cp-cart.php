<?php
/**
 * WC_CP_Cart class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 * @since    2.2.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Composite products cart API and hooks.
 *
 * @class    WC_CP_Cart
 * @version  3.11.3
 */

class WC_CP_Cart {

	/**
	 * Globally accessible validation context for 'validate_composite_configuration'.
	 * Possible values: 'add-to-cart'|'cart'.
	 *
	 * @var string
	 */
	public static $validation_context = 'add-to-cart';

	/**
	 * The single instance of the class.
	 * @var WC_CP_Cart
	 *
	 * @since 3.7.0
	 */
	protected static $_instance = null;

	/**
	 * Main WC_CP_Cart instance.
	 *
	 * Ensures only one instance of WC_CP_Cart is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_CP_Cart
	 * @since  3.7.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 3.7.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'ultimatewoo-pro' ), '3.7.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 3.7.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'ultimatewoo-pro' ), '3.7.0' );
	}

	/*
	 * Setup hooks.
	 */
	public function __construct() {

		// Validate composite configuration on adding-to-cart.
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 10, 6 );

		// Validate cart quantity updates.
		add_filter( 'woocommerce_update_cart_validation', array( $this, 'update_cart_validation' ), 10, 4 );

		// Validate composite configuration in cart.
		add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart_items' ), 15 );

		// Add composite configuration data to all composited items.
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 2 );

		// Add composited items to the cart.
		add_action( 'woocommerce_add_to_cart', array( $this, 'add_items_to_cart' ), 10, 6 );

		// Modify cart item data for composite products on first add.
		add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item_filter' ), 11, 2 );

		// Load composite data from session.
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 11, 2 );

		// Refresh composite configuration fields.
		add_filter( 'woocommerce_composite_container_cart_item', array( $this, 'update_composite_container_cart_item_configuration' ), 10, 2 );
		add_filter( 'woocommerce_composited_cart_item', array( $this, 'update_composited_cart_item_configuration' ), 10, 2 );

		// Ensure no orphans are in the cart at this point.
		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'cart_loaded_from_session' ), 11 );

		// Control modification of composited items' quantity.
		add_filter( 'woocommerce_cart_item_quantity', array( $this, 'cart_item_quantity' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'cart_item_remove_link' ), 10, 2 );

		// Sync quantities of children with parent.
		add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'update_quantity_in_cart' ), 1, 2 );
		add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'update_quantity_in_cart' ) );

		// Put back cart item data to allow re-ordering of composites.
		add_filter( 'woocommerce_order_again_cart_item_data', array( $this, 'order_again' ), 10, 3 );

		// Filter cart item price.
		add_filter( 'woocommerce_cart_item_price', array( $this, 'cart_item_price' ), 11, 3 );

		// Modify cart items subtotals depending on how components are priced.
		add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'item_subtotal' ), 11, 3 );
		add_filter( 'woocommerce_checkout_item_subtotal', array( $this, 'item_subtotal' ), 11, 3 );

		// Remove/restore composited items when the parent gets removed/restored.
		add_action( 'woocommerce_cart_item_removed', array( $this, 'cart_item_removed' ), 10, 2 );
		add_action( 'woocommerce_cart_item_restored', array( $this, 'cart_item_restored' ), 10, 2 );

		// Shipping fix - ensure that non-virtual containers/children, which are shipped, have a valid price that can be used for insurance calculations.
		// Additionally, composited item weights may have to be added in the container.
		add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'cart_shipping_packages' ), 6 );

		// Coupons - inherit children coupon validity from parent.
		add_filter( 'woocommerce_coupon_is_valid_for_product', array( $this, 'coupon_validity' ), 10, 4 );
	}

	/*
	|--------------------------------------------------------------------------
	| API Methods.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Adds a composite to the cart. Relies on specifying a composite configuration array with all necessary data - @see 'get_posted_composite_configuration()' for details.
	 *
	 * @param  mixed  $product_id      ID of the composite to add to the cart.
	 * @param  mixed  $quantity        Quantity of the composite.
	 * @param  array  $configuration   Composite configuration - @see 'get_posted_composite_configuration()'.
	 * @param  array  $cart_item_data  Custom cart item data to pass to 'WC_Cart::add_to_cart()'.
	 * @return string|WP_Error
	 */
	public function add_composite_to_cart( $product_id, $quantity, $configuration = array(), $cart_item_data = array() ) {

		$composite     = wc_get_product( $product_id );
		$added_to_cart = false;

		if ( $composite ) {

			if ( $this->validate_composite_configuration( $composite, $quantity, $configuration ) ) {
				$added_to_cart = WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), array_merge( $cart_item_data, array( 'composite_data' => $configuration, 'composite_children' => array() ) ) );
			} else {

				// No other way to collect notices reliably, including notices from 3rd party extensions.
				$notices = wc_get_notices( 'error' );
				$message = __( 'The submitted composite configuration could not be added to the cart.', 'ultimatewoo-pro' );

				$added_to_cart = new WP_Error( 'woocommerce_composite_configuration', $message, array( 'notices' => $notices ) );
			}

		} else {
			$message       = __( 'A composite with this ID does not exist.', 'ultimatewoo-pro' );
			$added_to_cart = new WP_Error( 'woocommerce_composite_invalid', $message );
		}

		return $added_to_cart;
	}

	/**
	 * Parses a composite configuration array to ensure that all mandatory cart item data fields are present.
	 * Can also be used to get an array with the minimum required data to fill in before calling 'add_composite_to_cart'.
	 *
	 * @param  WC_Product_Composite  $composite      Composite product whose configuration is being parsed or generated.
	 * @param  array                 $configuration  Initial configuration array to parse. Leave empty to get a minimum array that you can fill with data - @see 'get_posted_composite_configuration()'.
	 * @param  boolean               $strict_mode    Set true to initialize component selection IDs to an empty string if undefined in the source array.
	 * @return array
	 */
	public function parse_composite_configuration( $composite, $configuration = array(), $strict_mode = false ) {

		$components           = $composite->get_components();
		$parsed_configuration = array();

		foreach ( $components as $component_id => $component ) {

			$component_configuration = isset( $configuration[ $component_id ] ) ? $configuration[ $component_id ] : array();

			$defaults = array(
				'product_id' => $strict_mode ? '' : $component->get_default_option(),
				'quantity'   => $component->get_quantity( 'min' )
			);

			$parsed_configuration[ $component_id ] = wp_parse_args( $component_configuration, $defaults );

			$parsed_configuration[ $component_id ][ 'quantity_min' ] = $component->get_quantity( 'min' );
			$parsed_configuration[ $component_id ][ 'quantity_max' ] = $component->get_quantity( 'max' );
			$parsed_configuration[ $component_id ][ 'discount' ]     = $component->get_discount();
			$parsed_configuration[ $component_id ][ 'optional' ]     = $component->is_optional() ? 'yes' : 'no';
			$parsed_configuration[ $component_id ][ 'static' ]       = $component->is_static() ? 'yes' : 'no';
			$parsed_configuration[ $component_id ][ 'title' ]        = $component->get_title();
			$parsed_configuration[ $component_id ][ 'composite_id' ] = WC_CP_Core_Compatibility::get_id( $composite );

			if ( $parsed_configuration[ $component_id ][ 'product_id' ] > 0 ) {

				$product_id = $parsed_configuration[ $component_id ][ 'product_id' ];
				// Store the product type.
				$parsed_configuration[ $component_id ][ 'type' ] = WC_CP_Core_Compatibility::get_product_type( $product_id );
			}
		}

		return $parsed_configuration;
	}

	/**
	 * Build composite configuration array from posted data. Array example:
	 *
	 *    $config = array(
	 *        134567890 => array(                       // ID of component.
	 *            'product_id'        => 15,            // ID of selected option.
	 *            'quantity'          => 2,             // Qty of selected product, will fall back to component min.
	 *            'discount'          => 50.0,          // Component discount, defaults to the defined value.
	 *            'attributes'        => array(         // Array of selected variation attribute names, sanitized.
	 *                'attribute_color' => 'black',
	 *                'attribute_size'  => 'medium'
	 *             ),
	 *            'variation_id'      => 43             // ID of chosen variation, if applicable.
	 *        )
	 *    );
	 *
	 * @param  mixed  $composite
	 * @return array
	 */
	public function get_posted_composite_configuration( $composite ) {

		$posted_config = array();

		if ( is_numeric( $composite ) ) {
			$composite = wc_get_product( $composite );
		}

		if ( is_object( $composite ) && 'composite' === $composite->get_type() ) {

			/*
			 * Choose between $_POST or $_GET for grabbing data.
			 * We will not rely on $_REQUEST because a field name may not exist in $_POST but may well exist in $_GET, for instance when editing a composite from the cart.
			 */

			$posted_data = $_POST;

			if ( empty( $_POST[ 'add-to-cart' ] ) && ! empty( $_GET[ 'add-to-cart' ] ) ) {
				$posted_data = $_GET;
			}

			if ( isset( $posted_data[ 'wccp_component_selection' ] ) && is_array( $posted_data[ 'wccp_component_selection' ] ) ) {

				// Get components.
				$components = $composite->get_components();

				foreach ( $components as $component_id => $component ) {

					$composited_product_id                = ! empty( $posted_data[ 'wccp_component_selection' ][ $component_id ] ) ? absint( $posted_data[ 'wccp_component_selection' ][ $component_id ] ) : '';
					$composited_product_quantity          = isset( $posted_data[ 'wccp_component_quantity' ][ $component_id ] ) ? absint( $posted_data[ 'wccp_component_quantity' ][ $component_id ] ) : $component->get_quantity( 'min' );
					$composited_product_sold_individually = false;

					if ( $composited_product_id ) {

						$composited_product_wrapper = $component->get_option( $composited_product_id );

						if ( ! $composited_product_wrapper ) {
							continue;
						}

						$composited_product                   = $composited_product_wrapper->get_product();
						$composited_product_type              = $composited_product->get_type();
						$composited_product_sold_individually = $composited_product->is_sold_individually();

						if ( $composited_product_sold_individually && $composited_product_quantity > 1 ) {
							$composited_product_quantity = 1;
						}
					}

					$posted_config[ $component_id ] = array();

					$posted_config[ $component_id ][ 'product_id' ] = $composited_product_id;
					$posted_config[ $component_id ][ 'quantity' ]   = $composited_product_quantity;

					// Continue when selected product is 'None'.
					if ( ! $composited_product_id ) {
						continue;
					}

					if ( 'variable' === $composited_product_type ) {

						$attributes_config 	= array();
						$attributes 		= $composited_product->get_attributes();

						foreach ( $attributes as $attribute ) {

							if ( ! $attribute[ 'is_variation' ] ) {
								continue;
							}

							$taxonomy = WC_CP_Core_Compatibility::wc_variation_attribute_name( $attribute[ 'name' ] );

							if ( isset( $posted_data[ 'wccp_' . $taxonomy ][ $component_id ] ) ) {

								 // Get value from post data
								if ( $attribute[ 'is_taxonomy' ] ) {
									$value = sanitize_title( stripslashes( $posted_data[ 'wccp_' . $taxonomy ][ $component_id ] ) );
								} else {
									$value = wc_clean( stripslashes( $posted_data[ 'wccp_' . $taxonomy ][ $component_id ] ) );
								}

								$attributes_config[ $taxonomy ] = $value;
							}
						}

						$posted_config[ $component_id ][ 'attributes' ]   = $attributes_config;
						$posted_config[ $component_id ][ 'variation_id' ] = isset( $posted_data[ 'wccp_variation_id' ][ $component_id ] ) ? wc_clean( $posted_data[ 'wccp_variation_id' ][ $component_id ] ) : '';
					}
				}
			}
		}

		$posted_config = $this->parse_composite_configuration( $composite, $posted_config, true );

		return $posted_config;
	}

	/**
	 * Validates the components in a composite configuration.
	 *
	 * @param  mixed   $product
	 * @param  int     $composite_quantity
	 * @param  array   $configuration
	 * @param  string  $context
	 * @return boolean
	 */
	public function validate_composite_configuration( $composite, $composite_quantity, $configuration, $context = 'add-to-cart' ) {

		$passes_validation = true;

		if ( is_numeric( $composite ) ) {
			$composite = wc_get_product( $composite );
		}

		if ( is_object( $composite ) && 'composite' === $composite->get_type() ) {

			self::$validation_context = $context;

			$composite_id    = WC_CP_Core_Compatibility::get_id( $composite );
			$composite_title = $composite->get_title();
			$components      = $composite->get_components();
			$validation_data = array();

			// If a stock-managed product / variation exists in the bundle multiple times, its stock will be checked only once for the sum of all bundled quantities.
			// The WC_CP_Stock_Manager class does exactly that.
			$composited_stock = new WC_CP_Stock_Manager( $composite );

			foreach ( $components as $component_id => $component ) {

				$component_title = $component->get_title( true );

				$validation_data[ $component_id ] = array();

				/*
				 * Store product selection and quantity data for validation later.
				 */
				$composited_product_id = ! empty( $configuration[ $component_id ][ 'product_id' ] ) ? strval( absint( $configuration[ $component_id ][ 'product_id' ] ) ) : '0';

				$validation_data[ $component_id ][ 'product_id' ] = $composited_product_id;
				$validation_data[ $component_id ][ 'optional' ]   = $component->is_optional() ? 'yes' : 'no';
				$validation_data[ $component_id ][ 'title' ]      = $component_title;

				if ( '0' === $composited_product_id ) {
					continue;
				}

				// Prevent people from fucking around - only valid component options can be added to the cart.
				if ( ! in_array( $composited_product_id, $component->get_options() ) ) {
					if ( in_array( $context, array( 'add-to-cart', 'add-to-order' ) ) ) {
						wc_add_notice( sprintf( __( 'This &quot;%1$s&quot; configuration cannot be added to the cart. Please choose a valid &quot;%2$s&quot; option&hellip;', 'ultimatewoo-pro' ), $composite_title, $component_title ), 'error' );
					} elseif ( 'cart' === $context ) {
						wc_add_notice( sprintf( __( 'The &quot;%1$s&quot; configuration found in your cart cannot be purchased &ndash; the chosen &quot;%2$s&quot; option is unavailable.', 'ultimatewoo-pro' ), $composite_title, $component_title ), 'error' );
					}
					return false;
				}

				// Store quantity min/max data for later use.
				$item_quantity_min = $component->get_quantity( 'min' );
				$item_quantity_max = $component->get_quantity( 'max' );

				$validation_data[ $component_id ][ 'quantity_min' ] = $item_quantity_min;
				$validation_data[ $component_id ][ 'quantity_max' ] = $item_quantity_max;

				// Store quantity for validation.
				$item_quantity = isset( $configuration[ $component_id ][ 'quantity' ] ) ? absint( $configuration[ $component_id ][ 'quantity' ] ) : $item_quantity_min;
				$quantity      = $item_quantity * $composite_quantity;

				if ( ! $composited_product_wrapper = $composite->get_component_option( $component_id, $composited_product_id ) ) {
					if ( in_array( $context, array( 'add-to-cart', 'add-to-order' ) ) ) {
						wc_add_notice( sprintf( __( 'This &quot;%1$s&quot; configuration cannot be added to the cart. Please choose another &quot;%2$s&quot; option&hellip;', 'ultimatewoo-pro' ), $composite_title, $component_title ), 'error' );
					} elseif ( 'cart' === $context ) {
						wc_add_notice( sprintf( __( 'The &quot;%1$s&quot; configuration found in your cart cannot be purchased &ndash; the chosen &quot;%2$s&quot; option is unavailable.', 'ultimatewoo-pro' ), $composite_title, $component_title ), 'error' );
					}
					return false;
				}

				$composited_product      = $composited_product_wrapper->get_product();
				$composited_product_type = $composited_product->get_type();
				$item_sold_individually  = $composited_product->is_sold_individually();

				if ( $item_sold_individually && $quantity > 1 ) {
					$quantity = 1;
				}

				// Save data for validation.
				$validation_data[ $component_id ][ 'quantity' ]          = $item_quantity;
				$validation_data[ $component_id ][ 'sold_individually' ] = $item_sold_individually ? 'yes' : 'no';

				if ( $quantity === 0 ) {
					continue;
				}

				/*
				 * Validate attributes.
				 */

				if ( 'variable' === $composited_product_type ) {

					$composited_variation_id = isset( $configuration[ $component_id ][ 'variation_id' ] ) ? $configuration[ $component_id ][ 'variation_id' ] : '';
					$composited_variation    = $composited_variation_id ? wc_get_product( $composited_variation_id ) : false;

					if ( $composited_variation ) {
						// Add item for stock validation.
						$composited_stock->add_item( $composited_product_id, $composited_variation, $quantity );
						// Save variation ID for validation.
						$validation_data[ $component_id ][ 'variation_id' ] = $composited_variation_id;
					}

					// Verify all attributes for the variable product were set.
					$attributes         = $composited_product->get_attributes();
					$variation_data     = array();
					$missing_attributes = array();
					$all_set            = true;

					if ( $composited_variation ) {
						$variation_data = wc_get_product_variation_attributes( $composited_variation_id );
					}

					foreach ( $attributes as $attribute ) {

					    if ( ! $attribute[ 'is_variation' ] ) {
					    	continue;
					    }

					    $taxonomy = WC_CP_Core_Compatibility::wc_variation_attribute_name( $attribute[ 'name' ] );

						if ( isset( $configuration[ $component_id ][ 'attributes' ][ $taxonomy ] ) ) {

							// Get value from post data.
							if ( $attribute[ 'is_taxonomy' ] ) {
								$value = sanitize_title( stripslashes( $configuration[ $component_id ][ 'attributes' ][ $taxonomy ] ) );
							} else {
								$value = wc_clean( stripslashes( $configuration[ $component_id ][ 'attributes' ][ $taxonomy ] ) );
							}

							// Get valid value from variation.
							$valid_value = $variation_data[ $taxonomy ];

							// Allow if valid.
							if ( '' === $valid_value || $valid_value === $value ) {
								continue;
							}

							$missing_attributes[] = wc_attribute_label( $attribute[ 'name' ] );

						} else {
							$missing_attributes[] = wc_attribute_label( $attribute[ 'name' ] );
						}

					    $all_set = false;
					}

					if ( ! $all_set ) {
						if ( $missing_attributes && WC_CP_Core_Compatibility::is_wc_version_gte_2_3() ) {
							$required_fields_notice = sprintf( _n( '%1$s is a required &quot;%2$s&quot; field', '%1$s are required &quot;%2$s&quot; fields', sizeof( $missing_attributes ), 'ultimatewoo-pro' ), wc_format_list_of_items( $missing_attributes ), $component_title );
							if ( in_array( $context, array( 'add-to-cart', 'add-to-order' ) ) ) {
    							wc_add_notice( sprintf( __( 'This &quot;%1$s&quot; configuration cannot be added to the cart. %2$s.', 'ultimatewoo-pro' ), $composite_title, $required_fields_notice ), 'error' );
    						} elseif ( 'cart' === $context ) {
								wc_add_notice( sprintf( __( 'The &quot;%1$s&quot; configuration found in your cart cannot be purchased. %2$s.', 'ultimatewoo-pro' ), $composite_title, $required_fields_notice ), 'error' );
    						}
    						return false;
						} else {
							if ( in_array( $context, array( 'add-to-cart', 'add-to-order' ) ) ) {
    							wc_add_notice( sprintf( __( 'This &quot;%1$s&quot; configuration cannot be added to the cart. Please choose &quot;%2$s&quot; options&hellip;', 'ultimatewoo-pro' ), $composite_title, $component_title ), 'error' );
    						} elseif ( 'cart' === $context ) {
								wc_add_notice( sprintf( __( 'The &quot;%1$s&quot; configuration found in your cart cannot be purchased. &quot;%2$s&quot; is missing some required options.', 'ultimatewoo-pro' ), $composite_title, $component_title ), 'error' );
    						}
							return false;
						}
					}

				} else {
					// Add item for validation.
					$composited_stock->add_item( $composited_product_id, false, $quantity );
				}

				/**
				 * Filter to allow composited products to add extra items to the stock manager.
				 *
				 * @param  mixed   $stock
				 * @param  string  $composite_id
				 * @param  string  $component_id
				 * @param  string  $composited_product_id
				 * @param  int     $quantity
				 */
				$composited_stock->add_stock( apply_filters( 'woocommerce_composite_component_associated_stock', '', $composite_id, $component_id, $composited_product_id, $quantity ) );
			}

			/*
			 * Stock Validation.
			 */

			if ( 'add-to-cart' === $context && false === $composited_stock->validate_stock() ) {
				return false;
			}

			/*
			 * Selections and Quantities Validation.
			 */

			$scenario_meta    = $composite->get_scenario_data();
			$posted_scenarios = ! empty( $_POST[ 'wccp_active_scenarios' ] ) ? array_map( 'wc_clean', explode( ',', $_POST[ 'wccp_active_scenarios' ] ) ) : array();

			if ( ! empty( $posted_scenarios ) ) {
				$scenario_meta = array_intersect_key( $scenario_meta, array_flip( $posted_scenarios ) );
			}

			$composite_configuration = array();

			foreach ( $validation_data as $component_id => $component_validation_data ) {
				$composite_configuration[ $component_id ] = array(
					'product_id'   => absint( $component_validation_data[ 'product_id' ] ),
					'variation_id' => isset( $component_validation_data[ 'variation_id' ] ) ? absint( $component_validation_data[ 'variation_id' ] ) : 0
				);
			}

			// Validate selections.
			$matching_scenarios = $composite->scenarios()->find_matching( $composite_configuration );

			if ( is_wp_error( $matching_scenarios ) ) {

				$error_code = $matching_scenarios->get_error_code();

				if ( in_array( $error_code, array( 'woocommerce_composite_configuration_selection_required', 'woocommerce_composite_configuration_selection_invalid' ) ) ) {

					$error_data = $matching_scenarios->get_error_data( $error_code );

					if ( ! empty( $error_data[ 'component_id' ] ) ) {

						if ( 'woocommerce_composite_configuration_selection_required' === $error_code ) {

							if ( in_array( $context, array( 'add-to-cart', 'add-to-order' ) ) ) {
								wc_add_notice( sprintf( __( 'Please select a &quot;%s&quot; option.', 'ultimatewoo-pro' ), $validation_data[ $error_data[ 'component_id' ] ][ 'title' ] ), 'error' );
							} elseif ( 'cart' === $context ) {
								wc_add_notice( sprintf( __( 'The &quot;%1$s&quot; configuration found in your cart cannot be purchased. A &quot;%2$s&quot; selection is required.', 'ultimatewoo-pro' ), $composite_title, $validation_data[ $error_data[ 'component_id' ] ][ 'title' ] ), 'error' );
							}

						} elseif ( 'woocommerce_composite_configuration_selection_invalid' === $error_code ) {

							if ( in_array( $context, array( 'add-to-cart', 'add-to-order' ) ) ) {
								wc_add_notice( sprintf( __( 'Please select a different &quot;%s&quot; option &mdash; the selected product cannot be purchased at the moment.', 'ultimatewoo-pro' ), $composite_title, $validation_data[ $error_data[ 'component_id' ] ][ 'title' ] ), 'error' );
							} elseif ( 'cart' === $context ) {
								wc_add_notice( sprintf( __( 'The &quot;%1$s&quot; configuration found in your cart cannot be purchased &ndash; the chosen &quot;%2$s&quot; option is unavailable.', 'ultimatewoo-pro' ), $composite_title, $validation_data[ $error_data[ 'component_id' ] ][ 'title' ] ), 'error' );
							}
						}

						return false;
					}

				} elseif ( 'woocommerce_composite_configuration_invalid' === $error_code ) {

					if ( in_array( $context, array( 'add-to-cart', 'add-to-order' ) ) ) {
						wc_add_notice( __( 'The selected options cannot be purchased together. Please select a different configuration and try again.', 'ultimatewoo-pro' ), 'error' );
					} elseif ( 'cart' === $context ) {
						wc_add_notice( sprintf( __( 'The &quot;%1$s&quot; configuration found in your cart cannot be purchased. The selected options cannot be purchased together.', 'ultimatewoo-pro' ), $validation_data[ $error_data[ 'component_id' ] ][ 'title' ] ), 'error' );
					}

					return false;
				}
			}

			// Validate Quantities.
			foreach ( $validation_data as $component_id => $component_validation_data ) {

				// No need to validate the quantity of an empty selection if we have gotten this far.
				if ( '0' === $component_validation_data[ 'product_id' ] ) {
					continue;
				}

				$qty = $component_validation_data[ 'quantity' ];

				// Allow 3rd parties to modify the min/max qty settings of a component conditionally through scenarios.

				/**
				 * 'woocommerce_composite_component_validation_quantity_min' filter.
				 * Validation context for use in custom error messages available via 'WC_CP_Cart::$validation_context'.
				 *
				 * @param  int     $qty_min
				 * @param  string  $component_id
				 * @param  array   $config_data
				 * @param  array   $matching_scenarios
				 * @param  array   $scenario_data
				 * @param  string  $composite_id
				 */
				$qty_min = absint( apply_filters( 'woocommerce_composite_component_validation_quantity_min', $component_validation_data[ 'quantity_min' ], $component_id, $component_validation_data, $matching_scenarios, $composite ) );

				/**
				 * 'woocommerce_composite_component_validation_quantity_max' filter.
				 * Validation context for use in custom error messages available via 'WC_CP_Cart::$validation_context'.
				 *
				 * @param  int     $qty_min
				 * @param  string  $component_id
				 * @param  array   $config_data
				 * @param  array   $matching_scenarios
				 * @param  array   $scenario_data
				 * @param  string  $composite_id
				 */
				$qty_max = absint( apply_filters( 'woocommerce_composite_component_validation_quantity_max', $component_validation_data[ 'quantity_max' ], $component_id, $component_validation_data, $matching_scenarios, $composite ) );

				$sold_individually = $component_validation_data[ 'sold_individually' ];

				if ( $qty < $qty_min && 'yes' !== $sold_individually ) {
					if ( in_array( $context, array( 'add-to-cart', 'add-to-order' ) ) ) {
						wc_add_notice( sprintf( __( 'This &quot;%1$s&quot; configuration cannot be added to the cart. The quantity of &quot;%2$s&quot; cannot be lower than %3$d.', 'ultimatewoo-pro' ), $composite_title, $component_validation_data[ 'title' ], $qty_min ), 'error' );
					} elseif ( 'cart' === $context ) {
						wc_add_notice( sprintf( __( 'The &quot;%1$s&quot; configuration found in your cart cannot be purchased. The quantity of &quot;%2$s&quot; cannot be lower than %3$d.', 'ultimatewoo-pro' ), $composite_title, $component_validation_data[ 'title' ], $qty_min ), 'error' );
					}
					return false;
				} elseif ( $qty_max && $qty > $qty_max ) {
					if ( in_array( $context, array( 'add-to-cart', 'add-to-order' ) ) ) {
						wc_add_notice( sprintf( __( 'This &quot;%1$s&quot; configuration cannot be added to the cart. The quantity of &quot;%2$s&quot; cannot be higher than %3$d.', 'ultimatewoo-pro' ), $composite_title, $component_validation_data[ 'title' ], $qty_max ), 'error' );
					} elseif ( 'cart' === $context ) {
						wc_add_notice( sprintf( __( 'The &quot;%1$s&quot; configuration found in your cart cannot be purchased. The quantity of &quot;%2$s&quot; cannot be higher than %3$d.', 'ultimatewoo-pro' ), $composite_title, $component_validation_data[ 'title' ], $qty_max ), 'error' );
					}
					return false;
				}
			}
		}

		/**
		 * Filter composite configuration validation result.
		 * Validation context for use in custom error messages available via 'WC_CP_Cart::$validation_context'.
		 *
		 * @param  boolean              $result
		 * @param  string               $composite_id
		 * @param  WC_CP_Stock_Manager  $composited_stock
		 * @param  array                $composite_configuration
		 */
		return apply_filters( 'woocommerce_add_to_cart_composite_validation', $passes_validation, $composite_id, $composited_stock, $configuration );
	}

	/**
	 * Outputs a formatted subtotal.
	 *
	 * @param  WC_Product  $product
	 * @param  double      $subtotal
	 * @return string
	 */
	public function format_product_subtotal( $product, $subtotal ) {

		$cart = WC()->cart;

		$taxable = $product->is_taxable();

		// Taxable.
		if ( $taxable ) {

			if ( 'excl' === get_option( 'woocommerce_tax_display_cart' ) ) {

				$product_subtotal = wc_price( $subtotal );

				if ( $cart->prices_include_tax && $cart->tax_total > 0 ) {
					$product_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
				}

			} else {

				$product_subtotal = wc_price( $subtotal );

				if ( ! $cart->prices_include_tax && $cart->tax_total > 0 ) {
					$product_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
				}
			}

		// Non-taxable.
		} else {
			$product_subtotal = wc_price( $subtotal );
		}

		return $product_subtotal;
	}

	/**
	 * Modifies composited cart item virtual status and price depending on composite pricing and shipping strategies.
	 *
	 * @param  array                 $cart_item
	 * @param  WC_Product_Composite  $composite
	 * @return array
	 */
	private function set_composited_cart_item( $cart_item, $composite ) {

		$component_id = $cart_item[ 'composite_item' ];

		// Pricing.
		$cart_item = $this->set_composited_cart_item_price( $cart_item, $component_id, $composite );

		// Shipping.
		if ( $cart_item[ 'data' ]->needs_shipping() ) {

			$component_option = $composite->get_component_option( $component_id, $cart_item[ 'product_id' ] );

			if ( $component_option && false === $component_option->is_shipped_individually() ) {

				/**
				 * 'woocommerce_composited_product_has_bundled_weight' filter.
				 *
				 * When the shipping properties of a component are overridden by its container ("Shipped Individually" option unchecked), the container item weight is assumed static and the bundled product weight is ignored.
				 * You can use this filter to have the weight of bundled products appended to the container weight, instead of ignored, when the "Shipped Individually" option is unchecked.
				 *
				 * @param   boolean               $append_weight
				 * @param   WC_Product            $composited_product
				 * @param   string                $component_id
				 * @param   WC_Product_Composite  $composite_product
				 */
				if ( apply_filters( 'woocommerce_composited_product_has_bundled_weight', false, $cart_item[ 'data' ], $component_id, $composite ) ) {
					WC_CP_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'composited_weight', $cart_item[ 'data' ]->get_weight( 'edit' ) );
				}

				WC_CP_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'composited_value', WC_CP_Core_Compatibility::get_prop( $cart_item[ 'data' ], 'price', 'edit' ) );

				WC_CP_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'virtual', 'yes' );
				WC_CP_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'weight', '' );
			}
		}

		/**
		 * Last chance to filter the component cart item.
		 *
		 * @param  array                 $cart_item
		 * @param  WC_Product_Composite  $composite
		 */
		return apply_filters( 'woocommerce_composited_cart_item', $cart_item, $composite );
	}

	/**
	 * Get composited products prices with discounts.
	 *
	 * @param  int                   $product_id
	 * @param  mixed                 $variation_id
	 * @param  string                $component_id
	 * @param  WC_Product_Composite  $composite
	 * @return double
	 */
	private function set_composited_cart_item_price( $cart_item, $component_id, $composite ) {

		$product_id       = $cart_item[ 'product_id' ];
		$component_option = $composite->get_component_option( $component_id, $product_id );

		if ( ! $component_option ) {
			return $cart_item;
		}

		WC_CP_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'price', $component_option->get_raw_price( $cart_item[ 'data' ], 'cart' ) );

		if ( false === $component_option->is_priced_individually() && false === WC_CP_Core_Compatibility::get_prop( $cart_item[ 'data' ], 'price', 'edit' ) > 0 ) {

			WC_CP_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'regular_price', 0 );
			WC_CP_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'sale_price', '' );
		}

		return $cart_item;
	}

	/**
	 * Set container price equal to the base price.
	 *
	 * @param  array  $cart_item
	 * @return array
	 */
	private function set_composite_container_cart_item( $cart_item ) {

		$composite = $cart_item[ 'data' ];

		/**
		 * Last chance to filter the container cart item.
		 *
		 * @param  array                 $cart_item
		 * @param  WC_Product_Composite  $composite
		 */
		return apply_filters( 'woocommerce_composite_container_cart_item', $cart_item, $composite );
	}

	/**
	 * Refresh parent item configuration fields that might be out-of-date.
	 *
	 * @param  array                 $cart_item
	 * @param  WC_Product_Composite  $composite
	 * @return array
	 */
	public function update_composite_container_cart_item_configuration( $cart_item, $composite ) {

		if ( isset( $cart_item[ 'composite_data' ] ) ) {
			$cart_item[ 'composite_data' ] = $this->parse_composite_configuration( $composite, $cart_item[ 'composite_data' ], true );
		}

		return $cart_item;
	}

	/**
	 * Refresh child item configuration fields that might be out-of-date.
	 *
	 * @param  array                 $cart_item
	 * @param  WC_Product_Composite  $composite
	 * @return array
	 */
	public function update_composited_cart_item_configuration( $cart_item, $composite ) {

		if ( $composite_container_item = wc_cp_get_composited_cart_item_container( $cart_item ) ) {
			$cart_item[ 'composite_data' ] = $composite_container_item[ 'composite_data' ];
		}

		return $cart_item;
	}

	/**
	 * Add a composited product to the cart. Must be done without updating session data, recalculating totals or calling 'woocommerce_add_to_cart' recursively.
	 * For the recursion issue, see: https://core.trac.wordpress.org/ticket/17817.
	 *
	 * @param int     $composite_id
	 * @param int     $product_id
	 * @param string  $quantity
	 * @param int     $variation_id
	 * @param array   $variation
	 * @param array   $cart_item_data
	 * @return bool
	 */
	private function composited_add_to_cart( $composite_id, $product_id, $quantity = 1, $variation_id = '', $variation = '', $cart_item_data ) {

		if ( $quantity <= 0 ) {
			return false;
		}

		// Load cart item data when adding to cart. WC core filter.
		$cart_item_data = ( array ) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id );

		// Generate a ID based on product ID, variation ID, variation data, and other cart item data.
		$cart_id = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

		// See if this product and its options is already in the cart.
		$cart_item_key = WC()->cart->find_product_in_cart( $cart_id );

		// Ensure we don't add a variation to the cart directly by variation ID.
		if ( 'product_variation' == get_post_type( $product_id ) ) {
			$variation_id = $product_id;
			$product_id   = wp_get_post_parent_id( $variation_id );
		}

		// Get the product
		$product_data = wc_get_product( $variation_id ? $variation_id : $product_id );

		// If cart_item_key is set, the item is already in the cart and its quantity will be handled by update_quantity_in_cart.
		if ( ! $cart_item_key ) {

			$cart_item_key = $cart_id;

			// Add item after merging with $cart_item_data - allow plugins and 'add_cart_item_filter' to modify cart item. WC core filter.
			WC()->cart->cart_contents[ $cart_item_key ] = apply_filters( 'woocommerce_add_cart_item', array_merge( $cart_item_data, array(
				'product_id'   => absint( $product_id ),
				'variation_id' => absint( $variation_id ),
				'variation'    => $variation,
				'quantity'     => $quantity,
				'data'         => $product_data
			) ), $cart_item_key );
		}

		/**
		 * Action 'woocommerce_composited_add_to_cart'.
		 *
		 * @param  string  $cart_item_key
		 * @param  string  $product_id
		 * @param  string  $quantity
		 * @param  string  $variation_id
		 * @param  array   $variation
		 * @param  array   $cart_item_data
		 * @param  string  $composite_id
		 */
		do_action( 'woocommerce_composited_add_to_cart', $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data, $composite_id );

		return $cart_item_key;
	}

	/*
	|--------------------------------------------------------------------------
	| Filter Hooks.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Redirect to the cart when updating a composite cart item.
	 *
	 * @param  string  $url
	 * @return string
	 */
	public function update_composite_cart_redirect( $url ) {
		return WC()->cart->get_cart_url();
	}

	/**
	 * Filter the displayed notice after redirecting to the cart when updating a composite cart item.
	 *
	 * @param  string  $url
	 * @return string
	 */
	public function update_composite_cart_redirect_message( $message ) {
		return __( 'Cart updated.', 'woocommerce' );
	}

	/**
	 * Check composite cart item configurations on cart load.
	 */
	public function check_cart_items() {
		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {

			if ( wc_cp_is_composite_container_cart_item( $cart_item ) ) {

				$configuration = isset( $cart_item[ 'composite_data' ] ) ? $cart_item[ 'composite_data' ] : $this->get_posted_composite_configuration( $cart_item[ 'data' ] );

				self::$validation_context = 'cart';
				$this->validate_composite_configuration( $cart_item[ 'data' ], $cart_item[ 'quantity' ], $configuration, self::$validation_context );
				self::$validation_context = 'add-to-cart';
			}
		}
	}

	/**
	 * Validates that all composited items chosen can be added-to-cart before actually starting to add items.
	 *
	 * @param  bool  $add
	 * @param  int   $product_id
	 * @param  int   $quantity
	 * @return bool
	 */
	public function add_to_cart_validation( $add, $product_id, $quantity, $variation_id = '', $variations = array(), $cart_item_data = array() ) {

		// Get product type.
		$product_type = WC_CP_Core_Compatibility::get_product_type( $product_id );

		// Prevent composited items from getting validated - they will be added by the container item.
		if ( isset( $cart_item_data[ 'is_order_again_composited' ] ) ) {
			$add = false;
		}

		if ( $add && 'composite' === $product_type ) {

			// Get product.
			$composite = wc_get_product( $product_id );

			if ( ! $composite ) {
				return false;
			}

			$configuration = isset( $cart_item_data[ 'composite_data' ] ) ? $cart_item_data[ 'composite_data' ] : $this->get_posted_composite_configuration( $composite );

			if ( ! $this->validate_composite_configuration( $composite, $quantity, $configuration ) ) {
				return false;
			}

			foreach ( $configuration as $component_id => $component_configuration ) {
				/**
				 * Filter configuration validation result.
				 *
				 * @param  boolean               $result
				 * @param  string                $product_id
				 * @param  string                $component_id
				 * @param  string                $composited_product_id
				 * @param  int                   $composite_quantity
				 * @param  array                 $cart_item_data
				 * @param  WC_Product_Composite  $composite
				 */
				if ( false === apply_filters( 'woocommerce_composite_component_add_to_cart_validation', true, $product_id, $component_id, $component_configuration[ 'product_id' ], $quantity, $cart_item_data, $composite ) ) {
					return false;
				}
			}
		}

		return $add;
	}

	/**
	 * Adds configuration-specific cart-item data.
	 *
	 * @param  array  $cart_item_data
	 * @param  int    $product_id
	 * @return void
	 */
	public function add_cart_item_data( $cart_item_data, $product_id ) {

		// Get product type.
		$product_type = WC_CP_Core_Compatibility::get_product_type( $product_id );

		if ( 'composite' === $product_type ) {

			$updating_composite_in_cart = false;

			// Updating composite in cart?
			if ( isset( $_POST[ 'update-composite' ] ) ) {

				$updating_cart_key = wc_clean( $_POST[ 'update-composite' ] );

				if ( isset( WC()->cart->cart_contents[ $updating_cart_key ] ) ) {

					$updating_composite_in_cart = true;

					// Remove.
					WC()->cart->remove_cart_item( $updating_cart_key );

					// Redirect to cart.
					add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'update_composite_cart_redirect' ) );

					// Edit notice.
					add_filter( WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ? 'wc_add_to_cart_message_html' : 'wc_add_to_cart_message', array( $this, 'update_composite_cart_redirect_message' ) );
				}
			}

			// Use posted data to create a unique array with the composite configuration, if needed.
			if ( ! isset( $cart_item_data[ 'composite_data' ] ) ) {

				$configuration = $this->get_posted_composite_configuration( $product_id );

				foreach ( $configuration as $component_id => $component_configuration ) {
					/**
					 * Filter component configuration identifier. Use this hook to add configuration data for 3rd party input fields.
					 * Any custom data added here can be copied into the child cart item data array using the 'woocommerce_composited_cart_item_data' filter.
					 *
					 * @param   array   $component_configuration
					 * @param   string  $component_id
					 * @param   mixed   $product_id
					 */
					$configuration[ $component_id ] = apply_filters( 'woocommerce_composite_component_cart_item_identifier', $component_configuration, $component_id, $product_id );
				}

				$cart_item_data[ 'composite_data' ] = $configuration;

				// Check "Sold Individually" option context.
				if ( false === $updating_composite_in_cart && ( $composite = wc_get_product( $product_id ) ) && $composite->is_sold_individually() ) {
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
						if ( $product_id === $cart_item[ 'product_id' ] && 'product' === $composite->get_sold_individually_context() ) {
							throw new Exception( sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', WC()->cart->get_cart_url(), __( 'View Cart', 'woocommerce' ), sprintf( __( 'You cannot add another &quot;%s&quot; to your cart.', 'woocommerce' ), $composite->get_title() ) ) );
						} elseif ( wc_cp_is_composite_container_cart_item( $cart_item ) && $configuration === $cart_item[ 'composite_data' ] ) {
							throw new Exception( sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', WC()->cart->get_cart_url(), __( 'View Cart', 'woocommerce' ), sprintf( __( 'You have already added an identical &quot;%s&quot; to your cart. You cannot add another one.', 'ultimatewoo-pro' ), $composite->get_title() ) ) );
						}
					}
				}
			}

			// Prepare additional data for later use.
			if ( ! isset( $cart_item_data[ 'composite_children' ] ) ) {
				$cart_item_data[ 'composite_children' ] = array();
			}
		}

		return $cart_item_data;
	}

	/**
	 * Adds composited items to the cart.
	 *
	 * @param  string  $composite_cart_key
	 * @param  int     $composite_id
	 * @param  int     $composite_quantity
	 * @param  int     $variation_id
	 * @param  array   $variation
	 * @param  array   $cart_item_data
	 * @return void
	 */
	public function add_items_to_cart( $composite_cart_key, $composite_id, $composite_quantity, $variation_id, $variation, $cart_item_data ) {

		// Runs when adding container item - adds composited items.
		if ( wc_cp_is_composite_container_cart_item( $cart_item_data ) ) {

			// Only attempt to add composited items if they don't already exist.
			foreach ( WC()->cart->cart_contents as $cart_key => $cart_value ) {
				if ( isset( $cart_value[ 'composite_data' ] ) && isset( $cart_value[ 'composite_parent' ] ) && $composite_cart_key == $cart_value[ 'composite_parent' ] ) {
					return;
				}
			}

			// Results in a unique cart ID hash, so that composited and non-composited versions of the same product will be added separately to the cart.
			$composited_cart_data = array( 'composite_parent' => $composite_cart_key, 'composite_data' => $cart_item_data[ 'composite_data' ] );

			// Now add all items - yay!
			foreach ( $cart_item_data[ 'composite_data' ] as $component_id => $component_configuration ) {

				$composited_item_cart_data = $composited_cart_data;

				$composited_item_cart_data[ 'composite_item' ] = $component_id;

				$composited_product_id = $component_configuration[ 'product_id' ];
				$variation_id          = '';
				$variations            = array();

				if ( '' === $composited_product_id ) {
					continue;
				}

				// Get product type.
				$composited_product_type = WC_CP_Core_Compatibility::get_product_type( $composited_product_id );

				$item_quantity = $component_configuration[ 'quantity' ];
				$quantity      = $item_quantity * $composite_quantity;

				if ( $quantity === 0 ) {
					continue;
				}

				if ( 'variable' === $composited_product_type ) {

					$variation_id = ( int ) $component_configuration[ 'variation_id' ];
					$variations   = $component_configuration[ 'attributes' ];

				} elseif ( 'bundle' === $composited_product_type ) {

					$composited_item_cart_data[ 'stamp' ]         = $component_configuration[ 'stamp' ];
					$composited_item_cart_data[ 'bundled_items' ] = array();
				}

				/**
				 * Filter to allow loading child cart item data from the parent cart item data array.
				 *
				 * @param  array  $component_cart_item_data
				 * @param  array  $composite_cart_item_data
				 */
				$composited_item_cart_data = apply_filters( 'woocommerce_composited_cart_item_data', $composited_item_cart_data, $cart_item_data );

				/**
				 * Action 'woocommerce_composited_product_before_add_to_cart'.
				 *
				 * @param  string  $composited_product_id
				 * @param  string  $quantity
				 * @param  string  $variation_id
				 * @param  array   $variations
				 * @param  array   $composited_item_cart_data
				 *
				 * @hooked WC_CP_Addons_Compatibility::before_composited_add_to_cart()
				 */
				do_action( 'woocommerce_composited_product_before_add_to_cart', $composited_product_id, $quantity, $variation_id, $variations, $composited_item_cart_data );

				// Add to cart.
				$composited_item_cart_key = $this->composited_add_to_cart( $composite_id, $composited_product_id, $quantity, $variation_id, $variations, $composited_item_cart_data );

				if ( $composited_item_cart_key && ! in_array( $composited_item_cart_key, WC()->cart->cart_contents[ $composite_cart_key ][ 'composite_children' ] ) ) {
					WC()->cart->cart_contents[ $composite_cart_key ][ 'composite_children' ][] = $composited_item_cart_key;
				}

				/**
				 * Action 'woocommerce_composited_product_after_add_to_cart'.
				 *
				 * @param  string  $composited_product_id
				 * @param  string  $quantity
				 * @param  string  $variation_id
				 * @param  array   $variations
				 * @param  array   $composited_item_cart_data
				 *
				 * @hooked WC_CP_Addons_Compatibility::after_composited_add_to_cart()
				 */
				do_action( 'woocommerce_composited_product_after_add_to_cart', $composited_product_id, $quantity, $variation_id, $variations, $composited_item_cart_data );
			}
		}
	}

	/**
	 * Modifies cart item data - important for the first calculation of totals only.
	 *
	 * @param  array   $cart_item
	 * @param  string  $cart_item_key
	 * @return array
	 */
	public function add_cart_item_filter( $cart_item, $cart_item_key ) {

		$cart_contents = WC()->cart->cart_contents;

		if ( wc_cp_is_composite_container_cart_item( $cart_item ) ) {

			$cart_item = $this->set_composite_container_cart_item( $cart_item );

		} elseif ( $composite_container_item = wc_cp_get_composited_cart_item_container( $cart_item ) ) {

			$composite = $composite_container_item[ 'data' ];
			$cart_item = $this->set_composited_cart_item( $cart_item, $composite );
		}

		return $cart_item;
	}

	/**
	 * Load all composite-related session data.
	 *
	 * @param  array  $cart_item
	 * @param  array  $item_session_values
	 * @return void
	 */
	public function get_cart_item_from_session( $cart_item, $item_session_values ) {

		if ( ! isset( $cart_item[ 'composite_data' ] ) && isset( $item_session_values[ 'composite_data' ] ) ) {
			$cart_item[ 'composite_data' ] = $item_session_values[ 'composite_data' ];
		}

		if ( wc_cp_is_composite_container_cart_item( $item_session_values ) ) {

			if ( 'composite' === $cart_item[ 'data' ]->get_type() ) {

				if ( ! isset( $cart_item[ 'composite_children' ] ) ) {
					$cart_item[ 'composite_children' ] = $item_session_values[ 'composite_children' ];
				}

				$cart_item = $this->set_composite_container_cart_item( $cart_item );

			} else {

				if ( isset( $cart_item[ 'composite_children' ] ) ) {
					unset( $cart_item[ 'composite_children' ] );
				}
			}
		}

		if ( wc_cp_maybe_is_composited_cart_item( $item_session_values ) ) {

			if ( ! isset( $cart_item[ 'composite_parent' ] ) ) {
				$cart_item[ 'composite_parent' ] = $item_session_values[ 'composite_parent' ];
			}

			if ( ! isset( $cart_item[ 'composite_item' ] ) ) {
				$cart_item[ 'composite_item' ] = $item_session_values[ 'composite_item' ];
			}

			if ( $composite_container_item = wc_cp_get_composited_cart_item_container( $item_session_values ) ) {

				$composite = $composite_container_item[ 'data' ];

				if ( 'composite' === $composite->get_type() ) {
					$cart_item = $this->set_composited_cart_item( $cart_item, $composite );
				}
			}
		}

		return $cart_item;
	}

	/**
	 * Ensure any cart items marked as composited have a valid parent. If not, silently remove them.
	 *
	 * @param  WC_Cart  $cart
	 * @return void
	 */
	public function cart_loaded_from_session( $cart ) {

		$cart_contents = $cart->cart_contents;

		if ( ! empty( $cart_contents ) ) {

			foreach ( $cart_contents as $cart_item_key => $cart_item_values ) {
				if ( wc_cp_maybe_is_composited_cart_item( $cart_item_values ) ) {
					$container_item = wc_cp_get_composited_cart_item_container( $cart_item_values );
					if ( ! $container_item || ! isset( $container_item[ 'composite_children' ] ) || ! is_array( $container_item[ 'composite_children' ] ) || ! in_array( $cart_item_key, $container_item[ 'composite_children' ] ) ) {
						unset( WC()->cart->cart_contents[ $cart_item_key ] );
					} elseif ( isset( $cart_item_values[ 'composite_item' ] ) && 'composite' === $container_item[ 'data' ]->get_type() && ! $container_item[ 'data' ]->has_component( $cart_item_values[ 'composite_item' ] ) ) {
						unset( WC()->cart->cart_contents[ $cart_item_key ] );
					}
				}
			}
		}
	}

	/**
	 * Composited items can't be removed individually from the cart.
	 *
	 * @param  string  $link
	 * @param  string  $cart_item_key
	 * @return string
	 */
	public function cart_item_remove_link( $link, $cart_item_key ) {

		if ( isset( WC()->cart->cart_contents[ $cart_item_key ][ 'composite_data' ] ) && ! empty( WC()->cart->cart_contents[ $cart_item_key ][ 'composite_parent' ] ) ) {

			$parent_key = WC()->cart->cart_contents[ $cart_item_key ][ 'composite_parent' ];

			if ( isset( WC()->cart->cart_contents[ $parent_key ] ) ) {
				return '';
			}

		}

		return $link;
	}

	/**
	 * Composited item quantities may be changed between min_q and max_q.
	 *
	 * @param  string  $quantity
	 * @param  string  $cart_item_key
	 * @return string
	 */
	public function cart_item_quantity( $quantity, $cart_item_key ) {

		$cart_item = WC()->cart->cart_contents[ $cart_item_key ];

		if ( $parent = wc_cp_get_composited_cart_item_container( $cart_item ) ) {

			$component_id = $cart_item[ 'composite_item' ];

			if ( $cart_item[ 'composite_data' ][ $component_id ][ 'quantity_min' ] === $cart_item[ 'composite_data' ][ $component_id ][ 'quantity_max' ] ) {

				$quantity = $cart_item[ 'quantity' ];

			} else {

				$parent_quantity = $parent[ 'quantity' ];
				$max_stock       = $cart_item[ 'data' ]->managing_stock() && ! $cart_item[ 'data' ]->backorders_allowed() ? $cart_item[ 'data' ]->get_stock_quantity() : '';
				$max_stock       = $max_stock === null ? '' : $max_stock;

				if ( '' !== $max_stock ) {
					$max_qty = '' !== $cart_item[ 'composite_data' ][ $component_id ][ 'quantity_max' ] ? min( $max_stock, $parent_quantity * $cart_item[ 'composite_data' ][ $component_id ][ 'quantity_max' ] ) : $max_stock;
				} else {
					$max_qty = '' !== $cart_item[ 'composite_data' ][ $component_id ][ 'quantity_max' ] ? $parent_quantity * $cart_item[ 'composite_data' ][ $component_id ][ 'quantity_max' ] : '';
				}

				$min_qty = $parent_quantity * $cart_item[ 'composite_data' ][ $component_id ][ 'quantity_min' ];

				if ( ( $max_qty > $min_qty || '' === $max_qty ) && ! $cart_item[ 'data' ]->is_sold_individually() ) {

					$component_quantity = woocommerce_quantity_input( array(
						'input_name'  => "cart[{$cart_item_key}][qty]",
						'input_value' => $cart_item[ 'quantity' ],
						'min_value'   => $min_qty,
						'max_value'   => $max_qty,
						'step'        => $parent_quantity
					), $cart_item[ 'data' ], false );

					$quantity = $component_quantity;

				} else {
					$quantity = $cart_item[ 'quantity' ];
				}
			}
		}

		return $quantity;
	}

	/**
	 * Keeps composited items' quantities in sync with container item.
	 *
	 * @param  string  $cart_item_key
	 * @param  int     $quantity
	 * @return void
	 */
	public function update_quantity_in_cart( $cart_item_key, $quantity = 0 ) {

		if ( ! empty( WC()->cart->cart_contents[ $cart_item_key ] ) ) {

			if ( $quantity == 0 || $quantity < 0 ) {
				$quantity = 0;
			} else {
				$quantity = WC()->cart->cart_contents[ $cart_item_key ][ 'quantity' ];
			}

			$composite_children = wc_cp_get_composited_cart_items( WC()->cart->cart_contents[ $cart_item_key ] );

			if ( ! empty( $composite_children ) ) {

				// Change the quantity of all composited items that belong to the same config.
				foreach ( $composite_children as $child_key => $child_item ) {

					$child_item = WC()->cart->cart_contents[ $child_key ];

					if ( $child_item[ 'data' ]->is_sold_individually() && $quantity > 0 ) {

						WC()->cart->set_quantity( $child_key, 1, false );

					} else {

						$child_item_id  = $child_item[ 'composite_item' ];
						$child_quantity = $child_item[ 'composite_data' ][ $child_item_id ][ 'quantity' ];

						WC()->cart->set_quantity( $child_key, $child_quantity * $quantity, false );
					}
				}
			}
		}
	}

	/**
	 * Validates in-cart component quantity changes.
	 *
	 * @param  bool    $passed
	 * @param  string  $cart_item_key
	 * @param  array   $cart_item
	 * @param  int     $quantity
	 * @return bool
	 */
	public function update_cart_validation( $passed, $cart_item_key, $cart_item, $quantity ) {

		if ( $parent = wc_cp_get_composited_cart_item_container( $cart_item ) ) {

			$component_id    = $cart_item[ 'composite_item' ];
			$parent_key      = $cart_item[ 'composite_parent' ];
			$parent_quantity = $parent[ 'quantity' ];
			$min_quantity    = $parent_quantity * $cart_item[ 'composite_data' ][ $component_id ][ 'quantity_min' ];
			$max_quantity    = $cart_item[ 'composite_data' ][ $component_id ][ 'quantity_max' ] ? $parent_quantity * $cart_item[ 'composite_data' ][ $component_id ][ 'quantity_max' ] : '';

			if ( $quantity < $min_quantity ) {

				wc_add_notice( sprintf( __( 'The quantity of &quot;%s&quot; cannot be lower than %d.', 'ultimatewoo-pro' ), $cart_item[ 'data' ]->get_title(), $min_quantity ), 'error' );
				return false;

			} elseif ( $max_quantity && $quantity > $max_quantity ) {

				wc_add_notice( sprintf( __( 'The quantity of &quot;%s&quot; cannot be higher than %d.', 'ultimatewoo-pro' ), $cart_item[ 'data' ]->get_title(), $max_quantity ), 'error' );
				return false;

			} elseif ( $quantity % $parent_quantity != 0 ) {

				wc_add_notice( sprintf( __( 'The quantity of &quot;%s&quot; must be entered in multiples of %d.', 'ultimatewoo-pro' ), $cart_item[ 'data' ]->get_title(), $parent_quantity ), 'error' );
				return false;

			} else {

				// Update new component quantity in container/children composite_data array.
				// Note: updating the composite_data array will have no effect on the generated parent cart_id at this point.

				WC()->cart->cart_contents[ $parent_key ][ 'composite_data' ][ $component_id ][ 'quantity' ] = $quantity / $parent_quantity;

				foreach ( wc_cp_get_composited_cart_items( $parent, WC()->cart->cart_contents, true ) as $composite_child_key ) {
					WC()->cart->cart_contents[ $composite_child_key ][ 'composite_data' ][ $component_id ][ 'quantity' ] = $quantity / $parent_quantity;
				}
			}
		}

		return $passed;
	}

	/**
	 * Reinialize cart item data for re-ordering purchased orders.
	 *
	 * @param  mixed     $cart_item_data
	 * @param  mixed     $order_item
	 * @param  WC_Order  $order
	 * @return mixed
	 */
	public function order_again( $cart_item_data, $order_item, $order ) {

		if ( wc_cp_is_composited_order_item( $order_item, $order ) ) {

			// Identify this as a cart item that is originally part of a composite. Will be removed since it has already been added to the cart by its container.
			$cart_item_data[ 'is_order_again_composited' ] = 'yes';

			$component_id = isset( $order_item[ 'composite_item' ] ) ? $order_item[ 'composite_item' ] : '';

			// Copy all cart data of the "orphaned" composited cart item into the one already added along with the container.
			foreach ( WC()->cart->cart_contents as $check_cart_item_key => $check_cart_item_data ) {

				if ( isset( $check_cart_item_data[ 'composite_item' ] ) && absint( $component_id ) === absint( $check_cart_item_data[ 'composite_item' ] ) ) {

					$existing_composited_cart_item_data = $check_cart_item_data;
					$existing_composited_cart_item_key  = $check_cart_item_key;

					foreach ( $cart_item_data as $key => $value ) {
						if ( ! isset( $existing_composited_cart_item_data[ $key ] ) ) {
							WC()->cart->cart_contents[ $existing_composited_cart_item_key ][ $key ] = $value;
						}
					}
				}
			}

		} elseif ( wc_cp_is_composite_container_order_item( $order_item ) ) {
			$cart_item_data[ 'composite_data' ]     = maybe_unserialize( $order_item[ 'composite_data' ] );
			$cart_item_data[ 'composite_children' ] = array();
		}

		return $cart_item_data;
	}

	/**
	 * Modifies the cart.php & review-order.php templates formatted html prices visibility depending on pricing strategy.
	 *
	 * @param  string  $price
	 * @param  array   $values
	 * @param  string  $cart_item_key
	 * @return string
	 */
	public function cart_item_price( $price, $values, $cart_item_key ) {

		if ( empty( WC()->cart ) ) {
			return $price;
		}

		if ( $composite_container_item = wc_cp_get_composited_cart_item_container( $values ) ) {

			$product_id       = $values[ 'product_id' ];
			$component_id     = $values[ 'composite_item' ];
			$component_option = $composite_container_item[ 'data' ]->get_component_option( $component_id, $product_id );

			if ( $component_option ) {
				if ( false === $component_option->is_priced_individually() && $values[ 'line_subtotal' ] == 0 ) {
					return '';
				} elseif ( false === $component_option->get_component()->is_subtotal_visible( 'cart' ) ) {
					$price = '';
				}
			}

		} elseif ( wc_cp_is_composite_container_cart_item( $values ) ) {

			if ( $values[ 'data' ]->contains( 'priced_individually' ) && $values[ 'line_subtotal' ] == 0 ) {
				return '';
			}
		}

		return $price;
	}

	/**
	 * Modifies the cart.php & review-order.php templates formatted subtotal appearance depending on pricing strategy.
	 *
	 * @param  string  $price
	 * @param  array   $values
	 * @param  string  $cart_item_key
	 * @return string
	 */
	public function item_subtotal( $subtotal, $values, $cart_item_key ) {

		if ( $composite_container_item_key = wc_cp_get_composited_cart_item_container( $values, WC()->cart->cart_contents, true ) ) {

			$composite_container_item = WC()->cart->cart_contents[ $composite_container_item_key ];

			$product_id   = $values[ 'product_id' ];
			$component_id = $values[ 'composite_item' ];

			if ( $component_option = $composite_container_item[ 'data' ]->get_component_option( $component_id, $product_id ) ) {

				if ( false === $component_option->get_component()->is_subtotal_visible( 'cart' ) || false === $component_option->is_priced_individually() ) {
					$subtotal = '';
				} else {
					/**
					 * Controls whether to include composited cart item subtotals in the container cart item subtotal.
					 *
					 * @param  boolean  $add
					 * @param  array    $container_cart_item
					 * @param  string   $container_cart_item_key
					 */
					if ( apply_filters( 'woocommerce_add_composited_cart_item_subtotals', true, $composite_container_item, $composite_container_item_key ) ) {
						$subtotal = sprintf( _x( '%1$s: %2$s', 'component subtotal', 'ultimatewoo-pro' ), __( 'Option subtotal', 'ultimatewoo-pro' ), $subtotal );
					}

					$subtotal = '<span class="component-subtotal">' . $subtotal . '</span>';
				}
			}

		} elseif ( wc_cp_is_composite_container_cart_item( $values ) ) {

			/** Documented right above. Look up. See? */
			if ( apply_filters( 'woocommerce_add_composited_cart_item_subtotals', true, $values, $cart_item_key ) ) {

				$children         = wc_cp_get_composited_cart_items( $values, WC()->cart->cart_contents, false, true );
				$tax_display_cart = get_option( 'woocommerce_tax_display_cart' );

				if ( ! empty( $children ) ) {

					$composited_items_price = 0.0;
					$composite_price        = 'excl' === $tax_display_cart ? $values[ 'line_subtotal' ] : $values[ 'line_subtotal' ] + $values[ 'line_subtotal_tax' ];

					foreach ( $children as $child_key => $child_data ) {
						$composited_item_price   = 'excl' === $tax_display_cart ? $child_data[ 'line_subtotal' ] : $child_data[ 'line_subtotal' ] + $child_data[ 'line_subtotal_tax' ];
						$composited_items_price += (double) $composited_item_price;
					}

					$subtotal = (double) $composite_price + $composited_items_price;
					$subtotal = $this->format_product_subtotal( $values[ 'data' ], $subtotal );
				}
			}
		}

		return $subtotal;
	}

	/**
	 * Remove child cart items with parent.
	 *
	 * @param  string   $cart_item_key
	 * @param  WC_Cart  $cart
	 * @return void
	 */
	public function cart_item_removed( $cart_item_key, $cart ) {

		if ( wc_cp_is_composite_container_cart_item( $cart->removed_cart_contents[ $cart_item_key ] ) ) {

			$bundled_item_cart_keys = wc_cp_get_composited_cart_items( $cart->removed_cart_contents[ $cart_item_key ], $cart->cart_contents, true );

			foreach ( $bundled_item_cart_keys as $bundled_item_cart_key ) {

				$remove = $cart->cart_contents[ $bundled_item_cart_key ];
				$cart->removed_cart_contents[ $bundled_item_cart_key ] = $remove;

				unset( $cart->cart_contents[ $bundled_item_cart_key ] );

				/** WC core action. */
				do_action( 'woocommerce_cart_item_removed', $bundled_item_cart_key, $cart );
			}
		}
	}

	/**
	 * Restore child cart items with parent.
	 *
	 * @param  string   $cart_item_key
	 * @param  WC_Cart  $cart
	 * @return void
	 */
	public function cart_item_restored( $cart_item_key, $cart ) {

		if ( wc_cp_is_composite_container_cart_item( $cart->cart_contents[ $cart_item_key ] ) ) {

			$bundled_item_cart_keys = wc_cp_get_composited_cart_items( $cart->cart_contents[ $cart_item_key ], $cart->removed_cart_contents, true );

			foreach ( $bundled_item_cart_keys as $bundled_item_cart_key ) {

				$remove = $cart->removed_cart_contents[ $bundled_item_cart_key ];
				$cart->cart_contents[ $bundled_item_cart_key ] = $remove;

				unset( $cart->removed_cart_contents[ $bundled_item_cart_key ] );

				/** WC core action. */
				do_action( 'woocommerce_cart_item_restored', $bundled_item_cart_key, $cart );
			}
		}
	}

	/**
	 * Shipping fix - add the value of any children that are not shipped individually to the container value and, optionally, add their weight to the container weight, as well.
	 *
	 * @param  array  $packages
	 * @return array
	 */
	public function cart_shipping_packages( $packages ) {

		if ( ! empty( $packages ) ) {

			foreach ( $packages as $package_key => $package ) {

				if ( ! empty( $package[ 'contents' ] ) ) {
					foreach ( $package[ 'contents' ] as $cart_item_key => $cart_item_data ) {

						if ( wc_cp_is_composite_container_cart_item( $cart_item_data ) ) {

							$composite     = unserialize( serialize( $cart_item_data[ 'data' ] ) );
							$composite_qty = $cart_item_data[ 'quantity' ];

							/*
							 * Container needs shipping: Aggregate the prices of any children that are physically packaged in their parent and, optionally, aggregate their weights into the parent, as well.
							 */

							if ( $composite->needs_shipping() ) {

								$bundled_weight = 0.0;
								$bundled_value  = 0.0;

								$composite_totals = array(
									'line_subtotal'     => $cart_item_data[ 'line_subtotal' ],
									'line_total'        => $cart_item_data[ 'line_total' ],
									'line_subtotal_tax' => $cart_item_data[ 'line_subtotal_tax' ],
									'line_tax'          => $cart_item_data[ 'line_tax' ],
									'line_tax_data'     => $cart_item_data[ 'line_tax_data' ]
								);

								foreach ( wc_cp_get_composited_cart_items( $cart_item_data, WC()->cart->cart_contents, true ) as $child_item_key ) {

									/**
									 * 'woocommerce_composited_package_item' filter.
									 *
									 * @param  array   $child_item
									 * @param  string  $child_item_key
									 * @param  string  $parent_item_key
									 */
									$child_cart_item_data      = apply_filters( 'woocommerce_composited_package_item', WC()->cart->cart_contents[ $child_item_key ], $child_item_key, $cart_item_key );
									$composited_product        = $child_cart_item_data[ 'data' ];
									$composited_product_qty    = $child_cart_item_data[ 'quantity' ];
									$composited_product_value  = WC_CP_Core_Compatibility::get_prop( $composited_product, 'composited_value', 'shipping' );
									$composited_product_weight = WC_CP_Core_Compatibility::get_prop( $composited_product, 'composited_weight', 'shipping' );

									// Aggregate price of physically packaged child item - already converted to virtual.

									if ( $composited_product_value ) {

										$bundled_value += $composited_product_value * $composited_product_qty;

										$composite_totals[ 'line_subtotal' ]     += $child_cart_item_data[ 'line_subtotal' ];
										$composite_totals[ 'line_total' ]        += $child_cart_item_data[ 'line_total' ];
										$composite_totals[ 'line_subtotal_tax' ] += $child_cart_item_data[ 'line_subtotal_tax' ];
										$composite_totals[ 'line_tax' ]          += $child_cart_item_data[ 'line_tax' ];

										$packages[ $package_key ][ 'contents_cost' ] += $child_cart_item_data[ 'line_total' ];

										$child_item_line_tax_data = $child_cart_item_data[ 'line_tax_data' ];

										$composite_totals[ 'line_tax_data' ][ 'total' ]    = array_merge( $composite_totals[ 'line_tax_data' ][ 'total' ], $child_item_line_tax_data[ 'total' ] );
										$composite_totals[ 'line_tax_data' ][ 'subtotal' ] = array_merge( $composite_totals[ 'line_tax_data' ][ 'subtotal' ], $child_item_line_tax_data[ 'subtotal' ] );
									}

									// Aggregate weight of physically packaged child item - already converted to virtual.

									if ( $composited_product_weight ) {
										$bundled_weight += $composited_product_weight * $composited_product_qty;
									}
								}

								if ( $bundled_value > 0 ) {
									$composite_price = WC_CP_Core_Compatibility::get_prop( $composite, 'price', 'edit' );
									WC_CP_Core_Compatibility::set_prop( $composite, 'price', (double) $composite_price + $bundled_value / $composite_qty );
								}

								$packages[ $package_key ][ 'contents' ][ $cart_item_key ] = array_merge( $cart_item_data, $composite_totals );

								if ( $bundled_weight > 0 ) {
									$composite_weight = WC_CP_Core_Compatibility::get_prop( $composite, 'weight', 'edit' );
									WC_CP_Core_Compatibility::set_prop( $composite, 'weight', (double) $composite_weight + $bundled_weight / $composite_qty );
								}

								$packages[ $package_key ][ 'contents' ][ $cart_item_key ][ 'data' ] = $composite;
							}
						}
					}
				}
			}
		}

		return $packages;
	}

	/**
	 * Inherit coupon validity from parent:
	 *
	 * - Coupon is invalid for child item if parent is excluded.
	 * - Coupon is valid for child item if valid for parent, unless child item is excluded.
	 *
	 * @param  bool        $valid
	 * @param  WC_Product  $product
	 * @param  WC_Coupon   $coupon
	 * @param  array       $cart_item
	 * @return bool
	 */
	public function coupon_validity( $valid, $product, $coupon, $cart_item ) {

		if ( ! empty( WC()->cart ) ) {

			if ( $container_cart_item = wc_cp_get_composited_cart_item_container( $cart_item ) ) {

				$composite    = $container_cart_item[ 'data' ];
				$composite_id = WC_CP_Core_Compatibility::get_id( $composite );

				/**
				 * Filter to disable coupon validity inheritance from container.
				 *
				 * @param  boolean     $inherit
				 * @param  WC_Product  $product
				 * @param  WC_Coupon   $coupon
				 * @param  array       $component_cart_item_data
				 * @param  array       $container_cart_item_data
				 */
				if ( apply_filters( 'woocommerce_composite_inherit_coupon_validity', true, $product, $coupon, $cart_item, $container_cart_item ) ) {

					$product_id = WC_CP_Core_Compatibility::get_id( $product );
					$parent_id  = WC_CP_Core_Compatibility::get_parent_id( $product );

					$excluded_product_ids        = WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ? $coupon->get_excluded_product_ids() : $coupon->exclude_product_ids;
					$excluded_product_categories = WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ? $coupon->get_excluded_product_categories() : $coupon->exclude_product_categories;
					$excludes_sale_items         = WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ? $coupon->get_exclude_sale_items() : ( 'yes' === $coupon->exclude_sale_items );

					if ( $valid ) {

						$parent_excluded = false;

						// Parent ID excluded from the discount.
						if ( sizeof( $excluded_product_ids ) > 0 ) {
							if ( in_array( $composite_id, $excluded_product_ids ) ) {
								$parent_excluded = true;
							}
						}

						// Parent category excluded from the discount.
						if ( sizeof( $excluded_product_categories ) > 0 ) {

							$product_cats = wc_get_product_cat_ids( $composite_id );

							if ( sizeof( array_intersect( $product_cats, $excluded_product_categories ) ) > 0 ) {
								$parent_excluded = true;
							}
						}

						// Sale Items excluded from discount and parent on sale.
						if ( $excludes_sale_items ) {

							$product_ids_on_sale = wc_get_product_ids_on_sale();

							if ( in_array( $composite_id, $product_ids_on_sale, true ) ) {
								$parent_excluded = true;
							}
						}

						if ( $parent_excluded ) {
							$valid = false;
						}

					} else {

						$bundled_product_excluded = false;

						// Bundled product ID excluded from the discount.
						if ( sizeof( $excluded_product_ids ) > 0 ) {
							if ( in_array( $product_id, $excluded_product_ids ) || ( $parent_id && in_array( $parent_id, $excluded_product_ids ) ) ) {
								$bundled_product_excluded = true;
							}
						}

						// Bundled product category excluded from the discount.
						if ( sizeof( $excluded_product_categories ) > 0 ) {

							$product_cats = $parent_id ? wc_get_product_cat_ids( $parent_id ) : wc_get_product_cat_ids( $product_id );

							if ( sizeof( array_intersect( $product_cats, $excluded_product_categories ) ) > 0 ) {
								$bundled_product_excluded = true;
							}
						}

						// Bundled product on sale and sale items excluded from discount.
						if ( $excludes_sale_items ) {

							$product_ids_on_sale = wc_get_product_ids_on_sale();

							if ( in_array( $product_id, $product_ids_on_sale ) || ( $parent_id && in_array( $parent_id, $product_ids_on_sale ) ) ) {
								$bundled_product_excluded = true;
							}
						}

						if ( ! $bundled_product_excluded && $coupon->is_valid_for_product( $composite, $container_cart_item ) ) {
							$valid = true;
						}
					}
				}
			}
		}

		return $valid;
	}
}

<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Warranty_Cart {

    public function __construct() {
        add_action( 'woocommerce_before_add_to_cart_button', array($this, 'show_product_warranty') );
        add_filter( 'woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 2 );
        add_filter( 'woocommerce_add_cart_item', array($this, 'add_cart_item'), 10, 1 );

        add_filter( 'woocommerce_add_to_cart_validation', array($this, 'add_cart_validation'), 10, 2 );

        add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 10, 2 );
        add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 10, 2 );
        add_action( 'woocommerce_add_to_cart', array($this, 'add_warranty_index'), 10, 6 );
    }

    /**
     * Show a product's warranty information
     */
    function show_product_warranty() {
        global $post, $product, $woocommerce;

        if ( $product->is_type( 'external' ) ) {
            return;
        }

        $product_id     = $product->get_id();
        $warranty       = warranty_get_product_warranty( $product_id );
        $warranty_label = $warranty['label'];

        if ( $warranty['type'] == 'included_warranty' ) {
            if ( $warranty['length'] == 'limited' ) {
                $value      = $warranty['value'];
                $duration   = warranty_duration_i18n( $warranty['duration'], $value );

                echo '<p class="warranty_info"><b>'. $warranty_label .':</b> '. $value .' '. $duration .'</p>';
            } else {
                echo '<p class="warranty_info"><b>'. $warranty_label .':</b> '. __('Lifetime', 'ultimatewoo-pro') .'</p>';
            }
        } elseif ( $warranty['type'] == 'addon_warranty' ) {
            $addons = $warranty['addons'];

            if ( is_array($addons) && !empty($addons) ) {
                echo '<p class="warranty_info"><b>'. $warranty_label .'</b> <select name="warranty">';

                if ( isset($warranty['no_warranty_option']) && $warranty['no_warranty_option'] == 'yes' ) {
                    echo '<option value="-1">'. __('No warranty', 'ultimatewoo-pro') .'</option>';
                }

                foreach ( $addons as $x => $addon ) {
                    $amount     = $addon['amount'];
                    $value      = $addon['value'];
                    $duration   = warranty_duration_i18n( $addon['duration'], $value );

                    if ( $value == 0 && $amount == 0 ) {
                        // no warranty option
                        echo '<option value="-1">'. __('No warranty', 'ultimatewoo-pro') .'</option>';
                    } else {
                        if ( $amount == 0 ) {
                            $amount = __('Free', 'ultimatewoo-pro');
                        } else {
                            $amount = wc_price( $amount );
                        }
                        echo '<option value="'. $x .'">'. $value .' '. $duration . ' &mdash; '. $amount .'</option>';
                    }
                }

                echo '</select></p>';
            }
        } else {
            echo '<p class="warranty_info"></p>';
        }

    }

    /**
     * Adds a warranty_index to a cart item. Used in tracking the selected warranty options
     *
     * @see WC_Warranty_Frontend::add_cart_item()
     * @param array $item_data
     * @param int $product_id
     * @return array $item_data
     */
    function add_cart_item_data( $item_data, $product_id ) {
        global $woocommerce;

        if ( isset($_POST['warranty']) && $_POST['warranty'] !== '' ) {
            $item_data['warranty_index'] = $_POST['warranty'];
        }

        return $item_data;
    }

    /**
     * Add custom data to a cart item based on the selected warranty type
     *
     * @see WC_Warranty_Frontend::add_cart_item_data()
     * @param array $item_data
     * @return array $item_data
     */
    function add_cart_item( $item_data ) {
        global $woocommerce;

        $_product       = $item_data['data'];
        $warranty_index = false;

        if ( isset($item_data['warranty_index']) ) {
            $warranty_index = $item_data['warranty_index'];
        }

        $product_id = ( version_compare( WC_VERSION, '3.0', '<' ) && isset( $_product->variation_id ) ) ? $_product->variation_id : $_product->get_id();
        $warranty   = warranty_get_product_warranty( $product_id );

        if ( $warranty ) {
            if ( $warranty['type'] == 'addon_warranty' && $warranty_index !== false ) {
                $addons                         = $warranty['addons'];
                $item_data['warranty_index']    = $warranty_index;
                $add_cost                       = 0;

                if ( isset($addons[$warranty_index]) && !empty($addons[$warranty_index]) ) {
                    $addon = $addons[$warranty_index];
                    if ( $addon['amount'] > 0 ) {
                        $add_cost += $addon['amount'];

                        $_product->set_price( $_product->get_price() + $add_cost );
                    }
                }
            }
        }

        return $item_data;
    }

    /**
     * Make sure an add-to-cart request is valid
     *
     * @param bool $valid
     * @param int $product_id
     * @return bool $valid
     */
    function add_cart_validation( $valid = '', $product_id = '' ) {
        global $woocommerce;

        $warranty       = warranty_get_product_warranty( $product_id );
        $warranty_label = $warranty['label'];

        if ( $warranty['type'] == 'addon_warranty' && !isset($_REQUEST['warranty']) ) {
            $error = sprintf(__('Please select your %s first.', 'ultimatewoo-pro'), $warranty_label);
            if ( function_exists( 'wc_add_notice' ) ) {
                wc_add_notice( $error, 'error' );
            } else {
                $woocommerce->add_error( $error );
            }

            return false;
        }

        return $valid;
    }

    /**
     * Returns warranty data about a cart item
     *
     * @param array $other_data
     * @param array $cart_item
     * @return array $other_data
     */
    function get_item_data( $other_data, $cart_item ) {
        $_product   = $cart_item['data'];
        $product_id = $_product->get_id();

        $warranty       = warranty_get_product_warranty( $product_id );
        $warranty_label = $warranty['label'];

        if ( $warranty ) {
            if ( $warranty['type'] == 'addon_warranty' && isset($cart_item['warranty_index']) ) {
                $addons         = $warranty['addons'];
                $warranty_index = $cart_item['warranty_index'];

                if ( isset($addons[$warranty_index]) && !empty($addons[$warranty_index]) ) {
                    $addon  = $addons[$warranty_index];
                    $name   = $warranty_label;
                    $value  = $GLOBALS['wc_warranty']->get_warranty_string( $addon['value'], $addon['duration'] );

                    if ( $addon['amount'] > 0 ) {
                        $value .= ' (' . wc_price( $addon['amount'] ) . ')';
                    }

                    $other_data[] = array(
                        'name'      => $name,
                        'value'     => $value,
                        'display'   => ''
                    );
                }
            } elseif ( $warranty['type'] == 'included_warranty' ) {
                if ( $warranty['length'] == 'lifetime' ) {
                    $other_data[] = array(
                        'name'      => $warranty_label,
                        'value'     => __('Lifetime', 'ultimatewoo-pro'),
                        'display'   => ''
                    );
                } elseif ( $warranty['length'] == 'limited' ) {
                    $string = $GLOBALS['wc_warranty']->get_warranty_string( $warranty['value'], $warranty['duration'] );
                    $other_data[] = array(
                        'name'      => $warranty_label,
                        'value'     => $string,
                        'display'   => ''
                    );
                }
            }
        }

        return $other_data;
    }

    /**
     * Get warranty index and add it to the cart item
     *
     * @param array $cart_item
     * @param array $values
     * @return array $cart_item
     */
    function get_cart_item_from_session( $cart_item, $values ) {

        if ( isset( $values['warranty_index'] ) ) {
            $cart_item['warranty_index'] = $values['warranty_index'];
            $cart_item = $this->add_cart_item( $cart_item );
        }

        return $cart_item;
    }

    /**
     * Add warranty index to the cart items from POST
     *
     * @param string $cart_key
     * @param int $product_Id
     * @param int $quantity
     * @param int $variation_id
     * @param object $variation
     * @param array $cart_item_data
     */
    function add_warranty_index( $cart_key, $product_id, $quantity, $variation_id = null, $variation = null, $cart_item_data = null ) {
        global $woocommerce;

        if ( isset($_POST['warranty']) && $_POST['warranty'] !== '' ) {
            $woocommerce->cart->cart_contents[$cart_key]['warranty_index'] = $_POST['warranty'];
        }
    }

}

$GLOBALS['warranty_cart'] = new Warranty_Cart();

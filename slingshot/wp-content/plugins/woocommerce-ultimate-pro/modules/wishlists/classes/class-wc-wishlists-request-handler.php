<?php

class WC_Wishlists_Request_Handler {

	public static function process_request() {

		if ( isset( $_REQUEST['wlaction'] ) ) {


			$action = $_REQUEST['wlaction'];

			if ( !WC_Wishlists_Plugin::verify_nonce( $action ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'ultimatewoo-pro' ) );
			}

			$result = false;

			switch ( $action ) {
				case 'create-list':
					$result = self::create_list();
					break;
				case 'delete-list':
					$result = self::delete_list();
					break;
				case 'edit-list':
					$result = self::edit_list();
					break;
				case 'edit-lists':
					$result = self::edit_lists();
					break;
				case 'wishlists-remove-from-list':
					$result = self::remove_from_list();
					break;
				case 'manage-list':

					$bulkaction = $_REQUEST['wlupdateaction'];
					switch ( $bulkaction ) :
						case 'quantity' :
							$result = self::bulk_update_action();
							break;
						case 'quantity-add-to-cart' :
							$result = self::bulk_update_action(); //update the qquantity first
							$result = self::bulk_edit_action(); //this will call add to cart. 
							break;
						default:
							$result = self::bulk_edit_action();
					endswitch;

					break;
				case 'add-cart-item' :
					$result = self::add_to_cart();
					break;
				case 'add-cart-items' :
					$result = self::add_all_to_cart();
					break;
			}

			if ( $result !== false ) {
				if ( $result !== true ) {
					wp_redirect( esc_url_raw( add_query_arg( array( 'wlm' => uniqid() ), $result ) ) );
					die();
				}
			}
		}
	}

	public static function last_updated_class( $list_id ) {
		_deprecated_function( 'last_updated_class', '1.8.1' );

		return '';
	}

	private static function create_list() {
		$args = $_POST;

		WC_Wishlists_User::set_cookie();
		$args['wishlist_owner'] = WC_Wishlists_User::get_wishlist_key();

		if ( empty( $_POST['wishlist_title'] ) ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Please name your list', 'ultimatewoo-pro' ), 'error' );

			return false;
		}

		$result = WC_Wishlists_Wishlist::create_list( sanitize_text_field( $_POST['wishlist_title'] ), $args );

		if ( $result ) {
			$moved         = false;
			$session_items = WC_Wishlists_Wishlist_Item_Collection::get_items_from_session();
			if ( $session_items && count( $session_items ) ) {
				$moved = 0;
				foreach ( $session_items as $wishlist_item_key => $session_data ) {
					$moved += WC_Wishlists_Wishlist_Item_Collection::move_item_to_list_from_session( $result, $wishlist_item_key );
				}
			}

			if ( $moved ) {
				WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( 'Wishlist successfully created. %s items moved', 'ultimatewoo-pro' ), $moved ) );
			} else {
				WC_Wishlist_Compatibility::wc_add_notice( __( 'Wishlist successfully created', 'ultimatewoo-pro' ) );
			}

			if ( WC_Wishlists_Settings::get_setting( 'woocommerce_wishlist_redirect_after_add_to_cart', 'yes' ) == 'no' && isset( $_POST['wl_return_to'] ) ) {
				return get_permalink( $_POST['wl_return_to'] );
			} else {
				return WC_Wishlists_Wishlist::get_the_url_edit( $result );
			}
		}
	}

	private static function delete_list() {
		$post_id = isset( $_REQUEST['wlid'] ) ? $_REQUEST['wlid'] : 0;
		$post    = get_post( $post_id );

		if ( !$post || !$post_id || !$post->post_type == 'wishlist' ) {
			wp_die( __( 'Unable to locate wishlist', 'ultimatewoo-pro' ) );
		}

		$wishlist = new WC_Wishlists_Wishlist( $post_id );

		if ( !is_admin() ) {
			$wl_owner          = $wishlist->get_wishlist_owner();
			$current_owner_key = WC_Wishlists_User::get_wishlist_key();

			if ( $wl_owner != $current_owner_key && !current_user_can( 'manage_woocommerce' ) ) {
				wp_die( __( 'You can only manage your own lists', 'ultimatewoo-pro' ) );
			}
		}

		$result = WC_Wishlists_Wishlist::delete_list( $post_id );

		if ( $result && !is_admin() ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Wishlist successfully deleted', 'ultimatewoo-pro' ) );
		}

		return WC_Wishlists_Pages::get_url_for( 'my-lists' );
	}

	private static function edit_list() {
		$post_id = isset( $_POST['wlid'] ) ? $_POST['wlid'] : 0;
		$post    = get_post( $post_id );

		if ( !$post_id || !$post->post_type == 'wishlist' ) {
			wp_die( __( 'Unable to locate wishlist for updating', 'ultimatewoo-pro' ) );
		}

		if ( empty( $_POST['wishlist_title'] ) ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Name can not be empty', 'ultimatewoo-pro' ), 'error' );

			return false;
		}


		$wishlist = new WC_Wishlists_Wishlist( $post_id );
		if ( current_user_can( 'manage_woocommerce' ) ) {
			$wl_owner          = $wishlist->get_wishlist_owner();
			$current_owner_key = WC_Wishlists_User::get_wishlist_key();

			if ( $wl_owner != $current_owner_key && !current_user_can( 'manage_woocommerce' ) ) {
				wp_die( __( 'You can only update your own lists', 'ultimatewoo-pro' ) );
			}
		}


		$args   = $_POST;
		$result = WC_Wishlists_Wishlist::update_list( $post_id, $args );
		if ( $result ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Wishlist successfully updated', 'ultimatewoo-pro' ) );
		} else {

		}

		return WC_Wishlists_Wishlist::get_the_url_edit( $result ) . '#tab-wl-settings';
	}

	private static function edit_lists() {
		$result = true;

		$listids = isset( $_POST['sharing'] ) ? $_POST['sharing'] : false;
		if ( !$listids ) {
			return;
		}

		foreach ( $listids as $id => $sharing ) {
			$wishlist = new WC_Wishlists_Wishlist( $id );

			$wl_owner          = WC_Wishlists_Wishlist::get_the_wishlist_owner( $id );
			$current_owner_key = WC_Wishlists_User::get_wishlist_key();

			if ( $wl_owner != $current_owner_key && !current_user_can( 'manage_woocommerce' ) ) {
				wp_die( __( 'You can only update your own lists', 'ultimatewoo-pro' ) );
			}

			$result &= (bool) WC_Wishlists_Wishlist::update_list( $id, array( 'wishlist_sharing' => $sharing ) );
		}

		if ( $result ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Lists successfullly updated', 'ultimatewoo-pro' ) );
		} else {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'There was an error updating your lists.  Please refresh the page and try again.', 'ultimatewoo-pro' ), 'error' );
		}

		return WC_Wishlists_pages::get_url_for( 'my-lists' );
	}

	private static function add_to_list() {

	}

	private static function remove_from_list() {

		$wishlist_id       = isset( $_REQUEST['wlid'] ) ? $_REQUEST['wlid'] : false;
		$wishlist_item_key = isset( $_REQUEST['wishlist-item-key'] ) ? $_REQUEST['wishlist-item-key'] : false;


		if ( !$wishlist_id || !$wishlist_item_key ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Unable to remove item.  Please try again', 'ultimatewoo-pro' ), 'error' );

			return WC_Wishlists_Wishlist::get_the_url_edit( $wishlist_id );
		}

		$wl_owner          = WC_Wishlists_Wishlist::get_the_wishlist_owner( $wishlist_id );
		$current_owner_key = WC_Wishlists_User::get_wishlist_key();

		if ( $wl_owner != $current_owner_key && !current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'You can only update your own lists', 'ultimatewoo-pro' ) );
		}

		$result = WC_Wishlists_Wishlist_Item_Collection::remove_item( $wishlist_id, $wishlist_item_key );

		if ( $result ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Item removed from your list', 'ultimatewoo-pro' ) );
		}

		return WC_Wishlists_Wishlist::get_the_url_edit( $wishlist_id );
	}

	private static function bulk_edit_action() {

		$wishlist_id = isset( $_REQUEST['wlid'] ) ? $_REQUEST['wlid'] : false;
		if ( !$wishlist_id ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Unable to edit list.  Please try again', 'ultimatewoo-pro' ), 'error' );

			return WC_Wishlists_Wishlist::get_the_url_edit( $wishlist_id );
		}

		$bulk_action = isset( $_REQUEST['wlupdateaction'] ) ? $_REQUEST['wlupdateaction'] : false;
		if ( !$bulk_action ) {
			return WC_Wishlists_Wishlist::get_the_url_edit( $wishlist_id );
		}

		$wl_owner          = WC_Wishlists_Wishlist::get_the_wishlist_owner( $wishlist_id );
		$current_owner_key = WC_Wishlists_User::get_wishlist_key();

		if ( $wl_owner != $current_owner_key && !current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'You can only update your own lists', 'ultimatewoo-pro' ) );
		}

		if ( $bulk_action == 'remove' ) {
			$items  = isset( $_REQUEST['wlitem'] ) ? $_REQUEST['wlitem'] : array();
			$result = 0;
			foreach ( $items as $wishlist_item_key ) {
				$result += WC_Wishlists_Wishlist_Item_Collection::remove_item( $wishlist_id, $wishlist_item_key );
			}

			if ( $result ) {
				WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( '%s Items removed from your list', 'ultimatewoo-pro' ), $result ) );
			} else {
				WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( 'Please select at least one item before applying an action', 'ultimatewoo-pro' ), $result ), 'error' );
			}
		} elseif ( $bulk_action == 'create' ) {

			$items  = isset( $_REQUEST['wlitem'] ) ? $_REQUEST['wlitem'] : array();
			$result = 0;
			foreach ( $items as $wishlist_item_key ) {
				$result += WC_Wishlists_Wishlist_Item_Collection::move_item_to_session( $wishlist_id, $wishlist_item_key );
			}

			if ( $result ) {
				$session_items = WC_Wishlists_Wishlist_Item_Collection::get_items_from_session();
			} else {
				WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( 'Please select at least one item before applying an action', 'ultimatewoo-pro' ), $result ), 'error' );
			}

			return WC_Wishlists_Pages::get_url_for( 'create-a-list' );
		} elseif ( $bulk_action == 'add-to-cart' || $bulk_action == 'quantity-add-to-cart' ) {
			$items  = isset( $_REQUEST['wlitem'] ) ? $_REQUEST['wlitem'] : array();
			$result = 0;

			foreach ( $items as $wishlist_item_key ) {
				$result += ( self::add_to_cart( $wishlist_id, $wishlist_item_key, true ) !== false );
			}

			if ( $result ) {
				WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( '%s have been added to the cart', 'ultimatewoo-pro' ), $result ) );
			} else {
				WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( 'Please select at least one item before applying an action', 'ultimatewoo-pro' ), $result ), 'error' );
			}

			return self::get_add_to_cart_redirect_url( $wishlist_id );
		} else {
			$destination_id   = $bulk_action;
			$destination_list = new WC_Wishlists_Wishlist( $destination_id );

			if ( $destination_list->get_wishlist_owner() == WC_Wishlists_User::get_wishlist_key() ) {
				$items  = isset( $_REQUEST['wlitem'] ) ? $_REQUEST['wlitem'] : array();
				$result = 0;
				foreach ( $items as $wishlist_item_key ) {
					$result += WC_Wishlists_Wishlist_Item_Collection::move_item( $wishlist_id, $destination_id, $wishlist_item_key );
				}

				if ( $result ) {
					WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( '%s Items successfully moved to %s <a class="button" href="%s">Edit List</a>', 'ultimatewoo-pro' ), $result, esc_html( get_the_title( $destination_id ) ), WC_Wishlists_Wishlist::get_the_url_edit( $destination_id ) ) );
				} else {
					WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( 'Please select at least one item before applying an action', 'ultimatewoo-pro' ), $result ), 'error' );
				}
			}
		}


		return WC_Wishlists_Wishlist::get_the_url_edit( $wishlist_id );
	}

	private static function bulk_update_action() {

		$wishlist_id = isset( $_REQUEST['wlid'] ) ? $_REQUEST['wlid'] : false;
		if ( !$wishlist_id ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Unable to edit list.  Please try again', 'ultimatewoo-pro' ), 'error' );

			return WC_Wishlists_Wishlist::get_the_url_edit( $wishlist_id );
		}

		$wl_owner          = WC_Wishlists_Wishlist::get_the_wishlist_owner( $wishlist_id );
		$current_owner_key = WC_Wishlists_User::get_wishlist_key();

		if ( $wl_owner != $current_owner_key && !current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'You can only update your own lists', 'ultimatewoo-pro' ) );
		}

		$result = false;
		if ( isset( $_POST['cart'] ) ) {

			$items  = isset( $_REQUEST['wlitem'] ) ? $_REQUEST['wlitem'] : array();
			$result = 0;

			foreach ( $items as $key ) {
				if ( isset( $_POST['cart'][ $key ] ) ) {
					$data   = $_POST['cart'][ $key ];
					$result += WC_Wishlists_Wishlist_Item_Collection::update_item_quantity( $wishlist_id, $key, $data['qty'] );
				}
			}
		}

		if ( $result ) {
			WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( '%s Items updated', 'ultimatewoo-pro' ), $result ) );
		} else {
			WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( 'Please select at least one item before applying an action', 'ultimatewoo-pro' ), $result ), 'error' );
		}

		return WC_Wishlists_Wishlist::get_the_url_edit( $wishlist_id );
	}

	private static function add_all_to_cart() {
		$wishlist_id = filter_input( INPUT_GET, 'wlid', FILTER_SANITIZE_NUMBER_INT );
		$items       = WC_Wishlists_Wishlist_Item_Collection::get_items( $wishlist_id );
		if ( $items ) {
			$result = false;
			foreach ( $items as $wishlist_item_key => $data ) {
				$_product = $data['data'];
				if ( $_product->product_type != 'external' ) {
					$result += ( self::add_to_cart( $wishlist_id, $wishlist_item_key, true ) !== false );
				}
			}

			if ( $result ) {
				WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( '%s have been added to the cart', 'ultimatewoo-pro' ), $result ) );
			} else {
				WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( 'Please select at least one item before applying an action', 'ultimatewoo-pro' ), $result ), 'error' );
			}
		}

		$url = self::get_add_to_cart_redirect_url( $wishlist_id );
		if ( isset( $_GET['preview'] ) ) {
			return esc_url( add_query_arg( array( 'preview' => 'true' ), $url ) );
		} else {
			return esc_url( $url );
		}
	}

	private static function add_to_cart( $wishlist_id = false, $wishlist_item_key = false, $suppress_messages = false ) {
		$result = false;

		if ( !$wishlist_id && !$wishlist_item_key ) {
			$wishlist_id       = filter_input( INPUT_GET, 'wlid', FILTER_SANITIZE_NUMBER_INT );
			$wishlist_item_key = isset( $_GET['wishlist-item-key'] ) ? filter_input( INPUT_GET, 'wishlist-item-key', FILTER_SANITIZE_STRIPPED ) : false;
		}

		if ( !$wishlist_id ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Action failed. Please refresh the page and retry.', 'woocommerce' ), 'error' );

			return;
		}

		$wishlist = new WC_Wishlists_Wishlist( $wishlist_id );
		if ( !$wishlist ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Action failed. Please refresh the page and retry.', 'woocommerce' ), 'error' );

			return;
		}

		$wishlist_items = WC_Wishlists_Wishlist_Item_Collection::get_items( $wishlist->id );

		if ( $wishlist_item_key ) {
			if ( sizeof( $wishlist_items > 0 ) && isset( $wishlist_items[ $wishlist_item_key ] ) ) {

				$wishlist_item = $wishlist_items[ $wishlist_item_key ];
				if ( isset( $wishlist_item['wl_price'] ) ) {
					unset( $wishlist_item['wl_price'] );
				}

				$core_keys   = array( 'product_id', 'variation_id', 'variation', 'quantity', 'data', 'date' );
				$add_on_data = array();
				$cart_item   = array();

				foreach ( $wishlist_item as $key => $value ) {
					if ( !in_array( $key, $core_keys ) ) {
						$add_on_data[ $key ] = $value;
					} else {
						$cart_item[ $key ] = $value;
					}
				}

				$wishlist_prefix = WC_Wishlists_Settings::get_setting( 'wc_wishlists_cart_label', __( 'Wishlist', 'ultimatewoo-pro' ) );

				$add_on_data = apply_filters( 'woocommerce_copy_cart_item_data', $add_on_data, (int) $wishlist_item['product_id'], $wishlist_item );
				// Generate a ID based on product ID, variation ID, variation data, and other cart item data
				$check_cart_id = WC()->cart->generate_cart_id( (int) $cart_item['product_id'], $cart_item['variation_id'], $cart_item['variation'], $add_on_data );

				// See if this product and its options is already in the cart
				$check_cart_item_key = WC()->cart->find_product_in_cart( $check_cart_id );

				$product_data = wc_get_product( isset( $cart_item['variation_id'] ) && !empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'] );
				if ( !$product_data ) {
					WC_Wishlist_Compatibility::wc_add_notice( __( 'Unable to add product to the cart. Product no longer exists', 'ultimatewoo-pro' ), 'error' );

					return false;
				}

				if ( !apply_filters( 'woocommerce_wishlist_user_can_purcahse', true, $product_data ) ) {
					WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( 'Purchases are currently disabled for %s', 'ultimatewoo-pro' ), $product_data->get_title() ), 'error' );

					return false;
				}

				if ( $product_data->get_type() == 'external' ) {
					WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( 'Please use the external site to purchase %s', 'ultimatewoo-pro' ), $product_data->get_title() ), 'error' );

					return false;
				}

				if ( $product_data->is_sold_individually() ) {
					$in_cart_quantity = $check_cart_item_key ? WC()->cart->cart_contents[ $check_cart_item_key ]['quantity'] : 0;

					if ( $in_cart_quantity > 0 ) {
						WC_Wishlist_Compatibility::wc_add_notice( sprintf(
							'<a href="%s" class="button wc-forward">%s</a> %s', wc_get_cart_url(), __( 'View Cart', 'woocommerce' ), sprintf( __( 'You cannot add another &quot;%s&quot; to your cart.', 'woocommerce' ), $product_data->get_title() )
						), 'error' );

						return false;
					}
				}


				$add_on_data['wishlist-data']['list'] = array(
					'name'    => $wishlist_prefix,
					'value'   => $wishlist->id,
					'display' => get_the_title( $wishlist->id ),
					'price'   => false
				);

				$add_on_data['wishlist-data']['item'] = array(
					'name'    => false,
					'value'   => $wishlist_item_key,
					'display' => false,
					'price'   => false
				);

				$add_on_data['wishlist-data']['customer'] = array(
					'name'    => false,
					'value'   => $wishlist->get_wishlist_owner(),
					'display' => false,
					'price'   => false
				);

				$add_on_data = apply_filters( 'woocommerce_copy_cart_item_data', $add_on_data, (int) $wishlist_item['product_id'], $wishlist_item );
				$quantity    = 1;

				if ( WC()->cart->add_to_cart( (int) $cart_item['product_id'], $cart_item['quantity'], $cart_item['variation_id'], $cart_item['variation'], $add_on_data ) ) {

					if ( !$suppress_messages ) {
						$message = sprintf( __( 'Product successfully added to your cart.', 'woocommerce' ) );
						if ( WC_Wishlist_Compatibility::is_wc_version_gte_2_1() ) {
							$message = apply_filters( 'wc_add_to_cart_message', $message );
						} else {
							$message = apply_filters( 'woocommerce_add_to_cart_message', $message );
						}

						WC_Wishlist_Compatibility::wc_add_notice( $message );
					}

					$result = self::get_add_to_cart_redirect_url( $wishlist->id );
				} else {

					WC_Wishlist_Compatibility::wc_add_notice( __( 'Unable to add product to the cart. Please try again', 'ultimatewoo-pro' ), 'error' );
					$result = false;
				}
			}
		}

		return $result;
	}

	private static function get_add_to_cart_redirect_url( $wishlist_id ) {
		$wishlist = new WC_Wishlists_Wishlist( $wishlist_id );

		if ( is_page( WC_Wishlists_Pages::get_page_id( 'edit-my-list' ) ) ) {
			$w_url = WC_Wishlists_Wishlist::get_the_url_edit( $wishlist->id );
		} else {
			$wishlist_sharing = $wishlist->get_wishlist_sharing();
			$w_url            = '';
			if ( $wishlist_sharing == 'Public' ) {
				$w_url = WC_Wishlists_Wishlist::get_the_url_view( $wishlist->id );
			} elseif ( $wishlist_sharing == 'Shared' ) {
				if ( WC_Wishlists_User::get_wishlist_key() != $wishlist->get_wishlist_owner() ) {
					$w_url = WC_Wishlists_Wishlist::get_the_url_view( $wishlist->id, true );
				} else {
					$w_url = WC_Wishlists_Wishlist::get_the_url_view( $wishlist->id );
				}
			} else {
				$w_url = WC_Wishlists_Wishlist::get_the_url_view( $wishlist->id );
			}
		}

		$c_url = apply_filters( 'add_to_cart_from_wishlist_redirect_url', false );
		// If has custom URL redirect there
		if ( $c_url ) {
			$result = $c_url;
		} else {
			$result = $w_url;
		}

		return $result;
	}

}
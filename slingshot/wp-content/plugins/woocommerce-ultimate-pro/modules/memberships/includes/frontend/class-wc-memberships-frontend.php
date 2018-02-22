<?php
/**
 * WooCommerce Memberships
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Memberships to newer
 * versions in the future. If you wish to customize WooCommerce Memberships for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-memberships/ for more information.
 *
 * @package   WC-Memberships/Frontend
 * @author    SkyVerge
 * @category  Frontend
 * @copyright Copyright (c) 2014-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Frontend class, handles general frontend functionality.
 *
 * @since 1.0.0
 */
class WC_Memberships_Frontend {


	/** @var \WC_Memberships_Checkout instance */
	protected $checkout;

	/** @var \WC_Memberships_Members_Area instance */
	protected $members_area;

	/** @var array associative array for caching membership content classes */
	private $membership_content_classes = array();


	/**
	 * Initializes the frontend classes and hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// load classes
		$this->members_area = wc_memberships()->load_class( '/includes/frontend/class-wc-memberships-members-area.php', 'WC_Memberships_Members_Area' );
		$this->checkout     = wc_memberships()->load_class( '/includes/frontend/class-wc-memberships-checkout.php',     'WC_Memberships_Checkout' );

		// enqueue JS and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );

		// handle frontend actions
		add_action( 'template_redirect', array( $this, 'cancel_membership' ) );
		add_action( 'template_redirect', array( $this, 'renew_membership' ) );

		// add CSS classes for content that is part of a membership
		add_filter( 'body_class', array( $this, 'add_membership_content_body_class' ), 10, 1 );
		add_filter( 'post_class', array( $this, 'add_membership_content_post_class' ), 10, 3 );

		// display a thank you message when a membership is granted upon order received
		add_action( 'woocommerce_thankyou', array( $this, 'maybe_render_thank_you_content' ), 9 );
	}


	/**
	 * Returns the Checkout instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Memberships_Checkout
	 */
	public function get_checkout_instance() {
		return $this->checkout;
	}


	/**
	 * Returns the Members Area handler instance.
	 *
	 * @since 1.7.4
	 *
	 * @return \WC_Memberships_Members_Area
	 */
	public function get_members_area_instance() {
		return $this->members_area;
	}


	/**
	 * Returns the Members Area handler instance.
	 *
	 * TODO remove this method by version 1.10.0 {FN 2017-23-06}
	 *
	 * @since 1.4.0
	 * @deprecated since 1.7.4
	 * @see \WC_Memberships_Frontend::get_members_area_instance() instead
	 *
	 * @return \WC_Memberships_Members_Area
	 */
	public function get_member_area_instance() {
		_deprecated_function( 'wc_memberships()->get_frontend_instance()->get_member_area_instance()', '1.7.4', 'wc_memberships()->get_frontend_instance()->get_members_area_instance()' );
		return $this->get_members_area_instance();
	}


	/**
	 * Enqueues frontend scripts & styles.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts_and_styles() {

		wp_enqueue_style( 'wc-memberships-frontend', wc_memberships()->get_plugin_url() . '/assets/css/frontend/wc-memberships-frontend.min.css', '', WC_Memberships::VERSION );
	}


	/**
	 * Cancels a user membership.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function cancel_membership() {

		if ( ! isset( $_REQUEST['cancel_membership'] ) ) {
			return;
		}

		$user_membership_id = (int) $_REQUEST['cancel_membership'];
		$user_membership    = wc_memberships_get_user_membership( $user_membership_id );

		if ( ! $user_membership ) {

			$notice_message = __( 'Invalid membership.', 'ultimatewoo-pro' );
			$notice_type    = 'error';

		} else {

			if (     current_user_can( 'wc_memberships_cancel_membership', $user_membership_id )
			      && $user_membership->can_be_cancelled()
			      && isset( $_REQUEST['_wpnonce'] )
			      && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wc_memberships-cancel_membership_' . $user_membership_id ) ) {

				$user_membership->cancel_membership( __( 'Membership cancelled by customer.', 'ultimatewoo-pro' ) );

				/**
				 * Filters the user cancelled membership message on frontend.
				 *
				 * @since 1.0.0
				 *
				 * @param string $notice the user membership cancelled notice
				 */
				$notice_message =  apply_filters( 'wc_memberships_user_membership_cancelled_notice', __( 'Your membership was cancelled.', 'ultimatewoo-pro' ) );
				$notice_type    = 'notice';

				/**
				 * Fires right after a membership has been cancelled by a customer.
				 *
				 * @since 1.0.0
				 *
				 * @param int $user_membership_id a user membership ID
				 */
				do_action( 'wc_memberships_cancelled_user_membership', $user_membership_id );

			} else {

				$notice_message = __( 'Cannot cancel this membership.', 'ultimatewoo-pro' );
				$notice_type    = 'error';
			}
		}

		if ( isset( $notice_message, $notice_type ) ) {
			wc_add_notice( $notice_message, $notice_type );
		}

		wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
		exit;
	}


	/**
	 * Logs in a member.
	 *
	 * @since 1.7.0
	 *
	 * @throws \SV_WC_Plugin_Exception
	 * @param \WC_Memberships_User_Membership $user_membership Membership the member to log in belongs to
	 */
	private function log_member_in( $user_membership ) {

		// we're not really concerned with roles since membership / subscription sites probably use custom roles
		// instead, just be sure we don't log anyone in with high permissions
		$log_in_user_id = $user_membership->get_user_id();
		$user_is_admin  = user_can( $log_in_user_id, 'edit_others_posts' ) || user_can( $log_in_user_id, 'edit_users' );

		/**
		 * Lets third party code to toggle whether a user can be logged in automatically.
		 *
		 * @since 1.8.9
		 *
		 * @param bool $allow true if the user should be automatically logged in (default false, do not allow)
		 * @param int $log_in_user_id the user ID of the user to log in
		 */
		$allow_login = (bool) apply_filters( 'wc_memberships_allow_renewal_auto_user_login', false, $log_in_user_id );

		/**
		 * Let actors hook in before logging a member in.
		 *
		 * Can throw SV_WC_Plugin_Exception to halt the login completely.
		 *
		 * @since 1.9.0
		 *
		 * @param int $log_in_user_id the user ID of the member to log in
		 * @param \WC_Memberships_User_Membership $user_membership the user membership instance
		 * @param bool $allow_login whether automatic log in is allowed
		 */
		do_action( 'wc_memberships_before_renewal_auto_login', $log_in_user_id, $user_membership, $allow_login );

		// maybe log in the membership owner
		if ( is_user_logged_in() ) {

			// another user is logged in
			if ( $log_in_user_id !== get_current_user_id() ) {

				// log out existing user
				wp_logout();

				// do not log in a user with high privileges
				if ( ! $user_is_admin || $allow_login ) {

					wp_set_current_user( $log_in_user_id );
					wp_set_auth_cookie( $log_in_user_id );
				}
			}

		} elseif ( ! $user_is_admin || $allow_login ) {

			// log the member in automatically if has low privileges
			wp_set_current_user( $log_in_user_id );
			wp_set_auth_cookie( $log_in_user_id );

		} else {

			throw new SV_WC_Plugin_Exception( __( 'Cannot automatically log in. Please log into your account and renew this membership manually.' , 'ultimatewoo-pro' ) );
		}

		/**
		 * Let actors hook in after logging a member in.
		 *
		 * @since 1.9.0
		 *
		 * @param int $log_in_user_id the user ID of the member to log in
		 * @param \WC_Memberships_User_Membership $user_membership the user membership instance
		 * @param bool $allow_login whether automatic log in is allowed
		 */
		do_action( 'wc_memberships_after_renewal_auto_login', $log_in_user_id, $user_membership, $allow_login );
	}


	/**
	 * Renews a user membership.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function renew_membership() {

		if ( ! isset( $_REQUEST['renew_membership'] ) ) {
			return;
		}

		if ( ! isset( $_REQUEST['user_token'] ) ) {
			wc_add_notice( __( 'Invalid renewal URL. Please log in to your account to renew.', 'ultimatewoo-pro' ), 'error' );
			return;
		}

		$user_membership_id = (int) $_REQUEST['renew_membership'];
		$user_membership    = wc_memberships_get_user_membership( $user_membership_id );
		$user_token         = wc_clean( $_REQUEST['user_token'] );

		// we only need to redirect upon success; we should already be on the account page
		// based on how we generate this renewal URL so no need to redirect there
		try {

			$result       = $this->process_membership_renewal( $user_membership, $user_token );
			$redirect_url = $result['redirect'];

			if ( ! empty( $result['message'] ) ) {
				wc_add_notice( $result['message'], 'success' );
			}

			wp_safe_redirect( $redirect_url );
			exit;

		} catch ( SV_WC_Plugin_Exception $e ) {

			wc_add_notice( $e->getMessage(), 'error' );
			return;
		}
	}


	/**
	 * Processes user membership renewals with a valid renewal link.
	 *
	 * @since 1.8.9
	 *
	 * @throws \SV_WC_Plugin_Exception
	 *
	 * @param \WC_Memberships_User_Membership $user_membership user membership instance
	 * @param string $token user membership renewal token
	 * @return string[] updated redirect URL with a success message
	 */
	protected function process_membership_renewal( $user_membership, $token ) {

		if ( ! $user_membership instanceof WC_Memberships_User_Membership ) {
			throw new SV_WC_Plugin_Exception( __( 'Invalid membership.', 'ultimatewoo-pro' ) );
		}

		if ( $user_membership->can_be_renewed() ) {

			$renewal_token = $user_membership->get_renewal_login_token();

			// check the token in the URL with the user membership's stored token
			if ( ! isset( $renewal_token['token'] ) || $token !== $renewal_token['token'] ) {
				throw new SV_WC_Plugin_Exception( __( 'Invalid renewal token. Please log in to renew this membership.', 'ultimatewoo-pro' ) );
			}

			if ( ! isset( $renewal_token['expires'] ) || (int) $renewal_token['expires'] < time() ) {

				// wipe expired renewal token meta
				$user_membership->delete_renewal_login_token();

				throw new SV_WC_Plugin_Exception( __( 'Cannot log in as your renewal token has expired. Please log in to renew this membership from your account.', 'ultimatewoo-pro' ) );
			}

			// makes sure the member is logged in
			$this->log_member_in( $user_membership );

			// get the renewal product to be added to cart
			$product_for_renewal = $user_membership->get_product_for_renewal();

			/* this filter is documented in /includes/class-wc-memberships-membership-plan.php */
			$renew = (bool) apply_filters( 'wc_memberships_renew_membership', (bool) $product_for_renewal, $user_membership->get_plan(), array(
				'user_id'    => $user_membership->get_user_id(),
				'product_id' => $product_for_renewal->get_id(),
				'order_id'   => $user_membership->get_order_id(),
			) );

			if ( true === $renew && current_user_can( 'wc_memberships_renew_membership', $user_membership->get_id() ) ) {

				/**
				 * Filter whether to add to cart the renewal product and redirect to checkout, or redirect to the product page without adding it to cart.
				 *
				 * @since 1.7.4
				 *
				 * @param bool $add_to_cart whether to add to cart the product and redirect to checkout (true, default) or redirect to product page instead (false).
				 * @param \WC_Product $product_for_renewal the product that would renew access if purchased again.
				 * @param int $user_membership_id the membership being renewed upon purchase.
				 */
				if ( true === (bool) apply_filters( 'wc_memberships_add_to_cart_renewal_product', true, $product_for_renewal, $user_membership->get_id() ) ) {

					// empty the cart and add the one product to renew this membership
					wc_empty_cart();

					// set up variation data (if needed) before adding to the cart
					$product_id           = $product_for_renewal->is_type( 'variation' ) ? SV_WC_Product_Compatibility::get_prop( $product_for_renewal, 'parent_id' ) : $product_for_renewal->get_id();
					$variation_id         = $product_for_renewal->is_type( 'variation' ) ? $product_for_renewal->get_id() : 0;
					$variation_attributes = $product_for_renewal->is_type( 'variation' ) ? wc_get_product_variation_attributes( $variation_id ) : array();

					// add the product to the cart
					WC()->cart->add_to_cart( $product_id, 1, $variation_id, $variation_attributes );

					// then redirect to checkout instead of my account page
					$redirect_url = wc_get_checkout_url();

				} else {

					$redirect_url = get_permalink( $product_for_renewal->is_type( 'variation' ) ? SV_WC_Product_Compatibility::get_prop( $product_for_renewal, 'parent_id' ) : $product_for_renewal->get_id() );
				}

				/* translators: Placeholder: %s - a product to purchase to renew a membership */
				$message  = sprintf( __( 'Renew your membership by purchasing %s.', 'ultimatewoo-pro' ) . ' ', $product_for_renewal->get_title() );
				$message .= is_user_logged_in() ? ' ' : __( 'You must be logged to renew your membership.', 'ultimatewoo-pro' );

			} else {

				throw new SV_WC_Plugin_Exception( __( 'Cannot renew this membership. Please contact us if you need assistance.', 'ultimatewoo-pro' ) );
			}

		} else {

			throw new SV_WC_Plugin_Exception( __( 'This membership cannot be renewed. Please contact us if you need assistance.', 'ultimatewoo-pro' ) );
		}

		return array( 'redirect' => $redirect_url, 'message' => $message );
	}


	/**
	 * Prints a thank you message on the "Order Received" page when a membership is purchased.
	 *
	 * @since 1.8.4
	 *
	 * @param int $order_id the order ID
	 */
	public function maybe_render_thank_you_content( $order_id ) {

		echo wp_kses_post( wc_memberships_get_order_thank_you_links( $order_id ) );
	}


	/**
	 * Returns membership content CSS classes.
	 *
	 * @since 1.9.5
	 *
	 * @param \WP_Post|int $post post object or ID
	 * @return string[]
	 */
	private function get_membership_content_classes( $post ) {

		$post_id             = 0;
		$memberships_classes = array();

		if ( is_numeric( $post ) ) {
			$post_id = (int) $post;
		} elseif ( $post instanceof WP_Post ) {
			$post_id = $post->ID;
		}

		if ( $post_id > 0 ) {

			if ( isset( $this->membership_content_classes[ $post_id ] ) ) {

				$memberships_classes = $this->membership_content_classes[ $post_id ];

			} elseif ( ! wc_memberships_is_members_area() ) {

				if ( 'product' === get_post_type( $post ) ) {

					if ( wc_memberships_is_product_viewing_restricted( $post_id ) ) {

						$memberships_classes[] = 'membership-content';

						if ( current_user_can( 'wc_memberships_view_restricted_product', $post_id ) ) {
							if ( ! current_user_can( 'wc_memberships_view_delayed_product', $post_id ) ) {
								$memberships_classes[] = 'access-delayed';
							} else {
								$memberships_classes[] = 'access-granted';
							}
						} else {
							$memberships_classes[] = 'access-restricted';
						}
					}

					if ( wc_memberships_is_product_purchasing_restricted( $post_id ) ) {

						$memberships_classes[] = 'membership-content';

						if ( current_user_can( 'wc_memberships_purchase_restricted_product', $post_id ) ) {
							if ( ! current_user_can( 'wc_memberships_purchase_delayed_product', $post_id ) ) {
								$memberships_classes[] = 'purchase-delayed';
							} else {
								$memberships_classes[] = 'purchase-granted';
							}
						} else {
							$memberships_classes[] = 'purchase-restricted';
						}
					}

					if ( wc_memberships_product_has_member_discount( $post_id ) ) {

						$memberships_classes[] = 'member-discount';

						if ( wc_memberships_user_has_member_discount( $post_id ) ) {
							$memberships_classes[] = 'discount-granted';
						} else {
							$memberships_classes[] = 'discount-restricted';
						}
					}

				} elseif ( wc_memberships_is_post_content_restricted( $post_id ) ) {

					$memberships_classes[] = 'membership-content';

					if ( current_user_can( 'wc_memberships_view_restricted_post_content', $post_id ) ) {
						if ( ! current_user_can( 'wc_memberships_view_delayed_post_content', $post_id ) ) {
							$memberships_classes[] = 'access-delayed';
						} else {
							$memberships_classes[] = 'access-granted';
						}
					} else {
						$memberships_classes[] = 'access-restricted';
					}
				}

				$this->membership_content_classes[ $post_id ] = $memberships_classes;
			}
		}

		return $memberships_classes;
	}


	/**
	 * Adds CSS classes to the <body> HTML tag when viewing memberships content.
	 *
	 * @internal
	 *
	 * @since 1.9.5
	 *
	 * @param string[] $classes an array of CSS classes
	 * @return string[]
	 */
	public function add_membership_content_body_class( $classes ) {
		global $post;

		if ( is_array( $classes ) ) {

			$memberships_classes = array();

			if ( is_singular() ) {

				if ( wc_memberships_is_members_area() ) {
					$memberships_classes = array( 'members-area' );
				} else {
					$memberships_classes = $this->get_membership_content_classes( $post );
				}

				if ( ! empty( $memberships_classes ) ) {

					$is_member = current_user_can( 'wc_memberships_access_all_restricted_content' );

					if ( ! $is_member ) {

						if ( wc_memberships_is_members_area() ) {

							$is_member = wc_memberships_is_user_member();

						} elseif ( 'product' === get_post_type( $post ) ) {

							if ( wc_memberships_is_product_viewing_restricted() ) {
								$is_member = current_user_can( 'wc_memberships_view_restricted_product', $post->ID );
							}

							if ( wc_memberships_is_product_purchasing_restricted() ) {
								$is_member = current_user_can( 'wc_memberships_purchase_restricted_product', $post->ID );
							}

						} else {

							$is_member = current_user_can( 'wc_memberships_view_restricted_post_content', $post->ID );
						}
					}

					if ( $is_member ) {
						$memberships_classes[] = 'member-logged-in';
					}
				}
			}

			if (    empty( $memberships_classes )
			     && wc_memberships_is_user_active_member()
			     && ( is_archive() || get_queried_object_id() === (int) get_option( 'page_on_front' ) ) ) {

				$memberships_classes[] = 'member-logged-in';
			}

			$classes = array_merge( $classes, $memberships_classes );
		}

		return $classes;
	}


	/**
	 * Adds CSS classes to the post classes when viewing memberships content.
	 *
	 * @internal
	 *
	 * @since 1.9.5
	 *
	 * @param string[] $classes array of post classes
	 * @param string[] $additional_classes an array of additional classes added to the post
	 * @param int $post_id the current WP_Post ID
	 * @return string[]
	 */
	public function add_membership_content_post_class( $classes, $additional_classes, $post_id ) {
		return array_merge( $classes, $this->get_membership_content_classes( $post_id ) );
	}


	/**
	 * Handles deprecated methods.
	 *
	 * @since 1.9.0
	 *
	 * @param string $method method invoked
	 * @param array $args optional method arguments
	 * @return void|null|mixed
	 */
	public function __call( $method, $args ) {

		$deprecated = "WC_Memberships_Frontend_::{$method}()";

		switch ( $method ) {

			/* @deprecated since 1.9.0 - remove this by 1.12.0 or higher */
			case 'get_content_delayed_message' :
				_deprecated_function( $deprecated, '1.9.0', 'WC_Memberships_User_Messages::get_message_html()' );

				$user_id     = ! empty( $args[0] ) ? $args[0] : get_current_user_id();
				$access_type = isset( $args[2] )   ? $args[2] : '';

				if ( empty( $args[1] ) ) {
					global $post;
					$post_id = $post ? $post->ID : 0;
				} else {
					$post_id = $args[1];
				}

				$args = array(
					'post_id'     => $post_id,
					'access_time' => wc_memberships()->get_capabilities_instance()->get_user_access_start_time_for_post( $user_id, $post_id, $access_type ),
					'context'     => 'notice',
				);

				switch ( get_post_type( $post_id ) ) {
					case 'product':
					case 'product_variation':
						$message_code = 'product_access_delayed';
					break;
					case 'page':
						$message_code = 'page_content_delayed';
					break;
					case 'post':
						$message_code = 'post_content_delayed';
					break;
					default:
						$message_code = 'content_delayed';
					break;
				}

				return WC_Memberships_User_Messages::get_message_html( $message_code, $args );

			/* @deprecated since 1.9.0 - remove this by 1.12.0 or higher */
			case 'get_content_restricted_message' :
				_deprecated_function( $deprecated, '1.9.0', 'WC_Memberships_User_Messages::get_message_html()' );
				$args = isset( $args[0] ) ? array( 'post_id' => $args[0] ) : array( 'post_id' => $args );
				return WC_Memberships_User_Messages::get_message_html( 'content_restricted', $args );

			/* @deprecated since 1.9.0 - remove this by 1.12.0 or higher */
			case 'get_member_discount_message' :
				_deprecated_function( $deprecated, '1.9.0', "WC_Memberships_User_Messages::get_message_html( 'product_discount' )" );
				$args = isset( $args[0] ) ? array( 'post_id' => $args[0] ) : array( 'post_id' => $args );
				return WC_Memberships_User_Messages::get_message_html( 'product_discount', $args );

			/* @deprecated since 1.9.0 - remove this by 1.12.0 or higher */
			case 'get_product_purchasing_restricted_message' :
				_deprecated_function( $deprecated, '1.9.0', "WC_Memberships_User_Messages::get_message_html( 'product_purchasing_restricted' )" );
				$args = isset( $args[0] ) ? array( 'post_id' => $args[0] ) : array( 'post_id' => $args );
				return WC_Memberships_User_Messages::get_message_html( 'product_purchasing_restricted', $args );

			/* @deprecated since 1.9.0 - remove this by 1.12.0 or higher */
			case 'get_product_viewing_restricted_message' :
				_deprecated_function( $deprecated, '1.9.0', "WC_Memberships_User_Messages::get_message_html( 'product_viewing_restricted' )" );
				$args = isset( $args[0] ) ? array( 'post_id' => $args[0] ) : array( 'post_id' => $args );
				return WC_Memberships_User_Messages::get_message_html( 'product_viewing_restricted', $args );

			/* @deprecated since 1.9.0 - remove this by 1.12.0 or higher */
			case 'get_product_taxonomy_term_delayed_message' :
				_deprecated_function( $deprecated, '1.9.0', "WC_Memberships_User_Messages::get_message_html( 'product_category_viewing_delayed' )" );
				return WC_Memberships_User_Messages::get_message_html( 'product_category_viewing_delayed' );

			/* @deprecated since 1.9.0 - remove this by 1.12.0 or higher */
			case 'get_product_taxonomy_term_viewing_restricted_message';
				_deprecated_function( $deprecated, '1.9.0', "WC_Memberships_User_Messages::get_message_html( 'product_category_viewing_restricted' )" );
				return WC_Memberships_User_Messages::get_message_html( 'product_category_viewing_restricted' );

			/* @deprecated since 1.9.0 - remove this by 1.12.0 or higher */
			case 'get_restrictions_instance' :
				_deprecated_function( 'wc_memberships()->get_frontend_instance()->get_restrictions_instance()', '1.9.0', 'wc_memberships()->get_restrictions_instance()' );
				return wc_memberships()->get_restrictions_instance();

			/* @deprecated since 1.9.0 - remove this by 1.12.0 or higher */
			case 'get_valid_restriction_message_types' :
				_deprecated_function( $deprecated, '1.9.0', 'WC_Memberships_User_Messages::get_default_messages( false )' );
				return WC_Memberships_User_Messages::get_default_messages( false );

			/* @deprecated since 1.9.0 - remove this by 1.12.0 or higher */
			case 'restricted_content_redirect' :
				_deprecated_function( $deprecated, '1.9.0', 'WC_Memberships_Posts_Restrictions::redirect_to_member_content_upon_login()' );
				return wc_memberships()->get_restrictions_instance()->get_posts_restrictions_instance()->redirect_to_member_content_upon_login( isset( $args[0] ) ? $args[0] : $args );

			// you're probably doing it wrong...
			default :
				trigger_error( "Call to undefined method {$deprecated}", E_USER_ERROR );
				return null;
		}
	}


}

<?php
/**
 * WooCommerce Constant Contact
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Constant Contact to newer
 * versions in the future. If you wish to customize WooCommerce Constant Contact for your
 * needs please refer to http://www.skyverge.com/contact/ for more information.
 *
 * @package     WC-Constant-Contact/API
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * A simple wrapper for the Constant Contact SDK
 *
 * @link http://community.constantcontact.com/t5/Documentation/API-Quick-Reference/ba-p/36047
 *
 * @since 1.0
 */
class WC_Constant_Contact_API extends SV_WC_API_Base {


	/** string API production endpoint */
	const ENDPOINT = 'https://api.constantcontact.com/ws/customers';

	/** @var string username for constantcontact.com */
	private $username;


	/**
	 * Setup the API
	 *
	 * @since 1.0
	 * @param string $username the constantcontact.com username
	 * @param string $password the constantcontact.com password
	 * @param string $api_key the constant contact API key
	 * @return \WC_Constant_Contact_API
	 */
	public function __construct( $username, $password, $api_key ) {

		$this->username = $username;

		// set response handler
		$this->response_handler = 'WC_Constant_Contact_API_Response';

		// set content type
		$this->set_request_content_type_header( 'application/atom+xml;type=entry' );

		// set auth
		$this->set_http_basic_auth( "{$api_key}%{$username}", $password );
	}


	/**
	 * Gets the available email lists from the constant contact API
	 *
	 * GET /lists - returns all contact lists for the account
	 *
	 * @since 1.0
	 * @return array lists in the format 'list_id' => humanized list name
	 */
	public function get_lists() {

		// GET /lists
		$this->request_method = 'GET';
		$this->set_request_uri( 'lists' );

		$response = $this->perform_request( $this->get_new_request() );

		$lists = $response->get_lists();

		// check if there are additional lists to get, constant contact will paginate after the first 50 lists
		foreach ( $response->get_parsed_response()->link as $link ) {

			// found a link element pointing to the next set of lists
			if ( isset( $link['rel'] ) && 'next' === (string) $link['rel'] ) {

				// grab the query string of the next URL, looks like `next=50`
				$next_query_arg = parse_url( (string) $link['href'], PHP_URL_QUERY );
				break;
			}
		}

		// fetch the next set of lists
		// this isn't true pagination handling but should fit the majority of use cases (<100 lists)
		if ( ! empty( $next_query_arg ) ) {

			$this->set_request_uri( "lists?{$next_query_arg}" );

			$response = $this->perform_request( $this->get_new_request() );

			$lists = array_merge( $lists, $response->get_lists() );
		}

		return $lists;
	}


	/**
	 * Adds the given email to the selected constant contact list.
	 * If a list is not provided, the default list set in WooCommerce > Settings > Constant Contact
	 * will be used
	 *
	 * @since 1.0
	 * @param string $email the email to subscribe
	 * @param string $list_id optional; the list ID to subscribe the email to, defaults to the active list
	 */
	public function subscribe( $email, $list_id = null ) {

		// use the default list if not set
		if ( ! $list_id ) {
			$list_id = get_option( 'wc_constant_contact_email_list' );
		}

		// update contact if they exist already
		if ( $this->contact_exists( $email ) ) {

			$this->add_contact_to_list( $email, $list_id );

		} else {

			// otherwise create the contact
			$this->create_contact( $email, $list_id );
		}

		/** This action is documented in the subscribe_customer() method */
		do_action( 'wc_constant_contact_customer_subscribed', $email );
	}


	/**
	 * Checks if a contact exists in the constant contact account already
	 *
	 * GET /contacts?email={email} - returns an empty feed list without entries if the contact doesn't exist, populated list otherwise
	 *
	 * Per http://community.constantcontact.com/t5/Documentation/Tips-Tricks-and-Known-Issues-Using-the-WebServices-APIs/ba-p/24905
	 * this method will sometimes return a feed list without any entries, or error 500, which is why the exception is caught and checked
	 * inside the method
	 *
	 * @link http://community.constantcontact.com/t5/Documentation/Searching-for-a-Contact-by-Email-Address/ba-p/25123
	 * @since 1.0.1
	 * @param string $email the email address to check
	 * @throws Exception rethrows previous exception if not a 500 error code
	 * @return bool, true if the contact exists, false otherwise
	 */
	private function contact_exists( $email ) {

		try {

			// GET /contacts?email={email}
			$this->request_method = 'GET';
			$this->set_request_uri( "contacts?email={$email}" );

			$response = $this->perform_request( $this->get_new_request() );

			return $response->has_entry();

		} catch ( SV_WC_API_Exception $e ) {

			if ( '500' == $e->getCode() ) {
				return false;
			} else {
				throw $e;
			}
		}
	}


	/**
	 * Adds an existing contact to a new list
	 *
	 * PUT /contacts/{contact_id} with XML containing list to add contact to
	 *
	 * @link http://community.constantcontact.com/t5/Documentation/Adding-a-Contact-to-a-List/ba-p/25121
	 * @since 1.0.1
	 * @param string $email the email address to add
	 * @param string $list_id the list ID to add the contact to
	 * @throws Exception required data missing
	 */
	private function add_contact_to_list( $email, $list_id ) {

		// GET /contacts?email={email}
		$this->request_method = 'GET';
		$this->set_request_uri( "contacts?email={$email}" );

		// first, get the contact ID via their email
		$response = $this->perform_request( $this->get_new_request() );

		$contact_id = $response->get_contact_id();

		// GET /contacts/{id}
		$this->request_method = 'GET';
		$this->set_request_uri( "contacts/{$contact_id}" );

		// then, get the contact data to be updated
		$response = $this->perform_request( $this->get_new_request() );

		$contact = $response->get_parsed_response();

		// PUT /contacts/{id}
		$this->request_method = 'PUT';
		$this->set_request_uri( "contacts/{$contact_id}" );

		$request = $this->get_new_request();

		$request->add_contact_to_list( $contact, $list_id );

		// finally, update the contact
		$this->perform_request( $request );
	}


	/**
	 * Creates a new contact and adds them to the provided list
	 *
	 * POST /contacts with the required XML to create the contact
	 *
	 * @link http://community.constantcontact.com/t5/Documentation/Creating-a-Contact/ba-p/25059
	 * @since 1.0.1
	 * @param string $email the email address to add
	 * @param string $list_id the list ID to add the contact to
	 * @param null|array $contact_data optional data to create the contact with, array keys must be the proper constant contact field names
	 * @throws Exception required data missing
	 * @return string the newly-created contact ID
	 */
	private function create_contact( $email, $list_id, $contact_data = null ) {

		// POST /contacts
		$this->request_method = 'POST';
		$this->set_request_uri( 'contacts' );

		$request = $this->get_new_request();

		$request->create_contact( $email, $list_id, $contact_data );

		return $this->perform_request( $request )->get_id();
	}


	/**
	 * Retrieves stats for the given list ID from Constant Contact
	 * Due to limitations with the v1 API, this only includes total subscribers at the moment,
	 * but could be easily extended to include more info as the API improves
	 *
	 * GET /lists
	 *
	 * @since 1.0
	 * @param string $list_id the list ID to retrieve stats for
	 * @throws Exception required data is missing
	 * @return array stats in the format 'list_name' => <list name>, 'list_subscribers' => <total subscribers for list>
	 */
	public function get_stats( $list_id ) {

		// list IDs look like "http://api.constantcontact.com/ws/customers/jgalt%40sogetthis.com/lists/2" so this pulls the part of the path
		$list_id = substr( strrchr( $list_id, '/' ), 1 );

		// GET /lists/{id}
		$this->request_method = 'GET';
		$this->set_request_uri( "lists/{$list_id}" );

		return $this->perform_request( $this->get_new_request() )->get_stats();
	}


	/** Quasi-API methods *****************************************************/


	/**
	 * Checks if the customer has already opted in:
	 *
	 * + if the user is logged in and has a constant contact ID saved to their
	 *   user meta, or
	 *
	 * + if the 'wc_constant_contact_option' session key is set - this is used
	 *   for guest checkouts who may have opted in on the checkout page, and should
	 *   not be shown on the subscribe message on the order received page
	 *
	 * @since 1.0
	 */
	public function customer_has_already_subscribed() {

		if ( is_user_logged_in() && metadata_exists( 'user', get_current_user_id(), '_wc_constant_contact_id' ) ) {
			return true;
		}

		return ( isset( WC()->session->wc_constant_contact_subscribed ) && WC()->session->wc_constant_contact_subscribed );
	}


	/**
	 * Adds the customer for the given order to the admin-selected Constant Contact list
	 *
	 * @since 1.0
	 * @param int $order_id the WC_Order ID
	 */
	public function subscribe_customer( $order_id ) {

		$order         = wc_get_order( $order_id );
		$billing_email = SV_WC_Order_Compatibility::get_prop( $order, 'billing_email' );

		$list_id = get_option( 'wc_constant_contact_email_list' );

		// update the contact if they exist already
		if ( $this->contact_exists( $billing_email ) ) {

			$this->add_contact_to_list( $billing_email, $list_id );

		} else {

			// setup new contact data
			$customer_data = apply_filters( 'wc_constant_contact_new_contact_data', array(
				'FirstName'   => SV_WC_Order_Compatibility::get_prop( $order, 'billing_first_name' ),
				'LastName'    => SV_WC_Order_Compatibility::get_prop( $order, 'billing_last_name' ),
				'Addr1'       => SV_WC_Order_Compatibility::get_prop( $order, 'billing_address_1' ),
				'Addr2'       => SV_WC_Order_Compatibility::get_prop( $order, 'billing_address_2' ),
				'City'        => SV_WC_Order_Compatibility::get_prop( $order, 'billing_city' ),
				'State'       => SV_WC_Order_Compatibility::get_prop( $order, 'billing_state' ),
				'PostalCode'  => SV_WC_Order_Compatibility::get_prop( $order, 'billing_postcode' ),
				'CountryCode' => SV_WC_Order_Compatibility::get_prop( $order, 'billing_country' ),
			) );

			// create the contact
			$contact_id = $this->create_contact( $billing_email, $list_id, $customer_data );

			// set as user meta so we don't add an opt-in when the customer checks out next
			if ( $order->get_user_id() && $contact_id ) {
				update_user_meta( $order->get_user_id(), '_wc_constant_contact_id', $contact_id );
			}
		}

		// mark as opted in
		WC()->session->wc_constant_contact_subscribed = true;

		/**
		 * Constant Contact Customer Subscribed Action.
		 *
		 * Fired when a customer subscribes to a list in Constant Contact
		 *
		 * @since 1.1
		 *
		 * @param string $billing_email
		 */
		do_action( 'wc_constant_contact_customer_subscribed', $billing_email );

		// add order note
		$order->add_order_note( __( 'Customer subscribed to email list', 'ultimatewoo-pro' ) );
	}


	/** API Helper methods ****************************************************/


	/**
	 * Check if the response has any errors
	 *
	 * @since 1.3.1
	 * @see \SV_WC_API_Base::do_post_parse_response_validation()
	 * @throws \SV_WC_API_Exception if response has API error
	 */
	protected function do_pre_parse_response_validation() {

		// check error statuses - http://community.constantcontact.com/t5/Documentation/Error-Codes/ba-p/25077
		if ( ! in_array( $this->get_response_code(), array( '200', '201', '204' ) ) ) {
			throw new SV_WC_API_Exception( sprintf( __( 'Error Code %s', 'ultimatewoo-pro' ), $this->get_response_code() ), $this->get_response_code() );
		}
	}


	/**
	 * Set the request URI
	 *
	 * @since 1.3.1
	 * @param $route
	 */
	protected function set_request_uri( $route ) {

		$this->request_uri = self::ENDPOINT . "/{$this->username}/" . $route;
	}


	/**
	 * Builds and returns a new API request object
	 *
	 * @since 1.3.1
	 * @see \SV_WC_API_Base::get_new_request()
	 * @param array $type unused
	 * @return \WC_Constant_Contact_API_Request API request object
	 */
	protected function get_new_request( $type = array() ) {

		return new WC_Constant_Contact_API_Request();
	}


	/**
	 * Returns the main plugin class
	 *
	 * @since 1.3.1
	 * @see \SV_WC_API_Base::get_plugin()
	 * @return \WC_Constant_Contact
	 */
	protected function get_plugin() {
		return wc_constant_contact();
	}


}

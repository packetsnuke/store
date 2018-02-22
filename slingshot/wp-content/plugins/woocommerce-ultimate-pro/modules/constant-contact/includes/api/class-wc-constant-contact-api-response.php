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
 * Constant Contact API Request Class
 *
 * Parses XML received by Constant Contact API
 *
 * @since 1.3.1
 */
class WC_Constant_Contact_API_Response implements SV_WC_API_Response {


	/** @var string string representation of this response */
	private $raw_response_xml;

	/** @var SimpleXMLElement response XML object */
	protected $response_xml;


	/**
	 * Build a response object from the raw response xml
	 *
	 * @since 1.3.1
	 * @param string $raw_response_xml the raw response XML
	 */
	public function __construct( $raw_response_xml ) {

		$this->raw_response_xml = $raw_response_xml;

		if ( $raw_response_xml ) {

			// LIBXML_NOCDATA ensures that any XML fields wrapped in [CDATA] will be included as text nodes
			$this->response_xml = new SimpleXMLElement( $raw_response_xml, LIBXML_NOCDATA );
		}
	}


	/**
	 * Parse the GET /lists response into an array
	 *
	 * $list[ $list_id ] => $list_name
	 *
	 * @since 1.1
	 * @throws SV_WC_API_Exception
	 * @return array
	 */
	public function get_lists() {

		if ( empty( $this->response_xml->entry ) ) {
			throw new SV_WC_API_Exception( __( 'Get Lists - Entries are missing', 'ultimatewoo-pro' ) );
		}

		$lists = array();

		foreach ( $this->response_xml->entry as $list ) {

			// exclude default lists
			if ( in_array( (string) $list->title, array( 'Active', 'Do Not Mail', 'Removed' ) ) ) {
				continue;
			}

			$contact_count = ( isset( $list->content->ContactList->ContactCount ) ) ? $list->content->ContactList->ContactCount : 0;

			// format each list like: "<list name> (<list count> contacts)"
			$lists[ (string) $list->id ] = sprintf( '%1$s (%2$d %3$s)',
				$list->title,
				$contact_count,
				( $contact_count > 0 ) ? _n( 'contact', 'contacts', $contact_count, 'ultimatewoo-pro' ) : __( 'no contacts', 'ultimatewoo-pro' )
			);
		}

		return $lists;
	}


	/**
	 * Checks if the response has a feed entry, primarily used for checking
	 * if a contact exists
	 *
	 * @since 1.3.1
	 * @return bool
	 */
	public function has_entry() {

		return ! empty( $this->response_xml->entry );
	}


	/**
	 * Get the ID for a contact
	 *
	 * @since 1.3.1
	 * @throws \SV_WC_API_Exception if contact ID is missing
	 * @return string
	 */
	public function get_contact_id() {

		if ( empty( $this->response_xml->entry->id ) ) {
			throw new SV_WC_API_Exception( __( 'Contact ID is missing', 'ultimatewoo-pro' ) );
		}

		return substr( strrchr( (string) $this->response_xml->entry->id, '/' ), 1 );
	}


	/**
	 * Get ID
	 *
	 * @throws SV_WC_API_Exception
	 * @return string
	 */
	public function get_id() {

		if ( empty( $this->response_xml->id ) ) {
			throw new SV_WC_API_Exception( __( 'Created contact ID missing', 'ultimatewoo-pro' ) );
		}

		return (string) $this->response_xml->id;
	}


	/**
	 * Get Stats
	 *
	 * @throws SV_WC_API_Exception
	 * @return array
	 */
	public function get_stats() {

		if ( empty( $this->response_xml->id ) ) {
			throw new SV_WC_API_Exception( __( 'List ID is missing', 'ultimatewoo-pro' ) );
		}

		if ( ! empty( $this->response_xml->title ) && ! empty( $this->response_xml->content->ContactList->ContactCount ) ) {

			return array(
				'list_name'        => (string) $this->response_xml->title,
				'list_subscribers' => (int) $this->response_xml->content->ContactList->ContactCount
			);

		} else {

			return array();
		}
	}


	/**
	 * Get parsed response
	 *
	 * @return \SimpleXMLElement
	 */
	public function get_parsed_response() {

		return $this->response_xml;
	}


	/**
	 * Returns the string representation of this response
	 *
	 * @since 1.3.1
	 * @see SV_WC_API_Response::to_string()
	 * @return string response
	 */
	public function to_string() {

		$string = $this->raw_response_xml;

		$dom = new DOMDocument();

		// suppress errors for invalid XML syntax issues
		if ( @$dom->loadXML( $string ) ) {
			$dom->formatOutput = true;
			$string = $dom->saveXML();
		}

		return $string;
	}


	/**
	 * Returns the string representation of this response with any and all
	 * sensitive elements masked or removed
	 *
	 * @since 1.3.1
	 * @see SV_WC_API_Response::to_string_safe()
	 * @return string response safe for logging/displaying
	 */
	public function to_string_safe() {

		// no sensitive data to mask
		return $this->to_string();
	}


}

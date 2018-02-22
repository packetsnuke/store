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
 * Generates XML to communicate with Constant Contact API
 *
 * @since 1.3.1
 * @extends XMLWriter
 */
class WC_Constant_Contact_API_Request extends XMLWriter implements SV_WC_API_Request {


	/** @var SimpleXML instance */
	private $simple_xml;

	/** @var string generated request XML */
	private $request_xml;


	/**
	 * Open XML document in memory and set version/encoding
	 *
	 * @since 1.3.1
	 * @return \WC_Constant_Contact_API_Request
	 */
	public function __construct() {

		// Create XML document in memory
		$this->openMemory();

		// Set XML version & encoding
		$this->startDocument( '1.0', 'UTF-8' );
	}


	/**
	 * Add contact to list
	 *
	 * @param $contact
	 * @param $list_id
	 * @throws SV_WC_API_Exception
	 */
	public function add_contact_to_list( $contact, $list_id ) {

		if ( empty( $contact->content->Contact ) ) {
			throw new SV_WC_API_Exception( __( 'Add contact to List: Contact is missing', 'ultimatewoo-pro' ) );
		}

		// if the contact isn't subscribed to any lists, add the lists root
		if ( empty( $contact->content->Contact->ContactLists ) ) {
			$contact->content->Contact->addChild( 'ContactLists' );
		}

		// add the list to their contact data
		$list = $contact->content->Contact->ContactLists->addChild( 'ContactList' );
		$list->addAttribute( 'id', $list_id );
		$list->addChild( 'OptInSource', 'ACTION_BY_CUSTOMER' );

		$this->simple_xml = $contact;
	}


	/**
	 * Create contact
	 *
	 * @param $email
	 * @param $list_id
	 * @param null $contact_data
	 */
	public function create_contact( $email, $list_id, $contact_data = null ) {

		// <entry xmlns="http://www.w3.org/2005/Atom">
		$this->startElementNs( null, 'entry', 'http://www.w3.org/2005/Atom' );

		// <title type="text"></title>
		$this->startElement( 'title' );
		$this->startAttribute( 'type' );
		$this->text( 'text' );
		$this->endAttribute();
		$this->text( ' ' );
		$this->endElement();

		// <updated>2008-07-23T14:21:06.407Z</updated>
		$this->writeElement( 'updated', date( 'Y-m-d\TH:i:s.u\Z' ) );

		// <author></author>
		$this->writeElement( 'author', ' ' );

		// <id>{site URL}</id>
		$this->writeElement( 'id', get_site_url() );

		// <summary type="text">Contact</summary>
		$this->StartElement( 'summary' );
		$this->startAttribute( 'type' );
		$this->text( 'text' );
		$this->endAttribute();
		$this->text( 'Contact' );
		$this->endElement();

		// <content type="application/vnd.ctct+xml">
		$this->startElement( 'content' );
		$this->writeAttribute( 'type', 'application/vnd.ctct+xml' );

		// <Contact xmlns="http://ws.constantcontact.com/ns/1.0/">
		$this->startElementNs( null, 'Contact', 'http://ws.constantcontact.com/ns/1.0/' );

		// <EmailAddress>{email}</EmailAddress>
		$this->writeElement( 'EmailAddress', $email );

		// <OptInSource>ACTION_BY_CONTACT</OptInSource>
		$this->writeElement( 'OptInSource', 'ACTION_BY_CONTACT' );

		// <ContactLists>
		$this->startElement( 'ContactLists' );

		// <ContactList id="{list_id}" />
		$this->startElement( 'ContactList' );
		$this->writeAttribute( 'id', $list_id );
		$this->endElement();

		// </ContactLists>
		$this->endElement();

		// add additional data to the contact
		if ( ! empty( $contact_data ) ) {
			foreach ( $contact_data as $element_name => $element_value ) {
				$this->writeElement( $element_name, $element_value );
			}
		}

		// </Contact>
		$this->endElement();

		// </content>
		$this->endElement();

		// </entry>
		$this->endElement();
	}


	/**
	 * Helper to return completed XML document
	 *
	 * @since 1.1.0-2
	 * @return string XML
	 */
	public function to_xml() {

		if ( ! empty( $this->request_xml ) ) {

			return $this->request_xml;
		}

		$this->endDocument();

		return $this->request_xml = $this->outputMemory();
	}


	/**
	 * Returns the string representation of this request
	 *
	 * @since 1.1.0-2
	 * @see SV_WC_API_Request::to_string()
	 * @return string request XML
	 */
	public function to_string() {

		$string = empty( $this->simple_xml ) ? $this->to_xml() : $this->simple_xml->asXML();

		// if the XML document is empty, as with a GET request, send a blank body
		if ( '<?xml version="1.0" encoding="UTF-8"?>' === trim( $string ) ) {
			$string = '';
		}

		return $string;
	}


	/**
	 * Returns the string representation of this request with any and all
	 * sensitive elements masked or removed
	 *
	 * @since 1.1.0-2
	 * @see SV_WC_API_Request::to_string_safe()
	 * @return string the request XML, safe for logging/displaying
	 */
	public function to_string_safe() {

		$string = $this->to_string();

		$dom = new DOMDocument();

		// suppress errors for invalid XML syntax issues
		if ( @$dom->loadXML( $string ) ) {
			$dom->formatOutput = true;
			$string = $dom->saveXML();
		}

		return $string;
	}


	/**
	 * Returns the method for this request: one of HEAD, GET, PUT, PATCH, POST, DELETE
	 *
	 * @since  1.5.0
	 * @return null
	 */
	public function get_method() {

		return null;
	}


	/**
	 * Returns the request path
	 *
	 * @since  1.5.0
	 * @return string
	 */
	public function get_path() {

		return '';
	}


}

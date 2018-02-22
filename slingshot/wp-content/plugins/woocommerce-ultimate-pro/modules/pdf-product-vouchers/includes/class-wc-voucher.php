<?php
/**
 * WooCommerce PDF Product Vouchers
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce PDF Product Vouchers to newer
 * versions in the future. If you wish to customize WooCommerce PDF Product Vouchers for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-pdf-product-vouchers/ for more information.
 *
 * @package   WC-PDF-Product-Vouchers/Classes
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * WooCommerce Voucher class
 *
 * The WooCommerce PDF Product Voucher class. This is an instantiation of a
 * voucher template, it also contains the voucher data that was generated when
 * placing an order
 *
 * @since 3.0.0
 */
class WC_Voucher extends WC_Voucher_Base {


	/** @var string voucher number */
	public $number;

	/** @var string voucher (post) status */
	public $status;

	/** @var int voucher customer id */
	public $customer_id;

	/** @var int voucher product id */
	public $product_id;

	/** @var \WC_Order voucher order */
	public $order;

	/** @var \WC_Voucher_Template voucher template object */
	public $template;


	/**
	 * Constructs voucher with $id
	 *
	 * @since 3.0.0
	 * @param int|\WP_Post|\WC_Voucher $id voucher id or post object
	 */
	public function __construct( $id ) {

		parent::__construct( $id );

		if ( $this->post ) {
			$this->status      = $this->post->post_status;
			$this->number      = $this->post->post_title;
			$this->customer_id = (int) get_post_meta( $this->post->ID, '_customer_user', true );
		}
	}


	/**
	 * Returns the template id
	 *
	 * @since 3.0.0
	 * @return int|null voucher template id
	 */
	public function get_template_id() {
		return $this->post ? $this->post->post_parent : null;
	}


	/**
	 * Returns the template
	 *
	 * @since 3.0.0
	 * @return \WC_Voucher_Template|false
	 */
	public function get_template() {

		if ( ! isset( $this->template ) ) {
			$this->template = wc_pdf_product_vouchers_get_voucher_template( $this->get_template_id() );
		}

		return $this->template;
	}


	/**
	 * Sets the template ID
	 *
	 * @since 3.0.0
	 * @param int $template_id the template id
	 */
	public function set_template_id( $template_id ) {

		$template_id = is_numeric( $template_id ) ? (int) $template_id : 0;
		$template    = $template_id > 0 ? wc_pdf_product_vouchers_get_voucher_template( $template_id ) : null;

		// check that the template id belongs to an actual template
		if ( $template ) {

			wp_update_post( array( 'ID' => $this->id, 'post_parent' => $template_id ) );

			$this->post->post_parent = $template_id;
			$this->template = $template;
		}
	}


	/**
	 * Returns voucher number
	 *
	 * @since 3.0.0
	 * @return string voucher number
	 */
	public function get_voucher_number() {
		return $this->get_field_value( 'voucher_number' );
	}


	/**
	 * Returns voucher order ID
	 *
	 * @since 3.0.0
	 * @return int|null order ID
	 */
	public function get_order_id() {
		return get_post_meta( $this->id, '_order_id', true );
	}


	/**
	 * Returns the order that this voucher is attached to, when it is a product voucher.
	 *
	 * @since 1.0.0
	 * @return \WC_Order the order, or null
	 */
	public function get_order() {

		if ( isset( $this->order ) ) {
			return $this->order;
		}

		if ( $this->get_order_id() ) {
			$this->order = wc_get_order( $this->get_order_id() );
			return $this->order;
		}

		return null;
	}


	/**
	 * Sets the order id that this voucher was purchased with
	 *
	 * @since 3.0.0
	 * @param int $order_id the order id
	 */
	public function set_order_id( $order_id ) {

		$order_id = is_numeric( $order_id ) ? (int) $order_id : 0;
		$order    = $order_id > 0 ? wc_get_order( $order_id ) : null;

		// check that the order id belongs to an actual order
		if ( $order ) {

			update_post_meta( $this->id, '_order_id', $order_id );

			$this->order = $order;
		}
	}


	/**
	 * Returns voucher customer ID
	 *
	 * @since 3.0.0
	 * @return int|null the customer (user) id, or null
	 */
	public function get_customer_id() {
		return $this->customer_id;
	}


	/**
	 * Returns voucher product ID
	 *
	 * @since 3.0.0
	 * @return int|null the product id, or null
	 */
	public function get_product_id() {

		if ( ! isset( $this->product_id ) ) {
			$this->product_id = get_post_meta( $this->id, '_product_id', true );
		}

		return $this->product_id;
	}


	/**
	 * Returns voucher product
	 *
	 * @since 3.0.0
	 * @return \WC_Product|null
	 */
	public function get_product() {
		return $this->get_product_id() ? wc_get_product( $this->get_product_id() ) : null;
	}


	/**
	 * Sets the product id that this voucher was purchased for
	 *
	 * @since 3.0.0
	 * @param int $product_id the product id
	 */
	public function set_product_id( $product_id ) {

		$product_id = is_numeric( $product_id ) ? (int) $product_id : 0;
		$product    = $product_id > 0 ? wc_get_product( $product_id ) : null;

		// check that the product id belongs to an actual product
		if ( $product ) {

			update_post_meta( $this->id, '_product_id', $product_id );
		}
	}


	/**
	 * Returns the ID of the order item that the voucher was purchased with
	 *
	 * @since 3.0.0
	 * @return int|null order item id
	 */
	public function get_order_item_id() {
		return get_post_meta( $this->id, '_order_item_id', true );
	}


	/**
	 * Rurns the order item that the voucher was purchased with
	 *
	 * @since 3.0.0
	 * @return array|null order item or null if none associated/found
	 */
	public function get_order_item() {

		$order = $this->get_order();

		if ( ! $order ) {
			return null;
		}

		$item_id = $this->get_order_item_id();

		if ( ! $item_id ) {
			return null;
		}

		$items = $order->get_items();

		return isset( $items[ $item_id ] ) ? $items[ $item_id ] : null;
	}


	/**
	 * Returns the voucher order item data.
	 *
	 * @deprecated 3.0.0
	 *
	 * @since 1.1.1
	 * @return array order item
	 */
	public function get_item() {

		/* @deprecated since 3.0.0 */
		_deprecated_function( 'WC_Voucher::get_item()', '3.0.0', 'WC_Voucher::get_order_item()' );

		return $this->get_order_item();
	}


	/**
	 * Sets the order item id that this voucher was purchased with
	 *
	 * @since 3.0.0
	 * @param int $order_item_id order item id
	 */
	public function set_order_item_id( $order_item_id ) {
		update_post_meta( $this->id, '_order_item_id', $order_item_id );
	}


	/**
	 * Returns voucher currency
	 *
	 * @since 3.0.0
	 * @return string Voucher currency
	 */
	public function get_voucher_currency() {

		/**
		 * Filters the voucher currency
		 *
		 * @since 3.0.0
		 * @param string $currency voucher currency
		 * @param \WC_Voucher $voucher the voucher object
		 */
		return apply_filters( 'wc_pdf_product_vouchers_get_voucher_currency', get_post_meta( $this->id, '_voucher_currency', true ), $this );
	}


	/** Path & render helpers ******************************************************/


	/**
	 * Returns the relative voucher PDF file path for this voucher
	 *
	 * @since 1.0.0
	 * @return string voucher pdf file path
	 */
	public function get_voucher_path() {

		// vouchers are stored in directories based on their creation month, this should
		// avoid millions of files in a single directory on stores with very larges sales volumes
		$path = date( 'Y-m', $this->get_date( 'timestamp' ) );

		/**
		 * Filters the voucher relative path
		 *
		 * Allows 3rd parties to adjust the directory structure for voucher files. This
		 * could be useful for stores with very large volumes, where the default month-based
		 * directories may contain thousands of files.
		 *
		 * @since 3.0.0
		 * @param string $filename relative voucher path
		 * @param \WC_Voucher $voucher the voucher object
		 */
		return apply_filters( 'wc_pdf_product_vouchers_voucher_path', $path, $this );
	}


	/**
	 * Returns the file name for this product voucher
	 *
	 * In 3.0.0 added the $type param
	 *
	 * @since 1.0.0
	 * @param string $type Optional. File type. One of 'pdf' or 'png', defaults to 'pdf'
	 * @return string voucher file name
	 */
	public function get_voucher_filename( $type = 'pdf' ) {

		// we want a sanitized voucher number so as to avoid file name clashes
		$filename = 'voucher-' . sanitize_file_name( $this->get_voucher_number() ) . '.' . $type;

		/**
		 * Filters the voucher filename
		 *
		 * @since 1.2.0
		 * @param string $filename voucher filename
		 * @param \WC_Voucher $voucher the voucher object
		 */
		return apply_filters( 'wc_pdf_product_vouchers_voucher_filename', $filename, $this );
	}


	/**
	 * Returns the full path and voucher file name
	 *
	 * In 3.0.0 removed the $path param, added the $type param
	 *
	 * @since 1.2.0
	 * @param string $type (optional) file type, one of 'pdf' or 'png', defaults to 'pdf'
	 * @return string $path voucher full path and filename
	 */
	public function get_voucher_full_filename( $type = 'pdf' ) {
		return wc_pdf_product_vouchers()->get_uploads_path() . '/' . $this->get_voucher_path() . '/' . $this->get_voucher_filename( $type );
	}


	/**
	 * Returns true if the voucher file has been generated and exists
	 *
	 * In 3.0.0 removed the $path param, added the $type param
	 *
	 * @since 1.2.0
	 * @param string $type (optional) file type, one of 'pdf' or 'png', defaults to 'pdf'
	 * @return boolean true if the voucher file exists
	 */
	public function file_exists( $type = 'pdf' ) {
		return file_exists( $this->get_voucher_full_filename( $type ) );
	}


	/**
	 * Generates a secret key that will be used by the PDF generator
	 * to load the voucher preview template, and to handle voucher PDF downloads.
	 *
	 * @since 3.0.0
	 */
	public function generate_key() {
		add_post_meta( $this->id, '_voucher_key', 'voucher_' . md5( wp_generate_password() . time() ), true );
	}


	/**
	 * Returns the secret voucher key
	 *
	 * Voucher key is used to grant access to the otherwise private/hidden
	 * voucher view. This view is used by the PDF generator to convert HTML
	 * to PDF.
	 *
	 * @since 3.0.0
	 * @return string vouhcer key
	 */
	public function get_voucher_key() {
		return get_post_meta( $this->id, '_voucher_key', true );
	}


	/**
	 * Returns the URL that ouptuts the renderable HTML for this voucher
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_render_url() {
		return add_query_arg( 'voucher_key', $this->get_voucher_key(), get_permalink( $this->id ) );
	}


	/** Image methods ******************************************************/


	/**
	 * Sets the image id for the voucher
	 *
	 * @since 3.0.0
	 * @param int $image_id (attachment) id
	 */
	public function set_image_id( $image_id ) {

		$image_id = is_numeric( $image_id ) ? (int) $image_id : 0;

		// check that the order id belongs to an actual order
		if ( $image_id ) {

			update_post_meta( $this->id, '_thumbnail_id', $image_id );
		}
	}


	/**
	 * Returns the additional image ID for the voucher
	 *
	 * @since 3.0.0
	 * @return int image (attachment) id
	 */
	public function get_additional_image_id() {
		return $this->get_template()->get_additional_image_id();
	}


	/**
	 * Returns the logo image ID for the voucher
	 *
	 * @since 3.0.0
	 * @return int logo (attachment) id
	 */
	public function get_logo_id() {
		return $this->get_template()->get_logo_id();
	}


	/**
	 * Returns the logo image url for the voucher
	 *
	 * @since 3.1.0
	 * @return string logo (attachment) url
	 */
	public function get_logo_url() {
		return wp_get_attachment_url( $this->get_field_value( 'logo' ) );
	}


	/**
	 * Returns the logo image filesystem path for the voucher
	 *
	 * @since 3.1.0
	 * @return string logo (attachment) path
	 */
	public function get_logo_path() {
		return get_attached_file( $this->get_field_value( 'logo' ) );
	}


	/**
	 * Checks if the voucher has a preview image
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function has_preview_image() {
		return (bool) $this->get_preview_image_url();
	}


	/**
	 * Returns the preview image URL
	 *
	 * @since 3.0.0
	 * @return string|null
	 */
	public function get_preview_image_url() {

		$image_url = null;

		if ( $this->file_exists( 'png' ) ) {

			$image_path = $this->get_voucher_full_filename( 'png' );
			$image_url  = wc_pdf_product_vouchers_convert_path_to_url( $image_path );
		}

		return $image_url;
	}


	/**
	 * Returns the preview image tag
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_preview_image( $size = 'wc-pdf-product-vouchers-voucher-thumb' ) {
		global $_wp_additional_image_sizes;

		$image = '';

		if ( $this->has_preview_image() ) {

			$width = wc_pdf_product_vouchers_get_image_width( $size );
			$image = '<img src="' . $this->get_preview_image_url() . '" alt="" width="' . $width . '" />';
		}

		return $image;
	}


	/**
	 * Returns voucher image DPI
	 *
	 * @since 3.0.0
	 * @return int
	 */
	public function get_dpi() {
		return $this->get_template()->get_dpi();
	}


	/** Date methods **************************************************/


	/**
	 * Returns the voucher creation date
	 *
	 * @since 3.0.0
	 * @param string $format (optional) defaults to 'mysql'
	 * @return string
	 */
	public function get_date( $format = 'mysql' ) {
		return wc_pdf_product_vouchers_format_date( $this->post->post_date_gmt, $format );
	}


	/**
	 * Returns the voucher local creation date
	 *
	 * @since 3.0.0
	 * @param string $format (optional) defaults to 'mysql'
	 * @return string
	 */
	public function get_local_date( $format = 'mysql' ) {
		return wc_pdf_product_vouchers_format_date( $this->post->post_date, $format );
	}


	/**
	 * Returns the voucher date in the user-defined WordPress format
	 *
	 * @since 3.0.0
	 * @return string formatted localized date
	 */
	public function get_formatted_date() {
		return date_i18n( wc_date_format(), $this->get_local_date( 'timestamp' ) );
	}


	/**
	 * Returns the number of days this voucher is valid for
	 *
	 * @deprecated 3.0.0
	 *
	 * @since 1.0.0
	 * @return int expiry days
	 */
	public function get_expiry() {

		/* @deprecated since 3.0.0 */
		_deprecated_function( 'WC_Voucher::get_expiry()', '3.0.0', 'WC_Voucher::get_expiry_days()' );

		return $this->get_expiry_days();
	}


	/**
	 * Returns the number of days this voucher is valid for
	 *
	 * In 3.0.0 renamed from get_expiry() to get_expiry_days()
	 *
	 * @since 1.0.0
	 * @return int expiry days
	 */
	public function get_expiry_days() {

		$expiry = $this->get_template()->get_days_to_expiry();

		/**
		 * Filters the number of days this voucher is valid for
		 *
		 * In 3.0.0 renamed from wc_pdf_product_vouchers_get_expiry to
		 * wc_pdf_product_vouchers_get_expiry_days.
		 *
		 * @since 2.1.4
		 * @param int $expiry expiry days
		 * @param \WC_Voucher $voucher the voucher object
		 */
		return apply_filters( 'wc_pdf_product_vouchers_get_expiry_days', $expiry, $this );
	}


	/**
	 * Sets the voucher expiration datetime
	 *
	 * In 3.0.0 the $date param can now be either a timestamp or mysql string.
	 *
	 * @since 1.0.0
	 * @param string|int $date end date either as a unix timestamp or mysql datetime string, defaults to empty string (no expiration date)
	 */
	public function set_expiration_date( $date = '' ) {

		// validate the date
		if ( is_numeric( $date ) ) {
			$date = wc_pdf_product_vouchers_validate_date( $date, 'timestamp' );
		} elseif ( $date && is_string( $date ) ) {
			$date = wc_pdf_product_vouchers_validate_date( $date, 'mysql' );
		}

		// bail with any false-y dates
		if ( ! $date ) {
			return;
		}

		// if the date is valid, and is mysql, convert to a timestamp
		if ( is_string( $date ) ) {
			$date = strtotime( $date );
		}

		update_post_meta( $this->id, '_expiration_date', $date );
	}


	/**
	 * Returns the voucher expiration date
	 *
	 * @since 3.0.0
	 * @param string $format (optional) defaults to 'mysql'
	 * @return null|int|string the expiration date in the chosen format
	 */
	public function get_expiration_date( $format = 'mysql' ) {

		$date = get_post_meta( $this->id, '_expiration_date', true );

		return wc_pdf_product_vouchers_format_date( $date, $format );
	}


	/**
	 * Returns the voucher expiration local date
	 *
	 * @since 3.0.0
	 * @param string $format (optional) defaults to 'mysql'
	 * @return null|int|string the localized expiration date in the chosen format
	 */
	public function get_local_expiration_date( $format = 'mysql' ) {

		$date = $this->get_expiration_date( 'timestamp' );

		// adjust the date to the site's local timezone
		return ! empty( $date ) ? wc_pdf_product_vouchers_adjust_date_by_timezone( $date, $format ) : null;
	}


	/**
	 * Returns the expiration date (if any) in the user-defined WordPress format,
	 * or the empty string
	 *
	 * @since 1.0.0
	 * @return string formatted localized expiration date, if any, otherwise the empty string
	 */
	public function get_formatted_expiration_date() {

		$expiration_date = $this->get_local_expiration_date( 'timestamp' );
		$formatted_expiration_date = '';

		if ( $expiration_date ) {
			$formatted_expiration_date = date_i18n( wc_date_format(), $expiration_date );
		}

		/**
		 * Filters the formatted expiration date
		 *
		 * @since 2.1.4
		 * @param string $formatted_expiration_date formatted expiration date
		 * @param \WC_Voucher $this, voucher instance
		 */
		return apply_filters( 'wc_pdf_product_vouchers_get_formatted_expiration_date', $formatted_expiration_date, $this );
	}


	/**
	 * Sets the voucher voided datetime
	 *
	 * @since 3.0.0
	 * @param string $date date in mysql format
	 */
	public function set_voided_date( $date ) {

		if ( $voided_date = wc_pdf_product_vouchers_validate_date( $date, 'mysql' ) ) {
			update_post_meta( $this->id, '_voided_date', $voided_date );
		}
	}


	/**
	 * Reurns the voucher voided date
	 *
	 * @since 3.0.0
	 * @param string $format (optional) defaults to 'mysql'
	 * @return null|int|string the voided date in the chosen format
	 */
	public function get_voided_date( $format = 'mysql' ) {

		$date = get_post_meta( $this->id, '_voided_date', true );

		return ! empty( $date ) ? wc_pdf_product_vouchers_format_date( $date, $format ) : null;
	}


	/**
	 * Returns the voucher voided locale date
	 *
	 * @since 3.0.0
	 * @param string $format (optional) defaults to 'mysql'
	 * @return null|int|string the localized voided date in the chosen format
	 */
	public function get_local_voided_date( $format = 'mysql' ) {

		$date = $this->get_voided_date( 'timestamp' );

		// adjust the date to the site's local timezone
		return ! empty( $date ) ? wc_pdf_product_vouchers_adjust_date_by_timezone( $date, $format ) : null;
	}


	/** Totals & value methods **************************************************/


	/**
	 * Calculates and returns the product tax based on voucher customer or store base location.
	 *
	 * @since 3.1.0
	 * @param float $product_price (optional) the product price, defaults to $voucher->get_product_price()
	 * @return float product tax
	 */
	public function calculate_product_tax( $product_price = null ) {

		$product     = $this->get_product();
		$product_tax = 0;
		$tax_class   = $product->get_tax_class();
		$tax_status  = $product->get_tax_status();

		if ( '0' !== $tax_class && 'taxable' === $tax_status ) {

			$tax_based_on = get_option( 'woocommerce_tax_based_on' );
			$args         = array( 'tax_class' => $tax_class );

			// get customer location from order if possible
			if ( $order = $this->get_order() ) {

				$args['country']  = 'billing' === $tax_based_on ? $order->get_billing_country()  : $order->get_shipping_country();
				$args['state']    = 'billing' === $tax_based_on ? $order->get_billing_state()    : $order->get_shipping_state();
				$args['postcode'] = 'billing' === $tax_based_on ? $order->get_billing_postcode() : $order->get_shipping_postcode();
				$args['city']     = 'billing' === $tax_based_on ? $order->get_billing_city()     : $order->get_shipping_city();

			// or from customer data
			} elseif ( $customer_id = $this->get_customer_id() ) {

				$customer = new WC_Customer( $customer_id );

				$args['country']  = 'billing' === $tax_based_on ? $customer->get_billing_country()  : $customer->get_shipping_country();
				$args['state']    = 'billing' === $tax_based_on ? $customer->get_billing_state()    : $customer->get_shipping_state();
				$args['postcode'] = 'billing' === $tax_based_on ? $customer->get_billing_postcode() : $customer->get_shipping_postcode();
				$args['city']     = 'billing' === $tax_based_on ? $customer->get_billing_city()     : $customer->get_shipping_city();
			}

			// default to store base location
			if ( 'base' === $tax_based_on || empty( $args['country'] ) ) {
				$default          = wc_get_base_location();
				$args['country']  = $default['country'];
				$args['state']    = $default['state'];
				$args['postcode'] = '';
				$args['city']     = '';
			}

			if ( ! $product_price ) {
				$product_price = $this->get_product_price();
			}

			$tax_rates   = WC_Tax::find_rates( $args );
			$taxes       = WC_Tax::calc_tax( $product_price, $tax_rates, false );
			$product_tax = wc_round_tax_total( WC_Tax::get_tax_total( $taxes ) );
		}

		return $product_tax;
	}


	/**
	 * Returns the voucher (product) tax rate
	 *
	 * @since 3.1.0
	 * @return float
	 */
	public function get_tax_rate() {
		return $this->get_product_tax() ? $this->get_product_tax() / $this->get_product_price() : 0;
	}


	/**
	 * Returns the voucher value, which is product price X quantity.
	 *
	 * @since 3.0.0
	 * @return float
	 */
	public function get_voucher_value() {
		return $this->get_product_price() * $this->get_product_quantity();
	}


	/**
	 * Returns the voucher tax, which is product tax X quantity.
	 *
	 * @since 3.1.0
	 * @return float
	 */
	public function get_voucher_tax() {
		return $this->get_product_tax() * $this->get_product_quantity();
	}


	/**
	 * Returns the voucher value with tax.
	 *
	 * @since 3.1.0
	 * @return float
	 */
	public function get_voucher_value_incl_tax() {
		return $this->get_voucher_value() + $this->get_voucher_tax();
	}


	/**
	 * Returns the voucher value for display purposes, either
	 * including or exlcuding tax based on tax display settings.
	 *
	 * @since 3.1.0
	 * @return float
	 */
	public function get_voucher_value_for_display() {
		if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
			return $this->get_voucher_value_incl_tax();
		} else {
			return $this->get_voucher_value();
		}
	}


	/**
	 * Calculates the remaining value & stores it in the database
	 *
	 * @since 3.0.0
	 * @return float remaining amount
	 */
	public function calculate_remaining_value() {

		$total     = $this->get_voucher_value();
		$redeemed  = $this->calculate_total_redeemed();
		$remaining = round( $total - $redeemed, wc_get_price_decimals() );

		$this->set_remaining_value( $remaining );

		return $remaining;
	}


	/**
	 * Sets the remaining value
	 *
	 * @since 3.0.0
	 * @param float $amount remaining value amount
	 * @return bool
	 */
	public function set_remaining_value( $amount ) {
		return update_post_meta( $this->id, '_remaining_value', wc_format_decimal( $amount ) );
	}


	/**
	 * Returns the remaining value
	 *
	 * @since 3.0.0
	 * @param bool $include_voided (optional) whether to include the voided value or not, defaults to true
	 * @return float
	 */
	public function get_remaining_value( $include_voided = true ) {

		if ( $this->has_status( 'voided' ) && ! $include_voided ) {
			return 0;
		}

		$remaining_value = get_post_meta( $this->id, '_remaining_value', true );

		return is_numeric( $remaining_value ) ? $remaining_value : $this->get_voucher_value();
	}


	/**
	 * Returns the remaining value including tax
	 *
	 * @since 3.1.0
	 * @param bool $include_voided (optional) whether to include the voided value or not
	 * @return float
	 */
	public function get_remaining_value_incl_tax( $include_voided = true ) {

		$remaining_value = $this->get_remaining_value( $include_voided );
		$remaining_tax   = $remaining_value ? $remaining_value * $this->get_tax_rate() : 0;

		return $remaining_value + $remaining_tax;
	}


	/**
	 * Returns the remaining value for display purposes, either
	 * including or exlcuding tax based on tax display settings.
	 *
	 * @since 3.1.0
	 * @param bool $include_voided (optional) whether to include the voided value or not
	 * @return float
	 */
	public function get_remaining_value_for_display( $include_voided = true ) {
		if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
			return $this->get_remaining_value_incl_tax( $include_voided );
		} else {
			return $this->get_remaining_value( $include_voided );
		}
	}


	/**
	 * Returns the remaining quantity
	 *
	 * @since 3.0.0
	 * @param bool $include_voided (optional) whether to include the voided value or not, defaults to true
	 * @return float
	 */
	public function get_remaining_quantity( $include_voided = true ) {

		if ( ! $include_voided && $this->has_status( 'voided' ) ) {
			return 0;
		}

		$remaining_quantity = $this->get_product_quantity();
		$redeemed_quantity  = 0;

		$redemptions = $this->get_redemptions();

		if ( ! empty( $redemptions ) ) {
			foreach ( $redemptions as $redemption ) {

				if ( ! empty( $redemption['quantity'] ) ) {

					$redeemed_quantity += (float) $redemption['quantity'];
				}
			}
		}

		if ( $redeemed_quantity ) {
			$remaining_quantity = max( 0, $remaining_quantity - $redeemed_quantity );
		}

		return $remaining_quantity;
	}



	/**
	 * Returns the total redeemed value
	 *
	 * @since 3.0.0
	 * @return float
	 */
	public function get_total_redeemed() {
		return $this->get_voucher_value() - $this->get_remaining_value();
	}


	/**
	 * Returns the redeemed value for display purposes, either including or excluding tax based on tax display settings.
	 *
	 * @since 3.1.0
	 *
	 * @return float
	 */
	public function get_total_redeemed_for_display() {
		return $this->get_voucher_value_for_display() - $this->get_remaining_value_for_display();
	}


	/**
	 * Calculates the total amount redeemed
	 *
	 * @since 3.0.0
	 * @return float
	 */
	public function calculate_total_redeemed() {

		$redeemed = 0;
		$redemptions = $this->get_redemptions();

		if ( empty( $redemptions ) ) {
			return $redeemed;
		}

		foreach ( $redemptions as $redemption ) {
			$redeemed += $redemption['amount'];
		}

		return $redeemed;
	}


	/** Redemption methods ******************************************************/


	/**
	 * Returns voucher redemptions
	 *
	 * @since 3.0.0
	 * @return array Array of redemption dates (and/or unused quantities)
	 */
	public function get_redemptions() {
		return get_post_meta( $this->id, '_redemptions', true );
	}


	/**
	 * Checks if voucher has any redemptions
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function has_redemptions() {

		$redemptions = $this->get_redemptions();

		return ! empty( $redemptions );
	}


	/**
	 * Redeems the voucher
	 *
	 * @since 3.0.0
	 * @param float $amount (optional) a positive amount to redeem - if omitted, the full remaining amount will be redeemed
	 * @param array $args {
	 *     (optional) an associative array of arguments
	 *
	 *     @type int|string $date (optional) redemption date, either unix timestamp or a mysql datetime, defaults to now
	 *     @type string $notes (optional) redemption notes
	 *     @type int $quantity (optional) redeemed quantity
	 *     @type int $order_id (optional) the order id
	 *     @type int $user_id (optional) the user id
	 * }
	 * @throws SV_WC_Plugin_Exception
	 */
	public function redeem( $amount = null, $args = array() ) {

		if ( ! $this->get_remaining_value() ) {
			throw new SV_WC_Plugin_Exception( __( 'No remaining value', 'ultimatewoo-pro' ) );
		}

		if ( ! $amount || $amount < 0 ) {
			throw new SV_WC_Plugin_Exception( __( 'Redemption amount must be a positive number', 'ultimatewoo-pro' ) );
		}

		if ( 'single' === $this->get_voucher_type() && fmod( $amount, $this->get_product_price() ) ) {
			throw new SV_WC_Plugin_Exception( __( 'Redemption amount must be a multiple of the product price for a single-purpose voucher.', 'ultimatewoo-pro' ) );
		}

		if ( $this->get_remaining_value() < $amount ) {
			throw new SV_WC_Plugin_Exception( __( 'Redemption amount is greater than remaining voucher value', 'ultimatewoo-pro' ) );
		}

		$redemptions = $this->get_redemptions();

		if ( ! $redemptions ) {
			$redemptions = array();
		}

		$redemptions[] = $this->parse_redemption( $amount, $args );

		update_post_meta( $this->id, '_redemptions', $redemptions );

		$remaining = $this->calculate_remaining_value();

		if ( ! $remaining ) {
			$this->update_status( 'redeemed' );
		}
	}


	/**
	 * Sets voucher redemptions, overriding any previous redemptions.
	 *
	 * @since 3.0.0
	 * @param array $redemptions
	 * @throws SV_WC_Plugin_Exception
	 *
	 * @return bool
	 */
	public function set_redemptions( $redemptions = array() ) {

		$redeemed  = 0;
		$value     = $this->get_voucher_value();
		$is_single = 'single' === $this->get_voucher_type();

		foreach ( $redemptions as $i => $redemption ) {

			$amount = $redemption['amount'];

			$invalid_amount_message = sprintf( __( 'Invalid redemption amount for redemption #%d.', 'ultimatewoo-pro' ), $i + 1 );

			if ( ! $amount || $amount < 0 ) {
				throw new SV_WC_Plugin_Exception( $invalid_amount_message );
			}

			if ( $is_single && fmod( $amount, $this->get_product_price() ) ) {
				throw new SV_WC_Plugin_Exception( sprintf( '%s %s', $invalid_amount_message, __( 'Redemption amount must be a multiple of the product price for a single-purpose voucher.', 'ultimatewoo-pro' ) ) );
			}

			$redeemed += $amount;

			if ( $redeemed > $value ) {
				throw new SV_WC_Plugin_Exception( __( 'Total amount of redemptions cannot be greater than voucher value.', 'ultimatewoo-pro' ) );
			}

			$redemptions[ $i ] = $this->parse_redemption( $amount, $redemption );
		}

		if ( ! update_post_meta( $this->id, '_redemptions', $redemptions ) ) {
			return false;
		}

		$remaining = $this->calculate_remaining_value();

		if ( ! $remaining ) {
			$this->update_status( 'redeemed' );
		}

		return true;
	}


	/**
	 * Parses a redemption
	 *
	 * Ensures that a redemption has all the required fields set to
	 * at least their default values.
	 *
	 * @since 3.0.0
	 * @param float $amount (optional) a positive amount to redeem - if omitted, the full remaining amount will be redeemed
	 * @param array $args {
	 *     (optional) an associative array of arguments
	 *
	 *     @type int|string $date redemption date, either unix timestamp or a mysql datetime, defaults to now
	 *     @type string $notes redemption notes
	 *     @type int $order_id the order id that this redemption was created on
	 *     @type int $user_id the user id who created the redemption
	 * }
	 * @return array the parsed redemption
	 */
	public function parse_redemption( $amount, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'date'     => current_time( 'timestamp', true ),
			'quantity' => null,
			'notes'    => null,
			'order_id' => null,
			'user_id'  => null,
		) );

		if ( is_string( $args['date'] ) ) {
			$args['date'] = strtotime( $args['date'] );
		}

		if ( null !== $args['quantity'] ) {
			$args['quantity'] = absint( $args['quantity'] );
		}

		return array(
			'amount'   => $amount,
			'date'     => date( 'Y-m-d H:i:s', $args['date'] ),
			'quantity' => $args['quantity'],
			'notes'    => $args['notes'],
			'order_id' => $args['order_id'],
			'user_id'  => $args['user_id'],
		);
	}


	/** Void & restore methods ******************************************************/


	/**
	 * Voids the voucher
	 *
	 * @since 3.0.0
	 * @param array $args {
	 *     (optional) an associative array of arguments
	 *
	 *     @type string $reason void reason
	 *     @type int $user_id the user id who voided the voucher
	 * }
	 * @return boolean true if the voucher was successfully void, false otherwise
	 * @throws SV_WC_Plugin_Exception
	 */
	public function void( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'reason'  => null,
			'user_id' => null,
		) );

		update_post_meta( $this->id, '_void_reason', $args['reason'] );
		update_post_meta( $this->id, '_voided_by', $args['user_id'] );

		$this->update_status( 'voided' );

		return true;
	}


	/**
	 * Returns the void reason
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_void_reason() {
		return get_post_meta( $this->id, '_void_reason', true );
	}


	/**
	 * Restores the voucher if voided
	 *
	 * @since 3.0.0
	 * @return boolean true if the voucher restoration was successful, false otherwise
	 * @throws SV_WC_Plugin_Exception
	 */
	public function restore() {

		delete_post_meta( $this->id, '_void_reason' );
		delete_post_meta( $this->id, '_voided_by' );

		$this->update_status( 'active' );

		return true;
	}


	/** Download methods ******************************************************/


	/**
	 * Returns the download URL for this voucher
	 *
	 * @since 3.0.0
	 * @param string $type (optional) whether the download will be served for a customer or an admin, defaults to 'customer'
	 * @return string
	 */
	public function get_download_url( $type = 'customer' ) {

		$base_url = 'admin' === $type ? admin_url( 'admin.php' ) : home_url( '/' );

		return add_query_arg(
			array(
				'download_wc_voucher_pdf' => $this->get_id(),
				'key'                     => $this->get_voucher_key(),
			),
			$base_url
		);
	}


	/**
	 * Returns the voucher download count
	 *
	 * @since 3.0.0
	 * @return int
	 */
	public function get_download_count() {
		return (int) get_post_meta( $this->id, '_download_count', true );
	}


	/**
	 * Counts voucher downloads
	 *
	 * @since 3.0.0
	 */
	public function count_download() {

		$download_count = $this->get_download_count();

		$download_count++;

		update_post_meta( $this->id, '_download_count', $download_count );
	}


	/** Voucher fields ******************************************************/


	/**
	 * Sets a user input field's value
	 *
	 * @since 3.0.0
	 * @param string $field_id field identifier
	 * @param string $value field value
	 */
	public function set_user_input_field( $field_id, $value ) {
		update_post_meta( $this->id, '_' . $field_id, $value );
	}


	/**
	 * Returns the purchaser name if any for this product voucher
	 *
	 * @since 3.0.0
	 * @return string voucher purchaser name or empty string
	 */
	public function get_purchaser_name() {
		return $this->get_field_value( 'purchaser_name' );
	}


	/**
	 * Returns the purchaser email if any for this product voucher
	 *
	 * @since 3.0.0
	 * @return string voucher purchaser email or empty string
	 */
	public function get_purchaser_email() {
		return $this->get_field_value( 'purchaser_email' );
	}


	/**
	 * Returns the recipient name if any for this product voucher
	 *
	 * @since 1.0.0
	 * @return string voucher recipient name or empty string
	 */
	public function get_recipient_name() {
		return $this->get_field_value( 'recipient_name' );
	}


	/**
	 * Returns the recipient email if any for this product voucher
	 *
	 * @since 1.2.0
	 * @return string voucher recipient email or empty string
	 */
	public function get_recipient_email() {
		return $this->get_field_value( 'recipient_email' );
	}


	/**
	 * Returns the voucher message if any for this product voucher
	 *
	 * @since 1.0.0
	 * @return string voucher message or empty string
	 */
	public function get_message() {
		return $this->get_field_value( 'message' );
	}


	/**
	 * Returns the product name, if available
	 *
	 * @since 1.0.0
	 * @return string product name or empty string
	 */
	public function get_product_name() {
		return $this->get_field_value( 'product_name' );
	}


	/**
	 * Returns the product SKU, if available
	 *
	 * @since 1.0.0
	 * @return string product sku or empty string
	 */
	public function get_product_sku() {
		return $this->get_field_value( 'product_sku' );
	}


	/**
	 * Returns the product price, if available.
	 *
	 * @since 2.0.0
	 *
	 * @return float product price
	 */
	public function get_product_price() {
		return (float) $this->get_field_value( 'product_price' );
	}


	/**
	 * Returns the product tax, if available.
	 *
	 * @since 3.0.5
	 *
	 * @return float product tax
	 */
	public function get_product_tax() {
		return (float) $this->get_field_value( 'product_tax' );
	}


	/**
	 * Returns the product price including tax
	 *
	 * @since 3.1.0
	 *
	 * @return float product price including tax
	 */
	public function get_product_price_incl_tax() {
		return $this->get_product_price() + $this->get_product_tax();
	}


	/**
	 * Returns the product price for display purposes, either
	 * including or exlcuding tax based on tax display settings.
	 *
	 * @since 3.1.0
	 * @return float
	 */
	public function get_product_price_for_display() {
		if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
			return $this->get_product_price_incl_tax();
		} else {
			return $this->get_product_price();
		}
	}


	/**
	 * Returns the product quantity, if available
	 *
	 * @since 3.0.0
	 *
	 * @return float product quantity
	 */
	public function get_product_quantity() {
		return (float) $this->get_field_value( 'product_quantity' );
	}


	/**
	 * Returns voucher field value
	 *
	 * @since 3.0.0
	 * @param string $field_id
	 * @return mixed
	 */
	public function get_field_value( $field_id ) {

		$value = null;

		switch ( $field_id ) {

			case 'logo':
				$value = $this->get_logo_id();
			break;

			case 'voucher_number':
				$value = $this->number;
			break;

			// Product name is considered semi-historical. If the voucher is tied to an order,
			// the product name will be used from the order item that was used to purchase the
			// voucher. Otherwise, the product's current name will be used.
			case 'product_name':

				$value = '';
				$item  = $this->get_order_item();

				if ( $item && isset( $item['name'] ) ) {
					$value = $item['name'];

					if ( SV_WC_Plugin_Compatibility::is_wc_version_lt_3_0() ) {

						// add variation attributes to order item name; this is done automatically with WC 3.0+
						if ( isset( $item['variation_id'] ) && $variation = wc_get_product( $item['variation_id'] ) ) {
							$value .= ' - ' . implode( ', ', $variation->get_variation_attributes() );
						}
					}
				}

				if ( ! $value ) {
					$product = $this->get_product();
					$value   = ! empty( $product ) ? $product->get_title() : '';

					// add variation attributes to product name
					if ( $product && $product->is_type( 'variation' ) ) {
						$value .= ' - ' . implode( ', ', $product->get_variation_attributes() );
					}
				}
			break;

			// Product SKU is not considered historical - the current product SKU will always be used.
			case 'product_sku':
				$product = $this->get_product();

				if ( ! $product && $item = $this->get_order_item() ) {
					$order   = $this->get_order();
					$product = $order->get_product_from_item( $item );
				}

				$value = ! empty( $product ) ? $product->get_sku() : '';
			break;

			// product quantity is considered historical
			case 'product_quantity':
				$value = get_post_meta( $this->id, '_' . $field_id, true );
				$value = $value ? $value : 1; // always fall back to 1
			break;

			// It is assumed that all other voucher fields are "historical". This covers
			// recipient_name, recipient_email, message as well as any custom voucher field value.
			default:
				$value = get_post_meta( $this->id, '_' . $field_id, true );
			break;

		}

		// cast numerical values to floats
		if ( in_array( $field_id, array( 'product_price', 'product_tax', 'product_quantity' ), true ) ) {
			$value = (float) $value;
		}

		/**
		 * Filters the voucher field value
		 *
		 * @since 3.0.0
		 * @param mixed $value the field value
		 * @param \WC_Voucher $voucher the voucher instance
		 */
		return apply_filters( "wc_pdf_product_vouchers_get_{$field_id}", $value, $this );
	}


	/**
	 * Returns voucher field formatted value
	 *
	 * @since 3.0.0
	 * @param string $field_id
	 * @return mixed
	 */
	public function get_field_value_formatted( $field_id ) {

		$formatted_value = $this->get_field_value( $field_id );

		switch ( $field_id ) {

			case 'expiration_date':
				$formatted_value = $this->get_formatted_expiration_date();
			break;

			case 'product_price':

				if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
					$formatted_value = (float) $formatted_value + $this->get_product_tax();
				}

				$formatted_value = strip_tags( wc_price( $formatted_value ) );
			break;

			case 'logo':
				$formatted_value = '<img src="' . wp_get_attachment_url( $formatted_value ) . '" />';
			break;
		}

		/**
		 * Filters the voucher formatted field value
		 *
		 * @since 3.0.0
		 * @param mixed $formatted_value the formatted field value
		 * @param \WC_Voucher $vohucher the vouhcer object
		 */
		return apply_filters( "wc_pdf_product_vouchers_get_{$field_id}_formatted", $formatted_value, $this );
	}


	/**
	 * Returns all voucher fields with their formatted values
	 *
	 * @since 3.0.0
	 * @return array associative array of field IDs and their formatted values
	 */
	public function get_fields_formatted() {

		$voucher_fields = array();

		foreach ( WC_Voucher_Template::get_voucher_fields() as $field_id => $attrs ) {
			$voucher_fields[ $field_id ] = $this->get_field_value_formatted( $field_id );
		}

		return $voucher_fields;
	}


	/**
	 * Returns all voucher user-input fields with their formatted values
	 *
	 * @since 3.0.0
	 * @return array associative array of user-input field IDs and their formatted values
	 */
	public function get_user_input_fields_formatted() {

		$fields = array();

		foreach ( WC_Voucher_Template::get_voucher_user_input_fields() as $field_id => $attrs ) {
			$fields[ $field_id ] = $this->get_field_value_formatted( $field_id );
		}

		return $fields;
	}


	/**
	 * Checks if voucher has purchaser details set up
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function has_purchaser_details() {

		$has_details = $this->get_purchaser_name() || $this->get_purchaser_email();

		/**
		 * Filters whether the voucher has purchaser details set up or not
		 *
		 * @since 3.0.0
		 * @param bool $has_details
		 * @param \WC_Voucher $voucher the voucher object
		 */
		return apply_filters( 'wc_pdf_product_vouchers_voucher_has_purchaser_details', $has_details, $this );
	}


	/**
	 * Checks if voucher has recipient details set up
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function has_recipient_details() {

		$has_details = $this->get_recipient_name() || $this->get_recipient_email() || $this->get_message();

		/**
		 * Filters whether the voucher has recipient details set up or not
		 *
		 * @since 3.0.0
		 * @param bool $has_details
		 * @param \WC_Voucher $voucher the voucher instance
		 */
		return apply_filters( 'wc_pdf_product_vouchers_voucher_has_recipient_details', $has_details, $this );
	}


	/** Status methods ******************************************************/


	/**
	 * Updates the status of voucher
	 *
	 * @since 3.0.0
	 * @param string $new_status status to change the order to, no internal wcpdf- prefix is required
	 * @param string $note (optiona) note to add
	 */
	public function update_status( $new_status, $note = '' ) {

		if ( ! $this->id ) {
			return;
		}

		// standardise status names
		$new_status = 0 === strpos( $new_status, 'wcpdf-' ) ? substr( $new_status, 6 ) : $new_status;
		$old_status = $this->get_status();

		// get valid statuses
		$valid_statuses = wc_pdf_product_vouchers_get_voucher_statuses();

		// only update if they differ - and ensure post_status is a 'wcm' status.
		if ( $new_status !== $old_status && array_key_exists( 'wcpdf-' . $new_status, $valid_statuses ) ) {

			// note will be added to the voucher by the general vouchers utility class,
			// so that we add only 1 note instead of 2 when updating the status
			wc_pdf_product_vouchers()->get_voucher_handler_instance()->set_voucher_status_transition_note( $note );

			// update the order
			wp_update_post( array(
				'ID'          => $this->id,
				'post_status' => 'wcpdf-' . $new_status,
			) );

			$this->status            = 'wcpdf-' . $new_status;
			$this->post->post_status = 'wcpdf-' . $new_status;
		}
	}


	/**
	 * Returns the voucher status without wcpdf- internal prefix
	 *
	 * @since 3.0.0
	 * @return string Status slug
	 */
	public function get_status() {
		return 0 === strpos( $this->status, 'wcpdf-' ) ? substr( $this->status, 6 ) : $this->status;
	}


	/**
	 * Checks the voucher status against a passed in status.
	 *
	 * @since 3.0.0
	 * @param string|array $status A status or array of statuses to test
	 * @return bool
	 */
	public function has_status( $status ) {

		/**
		 * Filters whether a voucher has a status or not
		 *
		 * @since 3.0.0
		 * @param bool $has_status
		 * @param \WC_Voucher $voucher the vouhcer object
		 * @param string|array $status status slug, or an array of status slugs
		 */
		return apply_filters( 'wc_pdf_product_vouchers_voucher_has_status', ( is_array( $status ) && in_array( $this->get_status(), $status, true ) ) || $this->get_status() === $status, $this, $status );
	}


	/**
	 * Returns true if this voucher is still editable
	 *
	 * @since 3.0.0
	 * @return boolean|null true if the voucher is editable, false otherwise
	 */
	public function is_editable() {
		return $this->has_status( array( 'pending', 'active', 'auto-draft' ) );
	}


	/** Notes methods ******************************************************/


	/**
	 * Returns voucher notes
	 *
	 * @since 3.0.0
	 * @param int $paged (optional) pagination
	 * @return \WP_Comment[] array of comment (voucher notes) objects
	 */
	public function get_notes( $paged = 1 ) {

		$args = array(
			'post_id' => $this->id,
			'approve' => 'approve',
			'type'    => 'voucher_note',
			'paged'   => (int) $paged,
		);

		remove_filter( 'comments_clauses', array( wc_pdf_product_vouchers()->get_query_instance(), 'exclude_voucher_notes_from_queries' ), 10 );

		$notes = (array) get_comments( $args );

		add_filter( 'comments_clauses', array( wc_pdf_product_vouchers()->get_query_instance(), 'exclude_voucher_notes_from_queries' ), 10 );

		return $notes;
	}


	/**
	 * Adds a voucher note
	 *
	 * @since 3.0.0
	 * @param string $note note text
	 * @return int|false note (comment) id, false on error
	 */
	public function add_note( $note) {

		$note = trim( $note );

		if ( empty( $note ) ) {

			// a note can't be empty
			return false;

		} if ( is_user_logged_in() && current_user_can( 'edit_post', $this->id ) ) {

			$user                 = get_user_by( 'id', get_current_user_id() );
			$comment_author       = $user->display_name;
			$comment_author_email = $user->user_email;

		} else {

			$comment_author       = __( 'WooCommerce', 'ultimatewoo-pro' );

			$comment_author_email = strtolower( __( 'WooCommerce', 'ultimatewoo-pro' ) ) . '@';
			$comment_author_email .= isset( $_SERVER['HTTP_HOST'] ) ? str_replace( 'www.', '', $_SERVER['HTTP_HOST'] ) : 'noreply.com';

			$comment_author_email = sanitize_email( $comment_author_email );
		}

		$comment_post_ID    = $this->id;
		$comment_author_url = '';
		$comment_content    = $note;
		$comment_agent      = 'WooCommerce';
		$comment_type       = 'voucher_note';
		$comment_parent     = 0;
		$comment_approved   = 1;

		/**
		 * Filters new voucher note data
		 *
		 * @since 3.0.0
		 * @param array $commentdata array of arguments to insert the note as a comment to the voucher
		 * @param array $args extra arguments like voucher id
		 */
		$commentdata = apply_filters( 'wc_pdf_product_vouchers_new_voucher_note_data', compact( 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_agent', 'comment_type', 'comment_parent', 'comment_approved' ), array( 'voucher_id' => $this->id ) );

		$comment_id = wp_insert_comment( $commentdata );

		// prepare args for filter and send email notification
		$new_voucher_note_args =  array(
			'voucher_id'   => $this->id,
			'voucher_note' => $note,
		);

		/**
		 * Fires after a new voucher note is added
		 *
		 * @since 3.0.0
		 * @param array $new_voucher_note_args voucher note arguments
		 */
		do_action( 'wc_pdf_product_vouchers_new_voucher_note', $new_voucher_note_args );

		return $comment_id;
	}


	/** PDF Generation methods ******************************************************/


	/**
	 * Generates and saves or streams a PDF file for this product voucher
	 *
	 * @since 3.0.0
	 * @param bool $save (optional) whether to save the pdf to filesystem or stream the output
	 * @throws SV_WC_Plugin_Exception if the voucher image is not available
	 */
	public function generate_pdf( $save = true ) {

		// include the pdf generator
		require_once( wc_pdf_product_vouchers()->get_plugin_path() . '/includes/class-wc-pdf-product-vouchers-pdf-generator.php' );

		WC_PDF_Product_Vouchers_PDF_Generator::generate_voucher_pdf( $this, $save );
	}

}

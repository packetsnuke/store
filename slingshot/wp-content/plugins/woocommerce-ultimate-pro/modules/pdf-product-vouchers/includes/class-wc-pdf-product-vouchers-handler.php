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
 * Vouchers handler class
 *
 * This class handles general vouchers-related functionality.
 *
 * In 3.0.0 renamed from \WC_PDF_Product_Vouchers_Voucher
 * to \WC_PDF_Product_Vouchers_Handler to avoid confusion.
 *
 * @since 1.2.0
 */
class WC_PDF_Product_Vouchers_Handler {

	/** @var array of published voucher post data */
	private $vouchers;

	/** @var array of published voucher templates post data */
	private $voucher_templates;

	/** @var array of product id to item, used to associate items with products for a filter that does not provide the item */
	private $product_items = array();

	/** @var string voucher status transition note */
	private $voucher_status_transition_note;

	/** @var string|bool if we're rendering an email, this will be the email template path */
	private $rendering_email = false;


	/**
	 * Initializes the voucher handler class
	 *
	 * @since 1.2.0
	 */
	public function __construct() {

		add_action( 'woocommerce_order_status_completed', array( $this, 'activate_vouchers' ) );

		if ( 'yes' == get_option( 'woocommerce_downloads_grant_access_after_payment' ) ) {
			add_action( 'woocommerce_order_status_processing', array( $this, 'activate_vouchers' ) );
		}

		add_filter( 'woocommerce_email_attachments', array( $this, 'voucher_emails_attachments' ), 10, 3 );

		add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 10, 3 );

		// adjust voucher template edit link
		add_filter( 'get_edit_post_link',  array( $this, 'voucher_template_edit_link' ), 10, 3 );

		// add voucher number to admin emails
		add_action( 'woocommerce_before_template_part', array( $this, 'check_if_rendering_email' ) );
		add_action( 'woocommerce_after_template_part', array( $this, 'remove_rendering_email_check' ) );

		// add links to vouchers on order items table
		add_action( 'woocommerce_order_item_meta_start', array( $this, 'order_item_meta_start' ), 10, 3 );

		// add voucher details in PIP documents
		add_action( 'wc_pip_order_item_meta_start', array( $this, 'order_item_meta_start' ), 10, 3 );

		// add debug tool
		add_filter( 'woocommerce_debug_tools', array( $this, 'add_voucher_generation_test_tool' ) );
	}


	/**
	 * Sets voucher template edit link
	 *
	 * Voucher templates are edited in the customizer, thus we return the customizer
	 * link with the preview url being set to the post preview url.
	 *
	 * @since 3.0.0
	 * @param string $link original voucher edit link
	 * @param int $post_id post (voucher) id
	 * @param string $context context
	 * @return string modified post edit link
	 */
	public function voucher_template_edit_link( $link, $post_id, $context ) {

		if ( 'wc_voucher_template' !== get_post_type( $post_id ) ) {
			return $link;
		}

		$link = add_query_arg( array(
			'url'    => urlencode( get_preview_post_link( $post_id ) ),
			'return' => urlencode( $this->get_current_url() ),
		), admin_url( 'customize.php' ) );

		if ( 'display' === $context ) {
			$link = esc_url( $link );
		}

		return $link;
	}


	/**
	 * Returns a single voucher template
	 *
	 * @since 3.0.0
	 * @param int|\WP_Post|\WC_Voucher_Template $post (optional) post object or post id of the voucher template, defaults to the global $post object
	 * @return \WC_Voucher_Template|false
	 */
	public function get_voucher_template( $post = null ) {

		if ( empty( $post ) && isset( $GLOBALS['post'] ) ) {

			$post = $GLOBALS['post'];

		} elseif ( is_numeric( $post ) ) {

			$post = get_post( $post );

		} elseif ( $post instanceof WC_Voucher_Template ) {

			$post = get_post( $post->get_id() );

		} elseif ( ! ( $post instanceof WP_Post ) ) {

			$post = null;
		}

		// if no acceptable post is found, bail out
		if ( ! $post || 'wc_voucher_template' !== get_post_type( $post ) ) {
			return false;
		}

		/**
		 * Filters the found voucher template
		 *
		 * @since 3.0.0
		 * @param \WC_Voucher_Template $voucher_template the voucher template
		 * @param \WP_Post $post the voucher template post object
		 */
		return apply_filters( 'wc_pdf_product_vouchers_voucher_template', new WC_Voucher_Template( $post ), $post );
	}


	/**
	 * Returns a single voucher
	 *
	 * @since 3.0.0
	 * @param int|\WP_Post|\WC_Voucher $post (optional) post object or post id of the voucher, defaults to the global $post object
	 * @return \WC_Voucher|false
	 */
	public function get_voucher( $post = null ) {

		if ( empty( $post ) && isset( $GLOBALS['post'] ) ) {

			$post = $GLOBALS['post'];

		} elseif ( is_numeric( $post ) ) {

			$post = get_post( $post );

		} elseif ( $post instanceof WC_Voucher ) {

			$post = get_post( $post->get_id() );

		} elseif ( ! ( $post instanceof WP_Post ) ) {

			$post = null;
		}

		// if no acceptable post is found, bail out
		if ( ! $post || 'wc_voucher' !== get_post_type( $post ) ) {
			return false;
		}

		/**
		 * Filters the found voucher
		 *
		 * @since 3.0.0
		 * @param \WC_Voucher $voucher the voucher
		 * @param \WP_Post $post the voucher post object
		 */
		return apply_filters( 'wc_pdf_product_vouchers_voucher', new WC_Voucher( $post ), $post );
	}


	/** Voucher download and attachment methods ******************************************************/


	/**
	 * Attaches relevant voucher files to an email
	 *
	 * @since 1.2.0
	 * @param array $attachments array of file locations to attach to the email
	 * @param string $email_type the email type
	 * @param \WC_Order|array $object the object associated with this email, either a WC_Order object, or an array containing one
	 * @return array of file locations to attach to the email
	 */
	public function voucher_emails_attachments( $attachments, $email_type, $object ) {

		// if this is the completed order email, or the processing email and
		// download access is granted after payment, or the customer_invoice
		// email, and we have captured an order, attach any vouchers
		if ( in_array( $email_type, array( 'customer_completed_order', 'customer_invoice' ) ) ||
			( 'customer_processing_order' == $email_type && 'yes' == get_option( 'woocommerce_downloads_grant_access_after_payment' ) ) ) {

			$order = $object;

			foreach ( WC_PDF_Product_Vouchers_Order::get_vouchers( $order ) as $voucher ) {
				if ( $voucher->file_exists() && $voucher->has_status( 'active' ) ) {
					$attachments[] = $voucher->get_voucher_full_filename();
				}
			}

		} elseif ( 'wc_pdf_product_vouchers_voucher_recipient' == $email_type ) {
			// voucher recipient email type, only attach the vouchers that are destined for the recipient email

			$recipient_email = $object['recipient_email'];
			$order           = $object['order'];

			foreach ( $object['vouchers'] as $voucher ) {
				if ( $voucher->file_exists() && $voucher->has_status( 'active' ) ) {
					$attachments[] = $voucher->get_voucher_full_filename();
				}
			}

		}

		return $attachments;
	}


	/** Voucher Creation ******************************************************/


	/**
	 * Activates vouchers attached to an order
	 *
	 * Invoked when an order status changes to 'completed', or 'processing'
	 * depending on how WooCommerce is configured. If the order has any
	 * voucher items, the voucher PDF files are generated.
	 *
	 * In 3.0.0 renamed from generate_voucher_pdf() to activate_vouchers().
	 *
	 * @since 1.2.0
	 * @param int $order_id newly created order identifier
	 * @param boolean $force_generate_pdf use true to force the vouchers to be re-generated regardless of whether they already exist
	 */
	public function activate_vouchers( $order_id, $force_generate_pdf = false ) {
		global $wpdb;

		$order = wc_get_order( $order_id );

		foreach ( WC_PDF_Product_Vouchers_Order::get_vouchers( $order ) as $voucher ) {

			// skip activating vouchers that are already activated, expired, etc
			if ( ! $voucher->has_status( 'pending' ) ) {
				continue;
			}

			// activate the voucher
			$voucher->update_status( 'active' );

			if ( ! $voucher->get_expiration_date() && $voucher->get_expiry_days() ) {

				/**
				 * Filters the date that voucher expiration is calculated from
				 *
				 * @since 2.1.0
				 * @param int $date timestamp from date, as timestamp
				 * @param int $order_id the order id
				 * @param \WC_Voucher $voucher the voucher object
				 */
				$expiry_from_date = apply_filters( 'wc_pdf_product_vouchers_expiry_from_date', time(), $order_id, $voucher );

				$voucher->set_expiration_date( $expiry_from_date + $voucher->get_expiry_days() * DAY_IN_SECONDS );
			}

			// voucher has already been generated
			if ( $voucher->file_exists() && ! $force_generate_pdf ) {
				continue;
			}

			// try to generate the voucher
			try {
				$voucher->generate_pdf();
			} catch ( Exception $exception ) {
				/* translators: Placeholders: %1$s - voucher number, %2$s - error message */
				$order->add_order_note( sprintf( __( 'PDF Product Vouchers: unable to generate PDF for voucher %1$s: %2$s', 'ultimatewoo-pro' ), $voucher->get_voucher_number(), $exception->getMessage() ) );
			}
		}
	}


	/**
	 * Checks if we're rendering an email and store a reference to the
	 * email template for later usage in order_item_meta_start()
	 *
	 * @since 3.0.0
	 * @param string $template_name the template name
	 */
	public function check_if_rendering_email( $template_name ) {

		if ( ! $this->rendering_email && SV_WC_Helper::str_starts_with( $template_name, 'emails/' ) ) {
			$this->rendering_email = $template_name;
		}
	}


	/**
	 * Removes the reference to the email template being rendered once we've
	 * done rendering the whole template.
	 *
	 * @since 3.0.0
	 * @param string $template_name the template name
	 */
	public function remove_rendering_email_check( $template_name ) {

		if ( $this->rendering_email && $template_name === $this->rendering_email ) {
			$this->rendering_email = false;
		}
	}


	/**
	 * Displays voucher name and link to the voucher in order items table
	 *
	 * This will be called in
	 * - Myaccount > View Order screen
	 * - order receipt emails (both admin & customer)
	 *
	 * @since 3.0.0
	 * @param int $item_id order item identifier
	 * @param array $item order item data
	 * @param \WC_Order $order the order object
	 */
	public function order_item_meta_start( $item_id, $item, WC_Order $order ) {

		$vouchers = WC_PDF_Product_Vouchers_Order::get_order_item_vouchers( $item );

		if ( empty( $vouchers ) ) {
			return;
		}

		// check if we're in admin context
		$is_admin = $this->rendering_email && in_array( $this->rendering_email, array( 'emails/admin-new-order.php', 'emails/plain/admin-new-order.php' ), true );
		$links    = array();

		foreach ( $vouchers as $voucher ) {

			// skip non-active vouchers for customers
			if ( ! $is_admin && ! $voucher->has_status( 'active' ) ) {
				continue;
			}

			// edit lnk for admins, download link for customers
			$link = $is_admin ? get_edit_post_link( $voucher->get_id() ) : $voucher->get_download_url();

			$links[] = '<a href="' . esc_url( $link ) . '">' . esc_html( $voucher->get_voucher_number() ) . '</a>';
		}

		if ( empty( $links ) ) {
			return;
		}

		if ( $this->rendering_email ) {
			echo '<br><small>';
		} else {
			echo '<div class="wc-pdf-product-vouchers-order-item-voucher">';
		}

		printf( '<strong>%s</strong>%s', esc_html( _n( 'Voucher:', 'Vouchers:', count( $links ), 'ultimatewoo-pro' ) ), implode( ', ', $links ) );

		// while an order item may have multiple vouchers, these won't have unique user input field values
		// so we only need to check the first one
		$voucher         = $vouchers[0];
		$input_fields    = $voucher->get_user_input_fields_formatted();
		$template_fields = $voucher->get_template()->get_user_input_voucher_fields();

		// this one will be sort of obvious for the order
		unset( $input_fields['purchaser_name'] );

		foreach ( $input_fields as $key => $value ) {

			if ( isset( $template_fields[ $key ] ) ) {
				echo "<br /><strong>{$template_fields[ $key ]['label']}</strong>: {$value}";
			}
		}


		if ( $this->rendering_email ) {
			echo '</small>';
		} else {
			echo '</div>';
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * Returns the current URL, the WordPress-way
	 *
	 * Instead of relying on the HTTP_HOST server var, we use
	 * home_url(), so that we get the host configured in site options.
	 * Additionally, this automatically uses the correct domain when
	 * using Forward with the WooCommerce Dev Helper plugin.
	 *
	 * @since 3.0.0
	 * @return string
	 */
	private function get_current_url() {
		return home_url() . $_SERVER['REQUEST_URI'];
	}


	/**
	 * Returns all voucher templates
	 *
	 * @deprecated since 3.0.0
	 *
	 * @since 1.2.0
	 * @return \WC_Voucher_Template[]
	 */
	public function get_vouchers() {

		/* @deprecated since 3.0.0 */
		_deprecated_function( 'WC_PDF_Product_Vouchers_Handler::get_vouchers()', '3.0.0', 'WC_PDF_Product_Vouchers_Handler::get_voucher_templates()' );

		return $this->get_voucher_templates();
	}


	/**
	 * Returns all voucher templates
	 *
	 * @since 3.0.0
	 * @return \WC_Voucher_Template[]
	 */
	public function get_voucher_templates() {

		if ( ! isset( $this->voucher_templates ) ) {

			$args  = array( 'post_type' => 'wc_voucher_template', 'numberposts' => -1, 'order'=> 'ASC', 'orderby' => 'title', 'post_status' => 'private' );
			$posts = get_posts( $args );

			$this->voucher_templates = array();

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {

					$this->voucher_templates[] = wc_pdf_product_vouchers_get_voucher_template( $post );
				}
			}
		}

		return $this->voucher_templates;
	}


	/**
	 * Sets the voucher status transition note
	 *
	 * Sets a note to be saved along with the general "status changed from %s to %s" note
	 * when the status of a voucher changes.
	 *
	 * @since 3.0.0
	 * @param string $note note text
	 */
	public function set_voucher_status_transition_note( $note ) {
		$this->voucher_status_transition_note = $note;
	}


	/**
	 * Returns voucher status transition note
	 *
	 * Returns the note and resets it, so it does not interfere with
	 * any following status transitions.
	 *
	 * @since 3.0.0
	 * @return string $note note text
	 */
	public function get_voucher_status_transition_note() {

		$note = $this->voucher_status_transition_note;

		$this->voucher_status_transition_note = null;

		return $note;
	}


	/**
	 * Handles post status transitions for vouchers
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 * @param string $new_status new status slug
	 * @param string $old_status old status slug
	 * @param \WP_Post $post related WP_Post object
	 */
	public function transition_post_status( $new_status, $old_status, WP_Post $post ) {

		if ( 'wc_voucher' !== $post->post_type || $new_status === $old_status ) {
			return;
		}

		// skip for new posts and auto drafts
		if ( 'new' === $old_status || 'auto-draft' === $old_status ) {
			return;
		}

		$voucher = $this->get_voucher( $post );

		$old_status = str_replace( 'wcpdf-', '', $old_status );
		$new_status = str_replace( 'wcpdf-', '', $new_status );

		/* translators: Placeholders: Voucher status changed from status A (%1$s) to status B (%2$s) */
		$status_note   = sprintf( __( 'Voucher status changed from %1$s to %2$s.', 'ultimatewoo-pro' ), wc_pdf_product_vouchers_get_voucher_status_name( $old_status ), wc_pdf_product_vouchers_get_voucher_status_name( $new_status ) );
		$optional_note = $this->get_voucher_status_transition_note();

		// prepend optional note to status note, if provided
		$note = $optional_note ? $optional_note . ' ' . $status_note : $status_note;

		$voucher->add_note( $note );

		switch ( $new_status ) {

			case 'voided':
				$voucher->set_voided_date( current_time( 'mysql', true ) );
			break;

			case 'expired':

				// loose check to see if this was a manually triggered expiration
				$expiration_date = $voucher->get_expiration_date( 'timestamp' );

				// if manually expired, set expire date to now
				if ( $expiration_date > 0 && current_time( 'timestamp', true ) < $expiration_date ) {
					$voucher->set_expiration_date( current_time( 'mysql', true ) );
				}

			break;

			// TODO: should we do some sanity check with `active` status here as well? for example,
			// set expiration date if a voucher has defined expiry_days() but no exp. date is set? {IT 2016-12-21}

		}

		/**
		 * Fires when voucher status is updated
		 *
		 * @since 3.0.0
		 * @param \WC_Voucher $voucher the voucher object
		 * @param string $old_status old status, without the wcpdf- prefix
		 * @param string $new_status new status, without the wcpdf- prefix
		 */
		do_action( 'wc_pdf_product_vouchers_voucher_status_changed', $voucher, $old_status, $new_status );
	}


	/**
	 * Searches for a voucher using the provided term
	 *
	 * @since 3.0.0
	 * @param string $term term to search for
	 * @return array list of matching voucher ids
	 */
	public function search_vouchers( $term ) {
		global $wpdb;

		$term     = wc_clean( $term );
		$post_ids = array();

		/**
		 * Filters the searchable fields when searching vouchers
		 *
		 * @since 3.0.0
		 * @param array $search_fields
		 */
		$search_fields = array_map( 'wc_clean', apply_filters( 'wc_pdf_prdouct_vouchers_voucher_search_fields', array(
			'_purchaser_name',
			'_purchaser_email',
			'_recipient_name',
			'_recipient_email',
			'_message',
		) ) );

		// Search orders.
		if ( is_numeric( $term ) ) {

			$post_ids = array_unique( array_merge(
				$wpdb->get_col(
					$wpdb->prepare( "SELECT DISTINCT p1.post_id FROM {$wpdb->postmeta} p1 WHERE p1.meta_key IN ('" . implode( "','", array_map( 'esc_sql', $search_fields ) ) . "') AND p1.meta_value LIKE '%%%s%%';", wc_clean( $term ) )
				),
				array( absint( $term ) )
			) );

		} elseif ( ! empty( $search_fields ) ) {

			$post_ids = $wpdb->get_col( $wpdb->prepare( "
					SELECT DISTINCT p1.post_id
					FROM {$wpdb->postmeta} p1
					INNER JOIN {$wpdb->postmeta} p2 ON p1.post_id = p2.post_id
					WHERE ( p1.meta_key IN ('" . implode( "','", array_map( 'esc_sql', $search_fields ) ) . "') AND p1.meta_value LIKE '%%%s%%' )
			", $term, $term, $term ) );
		}

		return $post_ids;
	}


	/**
	 * Returns all voucher statuses
	 *
	 * @since 3.0.0
	 * @return array associative array of statuses
	 */
	public function get_voucher_statuses() {

		$statuses = array(

			// Pending vouchers are those that have been created, but not yet activated, for
			// example when the order has not yet been paid for. Pending vouchers cannot be
			// redeemed/used.
			'wcpdf-pending'  => array(
				'label'       => _x( 'Pending', 'Voucher Status', 'ultimatewoo-pro' ),
				/* translators: Pending Voucher(s) */
				'label_count' => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'ultimatewoo-pro' ),
			),

			// Active means a voucher can be used/redeemed.
			'wcpdf-active'   => array(
				'label'       => _x( 'Active', 'Voucher Status', 'ultimatewoo-pro' ),
				/* translators: Active Voucher(s) */
				'label_count' => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'ultimatewoo-pro' ),
			),

			// When a voucher is fully redeemed, it cannot be used anymore. Also, it does
			// not expire and cannot be voided. This status does not cover partially redeemed
			// vouchers, which are still considered `active`.
			'wcpdf-redeemed' => array(
				'label'       => _x( 'Redeemed', 'Voucher Status', 'woocommerce-memberships' ),
				/* translators: Redeemed Voucher(s) */
				'label_count' => _n_noop( 'Redeemed <span class="count">(%s)</span>', 'Redeemed <span class="count">(%s)</span>', 'ultimatewoo-pro' ),
			),

			// An expired voucher may or may not be partially redeemed. It cannot be used anymore.
			'wcpdf-expired'  => array(
				'label'       => _x( 'Expired', 'Voucher Status', 'woocommerce-memberships' ),
				/* translators: Expired Voucher(s) */
				'label_count' => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'ultimatewoo-pro' ),
			),

			// A voucher can be voided in order to prevent it from being used (for example, a customer
			// violated TOC). A voided voucher is not the same as expired, and it cannot expire.
			'wcpdf-voided'  => array(
				'label'       => _x( 'Voided', 'Voucher Status', 'woocommerce-memberships' ),
				/* translators: Expired Voucher(s) */
				'label_count' => _n_noop( 'Voided <span class="count">(%s)</span>', 'Voided <span class="count">(%s)</span>', 'ultimatewoo-pro' ),
			),

		);

		/**
		 * Filters available voucher statuses
		 *
		 * @since 3.0.0
		 * @param array $statuses associative array of statuses and labels
		 * @return array
		 */
		return apply_filters( 'wc_pdf_product_vouchers_voucher_statuses', $statuses );
	}


	/**
	 * Generates a unique voucher number
	 *
	 * Since 3.0.0 generates a unique, random string with order number as suffix,
	 * rather than a sequential number.
	 *
	 * @since 1.2
	 * @param int $order_id (optional) the order id the voucher is related to
	 * @return string
	 */
	public function generate_voucher_number( $order_id = null ) {
		global $wpdb;

		$prefix = get_option( 'wc_pdf_product_vouchers_voucher_number_prefix' );
		$random = strtoupper( wp_generate_password( 8, false ) );
		$order  = $order_id ? wc_get_order( $order_id ) : null;
		$number = $order instanceof WC_Order ? sprintf( '%s-%s', $random, $order->get_order_number() ) : $random;

		if ( $prefix ) {
			$number = sprintf( '%s-%s', $prefix, $number );
		}

		// ensure the generated voucher number is truly unique
		$query = $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE post_type = 'wc_voucher' AND post_title = %s", $number );

		if ( $wpdb->get_var( $query ) ) {
			return self::generate_voucher_number( $order_id );
		} else {

			/**
			 * Filters the generated voucher number
			 *
			 * Since 3.0.0, this should be used instead of `wc_pdf_product_vouchers_get_voucher_number`
			 * for controlling the generated voucher number.
			 *
			 * @since 3.0.0
			 * @param string $number generated voucher number
			 * @param string $prefix voucher number prefix, if available
			 * @param string $random random part of the voucher number
			 * @param int $order_id order ID associated with the voucher, if available
			 */
			$number = apply_filters( 'wc_pdf_product_vouchers_generated_voucher_number', $number, $prefix, $random, $order_id );

			/**
			 * Fires when a voucher number is generated
			 *
			 * This does not guarantee that a voucher with the generated number will be
			 * saved.
			 *
			 * In 3.0.0 added the $prefix, $random and $order_id params.
			 *
			 * @since 1.2
			 * @param string $number generated voucher number
			 * @param string $prefix voucher number prefix, if available
			 * @param string $random random part of the voucher number
			 * @param int $order_id order id associated with the voucher, if available
			 */
			do_action( 'wc_pdf_product_vouchers_generate_voucher_number', $number, $prefix, $random, $order_id );

			return $number;
		}
	}


	/**
	 * Adds voucher generation testing tool to WC system status -> tools page.
	 *
	 * @internal
	 * @since 3.0.5
	 *
	 * @param array $tools system status tools
	 * @return array
	 */
	public function add_voucher_generation_test_tool( $tools ) {

		$tools['wc_pdf_product_vouchers_test_voucher_generation'] = array(
			'name'     => __( 'PDF Vouchers', 'ultimatewoo-pro' ),
			'button'   => __( 'Test voucher generation', 'ultimatewoo-pro' ),
			'desc'     => __( 'This tool will test whether your server is capable of generating PDF vouchers.', 'ultimatewoo-pro' ),
			'callback' => array( $this, 'test_voucher_generation' ),
		);

		return $tools;
	}


	/**
	 * Tests whether vouchers can be generated by firing a GET request to the server itself
	 *
	 * @internal
	 * @since 3.0.5
	 *
	 * @return bool whether the request was successful or not
	 */
	public function test_voucher_generation() {

		$url     = add_query_arg( 'wc_pdf_product_vouchers_test_voucher_generation', 1, home_url( '/' ) );
		$result  = wp_remote_get( $url );
		$success = is_array( $result ) && ! empty( $result['body'] ) && '[TEST_VOUCHER_CONTENT]' === $result['body'];

		// add admin notice manually - we can't return an error message and we're a little too late for the admin message handler
		if ( ! $success && is_admin() ) {
			echo '<div class="error inline"><p>' . esc_html__( 'Could not generate test voucher. Please ensure your server has loopback connections enabled.', 'ultimatewoo-pro' ) . '</p></div>';
		}

		return $success;
	}

}

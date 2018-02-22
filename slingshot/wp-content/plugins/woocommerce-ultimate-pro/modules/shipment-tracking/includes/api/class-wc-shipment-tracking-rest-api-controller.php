<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API shipment tracking controller.
 *
 * Handles requests to /orders/shipment-tracking endpoint.
 *
 * @since 1.6.8
 */

class WC_Shipment_Tracking_REST_API_Controller extends WC_Shipment_Tracking_V1_REST_API_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v2';

}

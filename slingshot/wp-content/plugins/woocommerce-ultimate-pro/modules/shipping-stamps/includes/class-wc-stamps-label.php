<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Stamps_Label class
 */
class WC_Stamps_Label {

	private $id;
	private $url;
	private $tracking_number;

	/**
	 * Constructor
	 */
	public function __construct( $label_id ) {
		$label = get_post( $label_id );

		if ( $label && 'wc_stamps_label' === $label->post_type ) {
			$this->id              = $label_id;
			$this->tracking_number = $label->post_title;
			$this->url             = $label->post_content;
		}
	}

	/**
	 * See if label is valid
	 * @return boolean
	 */
	public function is_valid() {
		return ! empty( $this->id ) && ! empty( $this->url );
	}

	/**
	 * __isset function.
	 *
	 * @param mixed $key
	 * @return bool
	 */
	public function __isset( $key ) {
		return metadata_exists( 'post', $this->id, $key );
	}

	/**
	 * __get function.
	 *
	 * @param string $key
	 * @return string
	 */
	public function __get( $key ) {
		return get_post_meta( $this->id, $key, true );
	}

	/**
	 * Get value
	 * @return string
	 */
	public function get_value( $key ) {
		return get_post_meta( $this->id, $key, true );
	}

	/**
	 * Get ID of the label
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the URL to the label
	 * @return string
	 */
	public function get_label_url() {
		return $this->url;
	}

	/**
	 * Get the tracking number
	 * @return string
	 */
	public function get_tracking_number() {
		return $this->tracking_number;
	}
}
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WooCommerce Photography Admin Settings.
 *
 * @package  WC_Photography/Admin/Settings
 * @category Class
 * @author   WooThemes
 */
class WC_Photography_Admin_Settings {

	/**
	 * Settings id.
	 *
	 * @var string
	 */
	public $settings_id = 'woocommerce_photography';

	/**
	 * Initialize the plugin settings.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_settings' ) );
	}

	/**
	 * Photography settings.
	 *
	 * @return array
	 */
	public function get_settings() {
		return array(
			array(
				'id'     => 'collections_section',
				'title'  => __( 'Collections', 'ultimatewoo-pro' ),
				'fields' => array(
					array(
						'id'          => 'collections_default_visibility',
						'type'        => 'select',
						'label'       => __( 'Collections Default Visibility', 'ultimatewoo-pro' ),
						'description' => __( 'Set the default Collections visibiliy.' ),
						'default'     => 'restricted',
						'options'     => array(
							'restricted' => __( 'Restricted', 'ultimatewoo-pro' ),
							'public'     => __( 'Public', 'ultimatewoo-pro' )
						)
					)
				)
			),
			array(
				'id'     => 'thumbnails_section',
				'title'  => __( 'Thumbnails', 'ultimatewoo-pro' ),
				'fields' => array(
					array(
						'id'          => 'thumbnail_image_size',
						'type'        => 'image',
						'label'       => __( 'Collections Thumbnail size:', 'ultimatewoo-pro' ),
						'description' => '',
						'default'     => array(
							'width'  => 200,
							'height' => 200,
							'crop'   => false
						)
					),
					array(
						'id'          => 'lightbox_image_size',
						'type'        => 'image',
						'label'       => __( 'Collections Lightbox size:', 'ultimatewoo-pro' ),
						'description' => '',
						'default'     => array(
							'width'  => 600,
							'height' => 600,
							'crop'   => false
						)
					)
				)
			)
		);
	}

	/**
	 * Initialize the plugin settings
	 *
	 * @return void
	 */
	public function init_settings() {

		$settings = $this->get_settings();

		foreach ( $settings as $section ) {
			add_settings_section(
				$section['id'],
				$section['title'],
				'__return_null',
				$this->settings_id
			);

			foreach ( $section['fields'] as $field ) {
				add_settings_field(
					$field['id'],
					$field['label'],
					array( $this, $field['type'] . '_element_callback' ),
					$this->settings_id,
					$section['id'],
					array(
						'id'          => $field['id'],
						'description' => isset( $field['description'] ) ? $field['description'] : '',
						'default'     => isset( $field['default'] ) ? $field['default'] : '',
						'options'     => isset( $field['options'] ) ? $field['options'] : ''
					)
				);
			}
		}

		// Register settings
		register_setting( $this->settings_id, $this->settings_id, array( $this, 'validate_settings' ) );
	}

	/**
	 * Get a option
	 *
	 * @param  string $id
	 * @param  mixed $default
	 *
	 * @return mixed
	 */
	public function get_option( $id, $default ) {
		$saved = get_option( $this->settings_id, array() );

		if ( isset( $saved[ $id ] ) ) {
			return $saved[ $id ];
		} else {
			return $default;
		}
	}

	/**
	 * Image HTML element callback
	 *
	 * @param  array $params Element params
	 *
	 * @return string
	 */
	public function image_element_callback( $params ) {
		$id         = $params['id'];
		$name       = $this->settings_id . '[' . $id . ']';
		$default    = isset( $params['default'] ) && is_array( $params['default'] ) ? $params['default'] : array();
		$saved      = $this->get_option( $id, $default );
		$image_size = str_replace( '_image_size', '', $id );

		// Get the image size
		$width  = isset( $saved[ 'width' ] ) ? $saved[ 'width' ] : '150';
		$height = isset( $saved[ 'height' ] ) ? $saved[ 'height' ] : '150';
		$crop   = isset( $saved[ 'crop' ] ) ? $saved[ 'crop' ] : true;

		$disabled_attr    = '';
		$disabled_message = '';

		if ( has_filter( 'wc_photography_get_image_size_' . $image_size ) ) {
			$disabled_attr    = 'disabled="disabled"';
			$disabled_message = '<p><small>' . __( 'The settings of this image size have been disabled because its values are being overwritten by a filter.', 'ultimatewoo-pro' ) . '</small></p>';
		}

		echo '<div class="image_width_settings">';

		echo $disabled_message;

		echo sprintf( '<input name="%2$s[width]" %7$s id="%1$s-width" type="text" size="3" value="%3$s" /> &times; <input name="%2$s[height]" %7$s id="%1$s-height" type="text" size="3" value="%4$s" />px <label style="margin-left: 10px;"><input name="%2$s[crop]" %7$s id="%1$s-crop" type="checkbox" value="1" %5$s /> %6$s</label>', $id, $name, $width, $height, checked( 1, $crop, false ), __( 'Hard Crop?', 'ultimatewoo-pro' ), $disabled_attr );

		if ( ! empty( $params['description'] ) ) {
			echo'<p class="description">' . $params['description'] . '</p>';
		}

		echo '</div>';
	}

	/**
	 * Select HTML element callback
	 *
	 * @param  array $params Element params
	 *
	 * @return string
	 */
	public function select_element_callback( $params ) {
		$id       = $params['id'];
		$name     = $this->settings_id . '[' . $id . ']';
		$default  = isset( $params['default'] ) && is_array( $params['default'] ) ? $params['default'] : array();
		$saved    = $this->get_option( $id, $default );
		$options  = $params['options'];

		echo sprintf( '<select id="%s" name="%s" class="wc-enhanced-select" style="width: 25em;">', $id, $name );

		foreach ( $options as $key => $value ) {
			echo sprintf( '<option value="%s" %s>%s</option>', $key, selected( $key, $saved, false ), esc_attr( $value ) );
		}

		echo '</select>';

		if ( ! empty( $params['description'] ) ) {
			echo'<p class="description">' . $params['description'] . '</p>';
		}
	}

	/**
	 * Validate the settings
	 *
	 * @param  array $input options to valid.
	 *
	 * @return array        validated options.
	 */
	public function validate_settings( $input ) {
		$output = array();

		$settings = $this->get_settings();

		foreach ( $settings as $section ) {
			foreach ( $section['fields'] as $field ) {
				$id    = $field['id'];
				$value = '';

				if ( ! isset( $input[ $id ] ) ) {
					continue;
				}

				switch ( $field['type'] ) {
					case 'image' :
						$value = array(
							'width'  => absint( $input[ $id ]['width'] ),
							'height' => absint( $input[ $id ]['height'] ),
							'crop'   => isset( $input[ $id ]['crop'] ) ? true : false
						);
						break;

					default :
						$value = wc_clean( $input[ $id ] );
						break;
				}

				$output[ $id ] = $value;
			}
		}

		return $output;
	}
}

new WC_Photography_Admin_Settings();

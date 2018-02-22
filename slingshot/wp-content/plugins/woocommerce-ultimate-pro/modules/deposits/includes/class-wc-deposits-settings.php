<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Deposits_Settings class.
 */
class WC_Deposits_Settings {

	/** @var Settings Tab ID */
	private $settings_tab_id = 'deposits';

	/** @var object Class Instance */
	private static $instance;

	/**
	 * Get the class instance.
	 */
	public static function get_instance() {
		return null === self::$instance ? ( self::$instance = new self ) : self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Load in the new settings tabs.
		add_action( 'woocommerce_get_sections_products', array( $this, 'add_woocommerce_settings_tab' ), 50 );
		add_action( 'woocommerce_get_settings_products', array( $this, 'get_settings' ), 50, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'styles_and_scripts' ) );
	}

	/**
	 * Scripts.
	 */
	public function styles_and_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'woocommerce-deposits-admin', WC_DEPOSITS_PLUGIN_URL . '/assets/js/admin' . $suffix . '.js', array( 'jquery' ), WC_DEPOSITS_VERSION, true );
	}

	/**
	 * Add settings tab to woocommerce.
	 */
	public function add_woocommerce_settings_tab( $settings_tabs ) {
		$settings_tabs[ $this->settings_tab_id ] = __( 'Deposits', 'ultimatewoo-pro' );
		return $settings_tabs;
	}

	/**
	 * Returns settings array.
	 *
	 * @return array settings
	 */
	public function get_settings( $settings, $current_section ) {
		if ( 'deposits' !== $current_section ) {
			return $settings;
		}

		wp_enqueue_script( 'woocommerce-deposits-admin' );

		$payment_gateways        = WC()->payment_gateways->payment_gateways();
		$payment_gateway_options = array();

		foreach ( $payment_gateways as $gateway ) {
			$payment_gateway_options[ $gateway->id ] = $gateway->get_title();
		}

		$plans = WC_Deposits_Plans_Manager::get_plan_ids();

		return apply_filters( 'woocommerce_deposits_get_settings',
			array(
				array(
					'name' => __( 'Storewide Deposits Configuration', 'ultimatewoo-pro' ),
					'type' => 'title',
					'desc' => __( 'These settings affect all products storewide. You can override these settings on a per product basis to make exceptions.', 'ultimatewoo-pro' ),
					'id'   => 'deposits_defaults',
				),

				array(
					'name'     => __( 'Enable Deposits by Default', 'ultimatewoo-pro' ),
					'type'     => 'select',
					'desc'     => __( 'You must set a default amount below if setting this option to "yes".', 'ultimatewoo-pro' ),
					'default'  => 'no',
					'id'       => 'wc_deposits_default_enabled',
					'desc_tip' => true,
					'options'  => array(
						'optional' => __( 'Yes - deposits are optional', 'ultimatewoo-pro' ),
						'forced'   => __( 'Yes - deposits are required', 'ultimatewoo-pro' ),
						'no'       => __( 'No', 'ultimatewoo-pro' ),
					),
				),

				array(
					'name' => esc_html__( 'Default Deposit Type', 'ultimatewoo-pro' ),
					'type'        => 'select',
					'desc'        => esc_html__( 'The default way for customers to pay for this product using a deposit.', 'ultimatewoo-pro' ),
					'default'     => 'percent',
					'desc_tip'    => true,
					'id'          => 'wc_deposits_default_type',
					'options'     => array(
						'none'    => esc_html__( 'None', 'ultimatewoo-pro' ),
						'percent' => esc_html__( 'Percentage', 'ultimatewoo-pro' ),
						'fixed'   => esc_html__( 'Fixed Amount', 'ultimatewoo-pro' ),
						'plan'    => esc_html__( 'Payment Plan', 'ultimatewoo-pro' ),
					),
				),

				array(
					'name' => __( 'Default Deposit Amount', 'ultimatewoo-pro' ),
					'type'        => 'text',
					'desc'        => __( 'The default deposit amount.', 'ultimatewoo-pro' ),
					'default'     => '',
					'placeholder' => __( 'n/a', 'ultimatewoo-pro' ),
					'id'          => 'wc_deposits_default_amount',
					'desc_tip'    => true,
				),

				array(
					'name' => esc_html__( 'Default Deposit Selected Type', 'ultimatewoo-pro' ),
					'type'        => 'select',
					'desc'        => esc_html__( 'The default selected way for customers to pay for this product using a deposit.', 'ultimatewoo-pro' ),
					'default'     => 'deposit',
					'desc_tip'    => true,
					'id'          => 'wc_deposits_default_selected_type',
					'options'     => array(
						'deposit' => esc_html__( 'Pay Deposit', 'ultimatewoo-pro' ),
						'full'    => esc_html__( 'Pay in Full', 'ultimatewoo-pro' ),
					),
				),

				array(
					'name'        => esc_html__( 'Default Payment Plan', 'ultimatewoo-pro' ),
					'type'        => 'multiselect',
					'class'       => 'wc-enhanced-select',
					'css'         => 'width: 450px;',
					'desc'        => esc_html__( 'The default payment plans to use.', 'ultimatewoo-pro' ),
					'default'     => array(),
					'id'          => 'wc_deposits_default_plans',
					'desc_tip'    => true,
					'options'     => $plans,
				),

				array(
					'name'     => __( 'Disable Payment Gateways', 'ultimatewoo-pro' ),
					'type'     => 'multiselect',
					'class'    => 'wc-enhanced-select',
					'css'      => 'width: 450px;',
					'desc'     => __( 'Select payment gateways that should be disabled when accepting deposits.', 'ultimatewoo-pro' ),
					'default'  => '',
					'id'       => 'wc_deposits_disabled_gateways',
					'desc_tip' => true,
					'options'  => $payment_gateway_options,
				),
				array( 'type' => 'sectionend', 'id' => 'deposits_defaults' ),
			)
		);
	}
}

WC_Deposits_Settings::get_instance();

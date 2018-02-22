<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$webhook_url = WC_Stripe_Helper::get_webhook_url();

return apply_filters( 'wc_stripe_ideal_settings',
	array(
		'geo_target' => array(
			'description' => __( 'Relevant Payer Geography: The Netherlands', 'ultimatewoo-pro' ),
			'type'        => 'title',
		),
		'guide' => array(
			'description' => __( '<a href="https://stripe.com/payments/payment-methods-guide#ideal" target="_blank">Payment Method Guide</a>', 'ultimatewoo-pro' ),
			'type'        => 'title',
		),
		'activation' => array(
			'description' => __( 'Must be activated from your Stripe Dashboard Settings <a href="https://dashboard.stripe.com/account/payments/settings" target="_blank">here</a>', 'ultimatewoo-pro' ),
			'type'   => 'title',
		),
		'enabled' => array(
			'title'       => __( 'Enable/Disable', 'ultimatewoo-pro' ),
			'label'       => __( 'Enable Stripe iDeal', 'ultimatewoo-pro' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		),
		'title' => array(
			'title'       => __( 'Title', 'ultimatewoo-pro' ),
			'type'        => 'text',
			'description' => __( 'This controls the title which the user sees during checkout.', 'ultimatewoo-pro' ),
			'default'     => __( 'iDeal', 'ultimatewoo-pro' ),
			'desc_tip'    => true,
		),
		'description' => array(
			'title'       => __( 'Description', 'ultimatewoo-pro' ),
			'type'        => 'text',
			'description' => __( 'This controls the description which the user sees during checkout.', 'ultimatewoo-pro' ),
			'default'     => __( 'You will be redirected to iDeal.', 'ultimatewoo-pro' ),
			'desc_tip'    => true,
		),
		'webhook' => array(
			'title'       => __( 'Webhook Endpoints', 'ultimatewoo-pro' ),
			'type'        => 'title',
			/* translators: webhook URL */
			'description' => sprintf( __( 'You must add the webhook endpoint <strong style="background-color:#ddd;">&nbsp;&nbsp;%s&nbsp;&nbsp;</strong> to your Stripe Account Settings <a href="https://dashboard.stripe.com/account/webhooks" target="_blank">Here</a> so you can receive notifications on the charge statuses.', 'ultimatewoo-pro' ), $webhook_url ),
		),
	)
);

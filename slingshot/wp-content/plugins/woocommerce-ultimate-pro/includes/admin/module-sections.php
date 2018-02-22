<?php

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *	Module sections
 *	@package UltimateWoo Pro
 *	@author UltimateWoo
 *
 *	@return array
 */
function ultimatewoo_get_module_sections() {

	$sections = array(

		'sales_marketing' => array( # Sales and marketing

			'section_title' => __( 'Sales and Marketing Modules', 'ultimatewoo-pro' ),
			'section_description' => __( 'Boost your website\'s sales and marketing efforts.', 'ultimatewoo-pro' ),
			'section_modules' => array(
				array(

					'title' => __( 'Brands', 'ultimatewoo-pro' ),
					'key' => 'brands',
					'include_path' => '/brands/woocommerce-brands.php',
				),
				array(

					'title' => __( 'Cart Notices', 'ultimatewoo-pro' ),
					'key' => 'cart_notices',
					'include_path' => '/cart-notices/woocommerce-cart-notices.php',
				),
				array(

					'title' => __( 'Chained Products', 'ultimatewoo-pro' ),
					'key' => 'chained_products',
					'include_path' => '/chained-products/woocommerce-chained-products.php',
				),
				array(

					'title' => __( 'Checkout Addons', 'ultimatewoo-pro' ),
					'key' => 'checkout_addons',
					'include_path' => '/checkout-add-ons/woocommerce-checkout-add-ons.php',
				),
				array(

					'title' => __( 'Compare Products', 'ultimatewoo-pro' ),
					'key' => 'compare_products',
					'include_path' => '/compare-products/woocommerce-products-compare.php',
				),
				array(

					'title' => __( 'Composite Products', 'ultimatewoo-pro' ),
					'key' => 'composite_products',
					'include_path' => '/composite-products/woocommerce-composite-products.php',
				),
				array(

					'title' => __( 'Facebook Tabs', 'ultimatewoo-pro' ),
					'key' => 'facebook_tabs',
					'include_path' => '/facebook-tabs/woocommerce-facebook-tab.php',
				),
				array(

					'title' => __( 'Gravity Forms Product Addons', 'ultimatewoo-pro' ),
					'key' => 'gravity_forms_product_addons',
					'include_path' => '/gravityforms-product-addons/gravityforms-product-addons.php',
				),
				array(

					'title' => __( 'PDF Vouchers', 'ultimatewoo-pro' ),
					'key' => 'pdf_vouchers',
					'include_path' => '/pdf-product-vouchers/woocommerce-pdf-product-vouchers.php',
				),
				array(

					'title' => __( 'Points and Rewards', 'ultimatewoo-pro' ),
					'key' => 'points_rewards',
					'include_path' => '/points-and-rewards/woocommerce-points-and-rewards.php',
				),
				array(

					'title' => __( 'Pre Orders', 'ultimatewoo-pro' ),
					'key' => 'pre_orders',
					'include_path' => '/pre-orders/woocommerce-pre-orders.php',
				),
				array(

					'title' => __( 'Product Addons', 'ultimatewoo-pro' ),
					'key' => 'product_addons',
					'include_path' => '/product-addons/woocommerce-product-addons.php',
				),
				array(

					'title' => __( 'Product Bundles', 'ultimatewoo-pro' ),
					'key' => 'product_bundles',
					'include_path' => '/product-bundles/woocommerce-product-bundles.php',
				),
				array(

					'title' => __( 'Product Enquiry Form', 'ultimatewoo-pro' ),
					'key' => 'product_enquiry_form',
					'include_path' => '/product-enquiry-form/product-enquiry-form.php',
				),
				array(

					'title' => __( 'Products of the Day', 'ultimatewoo-pro' ),
					'key' => 'products_of_the_day',
					'include_path' => '/products-of-the-day/products-of-the-day.php',
				),
				array(

					'title' => __( 'Smart Coupons', 'ultimatewoo-pro' ),
					'key' => 'smart_coupons',
					'include_path' => '/smart-coupons/woocommerce-smart-coupons.php',
				),
				array(

					'title' => __( 'Splash Popup', 'ultimatewoo-pro' ),
					'key' => 'splash_popup',
					'include_path' => '/splash-popup/splash.php',
				),
				array(

					'title' => __( 'Store Credit', 'ultimatewoo-pro' ),
					'key' => 'store_credit',
					'include_path' => '/store-credit/store-credit.php',
				),
				array(

					'title' => __( 'URL Coupons', 'ultimatewoo-pro' ),
					'key' => 'url_coupons',
					'include_path' => '/url-coupons/woocommerce-url-coupons.php',
				),
				array(

					'title' => __( 'Waitlists', 'ultimatewoo-pro' ),
					'key' => 'waitlist',
					'include_path' => '/waitlist/woocommerce-waitlist.php',
				),
				array(

					'title' => __( 'Wishlists', 'ultimatewoo-pro' ),
					'key' => 'wishlists',
					'include_path' => '/wishlists/woocommerce-wishlists.php',
				),
			)
		),

		'product_types' => array( # Specialty product-types

			'section_title' => __( 'Product Types Modules', 'ultimatewoo-pro' ),
			'section_description' => __( 'Offer different types of products, such as subscriptions and appointments.', 'ultimatewoo-pro' ),
			'section_modules' => array(
				array(

					'title' => __( 'Bookings', 'ultimatewoo-pro' ),
					'key' => 'bookings',
					'include_path' => '/bookings/woocommerce-bookings.php',
				),
				array(

					'title' => __( 'Memberships', 'ultimatewoo-pro' ),
					'key' => 'memberships',
					'include_path' => '/memberships/woocommerce-memberships.php',
				),
				array(

					'title' => __( 'Photography', 'ultimatewoo-pro' ),
					'key' => 'photography',
					'include_path' => '/photography/woocommerce-photography.php',
				),
				array(

					'title' => __( 'Subscriptions', 'ultimatewoo-pro' ),
					'key' => 'subscriptions',
					'include_path' => '/subscriptions/woocommerce-subscriptions.php',
				),
			)
		),

		'email' => array( # Email

			'section_title' => __( 'Email Modules', 'ultimatewoo-pro' ),
			'section_description' => __( 'Increase your communication with customers through effective use of email.', 'ultimatewoo-pro' ),
			'section_modules' => array(
				array(

					'title' => __( 'Aweber', 'ultimatewoo-pro' ),
					'key' => 'aweber',
					'include_path' => '/aweber/aweber-newsletter-subscription.php',
				),
				array(

					'title' => __( 'Constant Contact', 'ultimatewoo-pro' ),
					'key' => 'constant_contact',
					'include_path' => '/constant-contact/woocommerce-constant-contact.php',
				),
				array(

					'title' => __( 'Drip', 'ultimatewoo-pro' ),
					'key' => 'drip',
					'include_path' => '/drip/woocommerce-drip.php',
				),
				array(

					'title' => __( 'Email Customizer', 'ultimatewoo-pro' ),
					'key' => 'email_customizer',
					'include_path' => '/email-customizer/woocommerce-email-customizer.php',
				),
				array(

					'title' => __( 'Follow Up Emails', 'ultimatewoo-pro' ),
					'key' => 'follow_up_emails',
					'include_path' => '/follow-up-emails/woocommerce-follow-up-emails.php',
				),
				array(

					'title' => __( 'Newsletter', 'ultimatewoo-pro' ),
					'key' => 'newsletter',
					'include_path' => 'subscribe-to-newsletter/woocommerce-subscribe-to-newsletter.php',
				),
			)
		),

		'payment_gateways' => array( # Payment gateways

			'section_title' => __( 'Payment Method Modules', 'ultimatewoo-pro' ),
			'section_description' => __( 'Integrate payment gateways to accept payment through various providers, such as Stripe and 2Checkout.', 'ultimatewoo-pro' ),
			'section_modules' => array(
				array(

					'title' => __( '2Checkout', 'ultimatewoo-pro' ),
					'key' => '2checkout',
					'include_path' => '/2checkout/gateway-2checkout.php',
				),
				array(

					'title' => __( 'Account Funds', 'ultimatewoo-pro' ),
					'key' => 'account_funds',
					'include_path' => '/account-funds/woocommerce-account-funds.php',
				),
				array(

					'title' => __( 'Authorize.net AIM', 'ultimatewoo-pro' ),
					'key' => 'authorize_net_aim',
					'include_path' => '/authorize-net-aim/woocommerce-gateway-authorize-net-aim.php',
				),
				array(

					'title' => __( 'Braintree', 'ultimatewoo-pro' ),
					'key' => 'braintree',
					'include_path' => '/braintree/woocommerce-gateway-braintree.php',
				),
				array(

					'title' => __( 'Deposits', 'ultimatewoo-pro' ),
					'key' => 'deposits',
					'include_path' => '/deposits/woocommmerce-deposits.php',
				),
				array(

					'title' => __( 'Intuit Quickbooks', 'ultimatewoo-pro' ),
					'key' => 'qbms',
					'include_path' => '/intuit-qbms/woocommerce-gateway-intuit-qbms.php',
				),
				array(

					'title' => __( 'PayPal Digital Goods', 'ultimatewoo-pro' ),
					'key' => 'paypal_digital_goods',
					'include_path' => '/paypal-digital-goods/gateway-paypal-digital-goods.php',
				),
				array(

					'title' => __( 'PayPal Express', 'ultimatewoo-pro' ),
					'key' => 'paypal_express',
					'include_path' => '/paypal-express/woocommerce-gateway-paypal-express.php',
				),
				array(

					'title' => __( 'PayPal Pro', 'ultimatewoo-pro' ),
					'key' => 'paypal_pro',
					'include_path' => '/paypal-pro/woocommerce-gateway-paypal-pro.php',
				),
				array(

					'title' => __( 'Purchase Order', 'ultimatewoo-pro' ),
					'key' => 'purchase_order',
					'include_path' => '/purchase-order/woocommerce-gateway-purchase-order.php',
				),
				array(

					'title' => __( 'Stripe', 'ultimatewoo-pro' ),
					'key' => 'stripe',
					'include_path' => '/stripe/woocommerce-gateway-stripe.php',
				),
			)
		),

		'shipping' => array( # Shipping

			'section_title' => __( 'Shipping Modules', 'ultimatewoo-pro' ),
			'section_description' => __( 'Provide shipping options for your customers so they can receive their orders in a professional way.', 'ultimatewoo-pro' ),
			'section_modules' => array(
				array(

					'title' => __( 'FedEx', 'ultimatewoo-pro' ),
					'key' => 'fedex',
					'include_path' => '/shipping-fedex/woocommerce-shipping-fedex.php',
				),
				array(

					'title' => __( 'Shipment Tracking', 'ultimatewoo-pro' ),
					'key' => 'shipment_tracking',
					'include_path' => '/shipment-tracking/woocommerce-shipment-tracking.php',
				),
				array(

					'title' => __( 'Stamps.com', 'ultimatewoo-pro' ),
					'key' => 'stamps',
					'include_path' => '/shipping-stamps/woocommmerce-shipping-stamps.php',
				),
				array(

					'title' => __( 'Table Rate Shipping', 'ultimatewoo-pro' ),
					'key' => 'table_rate_shipping',
					'include_path' => '/table-rate-shipping/woocommerce-table-rate-shipping.php',
				),
				array(

					'title' => __( 'USPS', 'ultimatewoo-pro' ),
					'key' => 'usps',
					'include_path' => '/shipping-usps/woocommerce-shipping-usps.php',
				),
				array(

					'title' => __( 'UPS', 'ultimatewoo-pro' ),
					'key' => 'ups',
					'include_path' => '/shipping-ups/woocommerce-shipping-ups.php',
				),
			)
		),

		'reporting' => array( # Reporting

			'section_title' => __( 'Reporting Modules', 'ultimatewoo-pro' ),
			'section_description' => __( 'Track and analyze important data to effectively manage your websit\'s operations.', 'ultimatewoo-pro' ),
			'section_modules' => array(
				array(

					'title' => __( 'Cart Reports', 'ultimatewoo-pro' ),
					'key' => 'cart_reports',
					'include_path' => '/cart-reports/woocommerce-cart-reports.php',
				),
				array(

					'title' => __( 'Cost of Goods', 'ultimatewoo-pro' ),
					'key' => 'cost_of_goods',
					'include_path' => '/cost-of-goods/woocommerce-cost-of-goods.php',
				),
				array(

					'title' => __( 'Customer History', 'ultimatewoo-pro' ),
					'key' => 'customer_history',
					'include_path' => '/customer-history/woocommerce-customer-history.php',
				),
				array(

					'title' => __( 'Sales Report Emails', 'ultimatewoo-pro' ),
					'key' => 'sales_report_email',
					'include_path' => '/sales-report-email/woocommerce-sales-report-email.php',
				),
			)
		),

		'utilities' => array( # Utilities

			'section_title' => __( 'Utility Modules', 'ultimatewoo-pro' ),
			'section_description' => __( 'A collection of modules that don\'t quite fall into the other categories but are still very awesome!', 'ultimatewoo-pro' ),
			'section_modules' => array(
				array(

					'title' => __( 'Advanced Notifications', 'ultimatewoo-pro' ),
					'key' => 'advanced_notifications',
					'include_path' => '/advanced-notifications/woocommerce-advanced-notifications.php',
				),
				array(

					'title' => __( 'Ajax Layered Navigation', 'ultimatewoo-pro' ),
					'key' => 'ajax_nav',
					'include_path' => '/ajax-layered-nav/ajax_layered_nav-widget.php',
				),
				array(

					'title' => __( 'Amazon S3 Storage', 'ultimatewoo-pro' ),
					'key' => 'amazon_s3',
					'include_path' => '/amazon-s3-storage/woocommerce-amazon-s3-storage.php',
				),
				array(

					'title' => __( 'Bulk Stock Management', 'ultimatewoo-pro' ),
					'key' => 'bulk_stock_management',
					'include_path' => '/bulk-stock-management/woocommerce-bulk-stock-management.php',
				),
				array(

					'title' => __( 'Bulk Variation Forms', 'ultimatewoo-pro' ),
					'key' => 'bulk_variations',
					'include_path' => '/bulk-variations/woocommerce-bulk-variations.php',
				),
				array(

					'title' => __( 'Catalog Visbility Options', 'ultimatewoo-pro' ),
					'key' => 'catalog_visibility_options',
					'include_path' => '/catalog-visibility-options/woocommerce-catalog-visibility-options.php',
				),
				array(

					'title' => __( 'Checkout Field Editor', 'ultimatewoo-pro' ),
					'key' => 'checkout_field_editor',
					'include_path' => '/checkout-field-editor/woocommerce-checkout-field-editor.php',
				),
				array(

					'title' => __( 'Conditional Ship. &amp; Pay.', 'ultimatewoo-pro' ),
					'key' => 'conditional_shipping_payments',
					'include_path' => '/conditional-shipping-payments/woocommerce-conditional-shipping-and-payments.php',
				),
				array(

					'title' => __( 'Currency Converter Widget', 'ultimatewoo-pro' ),
					'key' => 'currency_converter',
					'include_path' => '/currency-converter/currency-converter.php',
				),
				array(

					'title' => __( 'Customer/Order CSV Export', 'ultimatewoo-pro' ),
					'key' => 'customer_order_csv_export',
					'include_path' => '/customer-order-csv-export/woocommerce-customer-order-csv-export.php',
				),
				array(

					'title' => __( 'Customer/Order CSV Import', 'ultimatewoo-pro' ),
					'key' => 'customer_order_csv_import',
					'include_path' => '/customer-order-csv-import/woocommerce-customer-order-csv-import.php',
				),
				array(

					'title' => __( 'Dynamic Pricing', 'ultimatewoo-pro' ),
					'key' => 'dynamic_pricing',
					'include_path' => '/dynamic-pricing/woocommerce-dynamic-pricing.php',
				),
				array(

					'title' => __( 'Google Product Feed', 'ultimatewoo-pro' ),
					'key' => 'product_feeds',
					'include_path' => '/product-feeds/woocommerce-gpf.php',
				),
				array(

					'title' => __( 'Measurement Price Calc.', 'ultimatewoo-pro' ),
					'key' => 'measurement_price_calc',
					'include_path' => '/measurement-price-calculator/woocommerce-measurement-price-calculator.php',
				),
				array(

					'title' => __( 'Min/Max Quantities', 'ultimatewoo-pro' ),
					'key' => 'min_max_quantities',
					'include_path' => '/min-max-quantities/woocommerce-min-max-quantities.php',
				),
				array(

					'title' => __( 'MSRP Pricing', 'ultimatewoo-pro' ),
					'key' => 'msrp',
					'include_path' => '/msrp-pricing/woocommerce-msrp.php',
				),
				array(

					'title' => __( 'Name Your Price', 'ultimatewoo-pro' ),
					'key' => 'name_your_price',
					'include_path' => '/name-your-price/woocommerce-name-your-price.php',
				),
				array(

					'title' => __( 'One Page Checkout', 'ultimatewoo-pro' ),
					'key' => 'one_page_checkout',
					'include_path' => '/one-page-checkout/woocommerce-one-page-checkout.php',
				),
				array(

					'title' => __( 'Order Barcodes', 'ultimatewoo-pro' ),
					'key' => 'order_barcodes',
					'include_path' => '/order-barcodes/woocommerce-order-barcodes.php',
				),
				array(

					'title' => __( 'Order Status Manager', 'ultimatewoo-pro' ),
					'key' => 'order_status_manager',
					'include_path' => '/order-status-manager/woocommerce-order-status-manager.php',
				),
				array(

					'title' => __( 'PDF Catalog Download', 'ultimatewoo-pro' ),
					'key' => 'pdf_catalog',
					'include_path' => '/pdf-catalog/woocommerce-store-catalog-pdf-download.php',
				),
				array(

					'title' => __( 'PDF Invoice', 'ultimatewoo-pro' ),
					'key' => 'pdf_invoice',
					'include_path' => '/pdf-invoice/woocommerce-pdf-invoice.php',
				),
				array(

					'title' => __( 'Product CSV Import', 'ultimatewoo-pro' ),
					'key' => 'product_csv_import',
					'include_path' => '/product-csv-import-suite/woocommerce-product-csv-import-suite.php',
				),
				array(

					'title' => __( 'Product Image Watermark', 'ultimatewoo-pro' ),
					'key' => 'product_image_watermark',
					'include_path' => '/product-image-watermark/woocommerce-watermark.php',
				),
				array(

					'title' => __( 'Social Login', 'ultimatewoo-pro' ),
					'key' => 'social_login',
					'include_path' => '/social-login/woocommerce-social-login.php',
				),
				array(

					'title' => __( 'Sequential Order Numbers', 'ultimatewoo-pro' ),
					'key' => 'seq_order_numbers',
					'include_path' => '/sequential-order-numbers/woocommerce-sequential-order-numbers.php',
				),
				array(

					'title' => __( 'Tab Manager', 'ultimatewoo-pro' ),
					'key' => 'tab_manager',
					'include_path' => '/tab-manager/woocommerce-tab-manager.php',
				),
				array(

					'title' => __( 'Twilio SMS Notifications', 'ultimatewoo-pro' ),
					'key' => 'twilio_sms',
					'include_path' => '/twilio/woocommerce-twilio-sms-notifications.php',
				),
				array(

					'title' => __( 'Warranty &amp; RMA Mgmt.', 'ultimatewoo-pro' ),
					'key' => 'rma_warranty',
					'include_path' => '/warranty/woocommerce-warranty.php',
				),
			)
		),
	);

	return $sections;
}

/**
 *	Get a list of just the modules, without sections
 *
 *	@return array
 */
function ultimatewoo_get_all_modules() {

	$all_modules = array();
	$sections = ultimatewoo_get_module_sections();

	foreach ( $sections as $section ) {
		foreach ( $section['section_modules'] as $section_module ) {
			$all_modules[$section_module['key']] = $section_module['title'];
		}
	}

	return $all_modules;
}
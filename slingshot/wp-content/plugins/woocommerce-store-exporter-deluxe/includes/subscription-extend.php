<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	function woo_ce_extend_subscription_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the Subscription Export Type
		if( $export_type <> 'subscription' )
			return $args;

		$user_count = woo_ce_get_export_type_count( 'user' );
		$list_limit = apply_filters( 'woo_ce_order_filter_subscription_list_limit', 100, $user_count );
		if( $user_count < $list_limit )
			$args['subscription_customer'] = ( isset( $_POST['subscription_filter_customer'] ) ? array_map( 'absint', $_POST['subscription_filter_customer'] ) : false );
		else
			$args['subscription_customer'] = ( isset( $_POST['subscription_filter_customer'] ) ? sanitize_text_field( $_POST['subscription_filter_customer'] ) : false );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_extend_subscription_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

// Adds custom Subscription columns to the Subscription fields list
function woo_ce_extend_subscription_fields( $fields = array() ) {

	if( apply_filters( 'woo_ce_enable_product_attributes', true ) ) {

		// Attributes
		if( $attributes = woo_ce_get_product_attributes() ) {
			foreach( $attributes as $attribute ) {
				$attribute->attribute_label = trim( $attribute->attribute_label );
				if( empty( $attribute->attribute_label ) )
					$attribute->attribute_label = $attribute->attribute_name;
				$fields[] = array(
					'name' => sprintf( 'order_items_attribute_%s', $attribute->attribute_name ),
					'label' => sprintf( __( 'Subscription Items: %s Variation', 'woocommerce-exporter' ), ucwords( $attribute->attribute_label ) ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_subscription_fields_attribute', '%s: %s (#%d)' ), __( 'Product Variation', 'woocommerce-exporter' ), $attribute->attribute_name, $attribute->attribute_id )
				);
			}
			unset( $attributes, $attribute );
		}

	}

	// Gravity Forms - http://woothemes.com/woocommerce
	if( woo_ce_detect_export_plugin( 'gravity_forms' ) && woo_ce_detect_export_plugin( 'woocommerce_gravity_forms' ) ) {
		// Check if there are any Products linked to Gravity Forms
		$gf_fields = woo_ce_get_gravity_forms_fields();
		if( !empty( $gf_fields ) ) {
			$fields[] = array(
				'name' => 'order_items_gf_form_id',
				'label' => __( 'Order Items: Gravity Form ID', 'woocommerce-exporter' ),
				'hover' => __( 'Gravity Forms', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'order_items_gf_form_label',
				'label' => __( 'Order Items: Gravity Form Label', 'woocommerce-exporter' ),
				'hover' => __( 'Gravity Forms', 'woocommerce-exporter' )
			);
			foreach( $gf_fields as $gf_field ) {
				$gf_field_duplicate = false;
				// Check if this isn't a duplicate Gravity Forms field
				foreach( $fields as $field ) {
					if( isset( $field['name'] ) && $field['name'] == sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] ) ) {
						// Duplicate exists
						$gf_field_duplicate = true;
						break;
					}
				}
				// If it's not a duplicate go ahead and add it to the list
				if( $gf_field_duplicate !== true ) {
					$fields[] = array(
						'name' => sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] ),
						'label' => sprintf( apply_filters( 'woo_ce_extend_order_fields_gf_label', __( 'Order Items: %s - %s', 'woocommerce-exporter' ) ), ucwords( strtolower( $gf_field['formTitle'] ) ), ucfirst( strtolower( $gf_field['label'] ) ) ),
						'hover' => sprintf( apply_filters( 'woo_ce_extend_order_fields_gf_hover', '%s: %s (ID: %d)' ), __( 'Gravity Forms', 'woocommerce-exporter' ), ucwords( strtolower( $gf_field['formTitle'] ) ), $gf_field['formId'] )
					);
				}
			}
		}
		unset( $gf_fields, $gf_field );
	}

/*
	// @mod - Commented out as it overrides the Order details. Marked for re-inclusion after re-work in 2.4+
	// WooCommerce User Profile fields
	if( class_exists( 'WC_Admin_Profile' ) ) {
		$admin_profile = new WC_Admin_Profile();
		if( method_exists( 'WC_Admin_Profile', 'get_customer_meta_fields' ) ) {
			$show_fields = $admin_profile->get_customer_meta_fields();
			if( !empty( $show_fields ) ) {
				foreach( $show_fields as $fieldset ) {
					foreach( $fieldset['fields'] as $key => $field ) {
						$fields[] = array(
							'name' => $key,
							'label' => sprintf( apply_filters( 'woo_ce_extend_subscription_fields_wc', '%s: %s' ), $fieldset['title'], esc_html( $field['label'] ) )
						);
					}
				}
			}
			unset( $show_fields, $fieldset, $field );
		}
	}
*/

	// Custom Subscription fields
	$custom_subscriptions = woo_ce_get_option( 'custom_subscriptions', '' );
	if( !empty( $custom_subscriptions ) ) {
		foreach( $custom_subscriptions as $custom_subscription ) {
			if( !empty( $custom_subscription ) ) {
				$fields[] = array(
					'name' => $custom_subscription,
					'label' => woo_ce_clean_export_label( $custom_subscription )
				);
			}
		}
		unset( $custom_orders, $custom_order );
	}

	// Custom Order fields
	$custom_orders = woo_ce_get_option( 'custom_orders', '' );
	if( !empty( $custom_orders ) ) {
		foreach( $custom_orders as $custom_order ) {
			if( !empty( $custom_order ) ) {
				$fields[] = array(
					'name' => $custom_order,
					'label' => woo_ce_clean_export_label( $custom_order )
				);
			}
		}
		unset( $custom_orders, $custom_order );
	}

	// Custom User fields
	$custom_users = woo_ce_get_option( 'custom_users', '' );
	if( !empty( $custom_users ) ) {
		foreach( $custom_users as $custom_user ) {
			if( !empty( $custom_user ) ) {
				$fields[] = array(
					'name' => $custom_user,
					'label' => woo_ce_clean_export_label( $custom_user ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_subscription_fields_custom_user_hover', '%s: %s' ), __( 'Custom User', 'woocommerce-exporter' ), $custom_user )
				);
			}
		}
	}
	unset( $custom_users, $custom_user );

	return $fields;

}
add_filter( 'woo_ce_subscription_fields', 'woo_ce_extend_subscription_fields' );

// Populate Subscription details for export of 3rd party Plugins
function woo_ce_subscription_extend( $subscription, $subscription_id ) {

/*
	// @mod - Commented out as it overrides the Order details. Marked for re-inclusion after re-work in 2.4+
	// WooCommerce User Profile fields
	if( class_exists( 'WC_Admin_Profile' ) ) {
		$admin_profile = new WC_Admin_Profile();
		$show_fields = $admin_profile->get_customer_meta_fields();
		if( !empty( $show_fields ) ) {
			foreach( $show_fields as $fieldset ) {
				foreach( $fieldset['fields'] as $key => $field )
					$subscription->{$key} = esc_attr( get_user_meta( $subscription->user_id, $key, true ) );
			}
		}
		unset( $show_fields, $fieldset, $field );
	}
*/

	// Custom Subscription meta
	$custom_subscriptions = woo_ce_get_option( 'custom_subscriptions', '' );
	if( !empty( $custom_subscriptions ) ) {
		foreach( $custom_subscriptions as $custom_subscription ) {
			if( !empty( $custom_subscription ) ) {
				$subscription->{$custom_subscription} = woo_ce_format_custom_meta( get_post_meta( $subscription_id, $custom_subscription, true ) );
			}
		}
	}

	// Custom Order fields
	$custom_orders = woo_ce_get_option( 'custom_orders', '' );
	if( !empty( $custom_orders ) ) {
		foreach( $custom_orders as $custom_order ) {
			if( !empty( $custom_order ) && !isset( $subscription->{$custom_order} ) )
				$subscription->{$custom_order} = esc_attr( get_post_meta( $subscription->order_id, $custom_order, true ) );
		}
	}

	// Custom User fields
	$custom_users = woo_ce_get_option( 'custom_users', '' );
	if( !empty( $custom_users ) ) {
		foreach( $custom_users as $custom_user ) {
			if( !empty( $custom_user ) && !isset( $subscription->{$custom_user} ) ) {
				$subscription->{$custom_user} = woo_ce_format_custom_meta( get_user_meta( $subscription->user_id, $custom_user, true ) );
			}
		}
	}
	unset( $custom_users, $custom_user );

	return $subscription;

}
add_filter( 'woo_ce_subscription', 'woo_ce_subscription_extend', 10, 2 );
?>
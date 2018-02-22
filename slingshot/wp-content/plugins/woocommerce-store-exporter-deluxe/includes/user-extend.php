<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	// HTML template for Filter Users by User Membership widget on Store Exporter screen
	function woo_ce_users_filter_by_user_membership() {

		// WooCommerce Memberships - http://www.woothemes.com/products/woocommerce-memberships/
		if( woo_ce_detect_export_plugin( 'wc_memberships' ) == false )
			return;

		$membership_plans = woo_ce_get_membership_plans();

		ob_start(); ?>
<p><label><?php _e( 'Filter Users by User Membership', 'woocommerce-exporter' ); ?></label></p>
<div id="export-users-filters-user_membership" class="separator">
	<ul>
		<li>
<?php if( !empty( $membership_plans ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a User Membership...', 'woocommerce-exporter' ); ?>" name="user_filter_user_membership[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $membership_plans as $membership_plan ) { ?>
				<option value="<?php echo $membership_plan; ?>"><?php echo ucfirst( get_the_title( $membership_plan ) ); ?></option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No User Memberships were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the User Memberships you want to filter exported Users by. Default is to include all User Membership options.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-users-filters-user_membership -->
<?php
		ob_end_flush();

	}
	add_action( 'woo_ce_export_user_options_before_table', 'woo_ce_users_filter_by_user_membership' );

	function woo_ce_extend_user_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the User Export Type
		if( $export_type <> 'user' )
			return $args;

		// WooCommerce Memberships - http://www.woothemes.com/products/woocommerce-memberships/
		if( woo_ce_detect_export_plugin( 'wc_memberships' ) ) {
			$args['user_membership'] = ( isset( $_POST['user_filter_user_membership'] ) ? woo_ce_format_user_role_filters( array_map( 'sanitize_text_field', $_POST['user_filter_user_membership'] ) ) : false );
		}

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_extend_user_dataset_args', 10, 2 );

	function woo_ce_extend_get_users_args( $args, $export_args ) {

		// WooCommerce Memberships - http://www.woothemes.com/products/woocommerce-memberships/
		if( woo_ce_detect_export_plugin( 'wc_memberships' ) ) {
			if( !empty( $export_args['user_membership'] ) ) {
				// Get a list of User ID's with the Membership Plans linked to them
				$user_ids = woo_ce_get_user_membership_assoc_users( $export_args['user_membership'] );
				$args['include'] = array();
				if( !empty( $user_ids ) )
					$args['include'] = $user_ids;
			}
		}

		return $args;

	}
	add_filter( 'woo_ce_get_users_args', 'woo_ce_extend_get_users_args', 10, 2 );

	/* End of: WordPress Administration */

}

// Adds custom User columns to the User fields list
function woo_ce_extend_user_fields( $fields = array() ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$fields[] = array(
			'name' => 'blog_id',
			'label' => __( 'Blog ID', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress Multisite', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Hear About Us - https://wordpress.org/plugins/woocommerce-hear-about-us/
	if( woo_ce_detect_export_plugin( 'hear_about_us' ) ) {
		$fields[] = array(
			'name' => 'hear_about_us',
			'label' => __( 'Source', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Hear About Us', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Memberships - http://www.woothemes.com/products/woocommerce-memberships/
	if( woo_ce_detect_export_plugin( 'wc_memberships' ) ) {
		$fields[] = array(
			'name' => 'user_memberships',
			'label' => __( 'User Memberships', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Memberships', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'user_membership_status',
			'label' => __( 'User Membership Status', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Memberships', 'woocommerce-exporter' )
		);
	}

	// WooCommerce User fields
	if( class_exists( 'WC_Admin_Profile' ) ) {
		$admin_profile = new WC_Admin_Profile();
		if( method_exists( 'WC_Admin_Profile', 'get_customer_meta_fields' ) ) {
			$show_fields = $admin_profile->get_customer_meta_fields();
			if( !empty( $show_fields ) ) {
				foreach( $show_fields as $fieldset ) {
					foreach( $fieldset['fields'] as $key => $field ) {
						$fields[] = array(
							'name' => $key,
							'label' => sprintf( apply_filters( 'woo_ce_extend_user_fields_wc', '%s: %s' ), $fieldset['title'], esc_html( $field['label'] ) )
						);
					}
				}
			}
			unset( $show_fields, $fieldset, $field );
		}
	}

	// WC Vendors - http://wcvendors.com
	if( woo_ce_detect_export_plugin( 'wc_vendors' ) ) {
		$fields[] = array(
			'name' => 'shop_name',
			'label' => __( 'Shop Name' ),
			'hover' => __( 'WC Vendors', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'shop_slug',
			'label' => __( 'Shop Slug' ),
			'hover' => __( 'WC Vendors', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'paypal_email',
			'label' => __( 'PayPal E-mail' ),
			'hover' => __( 'WC Vendors', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'commission_rate',
			'label' => __( 'Commission Rate (%)' ),
			'hover' => __( 'WC Vendors', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'seller_info',
			'label' => __( 'Seller Info' ),
			'hover' => __( 'WC Vendors', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'shop_description',
			'label' => __( 'Shop Description' ),
			'hover' => __( 'WC Vendors', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/
	if( woo_ce_detect_export_plugin( 'subscriptions' ) ) {
		$fields[] = array(
			'name' => 'active_subscriber',
			'label' => __( 'Active Subscriber' ),
			'hover' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
	if( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		if( get_option( 'wccf_migrated_to_20' ) ) {
			$custom_fields = woo_ce_get_wccf_user_fields();
			if( !empty( $custom_fields ) ) {
				foreach( $custom_fields as $custom_field ) {
					$label = get_post_meta( $custom_field->ID, 'label', true );
					$key = get_post_meta( $custom_field->ID, 'key', true );
					$fields[] = array(
						'name' => sprintf( 'wccf_uf_%s', sanitize_key( $key ) ),
						'label' => ucfirst( $label ),
						'hover' => __( 'WooCommerce Custom Fields', 'woocommerce-exporter' )
					);
				}
			}
			unset( $custom_fields, $custom_field, $label, $key );
		}
	}

	// Custom User meta
	$custom_users = woo_ce_get_option( 'custom_users', '' );
	if( !empty( $custom_users ) ) {
		foreach( $custom_users as $custom_user ) {
			if( !empty( $custom_user ) ) {
				$fields[] = array(
					'name' => $custom_user,
					'label' => woo_ce_clean_export_label( $custom_user ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_user_fields_custom_user_hover', '%s: %s' ), __( 'Custom User', 'woocommerce-exporter' ), $custom_user )
				);
			}
		}
	}
	unset( $custom_users, $custom_user );

	return $fields;

}
add_filter( 'woo_ce_user_fields', 'woo_ce_extend_user_fields' );

function woo_ce_user_extend( $user ) {

	global $export;

	// WordPress MultiSite
	if( is_multisite() ) {
		$user->blog_id = get_current_blog_id();
	}

	// WooCommerce Hear About Us - https://wordpress.org/plugins/woocommerce-hear-about-us/
	if( woo_ce_detect_export_plugin( 'hear_about_us' ) ) {
		$source = get_user_meta( $user->ID, '_wchau_source', true );
		if( $source == '' )
			$source = __( 'N/A', 'woocommerce-exporter' );
		$user->hear_about_us = $source;
		unset( $source );
	}

	// WooCommerce Memberships - http://www.woothemes.com/products/woocommerce-memberships/
	if( woo_ce_detect_export_plugin( 'wc_memberships' ) ) {
		$user_memberships = woo_ce_get_user_assoc_user_memberships( $user->ID );
		if( !empty( $user_memberships ) ) {
			$user_membership_plans = array();
			$user_membership_status = array();
			foreach( $user_memberships as $user_membership ) {

				// The Post Parent is the Post ID of the Membership Plan
				if( isset( $user_membership->post_parent ) ) {
					$user_membership_plans[] = get_the_title( $user_membership->post_parent );
					$post_status = ( isset( $user_membership->post_status ) ? woo_ce_format_user_membership_status( $user_membership->post_status ) : '-' );
					$user_membership_status[] = sprintf( '%s: %s', get_the_title( $user_membership->post_parent ), $post_status );
				}

			}
			$user->user_memberships = implode( $export->category_separator, $user_membership_plans );
			$user->user_membership_status = implode( $export->category_separator, $user_membership_status );
		}
		unset( $user_memberships, $user_membership_plans, $user_membership_status, $post_status );
	}

	// WooCommerce User Profile fields
	if( class_exists( 'WC_Admin_Profile' ) ) {
		$admin_profile = new WC_Admin_Profile();
		$show_fields = $admin_profile->get_customer_meta_fields();
		if( !empty( $show_fields ) ) {
			foreach( $show_fields as $fieldset ) {
				foreach( $fieldset['fields'] as $key => $field )
					$user->{$key} = esc_attr( get_user_meta( $user->ID, $key, true ) );
			}
		}
		unset( $show_fields, $fieldset, $field );
	}

	// WC Vendors - http://wcvendors.com
	if( woo_ce_detect_export_plugin( 'wc_vendors' ) ) {
		$user->shop_name = get_user_meta( $user->ID, 'pv_shop_name', true );
		$user->shop_slug = get_user_meta( $user->ID, 'pv_shop_slug', true );
		$user->paypal_email = get_user_meta( $user->ID, 'pv_paypal', true );
		$user->commission_rate = get_user_meta( $user->ID, 'pv_custom_commission_rate', true );
		$user->seller_info = get_user_meta( $user->ID, 'pv_seller_info', true );
		$user->shop_description = get_user_meta( $user->ID, 'pv_shop_description', true );
	}

	// WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/
	if( woo_ce_detect_export_plugin( 'subscriptions' ) ) {
		if( function_exists( 'wcs_user_has_subscription' ) )
			$user->active_subscriber = woo_ce_format_switch( wcs_user_has_subscription( $user->ID, '', 'active' ) );
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
	if( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		if( get_option( 'wccf_migrated_to_20' ) ) {
			$custom_fields = woo_ce_get_wccf_user_fields();
			if( !empty( $custom_fields ) ) {
				foreach( $custom_fields as $custom_field ) {
					$key = get_post_meta( $custom_field->ID, 'key', true );
					$user->{sprintf( 'wccf_uf_%s', sanitize_key( $key ) )} = get_user_meta( $user->ID, sprintf( '_wccf_uf_%s', sanitize_key( $key ) ), true );
				}
			}
			unset( $custom_fields, $custom_field, $key );
		}
	}

	// Custom User fields
	$custom_users = woo_ce_get_option( 'custom_users', '' );
	if( !empty( $custom_users ) ) {
		foreach( $custom_users as $custom_user ) {
			// Check that the custom User name is filled and it hasn't previously been set
			if( !empty( $custom_user ) && !isset( $user->{$custom_user} ) ) {
				$user->{$custom_user} = woo_ce_format_custom_meta( get_user_meta( $user->ID, $custom_user, true ) );
			}
		}
	}
	unset( $custom_users, $custom_user );

	return $user;

}
add_filter( 'woo_ce_user', 'woo_ce_user_extend' );

// Return a list of WooCommerce Membership PLans
function woo_ce_get_membership_plans() {

	// WooCommerce Memberships - http://www.woothemes.com/products/woocommerce-memberships/
	$post_type = 'wc_membership_plan';
	$args = array(
		'post_type' => $post_type,
		'post_status' => 'any',
		'fields' => 'ids',
		'posts_per_page' => -1
	);
	$memberships = get_posts( $args );
	if( !empty( $memberships ) ) {
		return $memberships;
	}

}

// Return a list of WooCommerce Membership Plans linked to a specific User
function woo_ce_get_user_assoc_user_memberships( $user_id = 0 ) {

	// WooCommerce Memberships - http://www.woothemes.com/products/woocommerce-memberships/
	if( !empty( $user_id ) ) {
		$post_type = 'wc_user_membership';
		$args = array(
			'author' => $user_id,
			'post_type' => $post_type,
			'post_status' => 'any',
			'posts_per_page' => -1
		);
		$memberships = get_posts( $args );
		// A user can have multiple memberships
		if( !empty( $memberships ) ) {
			return $memberships;
		}
		unset( $memberships );
	}

}

// Return a list of Users linked to the specific WooCommerce Membership Plans
function woo_ce_get_user_membership_assoc_users( $user_memberships = '' ) {

	// WooCommerce Memberships - http://www.woothemes.com/products/woocommerce-memberships/
	if( !empty( $user_memberships ) ) {
		$post_ids = array();
		$post_type = 'wc_user_membership';
		// get_posts() doesn't support multiple Post Parents so we do this the slow way
		foreach( $user_memberships as $user_membership ) {
			// Get a list of Posts linked to each Membership Plan
			$args = array(
				'post_type' => $post_type,
				'post_parent' => $user_membership,
				'post_status' => 'any',
				'fields' => 'ids',
				'posts_per_page' => -1
			);
			$memberships = get_posts( $args );
			if( !empty( $memberships ) )
				$post_ids = array_merge( $post_ids, $memberships );
		}
		if( empty( $post_ids ) ) {
			// Return an empty array
			$user_ids = array( 0 );
		} else {
			// Pass the list of Posts to get the Users
			$args = array(
				'post_type' => $post_type,
				'include' => $post_ids,
				'post_status' => 'any',
				'posts_per_page' => -1
			);
			$memberships = get_posts( $args );
			if( !empty( $memberships ) ) {
				$user_ids = array();
				foreach( $memberships as $membership ) {
					if( isset( $membership->post_author ) )
						$user_ids[] = $membership->post_author;
				}
			}
		}
		return $user_ids;
	}

}

function woo_ce_format_user_membership_status( $post_status = '' ) {

	// WooCommerce Memberships - http://www.woothemes.com/products/woocommerce-memberships/
	$membership_statuses = ( function_exists( 'wc_memberships_get_user_membership_statuses' ) ? wc_memberships_get_user_membership_statuses() : false );

	if( !empty( $membership_statuses ) ) {
		if( isset( $membership_statuses[$post_status] ) )
			$post_status = $membership_statuses[$post_status]['label'];
	}
	return $post_status;

}

function woo_ce_get_wccf_user_fields() {

	$post_type = 'wccf_user_field';
	$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish',
		'posts_per_page' => -1
	);
	$user_fields = new WP_Query( $args );
	if( !empty( $user_fields->posts ) ) {
		return $user_fields->posts;
	}

}
?>
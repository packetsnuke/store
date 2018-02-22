<div id="bookings_resources" class="woocommerce_options_panel panel wc-metaboxes-wrapper">

	<div class="options_group" id="resource_options">

		<?php woocommerce_wp_text_input( array(
			'id'          => '_wc_booking_resource_label',
			'placeholder' => __( 'Type', 'ultimatewoo-pro' ),
			'label'       => __( 'Label', 'ultimatewoo-pro' ),
			'value'       => $bookable_product->get_resource_label( 'edit' ),
			'desc_tip'    => true,
			'description' => __( 'The label shown on the frontend if the resource is customer defined.', 'ultimatewoo-pro' ),
		) ); ?>

		<?php woocommerce_wp_select( array(
			'id'            => '_wc_booking_resources_assignment',
			'label'         => __( 'Resources are...', 'ultimatewoo-pro' ),
			'description'   => '',
			'desc_tip'      => true,
			'value'         => $bookable_product->get_resources_assignment( 'edit' ),
			'options'       => array(
				'customer'  => __( 'Customer selected', 'ultimatewoo-pro' ),
				'automatic' => __( 'Automatically assigned', 'ultimatewoo-pro' ),
			),
			'description'   => __( 'Customer selected resources allow customers to choose one from the booking form.', 'ultimatewoo-pro' ),
		) ); ?>

	</div>

	<div class="options_group">

		<div class="toolbar">
			<h3><?php _e( 'Resources', 'ultimatewoo-pro' ); ?></h3>
			<span class="toolbar_links"><a href="#" class="close_all"><?php _e( 'Close all', 'ultimatewoo-pro' ); ?></a><a href="#" class="expand_all"><?php _e( 'Expand all', 'ultimatewoo-pro' ); ?></a></span>
		</div>

		<div class="woocommerce_bookable_resources wc-metaboxes">

			<div id="message" class="inline woocommerce-message updated" style="margin: 1em 0;">
				<p><?php _e( 'Resources are used if you have multiple bookable items, e.g. room types, instructors or ticket types. Availability for resources is global across all bookable products.', 'ultimatewoo-pro' ); ?></p>
			</div>

			<?php
			global $post, $wpdb;

			$all_resources        = self::get_booking_resources();
			$product_resources    = $bookable_product->get_resource_ids( 'edit' );
			$resource_base_costs  = $bookable_product->get_resource_base_costs( 'edit' );
			$resource_block_costs = $bookable_product->get_resource_block_costs( 'edit' );
			$loop                 = 0;

			if ( $product_resources ) {
				foreach ( $product_resources as $resource_id ) {
					$resource            = new WC_Product_Booking_Resource( $resource_id );
					$resource_base_cost  = isset( $resource_base_costs[ $resource_id ] ) ? $resource_base_costs[ $resource_id ] : '';
					$resource_block_cost = isset( $resource_block_costs[ $resource_id ] ) ? $resource_block_costs[ $resource_id ] : '';

					include( 'html-booking-resource.php' );
					$loop++;
				}
			}
			?>
		</div>

		<p class="toolbar">
			<button type="button" class="button button-primary add_resource"><?php _e( 'Add/link Resource', 'ultimatewoo-pro' ); ?></button>
			<select name="add_resource_id" class="add_resource_id">
				<option value=""><?php _e( 'New resource', 'ultimatewoo-pro' ); ?></option>
				<?php
					if ( $all_resources ) {
						foreach ( $all_resources as $resource ) {
							if ( in_array( $resource->ID, $product_resources ) ){
								continue; // ignore resources that's already on the product
							}
							echo '<option value="' . esc_attr( $resource->ID ) . '">#' . absint( $resource->ID ) . ' - ' . esc_html( $resource->post_title ) . '</option>';
						}
					}
				?>
			</select>
			<a href="<?php echo admin_url( 'edit.php?post_type=bookable_resource' ); ?>" target="_blank"><?php _e( 'Manage Resources', 'ultimatewoo-pro' ); ?></a>
		</p>
	</div>
</div>
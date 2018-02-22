<div id="deposits" class="panel woocommerce_options_panel">
	<div class="options_group">
		<?php

			$inherit_wc_deposit_enabled = $inherit_wc_deposit_type = $inherit_wc_deposit_selected_type = esc_html__( 'Inherit storewide settings', 'ultimatewoo-pro' );

			switch ( get_option( 'wc_deposits_default_type', 'percent' ) ) {
				case 'percent' :
					$inherit_wc_deposit_type .= ' (' . esc_html__( 'percent', 'ultimatewoo-pro' ) . ')';
					break;
				case 'fixed' :
					$inherit_wc_deposit_type .= ' (' . esc_html__( 'fixed amount', 'ultimatewoo-pro' ) . ')';
					break;
				case 'plan' :
					$inherit_wc_deposit_type .= ' (' . esc_html__( 'payment plan', 'ultimatewoo-pro' ) . ')';
					break;
				case 'none' :
					$inherit_wc_deposit_type .= ' (' . esc_html__( 'none', 'ultimatewoo-pro' ) . ')';
					break;
			}
			switch ( get_option( 'wc_deposits_default_enabled', 'no' ) ) {
				case 'optional' :
					$inherit_wc_deposit_enabled .= ' (' . esc_html__( 'yes, optional', 'ultimatewoo-pro' ) . ')';
					break;
				case 'forced' :
					$inherit_wc_deposit_enabled .= ' (' . esc_html__( 'yes, required', 'ultimatewoo-pro' ) . ')';
					break;
				case 'no' :
					$inherit_wc_deposit_enabled .= ' (' . esc_html__( 'no', 'ultimatewoo-pro' ) . ')';
					break;
			}
			switch ( get_option( 'wc_deposits_default_selected_type', 'deposit' ) ) {
				case 'deposit' :
					$inherit_wc_deposit_selected_type .= ' (' . esc_html__( 'pay deposit', 'ultimatewoo-pro' ) . ')';
					break;
				case 'full' :
					$inherit_wc_deposit_selected_type .= ' (' . esc_html__( 'pay in full', 'ultimatewoo-pro' ) . ')';
					break;
			}

			woocommerce_wp_select( array(
				'id'          => '_wc_deposit_enabled',
				'label'       => __( 'Enable Deposits', 'ultimatewoo-pro' ),
				'description' => sprintf( __( 'Allow customers to pay a deposit for this product. <br> <a href="%s" target="_blank">Manage storewide settings</a>', 'ultimatewoo-pro' ), admin_url( 'admin.php?page=wc-settings&tab=products&section=deposits' ) ),
				'options'     => array(
					''         => $inherit_wc_deposit_enabled,
					'optional' => __( 'Yes - deposits are optional', 'ultimatewoo-pro' ),
					'forced'   => __( 'Yes - deposits are required', 'ultimatewoo-pro' ),
					'no'       => __( 'No', 'ultimatewoo-pro' )
				),
				'style'    => 'min-width:50%;',
				'desc_tip' => false,
				'class'    => 'select',
			) );

			woocommerce_wp_select( array(
				'id'          => '_wc_deposit_type',
				'label'       => __( 'Deposit Type', 'ultimatewoo-pro' ),
				'description' => __( 'Choose how customers can pay for this product using a deposit.', 'ultimatewoo-pro' ),
				'options'     => array(
					''        => $inherit_wc_deposit_type,
					'percent' => __( 'Percentage', 'ultimatewoo-pro' ),
					'fixed'   => __( 'Fixed Amount', 'ultimatewoo-pro' ),
					'plan'    => __( 'Payment Plan', 'ultimatewoo-pro' )
				),
				'style'    => 'min-width:50%;',
				'desc_tip' => true,
				'class'    => 'select',
			) );

			woocommerce_wp_checkbox( array(
				'id'            => '_wc_deposit_multiple_cost_by_booking_persons',
				'label'         => __( 'Booking Persons', 'ultimatewoo-pro' ),
				'description'   => __( 'Multiply fixed deposits by the number of persons booking', 'ultimatewoo-pro' ),
				'wrapper_class' => 'show_if_booking',
			) );

			woocommerce_wp_text_input( array(
				'id'          => '_wc_deposit_amount',
				'label'       => __( 'Deposit Amount', 'ultimatewoo-pro' ),
				'placeholder' => wc_format_localized_price( 0 ),
				'description' => __( 'The amount of deposit needed. Do not include currency or percent symbols.', 'ultimatewoo-pro' ),
				'data_type'   => 'price',
				'desc_tip'    => true,
			) );

			woocommerce_wp_select( array(
				'id'          => '_wc_deposit_selected_type',
				'label'       => __( 'Default Deposit Selected Type', 'ultimatewoo-pro' ),
				'description' => __( 'Choose the default selected type of payment on page load.', 'ultimatewoo-pro' ),
				'options'     => array(
					''        => $inherit_wc_deposit_selected_type,
					'deposit' => __( 'Pay Deposit', 'ultimatewoo-pro' ),
					'full'   => __( 'Pay in Full', 'ultimatewoo-pro' ),
				),
				'style'    => 'min-width:50%;',
				'desc_tip' => true,
				'class'    => 'select',
			) );
		?>

		<input type="hidden" class="_wc_deposits_default_enabled_field" value="<?php echo esc_attr( get_option( 'wc_deposits_default_enabled', 'no' ) ); ?>" />
		<input type="hidden" class="_wc_deposits_default_type_field" value="<?php echo esc_attr( get_option( 'wc_deposits_default_type', 'percent' ) ); ?>" />
		<input type="hidden" class="_wc_deposits_default_plans_field" value="<?php echo esc_attr( implode( ',', get_option( 'wc_deposits_default_plans', array() ) ) ); ?>" />
		<input type="hidden" class="_wc_deposits_default_amount_field" value="<?php echo esc_attr( get_option( 'wc_deposits_default_amount' ) ); ?>" />
		<input type="hidden" class="_wc_deposits_default_selected_type_field" value="<?php echo esc_attr( get_option( 'wc_deposits_default_selected_type', 'deposit' ) ); ?>" />

		<p class="form-field _wc_deposit_payment_plans_field">
			<label for="_wc_deposit_payment_plans"><?php _e( 'Payment Plans', 'ultimatewoo-pro' ) ?></label>
			<?php
			$plan_ids = WC_Deposits_Plans_Manager::get_plan_ids();
			$default_payment_plans = get_option( 'wc_deposits_default_plans', array() );
			if ( ! $plan_ids ) {
				echo __( 'You have not created any payment plans yet.', 'ultimatewoo-pro' );
				echo ' <a href="' .  esc_url( admin_url( 'edit.php?post_type=product&page=deposit_payment_plans' ) ) . '" class="button button-small" target="_blank">' . __( 'Create a Payment Plan', 'ultimatewoo-pro' ) . '</a>';
			} else {
				?>
				<select id="_wc_deposit_payment_plans" name="_wc_deposit_payment_plans[]" class="wc-enhanced-select" style="min-width: 50%;" multiple="multiple" placeholder="<?php _e( 'Choose some plans', 'ultimatewoo-pro' ) ?>">
				<?php
					global $post;

					$values = (array) get_post_meta( $post->ID, '_wc_deposit_payment_plans', true );

					foreach ( $plan_ids as $id => $name ) {
						echo '<option value="' . esc_attr( $id ) . '" ' . selected( in_array( $id, $values ), true ) . '>' . esc_attr( $name ) . '</option>';
					}
				?>
				</select> <img class="help_tip" data-tip="<?php _e( 'Choose which payment plans customers can use for this product.', 'ultimatewoo-pro' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16">
				<?php
				if ( ! empty ( $default_payment_plans ) ) {
					$default_payment_plan_string = '';
					foreach ( $default_payment_plans as $plan_id ) {
						$default_payment_plan_string .= $plan_ids[ $plan_id ] . ',';
					}
					$default_payment_plan_string = rtrim( $default_payment_plan_string, ',' );

					/* translators: default payment plan */
					echo '<br /><br />' . sprintf( esc_html__( 'The following plans will be used if no payment plan is selected: %s.', 'ultimatewoo-pro' ), '<em>' . $default_payment_plan_string . '</em>' );
				}
			} ?>
		</p>
	</div>
</div>

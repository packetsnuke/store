<?php
/**
 * WooCommerce Memberships
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Memberships to newer
 * versions in the future. If you wish to customize WooCommerce Memberships for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-memberships/ for more information.
 *
 * @package   WC-Memberships/Templates
 * @author    SkyVerge
 * @copyright Copyright (c) 2014-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Renders a section on My Account page to list customer memberships.
 *
 * @type \WC_Memberships_User_Membership[] $customer_memberships array of user membership objects
 * @type int $user_id the current user ID
 *
 * @version 1.9.0
 * @since 1.0.0
 */
global $post;

?>
<div class="woocommerce-account-my-memberships">

	<?php

	/**
	 * Fires before the Memberships table in My Account page.
	 *
	 * @since 1.4.0
	 */
	do_action( 'wc_memberships_before_my_memberships' );

	?>

	<?php if ( ! empty( $customer_memberships ) ) : ?>

		<table class="shop_table shop_table_responsive my_account_orders my_account_memberships">

			<thead>
				<tr>
					<?php

					/**
					 * Filters the Memberships table columns in My Account page.
					 *
					 * @since 1.4.0
					 *
					 * @param array $my_memberships_columns associative array of column ids and names
					 * @param int $user_id the member ID
					 */
					$my_memberships_columns = apply_filters( 'wc_memberships_my_memberships_column_names', array(
						'membership-plan'       => _x( 'Plan', 'Membership plan', 'ultimatewoo-pro' ),
						'membership-start-date' => _x( 'Start', 'Membership start date', 'ultimatewoo-pro' ),
						'membership-end-date'   => _x( 'Expires', 'Membership end date', 'ultimatewoo-pro' ),
						'membership-status'     => _x( 'Status', 'Membership status', 'ultimatewoo-pro' ),
						'membership-actions'    => '&nbsp;',
					), $user_id );

					?>
					<?php foreach ( $my_memberships_columns as $column_id => $column_name ) : ?>
						<?php

						// TODO remove `wc_memberships_my_memberships_column_headers` deprecated action by version 1.12.0 {FN 2017-06-28}
						if ( 'membership-actions' === $column_id && has_action( 'wc_memberships_my_memberships_column_headers' ) ) {

							_deprecated_function( 'The "wc_memberships_my_memberships_column_headers" action', '1.9.0', '"wc_memberships_my_memberships_column_names" filter' );

							/**
							 * Fires after the membership columns, before the actions column in my memberships table header.
							 *
							 * @since 1.0.0
							 *
							 * @deprecated use 'wc_memberships_my_memberships_column_names' filter hook instead
							 *
							 * @param WC_Memberships_User_Membership $customer_membership
							 */
							do_action( 'wc_memberships_my_memberships_column_headers', $customer_membership );
						}

						?>
						<th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>
				<?php foreach ( $customer_memberships as $customer_membership ) : ?>

					<?php if ( ! $customer_membership->get_plan() ) { continue; } ?>

					<tr class="membership">
						<?php foreach ( $my_memberships_columns as $column_id => $column_name ) : ?>

							<?php if ( 'membership-plan' === $column_id ) : ?>

								<td class="membership-plan" data-title="<?php echo esc_attr( $column_name ); ?>">
									<?php $members_area = $customer_membership->get_plan()->get_members_area_sections(); ?>
									<?php if ( ( ! empty ( $members_area ) && is_array( $members_area ) ) && ( wc_memberships_is_user_active_member( get_current_user_id(), $customer_membership->get_plan() ) || wc_memberships_is_user_delayed_member( get_current_user_id(), $customer_membership->get_plan() ) ) ) : ?>

										<?php $default_section = in_array( 'my-membership-details', $members_area, true ) ? 'my-membership-details' : current( $members_area ); ?>
										<a href="<?php echo esc_url( wc_memberships_get_members_area_url( $customer_membership->get_plan_id(), $default_section ) ); ?>"><?php echo esc_html( $customer_membership->get_plan()->get_name() ); ?></a>

									<?php else : ?>

										<?php echo esc_html( $customer_membership->get_plan()->get_name() ); ?>

									<?php endif;  ?>
								</td>

							<?php elseif ( 'membership-start-date' === $column_id ) : ?>

								<td class="membership-start-date" data-title="<?php echo esc_attr( $column_name ); ?>">
									<?php

									$order           = $customer_membership->get_order();
									$order_datetime  = $order ? SV_WC_Order_Compatibility::get_date_created( $order ) : null;
									$past_start_date = $order && $order_datetime ? ( $customer_membership->get_start_date( 'timestamp' ) < $order_datetime->getTimestamp() ) : false;

									// show the order date instead if the start date is in the past
									if ( $past_start_date && $order && $customer_membership->get_plan()->is_access_length_type( 'fixed' ) ) {
										$start_time = SV_WC_Order_Compatibility::get_date_created( $order )->getTimestamp();
									} else {
										$start_time = $customer_membership->get_local_start_date( 'timestamp' );
									}

									?>
									<?php if ( ! empty( $start_time ) && is_numeric( $start_time ) ) : ?>
										<time datetime="<?php echo date( 'Y-m-d', $start_time ); ?>" title="<?php echo esc_attr( date_i18n( wc_date_format(), $start_time ) ); ?>"><?php echo date_i18n( wc_date_format(), $start_time ); ?></time>
									<?php else : ?>
										<?php esc_html_e( 'N/A', 'ultimatewoo-pro' ); ?>
									<?php endif; ?>
								</td>

							<?php elseif ( 'membership-end-date' === $column_id ) : ?>

								<td class="membership-end-date" data-title="<?php echo esc_attr( $column_name ); ?>">
									<?php if ( $end_time = $customer_membership->get_local_end_date( 'timestamp' ) ) : ?>
										<time datetime="<?php echo date( 'Y-m-d', $end_time ); ?>" title="<?php echo esc_attr( date_i18n( wc_date_format(), $end_time ) ); ?>"><?php echo date_i18n( wc_date_format(), $end_time ); ?></time>
									<?php else : ?>
										<?php esc_html_e( 'N/A', 'ultimatewoo-pro' ); ?>
									<?php endif; ?>
								</td>

							<?php elseif ( 'membership-status' === $column_id ) : ?>

								<td class="membership-status" style="white-space:nowrap;" data-title="<?php echo esc_attr( $column_name ); ?>">
									<?php echo esc_html( wc_memberships_get_user_membership_status_name( $customer_membership->get_status() ) ); ?>
								</td>

							<?php elseif ( 'membership-actions' === $column_id ) :

								// TODO remove `wc_memberships_my_memberships_columns` deprecated action by version 1.12.0 {FN 2017-06-28}
								if ( has_action( 'wc_memberships_my_memberships_columns' ) ) {

									_deprecated_function( 'The "wc_memberships_my_memberships_columns" action', '1.9.0', '"wc_memberships_my_memberships_column_names" filter' );

									/**
									 * Fires after the membership columns, before the actions column in my memberships table.
									 *
									 * @since 1.0.0
									 * @deprecated Use 'wc_memberships_my_memberships_column_names' filter hook and matching id actions
									 *
									 * @param \WC_Memberships_User_Membership $user_membership
									 */
									do_action( 'wc_memberships_my_memberships_columns', $customer_membership );
								}

								?>
								<td class="membership-actions order-actions" data-title="<?php echo esc_attr( $column_name ); ?>">
									<?php

									echo wc_memberships_get_members_area_action_links( 'my-memberships', $customer_membership, $post );

									// ask confirmation before cancelling a membership
									wc_enqueue_js( "
										jQuery( document ).ready( function() {
											$( '.membership-actions' ).on( 'click', '.button.cancel', function( e ) {
												e.stopImmediatePropagation();
												return confirm( '" . esc_html__( 'Are you sure that you want to cancel your membership?', 'ultimatewoo-pro' ) . "' );
											} );
										} );
									" );
									?>
								</td>

							<?php else : ?>

								<td class="<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
									<?php

									/**
									 * Fires when populating a Members Area table column.
									 *
									 * @since 1.4.0
									 *
									 * @param \WC_Memberships_User_Membership $customer_membership the current membership
									 */
									do_action( "wc_memberships_my_memberships_column_{$column_id}", $customer_membership );

									?>
								</td>

							<?php endif; ?>

						<?php endforeach; ?>
					</tr>

				<?php endforeach; ?>
			</tbody>
		</table>

	<?php else : ?>

		<p>
			<?php

			/**
			 * Filters the text for non members in My Account area.
			 *
			 * @since 1.9.0
			 *
			 * @param string $no_memberships_text the text displayed to users without memberships
			 * @param int $user_id the current user
			 */
			echo (string) apply_filters( 'wc_memberships_my_memberships_no_memberships_text', __( "Looks like you don't have a membership yet!", 'ultimatewoo-pro' ), $user_id );

			?>
		</p>

	<?php endif; ?>


	<?php

	/**
	 * Fires after the Memberships table in My Account page.
	 *
	 * @since 1.4.0
	 */
	do_action( 'wc_memberships_after_my_memberships' );

	?>

</div>
<?php

<?php
/**
 * WooCommerce Order Status Manager
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Order Status Manager to newer
 * versions in the future. If you wish to customize WooCommerce Order Status Manager for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-order-status-manager/ for more information.
 *
 * @package     WC-Order-Status-Manager/Admin
 * @author      SkyVerge
 * @copyright   Copyright (c) 2015-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Order Status Manager Emails Admin
 *
 * @since 1.0.0
 */
class WC_Order_Status_Manager_Admin_Order_Status_Emails {


	/** array possible email types **/
	protected $email_types = array();


	/**
	 * Setup admin class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->email_types = array(
			'customer' => __( 'Customer', 'ultimatewoo-pro' ),
			'admin'    => __( 'Admin', 'ultimatewoo-pro' ),
		);

		add_filter( 'views_edit-wc_order_email',  '__return_empty_array' );

		add_filter( 'manage_edit-wc_order_email_columns', array( $this, 'order_status_email_columns' ) );

		add_filter( 'post_row_actions', array( $this, 'order_status_email_actions' ), 10, 2 );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_action( 'wc_order_status_manager_process_wc_order_email_meta', array( $this, 'save_order_status_email_meta' ), 10, 2 );

		add_action( 'manage_wc_order_email_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );

	}


	/**
	 * Customize order status email columns
	 *
	 * @since 1.0.0
	 * @param array $columns
	 * @return array
	 */
	public function order_status_email_columns( $columns ) {

		$columns['type']        = __( 'Type', 'ultimatewoo-pro' );
		$columns['description'] = __( 'Description', 'ultimatewoo-pro' );
		$columns['status']      = __( 'Status', 'ultimatewoo-pro' );

		return $columns;
	}


	/**
	 * Customize order status email row actions
	 *
	 * @since 1.0.0
	 * @param array $actions
	 * @param WP_Post $post
	 * @return array
	 */
	public function order_status_email_actions( $actions, WP_Post $post ) {

		$actions['customize_email'] = sprintf(
			'<a title="%1$s" href="%2$s">%3$s</a>',
			esc_attr__( 'Customize Email', 'ultimatewoo-pro' ),
			esc_url( admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_order_status_email_' . esc_attr( $post->ID ) ) ),
			__( 'Customize Email', 'ultimatewoo-pro' )
		);

		return $actions;
	}


	/**
	 * Add meta boxes to the order status email edit page
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {

		// Order Status data meta box
		add_meta_box(
			'woocommerce-order-status-email-data',
			__( 'Order Status Email Data', 'ultimatewoo-pro' ),
			array( $this, 'order_status_email_data_meta_box' ),
			'wc_order_email',
			'normal',
			'high'
		);

		// Order Status actions meta box
		add_meta_box(
			'woocommerce-order-status-email-actions',
			__( 'Order Status Email Actions', 'ultimatewoo-pro' ),
			array( $this, 'order_status_email_actions_meta_box' ),
			'wc_order_email',
			'side',
			'high'
		);

		remove_meta_box( 'slugdiv', 'wc_order_email', 'normal' );
	}


	/**
	 * Display the order status email data meta box
	 *
	 * @since 1.0.0
	 */
	public function order_status_email_data_meta_box() {
		global $post;

		wp_nonce_field( 'wc_order_status_manager_save_data', 'wc_order_status_manager_meta_nonce' );
		?>

		<div id="order_status_email_options" class="panel woocommerce_options_panel">
			<div class="options_group">

			<?php
			// Status Email Name
			woocommerce_wp_text_input( array(
				'id'    => 'post_title',
				'label' => __( 'Name', 'ultimatewoo-pro' ),
				'value' => $post->post_title,
			) );

			// Status Email Type
			woocommerce_wp_select( array(
				'id'          => '_email_type',
				'label'       => __( 'Type', 'ultimatewoo-pro' ),
				'options'     => $this->email_types,
				'desc_tip'    => true,
				'description' => __( "A customer email is dispatched to the order's customer, and admin email is sent to the store admin (you can define individual recipient's).", 'ultimatewoo-pro' ),
			) );

			// Status Email Description
			woocommerce_wp_textarea_input( array(
				'id'          => 'post_excerpt',
				'label'       => __( 'Description', 'ultimatewoo-pro' ),
				'desc_tip'    => true,
				'description' => __( 'Optional email description. This is for informational purposes only.', 'ultimatewoo-pro' ),
				'value'       => htmlspecialchars_decode( $post->post_excerpt, ENT_QUOTES ),
			) );

			// Status Email Dispatch conditions
			// TODO: Should we prefix 'any' with an underscore, or somehow reserve it?
			$status_options = array(
				'any' => __( 'Any', 'ultimatewoo-pro' )
			);

			foreach ( wc_get_order_statuses() as $slug => $name ) {
				$status_options[ str_replace( 'wc-', '', $slug ) ] = $name;
			}

			$conditions = get_post_meta( $post->ID, '_email_dispatch_condition' );

			// Parse existing condition parts
			if ( ! empty( $conditions ) ) {

				foreach ( $conditions as $key => $condition ) {
					$parts = explode( '_to_', $condition );
					$conditions[ $key ] = array(
						'from' => $parts[0],
						'to'   => $parts[1],
					);
				}
			}

			?>
			<fieldset class="form-field dispatch_field">
				<label for="_email_dispatch_condition"><?php esc_html_e( 'When to dispatch', 'ultimatewoo-pro' ); ?></label>

				<table class="dispatch_conditions">

					<thead <?php if ( empty( $conditions ) ) : ?>style="display:none;"<?php endif; ?>>
						<tr>
							<th><?php esc_html_e( 'From Status', 'ultimatewoo-pro' ); ?></th>
							<th colspan="2"><?php esc_html_e( 'To Status', 'ultimatewoo-pro' ); ?></th>
						</tr>
					</thead>

					<tbody <?php if ( empty( $conditions ) ) : ?>style="display:none;"<?php endif; ?>>

						<?php if ( ! empty( $conditions ) ) : ?>

							<?php foreach ( $conditions as $key => $condition ) : ?>

								<tr class="condition">
									<td>
										<select name="_email_dispatch_condition[<?php echo $key; ?>][from]">
											<?php foreach ( $status_options as $slug => $name ) : ?>
												<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $condition['from'] ); ?>><?php echo esc_html( $name ); ?></option>
											<?php endforeach; ?>
										</select>
									</td>
									<td>
										<select name="_email_dispatch_condition[<?php echo $key; ?>][to]">
											<?php foreach ( $status_options as $slug => $name ) : ?>
												<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $condition['to'] ); ?>><?php echo esc_html( $name ); ?></option>
											<?php endforeach; ?>
										</select>
									</td>
									<td>
										<button type="button"
										        class="button remove-condition">
											<?php esc_html_e( 'Remove', 'ultimatewoo-pro' ); ?>
										</button>
									</td>
								</tr>

							<?php endforeach; ?>

						<?php endif; ?>

					</tbody>

					<tfoot>
						<tr>
							<td colspan="3">
								<button type="button"
								        class="button add-condition">
									<?php esc_html_e( 'Add Condition', 'ultimatewoo-pro' ); ?>
								</button>
							</td>
						</tr>
					</tfoot>

				</table>

			</fieldset>

			</div><!-- // .options_group -->
		</div><!-- // .woocommerce_options_panel -->
		<?php
	}


	/**
	 * Display the order status email actions meta box
	 *
	 * @since 1.0.0
	 */
	public function order_status_email_actions_meta_box() {
		global $post, $pagenow;

		?>
		<ul class="order_status_email_actions submitbox">

			<?php
				/**
				 * Fires at the start of the order status email actions meta box
				 *
				 * @since 1.0.0
				 * @param int $post_id The post id of the wc_order_email post
				 */
				do_action( 'wc_order_status_manager_order_status_email_actions_start', $post->ID );
			?>

			<?php if ( 'post-new.php' !== $pagenow ) : ?>
				<li class="wide"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_order_status_email_' . esc_attr( $post->ID ) ) ); ?>"><?php esc_html_e( 'Customize Email', 'ultimatewoo-pro' ); ?></a></li>
			<?php endif; ?>

			<li class="wide">
				<div id="delete-action">
					<?php if ( current_user_can( "delete_post", $post->ID ) ) : ?>
						<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID, '', true ); ?>"><?php esc_html_e( 'Delete Permanently', 'ultimatewoo-pro' ); ?></a>
					<?php endif; ?>
				</div>

				<input type="submit"
				       name="publish"
				       class="button save_order_status_email save_action button-primary tips"
				       value="<?php esc_attr_e( 'Save Email', 'ultimatewoo-pro' ); ?>"
				       data-tip="<?php esc_attr_e( 'Save/update the order status email', 'ultimatewoo-pro' ); ?>" />
			</li>

			<?php
				/**
				 * Fires at the end of the order status email actions meta box
				 *
				 * @since 1.0.0
				 * @param int $post_id The post id of the wc_order_email post
				 */
				do_action( 'wc_order_status_manager_order_status_email_actions_end', $post->ID );
			?>

		</ul>
		<?php
	}


	/**
	 * Process and save order status email meta
	 *
	 * @since 1.0.0
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public function save_order_status_email_meta( $post_id, WP_Post $post ) {

		update_post_meta( $post_id, '_email_type',  $_POST['_email_type'] );

		// Remove any previously saved dispatch conditions
		delete_post_meta( $post_id, '_email_dispatch_condition' );

		// Add in new dispatch conditions
		if ( ! empty( $_POST['_email_dispatch_condition'] ) ) {

			foreach ( $_POST['_email_dispatch_condition'] as $condition ) {
				add_post_meta( $post_id, '_email_dispatch_condition', $condition['from'] . '_to_' . $condition['to'] );
			}
		}
	}


	/**
	 * Output custom column content
	 *
	 * @since 1.0.0
	 * @param string $column
	 * @param int $post_id
	 */
	public function custom_column_content( $column, $post_id ) {
		global $post;

		switch ( $column ) {

			case 'type':

				if ( $type = get_post_meta( $post_id, '_email_type', true ) ) {
					echo isset( $this->email_types[ $type ] ) ? $this->email_types[ $type ] : '';
				}

			break;

			case 'description':
				echo isset( $post->post_excerpt ) ? $post->post_excerpt : '';
			break;

			case 'status':

				$settings            = get_option( "woocommerce_wc_order_status_email_{$post_id}_settings" );
				$dispatch_conditions = get_post_meta( $post_id, '_email_dispatch_condition' );

				$url = admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_order_status_email_' . esc_attr( $post_id ) );

				if ( ! $dispatch_conditions ) {

					$status = __( 'Inactive', 'ultimatewoo-pro' );
					$tip    = __( 'No dispatch rules set for this email.', 'ultimatewoo-pro' );
					$url    = get_edit_post_link( $post_id );

				} else if ( isset( $settings['enabled'] ) && $settings['enabled'] === 'yes' ) {

					$status = __( 'Enabled', 'ultimatewoo-pro' );
					$tip    = __( 'This email is enabled.', 'ultimatewoo-pro' );

				} else {

					$status = __( 'Disabled', 'ultimatewoo-pro' );
					$tip    = __( 'This email is disabled.', 'ultimatewoo-pro' );
				}

				printf( '<a href="%1$s" class="tips badge %2$s" data-tip="%3$s">%4$s</a>',
					esc_url( $url ),
					sanitize_title( $status ),
					$tip,
					$status
				);

			break;

		}
	}


}

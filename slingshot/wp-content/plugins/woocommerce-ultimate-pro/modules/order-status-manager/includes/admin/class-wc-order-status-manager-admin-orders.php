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
 * Order Status Manager Orders Admin
 *
 * @since 1.0.0
 */
class WC_Order_Status_Manager_Admin_Orders {


	/**
	 * Setup admin class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// add order status "next" actions
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'custom_order_actions' ), 10, 2 );

		// handle custom order statuses icons
		add_action( 'admin_head', array( $this, 'custom_order_status_icons' ) );

		// add custom bulk actions and replace core labels with custom labels
		add_action( 'admin_footer-edit.php', array( $this, 'bulk_admin_footer' ), 1 );
	}


	/**
	 * Add custom order actions in order list view
	 *
	 * @since 1.0.0
	 * @param array $actions
	 * @param \WC_Order $order
	 * @return array
	 */
	public function custom_order_actions( $actions, WC_Order $order ) {

		$status = new WC_Order_Status_Manager_Order_Status( $order->get_status() );

		// sanity check: bail if status is not found
		// this can happen if some statuses are registered late
		if ( ! $status || ! $status->get_id() ) {
			return $actions;
		}

		$custom_actions = array();
		$next_statuses  = $status->get_next_statuses();

		if ( ! empty( $next_statuses ) ) {

			$order_statuses = wc_get_order_statuses();

			// add next statuses as actions
			foreach ( $next_statuses as $next_status ) {

				$custom_actions[ $next_status ] = array(
					'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=' . $next_status . '&order_id=' . SV_WC_Order_Compatibility::get_prop( $order, 'id' ) ), 'woocommerce-mark-order-status' ),
					'name'   => $order_statuses[ 'wc-' . $next_status ],
					'action' => $next_status,
				);
			}
		}

		return array_merge( $custom_actions, $this->trim_order_actions( $actions ) );
	}


	/**
	 * Remove Order Status Manager actions from Order actions
	 *
	 * @see WC_Order_Status_Manager_Admin_Orders::custom_order_actions()
	 *
	 * @since 1.4.3
	 * @param array $order_actions
	 * @return array
	 */
	private function trim_order_actions( $order_actions ) {

		if ( $order_statuses = wc_order_status_manager()->get_order_statuses_instance()->get_order_status_posts() ) {

			foreach ( $order_statuses as $post ) {

				if ( $status = new WC_Order_Status_Manager_Order_Status( $post ) ) {

					$slug = $status->get_slug();

					if ( isset( $order_statuses[ $slug ] ) ) {
						unset( $order_actions[ $slug ] );
					} elseif ( 'completed' === $slug ) {
						unset( $order_actions['complete'] );
					}
				}
			}
		}

		return $order_actions;
	}


	/**
	 * Print styles for custom order status icons
	 *
	 * @since 1.0.0
	 */
	public function custom_order_status_icons() {

		$custom_status_colors = array();
		$custom_status_badges = array();
		$custom_status_icons  = array();
		$custom_action_icons  = array();

		foreach ( wc_get_order_statuses() as $slug => $name ) {

			$status = new WC_Order_Status_Manager_Order_Status( $slug );

			// sanity check: bail if no status was found
			// this can happen if some statuses are registered late
			if ( ! $status || ! $status->get_id() ) {
				continue;
			}

			$color       = $status->get_color();
			$icon        = $status->get_icon();
			$action_icon = $status->get_action_icon();
			$slug        = (string) esc_attr( $status->get_slug() );

			if ( $color ) {
				$custom_status_colors[ $slug ] = $color;
			}

			// Font icon
			if ( $icon && $icon_details = wc_order_status_manager()->get_icons_instance()->get_icon_details( $icon ) ) {
				$custom_status_icons[ $slug ] = $icon_details;
			}

			// Image icon
			elseif ( is_numeric( $icon ) && $icon_src = wp_get_attachment_image_src( $icon, 'wc_order_status_icon' ) ) {
				$custom_status_icons[ $slug ] = $icon_src[0];
			}

			// Badge
			elseif ( ! $icon ) {
				$custom_status_badges[] = $slug;
			}

			// Font action icon
			if ( $action_icon && $action_icon_details = wc_order_status_manager()->get_icons_instance()->get_icon_details( $action_icon ) ) {
				$custom_action_icons[ $slug ] = $action_icon_details;
			}

			// Image action icon
			elseif ( is_numeric( $action_icon ) && $action_icon_src = wp_get_attachment_image_src( $action_icon, 'wc_order_status_icon' ) ) {
				$custom_action_icons[ $slug ] = $action_icon_src[0];
			}

		}

		?>
		<!-- Custom Order Status Icon styles -->
		<style type="text/css">
			/*<![CDATA[*/

			<?php // General styles for status badges ?>
			<?php if ( ! empty( $custom_status_badges ) ) : ?>
				.widefat .column-order_status mark.<?php echo implode( ', .widefat .column-order_status mark.', $custom_status_badges ); ?> {
					display: inline-block;
					font-size: 0.8em;
					line-height: 1.1;
					text-indent: 0;
					background-color: #666;
					width: auto;
					height: auto;
					padding: 0.4em;
					color: #fff;
					border-radius: 2px;
					word-wrap: break-word;
					max-width: 100%;
				}

				.widefat .column-order_status mark.<?php echo implode( ':after, .widefat .column-order_status mark.', $custom_status_badges ); ?>:after {
					display: none;
				}
			<?php endif; ?>

			<?php // General styles for status icons ?>
			<?php if ( ! empty( $custom_status_icons ) ) : ?>

				<?php $custom_status_font_icons = array_filter( $custom_status_icons, 'is_array' ); ?>

				<?php if ( ! empty( $custom_status_font_icons ) ) : ?>

					.widefat .column-order_status mark.<?php echo implode( ':after, .widefat .column-order_status mark.', array_keys( $custom_status_font_icons ) ); ?>:after {
						speak: none;
						font-weight: normal;
						font-variant: normal;
						text-transform: none;
						line-height: 1;
						-webkit-font-smoothing: antialiased;
						margin: 0;
						text-indent: 0;
						position: absolute;
						top: 0;
						left: 0;
						width: 100%;
						height: 100%;
						text-align: center;
					}

				<?php endif; ?>
			<?php endif; ?>

			<?php // General styles for action icons ?>
			.widefat .column-order_actions a.button {
				padding: 0 0.5em;
				height: 2em;
				line-height: 1.9em;
			}

			<?php if ( ! empty( $custom_action_icons ) ) : ?>

				<?php $custom_action_font_icons = array_filter( $custom_action_icons, 'is_array' ); ?>
				<?php if ( ! empty( $custom_action_font_icons ) ) : ?>

					.order_actions .<?php echo implode( ', .order_actions .', array_keys( $custom_action_icons ) ); ?> {
						display: block;
						text-indent: -9999px;
						position: relative;
						padding: 0!important;
						height: 2em!important;
						width: 2em;
					}
					.order_actions .<?php echo implode( ':after, .order_actions .', array_keys( $custom_action_icons ) ); ?>:after {
						speak: none;
						font-weight: 400;
						font-variant: normal;
						text-transform: none;
						-webkit-font-smoothing: antialiased;
						margin: 0;
						text-indent: 0;
						position: absolute;
						top: 0;
						left: 0;
						width: 100%;
						height: 100%;
						text-align: center;
						line-height: 1.85;
					}

				<?php endif; ?>
			<?php endif; ?>

			<?php // Specific status icon styles ?>
			<?php if ( ! empty( $custom_status_icons ) ) : ?>
				<?php foreach ( $custom_status_icons as $status => $value ) : ?>

					<?php if ( is_array( $value ) ) : ?>
						.widefat .column-order_status mark.<?php echo $status; ?>:after {
							font-family: "<?php echo $value['font']; ?>";
							content:     "<?php echo $value['glyph']; ?>";
						}
					<?php else : ?>
						.widefat .column-order_status mark.<?php echo $status; ?> {
							background-size: 100% 100%;
							background-image: url( <?php echo $value; ?> );
						}
					<?php endif; ?>

				<?php endforeach; ?>
			<?php endif; ?>

			<?php // Specific status color styles ?>
			<?php if ( ! empty( $custom_status_colors ) ) : ?>
				<?php foreach ( $custom_status_colors as $status => $color ) : ?>

					<?php if ( in_array( $status, $custom_status_badges, true ) ) : ?>
						.widefat .column-order_status mark.<?php echo $status; ?> {
							background-color: <?php echo $color; ?>;
							color: <?php echo wc_order_status_manager()->get_icons_instance()->get_contrast_text_color( $color ); ?>;
						}
					<?php endif; ?>

					<?php if ( isset( $custom_status_icons[ $status ] ) ) : ?>
						.widefat .column-order_status mark.<?php echo $status; ?>:after {
							color: <?php echo $color; ?>;
						}
					<?php endif; ?>

				<?php endforeach; ?>
			<?php endif; ?>

			<?php // Specific  action icon styles ?>
			<?php if ( ! empty( $custom_action_icons ) ) : ?>
				<?php foreach ( $custom_action_icons as $status => $value ) : ?>

					<?php if ( is_array( $value ) ) : ?>
						.order_actions .<?php echo $status; ?>:after {
							font-family: "<?php echo $value['font']; ?>";
							content:     "<?php echo $value['glyph']; ?>";
						}
					<?php else : ?>
						.order_actions .<?php echo $status; ?>,
						.order_actions .<?php echo $status; ?>:focus,
						.order_actions .<?php echo $status; ?>:hover {
							background-size: 69% 69%;
							background-position: center center;
							background-repeat: no-repeat;
							background-image: url( <?php echo $value; ?> );
						}
					<?php endif; ?>

				<?php endforeach; ?>
			<?php endif; ?>

			/*]]>*/
		</style>
		<?php
	}


	/**
	 * Add extra bulk action options to mark orders with custom statuses
	 *
	 * Using Javascript until WordPress core fixes: http://core.trac.wordpress.org/ticket/16031
	 *
	 * @since 1.0.0
	 */
	public function bulk_admin_footer() {
		global $post_type;

		if ( 'shop_order' === $post_type ) :

			// get statuses
			$custom_order_statuses = wc_order_status_manager()->get_order_statuses_instance()->get_order_status_posts( array(
				'suppress_filters' => false,
			) );

			// sanity check
			if ( ! $custom_order_statuses ) {
				return;
			}

			?>
			<script type="text/javascript">
				jQuery( document ).ready( function( $ ) {

					var $dropdownTop, $dropdownBottom, $filterPostsList,
					    $breadcrumb, $count, label, $optionTop, $optionBottom;

					$dropdownTop     = $( 'select[name="action"]' );
					$dropdownBottom  = $( 'select[name="action2"]' );
					$filterPostsList = $( 'div.wrap > ul.subsubsub' );

					<?php foreach ( $custom_order_statuses as $custom_order_status ) :

						$status  = new WC_Order_Status_Manager_Order_Status( $custom_order_status );
						$slug    = $status->get_slug();
						$name    = $status->get_name();

						// replace status filter labels for core statuses ?>
						<?php if ( $status->is_core_status() && SV_WC_Plugin_Compatibility::is_wc_version_lt_2_6() ) : ?>

							$breadcrumb = $filterPostsList.find( 'li[class="wc-<?php echo sanitize_html_class( $slug ); ?>"] > a' );
							$count      = $( '.count', $breadcrumb );

							if ( null != $breadcrumb ) {
								$breadcrumb.text( '<?php echo esc_html( $name ); ?> ' );
								$breadcrumb.append( $count );
							}

						<?php endif; ?>

						<?php // bulk actions ?>
						$optionTop    = $dropdownTop.find( 'option[value="mark_<?php echo sanitize_html_class( $slug ); ?>"]' );
						$optionBottom = $dropdownBottom.find( 'option[value="mark_<?php echo sanitize_html_class( $slug ); ?>"]' );

						<?php // remove all status bulk actions - they will be re-added below as needed ?>
						$optionTop.remove();
						$optionBottom.remove();

						<?php if ( $status->is_bulk_action() ) :

							/* translators: Placeholder: %s - order status name */ ?>
							label = '<?php printf( __( 'Mark %s', 'ultimatewoo-pro' ), esc_html( strtolower( $name ) ) ); ?>';

							<?php // append statuses actions marked to be included in bulk actions ?>
							$( '<option>' ).val( 'mark_<?php echo sanitize_html_class( $slug ); ?>' ).text( label ).appendTo( $dropdownTop );
							$( '<option>' ).val( 'mark_<?php echo sanitize_html_class( $slug ); ?>' ).text( label ).appendTo( $dropdownBottom );

						<?php endif; ?>

					<?php endforeach; ?>

				} );
			</script>
			<?php

		endif;
	}


}

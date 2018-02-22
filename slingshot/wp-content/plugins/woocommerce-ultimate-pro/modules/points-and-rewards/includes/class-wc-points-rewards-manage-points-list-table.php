<?php
/**
 * WooCommerce Points and Rewards
 *
 * @package     WC-Points-Rewards/List-Table
 * @author      WooThemes
 * @copyright   Copyright (c) 2013, WooThemes
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

/**
 * Points and Rewards Manage Points List Table class
 *
 * Extends WP_List_Table to display customer reward points
 *
 * @since 1.0
 * @extends \WP_List_Table
 */
class WC_Points_Rewards_Manage_Points_List_Table extends WP_List_Table {


	/**
	 * Setup list table
	 *
	 * @see WP_List_Table::__construct()
	 * @since 1.0
	 * @return \WC_Points_Rewards_Manage_Points_List_Table
	 */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'Point', 'ultimatewoo-pro' ),
				'plural'   => __( 'Points', 'ultimatewoo-pro' ),
				'ajax'     => false,
				'screen'   => 'woocommerce_page_wc_points_rewards_manage_points',
			)
		);
	}

	/**
	 * Gets the bulk action available for user points: update
	 *
	 * @see WP_List_Table::get_bulk_actions()
	 * @since 1.0
	 * @return array associative array of action_slug => action_title
	 */
	public function get_bulk_actions() {

		$actions = array(
			'update' => __( 'Update', 'ultimatewoo-pro' ),
		);

		return $actions;
	}

	/**
	 * Returns the column slugs and titles
	 *
	 * @see WP_List_Table::get_columns()
	 * @since 1.0
	 * @return array of column slug => title
	 */
	public function get_columns() {

		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'customer' => __( 'Customer', 'ultimatewoo-pro' ),
			'points'   => __( 'Points', 'ultimatewoo-pro' ),
			'update'   => __( 'Update', 'ultimatewoo-pro' ),
		);

		return $columns;
	}

	/**
	 * Returns the sortable columns and initial direction
	 *
	 * @see WP_List_Table::get_sortable_columns()
	 * @since 1.0
	 * @return array of sortable column slug => array( 'orderby', boolean )
	 *         where true indicates the initial sort is descending
	 */
	public function get_sortable_columns() {

		// really the only thing that makes sense to sort is the points column
		return array(
			'points' => array( 'points', false ),  // false because the inital sort direction is DESC so we want the first column click to sort ASC
		);
	}

	/**
	 * Get content for the special checkbox column
	 *
	 * @see WP_List_Table::single_row_columns()
	 * @since 1.0
	 * @param object $row one row (item) in the table
	 * @return string the checkbox column content
	 */
	public function column_cb( $row ) {
		return '<input type="checkbox" name="user_id[]" value="' . $row->ID . '" />';
	}

	/**
	 * Get column content, this is called once per column, per row item ($user_points)
	 * returns the content to be rendered within that cell.
	 *
	 * @see WP_List_Table::single_row_columns()
	 * @since 1.0
	 * @param object $user_points one row (item) in the table
	 * @param string $column_name the column slug
	 * @return string the column content
	 */
	public function column_default( $user_points, $column_name ) {
		$points_balance = 0;
		
		// todo: we need to rethink the logic of getting/saving points
		// for now this is a temporary work around to allow zero points
		// users to have their points updated
		if ( null === $user_points->points_balance ) {
			// get user id from email
			$user = get_user_by( 'email', $user_points->user_email );

			$user_points->ID = $user->ID;
		}

		if ( isset( $user_points->points_balance ) ) {
			$points_balance = intval( $user_points->points_balance );
		}

		switch ( $column_name ) {
			case 'customer':
				$customer_email = $user_points->user_email;
				$column_content = sprintf( '<a href="%s">%s</a>', get_edit_user_link( $user_points->ID ), $customer_email );
			break;

			case 'points':
				$column_content = $points_balance;
			break;

			case 'update':
				$column_content = '<input type="text" class="points_balance" name="points_balance[' . esc_attr( $user_points->ID ) . ']" value="' . $points_balance . '" />' .
					' <a class="button update_points" href="' . wp_nonce_url( remove_query_arg( 'points_balance', add_query_arg( array( 'action' => 'update', 'user_id' => $user_points->ID ) ) ), 'wc_points_rewards_update' ) . '">' . esc_html__( 'Update', 'ultimatewoo-pro' ) . '</a>';
			break;

			default:
				$column_content = '';
			break;
		}

		return $column_content;
	}

	/**
	 * Get the current action selected from the bulk actions dropdown, verifying
	 * that it's a valid action to perform
	 *
	 * @see WP_List_Table::current_action()
	 * @since 1.0
	 * @return string|bool The action name or False if no action was selected
	 */
	public function current_action() {

		$current_action = parent::current_action();

		if ( $current_action && ! array_key_exists( $current_action, $this->get_bulk_actions() ) ) return false;

		return $current_action;
	}

	/**
	 * Handle actions for both individual items and bulk update
	 *
	 * @since 1.0
	 */
	public function process_actions() {
		global $wc_points_rewards;

		// get the current action (if any)
		$action = $this->current_action();

		// get the set of users to operate on
		$user_ids = isset( $_REQUEST['user_id'] ) ? array_map( 'absint', (array) $_REQUEST['user_id'] ): array();

		// no action, or invalid action
		if ( false === $action || empty( $user_ids ) || ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wc_points_rewards_update' ) && ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-points' ) ) ) {
			return;
		}

		$success_count = $error_count = 0;

		// process the users
		foreach ( $user_ids as $user_id ) {

			// perform the action
			switch ( $action ) {
				case 'update':
					if ( WC_Points_Rewards_Manager::set_points_balance( $user_id, $_REQUEST['points_balance'][ $user_id ], 'admin-adjustment' ) ) {
						$success_count++;
					} else {
						$error_count++;
					}
				break;
			}
		}

		// build the result message(s)
		switch ( $action ) {
			case 'update':
				if ( $success_count > 0 ) {
					$wc_points_rewards->admin_message_handler->add_message( sprintf( _n( '%d customer updated.', '%s customers updated.', $success_count, 'ultimatewoo-pro' ), $success_count ) );
				}
				if ( $error_count > 0 ) {
					$wc_points_rewards->admin_message_handler->add_message( sprintf( _n( '%d customer could not be updated.', '%s customers could not be updated.', $error_count, 'ultimatewoo-pro' ), $error_count ) );
				}
			break;
		}
	}

	/**
	 * Output any messages from the bulk action handling
	 *
	 * @since 1.0
	 */
	public function render_messages() {
		global $wc_points_rewards;

		if ( $wc_points_rewards->admin_message_handler->message_count() > 0 ) {
			echo '<div id="moderated" class="updated"><ul><li><strong>' . implode( '</strong></li><li><strong>', $wc_points_rewards->admin_message_handler->get_messages() ) . '</strong></li></ul></div>';
		}
	}

	/**
	 * Gets the current orderby, defaulting to 'user_id' if none is selected
	 *
	 * @since 1.0
	 */
	private function get_current_orderby() {

		$orderby = ( isset( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'points';

		// order by points or default of user ID
		switch ( $orderby ) {
			case 'points': return 'points';
			default: return 'ID';
		}
	}

	/**
	 * Gets the current orderby, defaulting to 'DESC' if none is selected
	 *
	 * @since 1.0
	 */
	private function get_current_order() {
		return isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
	}

	/**
	 * Generates queries to get our list table items.
	 */
	private function get_items() {
		global $wpdb;

		$per_page = $this->get_items_per_page( 'wc_points_rewards_manage_points_customers_per_page' );
		$offset =  ( $this->get_pagenum() - 1 ) * $per_page;
		switch ( $this->get_current_order() ) {
			case 'asc':
				$order = 'ASC';
				break;
			default:
				$order = 'DESC';
				break;
		}

		// Do we need to filter by customer?
		$where = '';

		if ( isset( $_GET['_customer_user'] ) && $_GET['_customer_user'] > 0 ) {
			$where = $wpdb->prepare( "AND users.ID = %d", $_GET['_customer_user'] );
		}

		// Build a query we can use for count and results
		$query = "FROM {$wpdb->users} as users LEFT JOIN {$wpdb->prefix}wc_points_rewards_user_points as points_table ON users.ID = points_table.user_id WHERE 1=1 {$where} GROUP BY users.ID ORDER BY (points_table.points_balance+0) {$order}";

		return array(
			'count'   => $wpdb->get_var( "SELECT COUNT( DISTINCT users.ID ) as found_user_points FROM {$wpdb->users} as users LEFT JOIN {$wpdb->prefix}wc_points_rewards_user_points as points_table ON users.ID = points_table.user_id WHERE 1=1 {$where}" ),
			'results' => $wpdb->get_results( $wpdb->prepare( "SELECT points_table.user_id as ID, SUM( points_table.points_balance ) as points_balance, users.user_email {$query} LIMIT %d, %d", $offset, $per_page ) )
		);
	}

	/**
	 * Prepare the list of user points items for display
	 *
	 * @see WP_List_Table::prepare_items()
	 * @since 1.0
	 */
	public function prepare_items() {
		global $wpdb;

		$this->process_actions();
		$per_page = $this->get_items_per_page( 'wc_points_rewards_manage_points_customers_per_page' );

		$items       = $this->get_items();
		$this->items = $items['results'];
		$count       = $items['count'];

		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);
	}

	/**
	 * Adds in any query arguments based on the current filters
	 *
	 * @since 1.0
	 * @param array $args associative array of WP_Query arguments used to query and populate the list table
	 * @return array associative array of WP_Query arguments used to query and populate the list table
	 */
	private function add_filter_args( $args ) {
		global $wpdb;

		// filter by customer
		if ( isset( $_GET['_customer_user'] ) && $_GET['_customer_user'] > 0 ) {
		$args['include'] = array( $_GET['_customer_user'] );
		}

		return $args;
	}

	/**
	 * The text to display when there are no user pointss
	 *
	 * @see WP_List_Table::no_items()
	 * @since 1.0
	 */
	public function no_items() {
		if ( isset( $_REQUEST['s'] ) ) : ?>
			<p><?php _e( 'No user points found', 'ultimatewoo-pro' ); ?></p>
		<?php else : ?>
			<p><?php _e( 'User points will appear here for you to view and manage once you have customers.', 'ultimatewoo-pro' ); ?></p>
		<?php endif;
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination, which
	 * includes our Filters: Customers, Products, Availability Dates
	 *
	 * @see WP_List_Table::extra_tablenav();
	 * @since 1.0
	 * @param string $which the placement, one of 'top' or 'bottom'
	 */
	public function extra_tablenav( $which ) {
		if ( 'top' == $which ) {
			echo '<div class="alignleft actions">';

			// Customers, products
			$user_string = '';
			$customer_id = '';
			if ( ! empty( $_GET['_customer_user'] ) ) {
				$customer_id = absint( $_GET['_customer_user'] );
				$user        = get_user_by( 'id', $customer_id );
				$user_string = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email );
			}
			if ( version_compare( WC_VERSION, '3.0.0', '>=' ) ) { ?>
				<select id="customer_user" style="width: 200px;" class="wc-customer-search" name="_customer_user" data-placeholder="<?php esc_attr_e( 'Show All Customers', 'ultimatewoo-pro' ); ?>">

					<?php
						if ( ! empty( $customer_id ) ) {
							echo '<option value="' . esc_attr( $customer_id ) . '">' . wp_kses_post( $user_string ) . '</option>';						
						}
					?>
				</select>
			<?php } else { ?>
				<input type="hidden" class="wc-customer-search" id="customer_user" name="_customer_user" data-placeholder="<?php _e( 'Show All Customers', 'ultimatewoo-pro' ); ?>" data-selected="<?php echo esc_attr( $user_string ); ?>" value="<?php echo $customer_id; ?>" data-allow_clear="true" style="width:200px" />
			<?php }

			submit_button( __( 'Filter', 'ultimatewoo-pro' ), 'button', false, false, array( 'id' => 'post-query-submit' ) );
			echo '</div>';

			// javascript
			wc_enqueue_js( "
				// submit the single-row Update action
				$( 'a.update_points' ).click( function() {
					var \$el = $( this );
					\$el.attr( 'href', \$el.attr( 'href' ) + '&' + \$el.prev().attr('name') + '=' + \$el.prev().val() );
				} );

				// when the focus is on one of the 'points balance' inputs, and the form is submitted, assume we're updating only that one record
				$( 'form#mainform' ).submit( function() {
					var \$focused = $( ':focus' );

					if ( \$focused && \$focused.hasClass( 'points_balance' ) ) {
						location.href = \$focused.next().attr( 'href' ) + '&' + \$focused.attr('name') + '=' + \$focused.val();
						return false;
					}

					return true;
				} );
			" );
		}
	}


} // end \WC_Pre_Orders_List_Table class

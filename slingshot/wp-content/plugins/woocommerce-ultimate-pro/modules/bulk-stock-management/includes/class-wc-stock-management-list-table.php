<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * WC_Stock_Management_List_Table class.
 *
 * @extends WP_List_Table
 */
class WC_Stock_Management_List_Table extends WP_List_Table {

	/** @var integer Index of product being output */
	private $index = 0;

	/** @var int Product ID being output */
	private $last_product_id;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'product',     //singular name of the listed records
			'plural'   => 'products',    //plural name of the listed records
			'ajax'     => false,         //does this table support ajax?
		) );
	}

	/**
	 * Output column data
	 * @param  object $product
	 * @param  string $column_name
	 * @return string
	 */
	public function column_default( $product, $column_name ) {
		switch ( $column_name ) {
			case 'thumb' :
				return $product->get_image();
			break;
			case 'title' :
				$title     = $product->get_title();
				$bwc = version_compare( WC_VERSION, '3.0', '<' );

				if ( $product->is_type( 'variation' ) ) {
					$attributes = $product->get_variation_attributes();
					$extra_data = implode( ', ', $attributes ) . ' &ndash; ' . wc_price( $product->get_price() );

					if ( $this->last_product_id !== $product->get_id() ) {
						$title = $title . ' &mdash; ' . $extra_data;
					} else {
						$title = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&mdash; ' . $extra_data;
					}

					$parent_id = $bwc ? $product->id : $product->get_parent_id();
				} else {
					$parent_id = $bwc ? $product->id : $product->get_id();
				}

				$edit_link = admin_url( 'post.php?post=' . $parent_id . '&action=edit' );

				return '<a href="' . esc_url( $edit_link ) . '">' . esc_html( strip_tags( $title ) ) . '</a>';
			break;
			case 'id' :
				if ( ! $product->is_type( 'variation' ) ) {
					$this->last_product_id = $product->get_id();
				}
				return $product->get_id();
			break;
			case 'sku':
				return $product->get_sku() ? $product->get_sku() : '<span class="na">&ndash;</span>';
			break;
			case 'manage_stock' :
				if ( ! $product->is_type( 'variation' ) && $product->managing_stock() ) {
					return '<mark class="yes">' . __( 'Parent', 'ultimatewoo-pro' ) . '</mark>';
				} else {
					return ( $product->managing_stock() ) ? '<mark class="yes">' . __( 'Yes', 'ultimatewoo-pro' ) . '</mark>' : '<span class="na">&ndash;</span>';
				}
			break;
			case 'stock' :
				$this->index++;
				?>
				<input type="text" class="input-text wc_bulk_stock_quantity_value" tabindex="<?php echo $this->index; ?>" data-name="stock_quantity[<?php echo $product->get_id(); ?>]" placeholder="<?php
				if ( $product->managing_stock() ) {
					echo wc_stock_amount( $product->get_stock_quantity() );
				} else {
					_e( 'N/A', 'ultimatewoo-pro' );
				}
				?>" />

				<input type="hidden" class="input-text" data-name="current_stock_quantity[<?php echo $product->get_id(); ?>]" value="<?php if ( ! $product->is_type( 'variation' ) || $product->managing_stock() ) { echo $product->get_stock_quantity(); } ?>" />
				<?php

			break;
			case 'stock_status' :
				return ( $product->is_in_stock() ) ? '<mark class="instock">' . __( 'In stock', 'ultimatewoo-pro' ) . '</mark>' : '<mark class="outofstock">' . __( 'Out of stock', 'ultimatewoo-pro' ) . '</mark>';
			break;
			case 'backorders' :
				if ( $product->backorders_allowed() && $product->backorders_require_notification() ) {
					echo '<mark class="yes">' . __( 'Notify', 'ultimatewoo-pro' ) . '</mark>';
				} elseif ( $product->backorders_allowed() ) {
					echo '<mark class="yes">' . __( 'Yes', 'ultimatewoo-pro' ) . '</mark>';
				} else {
					echo '<span class="na">&ndash;</span>';
				}
			break;
		} // End switch().
	}

	/**
	 * Checkbox column
	 */
	public function column_cb( $item ) {
		$id = $item->get_id();

		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$id
		);
	}

	/**
	 * Get columns
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 */
	public function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'thumb'        => __( 'Image', 'ultimatewoo-pro' ),
			'id'           => __( 'ID', 'ultimatewoo-pro' ),
			'title'        => __( 'Name', 'ultimatewoo-pro' ),
			'sku'          => __( 'SKU', 'ultimatewoo-pro' ),
			'manage_stock' => __( 'Manage Stock', 'ultimatewoo-pro' ),
			'stock_status' => __( 'Stock Status', 'ultimatewoo-pro' ),
			'backorders'   => __( 'Backorders', 'ultimatewoo-pro' ),
			'stock'        => __( 'Quantity', 'ultimatewoo-pro' ),
		);

		if ( ! wc_product_sku_enabled() ) {
			unset( $columns['sku'] );
		}

		return $columns;
	}

	/**
	 * If you want one or more columns to be sortable (ASC/DESC toggle),
	 * you will need to register it here. This should return an array where the
	 * key is the column that needs to be sortable, and the value is db column to
	 * sort by. Often, the key and value will be the same, but this is not always
	 * the case (as the value is a column name from the database, not the list table).
	 *
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting. You still need to detect
	 * the ORDERBY and ORDER querystring variables within prepare_items() and sort
	 * your data accordingly (usually by modifying your query).
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'title' => array( 'title', true ),
			'id'    => array( 'ID', false ),
			'sku'   => array( 'sku', false ),
			'stock' => array( 'stock', false ),
		);
		return $sortable_columns;
	}

	 /**
	 * Get bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'save'                    => __( 'Save stock quantities', 'ultimatewoo-pro' ),
			'manage_stock'            => __( 'Selected: Turn on stock management', 'ultimatewoo-pro' ),
			'do_not_manage_stock'     => __( 'Selected: Turn off stock management', 'ultimatewoo-pro' ),
			'in_stock'                => __( 'Selected: Mark "In stock"', 'ultimatewoo-pro' ),
			'out_of_stock'            => __( 'Selected: Mark "Out of stock"', 'ultimatewoo-pro' ),
			'allow_backorders'        => __( 'Selected: Allow backorders', 'ultimatewoo-pro' ),
			'allow_backorders_notify' => __( 'Selected: Allow backorders, but notify customer', 'ultimatewoo-pro' ),
			'do_not_allow_backorders' => __( 'Selected: Do not allow backorders', 'ultimatewoo-pro' ),
		);
		return $actions;
	}

	/**
	 * Display the bulk actions dropdown.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param string $which The location of the bulk actions: 'top' or 'bottom'.
	 *                      This is designated as optional for backwards-compatibility.
	 */
	protected function bulk_actions( $which = '' ) {
		if ( is_null( $this->_actions ) ) {
			$this->_actions = $this->get_bulk_actions();
			$two = '';
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) ) {
			return;
		}

		echo "<label for='bulk-action-selector-" . esc_attr( $which ) . "' class='screen-reader-text'>" . __( 'Select bulk action' ) . '</label>';
		echo "<select name='action$two' id='bulk-action-selector-" . esc_attr( $which ) . "'>\n";
		echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions' ) . "</option>\n";

		foreach ( $this->_actions as $name => $title ) {
			echo "\t<option value='$name'>$title</option>\n";
		}

		echo "</select>\n";

		submit_button( __( 'Apply' ), 'primary', '', false, array( 'id' => "doaction$two" ) );
		echo "\n";
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	public function display_tablenav( $which ) {
		include_once( WC()->plugin_path() . '/includes/walkers/class-product-cat-dropdown-walker.php' );

		if ( 'top' == $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
			?>

			<ul class="subsubsub">
				<li class="all"><a href="<?php echo admin_url( 'edit.php?post_type=product&page=woocommerce-bulk-stock-management' ) ?>" class="<?php if ( empty( $_REQUEST['filter_product_type'] ) ) { echo 'current'; } ?>"><?php _e( 'All', 'ultimatewoo-pro' ); ?></a> |</li>
				<li class="product"><a href="<?php echo admin_url( 'edit.php?post_type=product&page=woocommerce-bulk-stock-management&filter_product_type=product' ) ?>" class="<?php if ( ! empty( $_REQUEST['filter_product_type'] ) && 'product' == $_REQUEST['filter_product_type'] ) { echo 'current'; } ?>"><?php _e( 'Products', 'ultimatewoo-pro' ); ?></a> |</li>
				<li class="variation"><a href="<?php echo admin_url( 'edit.php?post_type=product&page=woocommerce-bulk-stock-management&filter_product_type=product_variation' ) ?>" class="<?php if ( ! empty( $_REQUEST['filter_product_type'] ) && 'product_variation' == $_REQUEST['filter_product_type'] ) { echo 'current'; } ?>"><?php _e( 'Variations', 'ultimatewoo-pro' ); ?></a></li>
			</ul>

			<?php $this->search_box( __( 'Search', 'ultimatewoo-pro' ), 'search-products' );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php if ( 'top' == $which ) : ?>
				<div class="alignleft actions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
				<div class="alignleft actions">
					<input type="hidden" name="filter_product_type" value="<?php if ( ! empty( $_REQUEST['filter_product_type'] ) ) { echo $_REQUEST['filter_product_type']; } ?>" />
					<select name="filter_manage_stock">
						<option value=""><?php _e( 'All Products', 'ultimatewoo-pro' ); ?></option>
						<option value="yes" <?php if ( ! empty( $_REQUEST['filter_manage_stock'] ) && 'yes' == $_REQUEST['filter_manage_stock'] ) { selected( 1 ); } ?>><?php _e( 'Managing stock', 'ultimatewoo-pro' ); ?></option>
						<option value="no" <?php if ( ! empty( $_REQUEST['filter_manage_stock'] ) && 'no' == $_REQUEST['filter_manage_stock'] ) { selected( 1 ); } ?>><?php _e( 'Not managing stock', 'ultimatewoo-pro' ); ?></option>
					</select>
					<select name="filter_stock_status">
						<option value=""><?php _e( 'Any stock status', 'ultimatewoo-pro' ); ?></option>
						<option value="instock" <?php if ( ! empty( $_REQUEST['filter_stock_status'] ) && 'instock' == $_REQUEST['filter_stock_status'] ) { selected( 1 ); } ?>><?php _e( 'In stock', 'ultimatewoo-pro' ); ?></option>
						<option value="outofstock" <?php if ( ! empty( $_REQUEST['filter_stock_status'] ) && 'outofstock' == $_REQUEST['filter_stock_status'] ) { selected( 1 ); } ?>><?php _e( 'Out of stock', 'ultimatewoo-pro' ); ?></option>
					</select>
					<?php
						global $wp_query;

						$r               = array();
						$r['pad_counts'] = 0;
						$r['hierarchal'] = 1;
						$r['hide_empty'] = 1;
						$r['show_count'] = 0;
						$r['selected']   = ( isset( $_REQUEST['filter_product_cat'] ) ) ? $_REQUEST['filter_product_cat'] : '';

						$terms = get_terms( 'product_cat', $r );

					if ( $terms ) {
							?>
							<select name='filter_product_cat' id='dropdown_product_cat'>
								<option value=""><?php _e( 'Any category', 'ultimatewoo-pro' ); ?></option>
						<?php
						echo wc_walk_category_dropdown_tree( $terms, 0, $r );

						echo '<option value="0" ' . selected( isset( $_REQUEST['filter_product_cat'] ) ? $_REQUEST['filter_product_cat'] : '', '0', false ) . '>' . __( 'Uncategorized', 'ultimatewoo-pro' ) . '</option>';
						?>
							</select>
							<?php
					}
					?>
					<input type="hidden" name="paged" value="<?php echo absint( ! empty( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1 ); ?>" />
					<input type="submit" name="filter" value="<?php _e( 'Filter', 'ultimatewoo-pro' ); ?>" class="button" />
				</div>
			<?php else : ?>
				<div class="alignleft actions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
				<?php $this->extra_tablenav( $which ); ?>
			<?php endif; ?>
			<?php $this->pagination( 'bottom' ); ?>
			<br class="clear" />
		</div>
		<?php
	}

	/**
	 * Get the page number
	 * @return int
	 */
	public function get_pagenum() {
		return absint( ! empty( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1 );
	}

	/**
	 * Defines the hidden columns
	 *
	 * @access public
	 * @since 2.0.2
	 * @version 2.0.2
	 * @return array $columns
	 */
	public function get_hidden_columns() {
		// get user hidden columns
		$hidden = get_hidden_columns( $this->screen );

		$new_hidden = array();

		foreach ( $hidden as $k => $v ) {
			if ( ! empty( $v ) ) {
				$new_hidden[] = $v;
			}
		}

		return $new_hidden;
	}

	/**
	 * Get items to display
	 */
	public function prepare_items() {
		global $wpdb;

		$current_page = $this->get_pagenum();
		$post_type    = ! empty( $_REQUEST['filter_product_type'] ) ? wc_clean( $_REQUEST['filter_product_type'] ) : '';
		$orderby      = ! empty( $_REQUEST['orderby'] ) ? wc_clean( $_REQUEST['orderby'] ) : 'ID';
		$order        = ! empty( $_REQUEST['order'] ) ? strtoupper( wc_clean( $_REQUEST['order'] ) ) : 'ASC';
		$stock_status = ! empty( $_REQUEST['filter_stock_status'] ) ? wc_clean( $_REQUEST['filter_stock_status'] ) : '';
		$stock_status = 'instock' !== $stock_status && 'outofstock' !== $stock_status ? '' : $stock_status;
		$product_cat  = isset( $_REQUEST['filter_product_cat'] ) ? wc_clean( $_REQUEST['filter_product_cat'] ) : '';
		$per_page     = $this->get_items_per_page( 'wc_bulk_stock_products_per_page', apply_filters( 'wc_bulk_stock_default_items_per_page', 50 ) );

		/**
		 * Init column headers
		 */
		$this->_column_headers = array( $this->get_columns(), $this->get_hidden_columns(), $this->get_sortable_columns() );

		/**
		 * Prepare ordering args
		 */
		switch ( $orderby ) {
			case 'sku' :
				$meta_key 	= '_sku';
				$orderby 	= 'meta_value';
			break;
			case 'stock' :
				$meta_key = '_stock';
				$orderby 	= 'meta_value_num';
			break;
			default :
				$meta_key = '';
			break;
		}

		$tax_query = array();

		if ( $product_cat ) {
			$tax_query[] = array(
				'taxonomy'	=> 'product_cat',
				'field'		=> 'slug',
				'terms'	 	=> array( $product_cat ),
			);
		} elseif ( '0' === $product_cat ) {
			$tax_query[] = array(
				'taxonomy'	=> 'product_cat',
				'field'		=> 'id',
				'terms' 	=> get_terms( 'product_cat', array( 'fields' => 'ids' ) ),
				'operator' 	=> 'NOT IN',
			);
		}

		$meta_query = array();

		if ( ! empty( $_REQUEST['filter_manage_stock'] ) ) {
			$meta_query[] = array(
				'key'	=> '_manage_stock',
				'value'	=> ( 'yes' == $_REQUEST['filter_manage_stock'] ) ? 'yes' : 'no',
			);
		}

		if ( $stock_status ) {
			$meta_query[] = array(
				'key'	=> '_stock_status',
				'value'	=> $stock_status,
			);
		}

		if ( $post_type ) {
			$post_types = 'product' === $post_type ? 'product' : 'product_variation';

		} else {
			$post_types = array( 'product', 'product_variation' );
		}

		$products = new WP_Query( array(
			'post_type'      => $post_types,
			'posts_per_page' => $per_page,
			'offset'         => ( $current_page - 1 ) * $per_page,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'meta_query'     => $meta_query,
			'tax_query'      => 'product' === $post_types ? $tax_query : array(),
			'meta_key'       => $meta_key,
			's'              => ( ! empty( $_REQUEST['s'] ) ) ? wc_clean( $_REQUEST['s'] ) : '',
			'orderby'        => array( $orderby => $order ),
		) );

		// Set the pagination
		$this->set_pagination_args( array(
			'total_items' => $products->found_posts,
			'per_page'    => $per_page,
			'total_pages' => $products->max_num_pages,
		) );

		if ( $products->posts ) {
			foreach ( $products->posts as $id ) {
				$product = wc_get_product( $id );

				if ( ! $product ) {
					continue;
				}

				if ( ! isset( $this->items[ $id ] ) ) {

					if ( 'product_variation' === $post_types && ! empty( $product_cat ) ) {

						// get the variation's parent product id
						$parent_id = wp_get_post_parent_id( $id );

						// get the terms of the parent
						$parent_terms = wp_get_post_terms( $parent_id, 'product_cat', array( 'fields' => 'slugs' ) );

						// if variation's parent does not have term, skip
						if ( empty( $parent_terms ) || ! in_array( $product_cat, $parent_terms ) ) {

							continue;
						}
					}

					$this->items[ $id ] = $product;

					if ( ! $post_type && $product->is_type( 'variable' ) ) {
						$variations = get_posts( array(
							'post_type'      => 'product_variation',
							'posts_per_page' => -1,
							'post_status'    => 'publish',
							'orderby'        => array( 'menu_order' => 'ASC', 'ID' => 'DESC' ),
							'fields'         => 'ids',
							'meta_query'     => $meta_query,
							'meta_key'       => $meta_key,
							's'              => ( ! empty( $_REQUEST['s'] ) ) ? wc_clean( $_REQUEST['s'] ) : '',
							'post_parent'    => $id,
						) );

						foreach ( $variations as $variation_id ) {
							$variation = wc_get_product( $variation_id );
							if ( $variation ) {
								$this->items[ $variation_id ] = $variation;
							}
						}
					}
				} // End if().
			} // End foreach().

			// if variation only filter, we need to recount items per page
			if ( 'product_variation' === $post_types && ! empty( $product_cat ) ) {

				// Set the pagination
				$this->set_pagination_args( array(
					'total_items' => count( $this->items ),
					'per_page'    => $per_page,
					'total_pages' => count( $this->items ) / $per_page,
				) );
			}
		} // End if().
	}
}

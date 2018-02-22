<?php
if ( !class_exists( 'tp_megamenu' ) ) {

	/**
	 * The tp megamenu class contains various methods necessary to create mega menus out of the admin backend
	 * @package    tpFramework
	 */
	class tp_megamenu {

		/**
		 * tp_megamenu constructor
		 * The constructor uses wordpress hooks and filters provided and
		 * replaces the default menu with custom functions and classes within this file
		 * @package    tpFramework
		 */
		function wp_enqueue_media() {
			if ( is_admin() ) {
				wp_enqueue_media();
			}
		}

		function __construct() {
			//adds stylesheet and javascript to the menu page
			add_action( 'admin_menu', array( &$this, 'tp_menu_header' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'wp_enqueue_media' ) );


			//exchange arguments and tell menu to use the tp walker for front end rendering
			add_filter( 'wp_nav_menu_args', array( &$this, 'modify_arguments' ), 100 );

			//exchange argument for backend menu walker
			add_filter( 'wp_edit_nav_menu_walker', array( &$this, 'modify_backend_walker' ), 100 );

			//save tp options:
			add_action( 'wp_update_nav_menu_item', array( &$this, 'update_menu' ), 100, 3 );
		}

		/**
		 * If we are on the nav menu page add javascript and css for the page
		 */
		function tp_menu_header() {
			if ( basename( $_SERVER['PHP_SELF'] ) == "nav-menus.php" ) {
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_style( 'tp-mega-menu-backend', TP_FRAMEWORK_LIBS_URI . 'megamenu/css/backend.css' );
				wp_enqueue_style( 'tp-mega-menu-frontend', TP_FRAMEWORK_LIBS_URI . 'megamenu/css/tp-mega-menu.css' );
				wp_enqueue_style( 'tp-font-awesome', TP_THEME_FRAMEWORK_URI . 'css/font-awesome.min.css' );
				wp_enqueue_script( 'tp-mega-menu-js', TP_FRAMEWORK_LIBS_URI . 'megamenu/js/tp-mega-menu.js', array(
					'jquery',
					'jquery-ui-sortable'
				), false, true );
				wp_enqueue_script( 'wp-color-picker' );
			}
			add_thickbox();
		}

		/**
		 * Replaces the default arguments for the front end menu creation with new ones
		 */
		function modify_arguments( $arguments ) {

			$arguments['walker']          = new tp_walker();
			$arguments['container_class'] = $arguments['container_class'] .= ' megaWrapper';
			return $arguments;
		}

		/**
		 * Tells wordpress to use our backend walker instead of the default one
		 */
		function modify_backend_walker( $name ) {
			return 'tp_backend_walker';
		}

		/*
		 * Save and Update the Custom Navigation Menu Item Properties by checking all $_POST vars with the name of $check
		 * @param int $menu_id
		 * @param int $menu_item_db
		 */

		function update_menu( $menu_id, $menu_item_db ) {
			$check = apply_filters( 'avf_mega_menu_post_meta_fields', array(
				'megamenu',
				'item-style',
				'menu-icon',
				'bg-color',
				'bg-image',
				'hide-text',
				'disable-link',
				'full-width-dropdown',
				'submenu-columns',
				'side-dropdown-elements',
				'submenu-type',
				'widget-area',
				'bg-image-repeat',
				'bg-image-attachment',
				'bg-image-position',
				'bg-image-size'
			), $menu_id, $menu_item_db );

			foreach ( $check as $key ) {
				if ( !isset( $_POST['menu-item-tp-' . $key][$menu_item_db] ) ) {
					$_POST['menu-item-tp-' . $key][$menu_item_db] = "";
				}

				$value = $_POST['menu-item-tp-' . $key][$menu_item_db];
				update_post_meta( $menu_item_db, '_menu-item-tp-' . $key, $value );
			}
		}

	}

}


if ( !class_exists( 'tp_walker' ) ) {

	/**
	 * The tp walker is the frontend walker and necessary to display the menu, this is a advanced version of the wordpress menu walker
	 * @package WordPress
	 * @since   1.0.0
	 * @uses    Walker
	 */
	class tp_walker extends Walker {

		/**
		 * @see Walker::$tree_type
		 * @var string
		 */
		var $tree_type = array( 'post_type', 'taxonomy', 'custom' );

		/**
		 * @see  Walker::$db_fields
		 * @todo Decouple this.
		 * @var array
		 */
		var $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );

		/**
		 * @var int $columns
		 */
		var $columns = 0;

		/**
		 * @var int $max_columns maximum number of columns within one mega menu
		 */
		var $max_columns = 0;

		/**
		 * @var int $rows holds the number of rows within the mega menu
		 */
		var $rows = 1;

		/**
		 * @var array $rowsCounter holds the number of columns for each row within a multidimensional array
		 */
		var $rowsCounter = array();

		/**
		 * @var string $mega_active hold information whetever we are currently rendering a mega menu or not
		 */
		var $mega_active = 0;

		/**
		 * Traverse elements to create list from elements.
		 *
		 * Display one element if the element doesn't have any children otherwise,
		 * display the element and its children. Will only traverse up to the max
		 * depth and no ignore elements under that depth. It is possible to set the
		 * max depth to include all depths, see walk() method.
		 *
		 * This method should not be called directly, use the walk() method instead.
		 *
		 * @since 2.5.0
		 *
		 * @param object $element           Data object.
		 * @param array  $children_elements List of elements to continue traversing.
		 * @param int    $max_depth         Max depth to traverse.
		 * @param int    $depth             Depth of current element.
		 * @param array  $args              An array of arguments.
		 * @param string $output            Passed by reference. Used to append additional content.
		 *
		 * @return null Null on failure with no changes to parameters.
		 */
		function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {

			if ( !$element ) {
				return;
			}

			$id_field = $this->db_fields['id'];

			//display this element
			if ( isset( $args[0] ) && is_array( $args[0] ) ) {
				$args[0]['has_children'] = !empty( $children_elements[$element->$id_field] );
			}
			$cb_args = array_merge( array( &$output, $element, $depth ), $args );
			call_user_func_array( array( $this, 'start_el' ), $cb_args );

			$id = $element->$id_field;

			// descend only when the depth is right and there are childrens for this element
			if ( ( $max_depth == 0 || $max_depth > $depth + 1 ) && isset( $children_elements[$id] ) ) {
				$b          = $args[0];
				$b->element = $element;
				$args[0]    = $b;
				foreach ( $children_elements[$id] as $child ) {

					if ( !isset( $newlevel ) ) {
						$newlevel = true;
						//start the child delimiter
						$cb_args = array_merge( array( &$output, $depth ), $args );
						call_user_func_array( array( $this, 'start_lvl' ), $cb_args );
					}
					$this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
				}
				unset( $children_elements[$id] );
			}

			if ( isset( $newlevel ) && $newlevel ) {
				//end the child delimiter
				$cb_args = array_merge( array( &$output, $depth ), $args );
				call_user_func_array( array( $this, 'end_lvl' ), $cb_args );
			}

			//end this element
			$cb_args = array_merge( array( &$output, $element, $depth ), $args );
			call_user_func_array( array( $this, 'end_el' ), $cb_args );
		}

		/**
		 * @see Walker::start_lvl()
		 *
		 * @param string $output Passed by reference. Used to append additional content.
		 * @param int    $depth  Depth of page. Used for padding.
		 */
		function start_lvl( &$output, $depth = 0, $args = array() ) {
			$item_id         = isset( $args->element->ID ) ? $args->element->ID : '';
			$bg_image        = get_post_meta( $item_id, '_menu-item-tp-bg-image', true );
			$bg_color        = get_post_meta( $item_id, '_menu-item-tp-bg-color', true );
			$submenu_columns = get_post_meta( $item_id, '_menu-item-tp-submenu-columns', true );
			$dropdown_stype  = get_post_meta( $item_id, '_menu-item-tp-submenu-type', true );

			$style = $style_inline = '';

			if ( $bg_color ) {
				$style_inline .= 'background-color:' . $bg_color . ';';
			}

			if ( $bg_image ) {
				$bg_image_repeat     = get_post_meta( $item_id, '_menu-item-tp-bg-image-repeat', true );
				$bg_image_attachment = get_post_meta( $item_id, '_menu-item-tp-bg-image-attachment', true );
				$bg_image_position   = get_post_meta( $item_id, '_menu-item-tp-bg-image-position', true );
				$bg_image_size       = get_post_meta( $item_id, '_menu-item-tp-bg-image-size', true );

				$style_inline .= 'background-image:url(' . $bg_image . ');background-repeat:' . $bg_image_repeat . ';background-attachment:' . $bg_image_attachment . ';background-position:' . esc_attr( $bg_image_position ) . ';background-size:' . $bg_image_size . ';';
			}
			if ( $bg_color || $bg_image ) {
				$style = 'style="' . $style_inline . '"';
			}
			$class = '';
			if ( $dropdown_stype != "standard" ) {
				if ( $submenu_columns ) {
					$class .= ' megacol submenu_columns_' . $submenu_columns;
				}
			}


			$indent = str_repeat( "\t", $depth );

			$output .= "\n$indent<ul class=\"sub-menu$class\" " . $style . ">\n";
		}

		/**
		 * @see Walker::end_lvl()
		 *
		 * @param string $output Passed by reference. Used to append additional content.
		 * @param int    $depth  Depth of page. Used for padding.
		 */
		function end_lvl( &$output, $depth = 0, $args = array() ) {
			$indent = str_repeat( "\t", $depth );
			$output .= "$indent</ul>\n";
		}

		/**
		 * @see Walker::start_el()
		 *
		 * @param string $output       Passed by reference. Used to append additional content.
		 * @param object $item         Menu item data object.
		 * @param int    $depth        Depth of menu item. Used for padding.
		 * @param int    $current_page Menu item ID.
		 * @param object $args
		 */
		function start_el( &$output, $item, $depth = 0, $args = array(), $current_object_id = 0 ) {
			global $wp_query;

			//set maxcolumns
			$hide_text    = get_post_meta( $item->ID, '_menu-item-tp-hide-text', true );
			$disable_link = get_post_meta( $item->ID, '_menu-item-tp-disable-link', true );
			$item_output  = $li_text_block_class = $column_class = "";

			$attributes = !empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
			$attributes .= !empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
			$attributes .= !empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
			$attributes .= !empty( $item->url ) ? ' href="' . esc_attr( $item->url ) . '"' : '';

			$menu_icon   = get_post_meta( $item->ID, '_menu-item-tp-menu-icon', true );
			$description = ( !empty( $item->description ) and 0 == $depth ) ? '<small class="nav_desc">' . esc_attr( $item->description ) . '</small>' : '';

			if ( isset( $args->before ) ) {
				$item_output .= $args->before;
			}

			$before_data_hover = $after_data_hover = '';
			if ( $depth == 0 ) {
				if ( $item->title ) {
					$title_hover = $item->title;
				} else {
					$title_hover = $item->post_title;
				}
				$before_data_hover = '<span data-hover="' . $title_hover . '">';
				$after_data_hover  = '</span>';
			}


			if ( $hide_text <> '' && $disable_link <> '' ) {
				$item_output .= '';
			} elseif ( $disable_link <> '' ) {
				$item_output .= '<span class="disable_link">' . $before_data_hover;
			} else {
				$item_output .= '<a' . $attributes . '>' . $before_data_hover;
			}
			$item_output .= $description;

			if ( $menu_icon ) {
				$item_output .= '<i class="fa fa-fw fa-' . $menu_icon . '"></i> ';
			}

			$link_before = isset( $args->link_before ) ? $args->link_before : '';
			$link_after  = isset( $args->link_after ) ? $args->link_after : '';
			$title       = isset( $item->title ) ? $item->title : $item->post_title;
			if ( $hide_text == '' ) {
				$item_output .= $link_before . apply_filters( 'the_title', $title, $item->ID ) . $link_after;
			}

			if ( $hide_text <> '' && $disable_link <> '' ) {
				$item_output .= '';
			} elseif ( $disable_link <> '' ) {
				$item_output .= $after_data_hover . '</span>';
			} else {
				$item_output .= $after_data_hover . '</a>';
			}

			if ( isset( $args->link_after ) ) {
				$item_output .= $args->after;
			}


			$indent      = ( $depth ) ? str_repeat( "\t", $depth ) : '';
			$class_names = $value = $class_full_width = '';

			$classes      = empty( $item->classes ) ? array() : (array) $item->classes;
			$submenu_type = get_post_meta( $item->ID, '_menu-item-tp-submenu-type', true );

			$menu_full_width = get_post_meta( $item->ID, '_menu-item-tp-full-width-dropdown', true );
			if ( $submenu_type == 'standard' ) {
				$menu_full_width = false;
			}
			$menu_dropdown = get_post_meta( $item->ID, '_menu-item-tp-side-dropdown-elements', true );
			if ( $menu_dropdown == '' && $depth == 0 ) {
				$menu_dropdown = 'standard';
			}

			$dropdown_stype = get_post_meta( $item->ID, '_menu-item-tp-submenu-type', true );
			$class_full_width .= ( $menu_full_width ) ? ' dropdown_full_width ' : '';
			if ( $menu_dropdown ) {
				$class_full_width .= ' ' . $menu_dropdown;
			}
			if ( $dropdown_stype ) {
				$class_full_width .= ' ' . $dropdown_stype;
			}
			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
			$class_names = ' class="' . $li_text_block_class . esc_attr( $class_names ) . $column_class . $class_full_width . '"';

			if ( $depth == 1 ) {
				$columns        = 'menu_item_parent';
				$parent_item_id = $item->menu_item_parent;
			}
			$output .= $indent . '<li ' . $value . $class_names . '>';
			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}

		/**
		 * @see Walker::end_el()
		 *
		 * @param string $output Passed by reference. Used to append additional content.
		 * @param object $item   Page data object. Not used.
		 * @param int    $depth  Depth of page. Not Used.
		 */
		function end_el( &$output, $item, $depth = 0, $args = array() ) {
			$submenu_type = get_post_meta( $item->ID, '_menu-item-tp-submenu-type', true );
			if ( $submenu_type && $submenu_type == 'widget_area' ) {
				ob_start();
				$widget_area = get_post_meta( $item->ID, '_menu-item-tp-widget-area', true );
				dynamic_sidebar( substr( $widget_area, 12 ) );
				$content         = ob_get_clean();
				$submenu_columns = get_post_meta( $item->ID, '_menu-item-tp-submenu-columns', true );
				if ( $content ) {
					if ( $submenu_columns ) {
						$output .= '<ul class="sub-menu submenu_columns_' . $submenu_columns . ' submenu-widget">' . "\n" . $content . '</ul>';
					}
				}
			}
			$output .= "</li>\n";
		}

	}

}


if ( !class_exists( 'tp_backend_walker' ) ) {

	/**
	 * Create HTML list of nav menu input items.
	 * This walker is a clone of the wordpress edit menu walker with some options appended, so the user can choose to create mega menus
	 *
	 * @package tpFramework
	 * @since   1.0
	 * @uses    Walker_Nav_Menu
	 */
	class tp_backend_walker extends Walker_Nav_Menu {

		public static $added_script_iconbox = false;
		public static $added_html_iconbox = false;
		public static $added_script_bg_image = false;
		public static $added_html_bg_image = false;

		/**
		 * @see   Walker_Nav_Menu::start_lvl()
		 * @since 3.0.0
		 *
		 * @param string $output Passed by reference.
		 * @param int    $depth  Depth of page.
		 */
		function start_lvl( &$output, $depth = 0, $args = array() ) {

		}

		/**
		 * @see   Walker_Nav_Menu::end_lvl()
		 * @since 3.0.0
		 *
		 * @param string $output Passed by reference.
		 * @param int    $depth  Depth of page.
		 */
		function end_lvl( &$output, $depth = 0, $args = array() ) {

		}

		/**
		 * @see   Walker::start_el()
		 * @since 3.0.0
		 *
		 * @param string $output       Passed by reference. Used to append additional content.
		 * @param object $item         Menu item data object.
		 * @param int    $depth        Depth of menu item. Used for padding.
		 * @param int    $current_page Menu item ID.
		 * @param object $args
		 */
		function start_el( &$output, $item, $depth = 0, $args = array(), $current_object_id = 0 ) {
			global $_wp_nav_menu_max_depth;
			$_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;
			$indent                 = ( $depth ) ? str_repeat( "\t", $depth ) : '';
			ob_start();

			$item_id      = esc_attr( $item->ID );
			$removed_args = array(
				'action',
				'customlink-tab',
				'edit-menu-item',
				'menu-item',
				'page-tab',
				'_wpnonce',
			);

			$original_title = '';
			if ( 'taxonomy' == $item->type ) {
				$original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
			} elseif ( 'post_type' == $item->type ) {
				$original_object = get_post( $item->object_id );
				$original_title  = $original_object->post_title;
			}

			$classes = array(
				'menu-item menu-item-depth-' . $depth,
				'menu-item-' . esc_attr( $item->object ),
				'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? 'active' : 'inactive' ),
			);

			$title = $item->title;

			if ( isset( $item->post_status ) && 'draft' == $item->post_status ) {
				$classes[] = 'pending';
				/* translators: %s: title of menu item in draft status */
				$title = sprintf( __( '%s (Pending)', 'thim' ), $item->title );
			}

			$title = empty( $item->label ) ? $title : $item->label;

			$itemValue = "";
			if ( $depth == 0 ) {
				$itemValue = get_post_meta( $item->ID, '_menu-item-tp-megamenu', true );
				if ( $itemValue != "" ) {
					$itemValue = 'tp_mega_active ';
				}
			}
			?>

		<li id="menu-item-<?php echo esc_attr( $item_id ); ?>" class="<?php
		echo esc_html( $itemValue );
		echo implode( ' ', $classes );
		?>">
			<?php
			if ( !self::$added_script_bg_image ) {
				self::$added_script_bg_image = true;
				?>
				<script>
					function open_media_browser(target_id) {
						var input_text = jQuery('#' + target_id);
						wp.media.editor.send.attachment = function (props, attachment) {
							input_text.val(attachment.url);
						};
						wp.media.editor.open(input_text);
						return false;
					}
				</script>
				<?php
			}

			if ( !self::$added_html_iconbox ) {
				self::$added_html_iconbox = true;
				$icons                    = array(
					'none',
					'glass',
					'music',
					'search',
					'envelope-o',
					'heart',
					'star',
					'star-o',
					'user',
					'film',
					'th-large',
					'th',
					'th-list',
					'check',
					'remove',
					'close',
					'times',
					'search-plus',
					'search-minus',
					'power-off',
					'signal',
					'gear',
					'cog',
					'trash-o',
					'home',
					'file-o',
					'clock-o',
					'road',
					'download',
					'arrow-circle-o-down',
					'arrow-circle-o-up',
					'inbox',
					'play-circle-o',
					'rotate-right',
					'repeat',
					'refresh',
					'list-alt',
					'lock',
					'flag',
					'headphones',
					'volume-off',
					'volume-down',
					'volume-up',
					'qrcode',
					'barcode',
					'tag',
					'tags',
					'book',
					'bookmark',
					'print',
					'camera',
					'font',
					'bold',
					'italic',
					'text-height',
					'text-width',
					'align-left',
					'align-center',
					'align-right',
					'align-justify',
					'list',
					'dedent',
					'outdent',
					'indent',
					'video-camera',
					'photo',
					'image',
					'picture-o',
					'pencil',
					'map-marker',
					'adjust',
					'tint',
					'edit',
					'pencil-square-o',
					'share-square-o',
					'check-square-o',
					'arrows',
					'step-backward',
					'fast-backward',
					'backward',
					'play',
					'pause',
					'stop',
					'forward',
					'fast-forward',
					'step-forward',
					'eject',
					'chevron-left',
					'chevron-right',
					'plus-circle',
					'minus-circle',
					'times-circle',
					'check-circle',
					'question-circle',
					'info-circle',
					'crosshairs',
					'times-circle-o',
					'check-circle-o',
					'ban',
					'arrow-left',
					'arrow-right',
					'arrow-up',
					'arrow-down',
					'mail-forward',
					'share',
					'expand',
					'compress',
					'plus',
					'minus',
					'asterisk',
					'exclamation-circle',
					'gift',
					'leaf',
					'fire',
					'eye',
					'eye-slash',
					'warning',
					'exclamation-triangle',
					'plane',
					'calendar',
					'random',
					'comment',
					'magnet',
					'chevron-up',
					'chevron-down',
					'retweet',
					'shopping-cart',
					'folder',
					'folder-open',
					'arrows-v',
					'arrows-h',
					'bar-chart-o',
					'bar-chart',
					'twitter-square',
					'facebook-square',
					'camera-retro',
					'key',
					'gears',
					'cogs',
					'comments',
					'thumbs-o-up',
					'thumbs-o-down',
					'star-half',
					'heart-o',
					'sign-out',
					'linkedin-square',
					'thumb-tack',
					'external-link',
					'sign-in',
					'trophy',
					'github-square',
					'upload',
					'lemon-o',
					'phone',
					'square-o',
					'bookmark-o',
					'phone-square',
					'twitter',
					'facebook',
					'github',
					'unlock',
					'credit-card',
					'rss',
					'hdd-o',
					'bullhorn',
					'bell',
					'certificate',
					'hand-o-right',
					'hand-o-left',
					'hand-o-up',
					'hand-o-down',
					'arrow-circle-left',
					'arrow-circle-right',
					'arrow-circle-up',
					'arrow-circle-down',
					'globe',
					'wrench',
					'tasks',
					'filter',
					'briefcase',
					'arrows-alt',
					'group',
					'users',
					'chain',
					'link',
					'cloud',
					'flask',
					'cut',
					'scissors',
					'copy',
					'files-o',
					'paperclip',
					'save',
					'floppy-o',
					'square',
					'navicon',
					'reorder',
					'bars',
					'list-ul',
					'list-ol',
					'strikethrough',
					'underline',
					'table',
					'magic',
					'truck',
					'pinterest',
					'pinterest-square',
					'google-plus-square',
					'google-plus',
					'money',
					'caret-down',
					'caret-up',
					'caret-left',
					'caret-right',
					'columns',
					'unsorted',
					'sort',
					'sort-down',
					'sort-desc',
					'sort-up',
					'sort-asc',
					'envelope',
					'linkedin',
					'rotate-left',
					'undo',
					'legal',
					'gavel',
					'dashboard',
					'tachometer',
					'comment-o',
					'comments-o',
					'flash',
					'bolt',
					'sitemap',
					'umbrella',
					'paste',
					'clipboard',
					'lightbulb-o',
					'exchange',
					'cloud-download',
					'cloud-upload',
					'user-md',
					'stethoscope',
					'suitcase',
					'bell-o',
					'coffee',
					'cutlery',
					'file-text-o',
					'building-o',
					'hospital-o',
					'ambulance',
					'medkit',
					'fighter-jet',
					'beer',
					'h-square',
					'plus-square',
					'angle-double-left',
					'angle-double-right',
					'angle-double-up',
					'angle-double-down',
					'angle-left',
					'angle-right',
					'angle-up',
					'angle-down',
					'desktop',
					'laptop',
					'tablet',
					'mobile-phone',
					'mobile',
					'circle-o',
					'quote-left',
					'quote-right',
					'spinner',
					'circle',
					'mail-reply',
					'reply',
					'github-alt',
					'folder-o',
					'folder-open-o',
					'smile-o',
					'frown-o',
					'meh-o',
					'gamepad',
					'keyboard-o',
					'flag-o',
					'flag-checkered',
					'terminal',
					'code',
					'mail-reply-all',
					'reply-all',
					'star-half-empty',
					'star-half-full',
					'star-half-o',
					'location-arrow',
					'crop',
					'code-fork',
					'unlink',
					'chain-broken',
					'question',
					'info',
					'exclamation',
					'superscript',
					'subscript',
					'eraser',
					'puzzle-piece',
					'microphone',
					'microphone-slash',
					'shield',
					'calendar-o',
					'fire-extinguisher',
					'rocket',
					'maxcdn',
					'chevron-circle-left',
					'chevron-circle-right',
					'chevron-circle-up',
					'chevron-circle-down',
					'html5',
					'css3',
					'anchor',
					'unlock-alt',
					'bullseye',
					'ellipsis-h',
					'ellipsis-v',
					'rss-square',
					'play-circle',
					'ticket',
					'minus-square',
					'minus-square-o',
					'level-up',
					'level-down',
					'check-square',
					'pencil-square',
					'external-link-square',
					'share-square',
					'compass',
					'toggle-down',
					'caret-square-o-down',
					'toggle-up',
					'caret-square-o-up',
					'toggle-right',
					'caret-square-o-right',
					'euro',
					'eur',
					'gbp',
					'dollar',
					'usd',
					'rupee',
					'inr',
					'cny',
					'rmb',
					'yen',
					'jpy',
					'ruble',
					'rouble',
					'rub',
					'won',
					'krw',
					'bitcoin',
					'btc',
					'file',
					'file-text',
					'sort-alpha-asc',
					'sort-alpha-desc',
					'sort-amount-asc',
					'sort-amount-desc',
					'sort-numeric-asc',
					'sort-numeric-desc',
					'thumbs-up',
					'thumbs-down',
					'youtube-square',
					'youtube',
					'xing',
					'xing-square',
					'youtube-play',
					'dropbox',
					'stack-overflow',
					'instagram',
					'flickr',
					'adn',
					'bitbucket',
					'bitbucket-square',
					'tumblr',
					'tumblr-square',
					'long-arrow-down',
					'long-arrow-up',
					'long-arrow-left',
					'long-arrow-right',
					'apple',
					'windows',
					'android',
					'linux',
					'dribbble',
					'skype',
					'foursquare',
					'trello',
					'female',
					'male',
					'gittip',
					'sun-o',
					'moon-o',
					'archive',
					'bug',
					'vk',
					'weibo',
					'renren',
					'pagelines',
					'stack-exchange',
					'arrow-circle-o-right',
					'arrow-circle-o-left',
					'toggle-left',
					'caret-square-o-left',
					'dot-circle-o',
					'wheelchair',
					'vimeo-square',
					'turkish-lira',
					'try',
					'plus-square-o',
					'space-shuttle',
					'slack',
					'envelope-square',
					'wordpress',
					'openid',
					'institution',
					'bank',
					'university',
					'mortar-board',
					'graduation-cap',
					'yahoo',
					'google',
					'reddit',
					'reddit-square',
					'stumbleupon-circle',
					'stumbleupon',
					'delicious',
					'digg',
					'pied-piper',
					'pied-piper-alt',
					'drupal',
					'joomla',
					'language',
					'fax',
					'building',
					'child',
					'paw',
					'spoon',
					'cube',
					'cubes',
					'behance',
					'behance-square',
					'steam',
					'steam-square',
					'recycle',
					'automobile',
					'car',
					'cab',
					'taxi',
					'tree',
					'spotify',
					'deviantart',
					'soundcloud',
					'database',
					'file-pdf-o',
					'file-word-o',
					'file-excel-o',
					'file-powerpoint-o',
					'file-photo-o',
					'file-picture-o',
					'file-image-o',
					'file-zip-o',
					'file-archive-o',
					'file-sound-o',
					'file-audio-o',
					'file-movie-o',
					'file-video-o',
					'file-code-o',
					'vine',
					'codepen',
					'jsfiddle',
					'life-bouy',
					'life-buoy',
					'life-saver',
					'support',
					'life-ring',
					'circle-o-notch',
					'ra',
					'rebel',
					'ge',
					'empire',
					'git-square',
					'git',
					'hacker-news',
					'tencent-weibo',
					'qq',
					'wechat',
					'weixin',
					'send',
					'paper-plane',
					'send-o',
					'paper-plane-o',
					'history',
					'circle-thin',
					'header',
					'paragraph',
					'sliders',
					'share-alt',
					'share-alt-square',
					'bomb',
					'soccer-ball-o',
					'futbol-o',
					'tty',
					'binoculars',
					'plug',
					'slideshare',
					'twitch',
					'yelp',
					'newspaper-o',
					'wifi',
					'calculator',
					'paypal',
					'google-wallet',
					'cc-visa',
					'cc-mastercard',
					'cc-discover',
					'cc-amex',
					'cc-paypal',
					'cc-stripe',
					'bell-slash',
					'bell-slash-o',
					'trash',
					'copyright',
					'at',
					'eyedropper',
					'paint-brush',
					'birthday-cake',
					'area-chart',
					'pie-chart',
					'line-chart',
					'lastfm',
					'lastfm-square',
					'toggle-off',
					'toggle-on',
					'bicycle',
					'bus',
					'ioxhost',
					'angellist',
					'cc',
					'shekel',
					'sheqel',
					'ils',
					'meanpath'
				);
				$icons_str                = implode( ' ', $icons );
				?>
				<script type="text/javascript">

					function on_open_icon_browser(target_id) {
						jQuery('#iconbox_target_element').val(target_id);
					}


					function onSearchIconBoxKeyUp(filter) {
						var icons_str = "<?php echo ent2ncr($icons_str); ?>";
						if (!filter) {
							jQuery("#icon-dropdown .icon-list li").show();
						}
						var regex_str = "[^\\s]?" + filter + "[^\\s]*";
						var re = new RegExp(regex_str, "gi");
						var match = icons_str.match(re);
						if (filter.length) {
							jQuery("#icon-dropdown .icon-list li").hide();
						}
						if (match && match.length) {
							jQuery.each(match, function (index, value) {
								jQuery("#icon-dropdown .icon-list li#icon-list-" + value).show();
							});
						}
						return;
					}


					jQuery(document).ready(function () {
						jQuery("#icon-dropdown li").click(function () {
							jQuery(this).attr("class", "selected").siblings().removeAttr("class");
							var icon = jQuery(this).attr("data-icon");
							var target_id = jQuery('#iconbox_target_element').val();
							var match = target_id.match(/\d+/gi);
							jQuery("#" + target_id).val(icon);
							jQuery(".icon-preview-" + match[0]).html("<i class=\'icon fa fa-" + icon + "\'></i>");
						});


					});
				</script>
				<div id="iconbox-popup" style="display:none;">

					<?php
					$html = '<div id="iconbox-search-bar">';
					$html .= '<input type="hidden" name="" class="wpb_vc_param_value" value="" id="trace"/> ';
					$html .= '<input id="icon_box_search_box" onkeyup="onSearchIconBoxKeyUp(this.value)" class="search icon-search" type="text" placeholder="Search" /><div class="icon-preview"><i class=""></i></div>';
					$html .= '<input id="iconbox_target_element" type="hidden" />';
					$html .= '</div>';

					$html .= '<div id="icon-dropdown" >';
					$html .= '<ul class="icon-list">';
					$n = 1;
					foreach ( $icons as $icon ) {
						$html .= '<li id="icon-list-' . $icon . '" data-icon="' . $icon . '"><i class="icon fa fa-' . $icon . '"></i></li>';
						$n ++;
					}
					$html .= '</ul>';
					$html .= '</div>';
					echo ent2ncr( $html );
					?>
				</div>
				<?php
			}
			?>
			<dl class="menu-item-bar">
				<dt class="menu-item-handle">
					<span class="item-title"><?php echo esc_html( $title ); ?></span>
                    <span class="item-controls">
                        <span class="item-type item-type-default"><?php echo esc_html( $item->type_label ); ?></span>
                        <span class="item-type item-type-tp"><?php esc_attr_e( 'Column', 'thim' ); ?></span>
                        <span class="item-type item-type-megafied"><?php esc_attr_e( '(Mega Menu)', 'thim' ); ?></span>
                        <span class="item-order">
                            <a href="<?php
							echo wp_nonce_url(
								add_query_arg(
									array(
										'action'    => 'move-up-menu-item',
										'menu-item' => $item_id,
									), remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
								), 'move-menu_item'
							);
							?>" class="item-move-up"><abbr title="<?php esc_attr_e( 'Move up', 'thim' ); ?>">&#8593;</abbr></a>
                            |
                            <a href="<?php
							echo wp_nonce_url(
								add_query_arg(
									array(
										'action'    => 'move-down-menu-item',
										'menu-item' => $item_id,
									), remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
								), 'move-menu_item'
							);
							?>" class="item-move-down"><abbr title="<?php esc_attr_e( 'Move down', 'thim' ); ?>">&#8595;</abbr></a>
                        </span>
                        <a class="item-edit" id="edit-<?php echo esc_attr( $item_id ); ?>" title="<?php esc_attr_e( 'Edit Menu Item', 'thim' ); ?>" href="<?php
						echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : esc_url( add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) ) );
						?>"><?php esc_attr_e( 'Edit Menu Item', 'thim' ); ?></a>
                    </span>
				</dt>
			</dl>

			<div class="menu-item-settings" id="menu-item-settings-<?php echo esc_attr( $item_id ); ?>">
				<?php if ( 'custom' == $item->type ) : ?>
					<p class="field-url description description-wide">
						<label for="edit-menu-item-url-<?php echo esc_attr( $item_id ); ?>">
							<?php esc_attr_e( 'URL', 'thim' ); ?><br />
							<input type="text" id="edit-menu-item-url-<?php echo esc_attr( $item_id ); ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->url ); ?>" />
						</label>
					</p>
				<?php endif; ?>

				<p class="description description-thin description-label tp_label_desc_on_active">
					<label for="edit-menu-item-title-<?php echo esc_attr( $item_id ); ?>">
						<span class='tp_default_label'><?php esc_attr_e( 'Navigation Label', 'thim' ); ?></span>
						<!-- <span class='tp_mega_label'><?php _e( 'Mega Menu Column Title <span class="tp_supersmall">(if you dont want to display a title just enter a single dash: "-" )</span>', 'thim' ); ?></span> -->
						<br />
						<input type="text" id="edit-menu-item-title-<?php echo esc_attr( $item_id ); ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->title ); ?>" />
					</label>
				</p>

				<p class="description description-thin description-title">
					<label for="edit-menu-item-attr-title-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_attr_e( 'Title Attribute', 'thim' ); ?><br />
						<input type="text" id="edit-menu-item-attr-title-<?php echo esc_attr( $item_id ); ?>" class="widefat edit-menu-item-attr-title" name="menu-item-attr-title[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->post_excerpt ); ?>" />
					</label>
				</p>

				<p class="field-link-target description description-thin">
					<label for="edit-menu-item-target-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_attr_e( 'link Target', 'thim' ); ?><br />
						<select id="edit-menu-item-target-<?php echo esc_attr( $item_id ); ?>" class="widefat edit-menu-item-target" name="menu-item-target[<?php echo esc_attr( $item_id ); ?>]">
							<option value="" <?php selected( $item->target, '' ); ?>><?php esc_attr_e( 'Same window or tab', 'thim' ); ?></option>
							<option value="_blank" <?php selected( $item->target, '_blank' ); ?>><?php esc_attr_e( 'New window or tab', 'thim' ); ?></option>
						</select>
					</label>
				</p>
				<p class="field-css-classes description description-thin">
					<label for="edit-menu-item-classes-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_attr_e( 'CSS Classes (optional)', 'thim' ); ?><br />
						<input type="text" id="edit-menu-item-classes-<?php echo esc_attr( $item_id ); ?>" class="widefat code edit-menu-item-classes" name="menu-item-classes[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( implode( ' ', $item->classes ) ); ?>" />
					</label>
				</p>

				<p class="field-xfn description description-thin">
					<label for="edit-menu-item-xfn-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_attr_e( 'link Relationship (XFN)', 'thim' ); ?><br />
						<input type="text" id="edit-menu-item-xfn-<?php echo esc_attr( $item_id ); ?>" class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />
					</label>
				</p>

				<p class="field-description description description-wide">
					<label for="edit-menu-item-description-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_attr_e( 'Description', 'thim' ); ?><br />
						<textarea id="edit-menu-item-description-<?php echo esc_attr( $item_id ); ?>" class="widefat edit-menu-item-description" rows="3" cols="20" name="menu-item-description[<?php echo esc_attr( $item_id ); ?>]"><?php echo esc_html( $item->post_content ); ?></textarea>
					</label>
				</p>

				<div class='tp_mega_menu_options'>
					<?php
					$key   = "menu-item-tp-menu-icon";
					$value = get_post_meta( $item->ID, '_' . $key, true );
					?>
					<p class="description description-wide tp_checkbox tp_mega_menu tp_mega_menu_d1">
						<label for="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>">
							<?php esc_attr_e( 'Menu Icon', 'thim' ) ?><br />
							<input type="text" value="<?php echo esc_html( $value ); ?>" id="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>" <?php
							?>class=" <?php echo esc_attr( $key ); ?>" name="<?php echo ent2ncr( $key . "[" . $item_id . "]" ); ?>" />
							<input alt="#TB_inline?height=400&width=500&inlineId=iconbox-popup" <?php
							?>title="<?php esc_attr_e( 'Click to browse icon', 'thim' ) ?>" <?php
								   ?>class="thickbox button-secondary submit-add-to-menu" <?php
								   ?>type="button" value="<?php esc_attr_e( 'Browse Icon', 'thim' ) ?>"
								   onclick="on_open_icon_browser('edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>');" />
							<span class="icon-preview  icon-preview<?php echo '-' . $item_id; ?>"><i class=" fa fa-<?php echo esc_html( $value ); ?>"></i></span>
						</label>
					</p>
					<!-- ***************  end item *************** -->
					<?php
					$key   = "menu-item-tp-hide-text";
					$value = get_post_meta( $item->ID, '_' . $key, true );
					$value = ( $value == 'active' ) ? ' checked="checked" ' : '';
					?>
					<p class="description description-wide">
						<label for="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>">
							<input type="checkbox" value="active" id="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>" class=" <?php echo esc_attr( $key ); ?>" name="<?php echo ent2ncr( $key . "[" . $item_id . "]" ); ?>" <?php echo esc_html( $value ); ?> /><?php esc_attr_e( 'Hide text of This Item', 'thim' ); ?>
						</label>
					</p>


					<?php
					$key   = "menu-item-tp-disable-link";
					$value = get_post_meta( $item->ID, '_' . $key, true );
					$value = ( $value == 'active' ) ? ' checked="checked" ' : '';
					?>
					<p class="description description-wide">
						<label for="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>">
							<input type="checkbox" value="active" id="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>" class=" <?php echo esc_attr( $key ); ?>" name="<?php echo ent2ncr( $key . "[" . $item_id . "]" ); ?>" <?php echo esc_html( $value ); ?> /><?php esc_attr_e('Disable Link', 'thim'); ?>
						</label>
					</p>

					<?php
					if ( !$depth ) {
						?>
						<?php
						$key                = "menu-item-tp-submenu-type";
						$submenu_type_value = $value = get_post_meta( $item->ID, '_' . $key, true );
						?>
						<p class="description description-wide description_width_100">
							<?php esc_attr_e( 'Submenu Type', 'thim' ); ?><br />
							<label for="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>">
								<select id="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>" class=" <?php echo esc_attr( $key ); ?>" name="<?php echo ent2ncr( $key . "[" . $item_id . "]" ); ?>" onchange="onChangeSubmenuType(this);">
									<option value="standard" <?php echo ( $value == 'standard' ) ? ' selected="selected" ' : ''; ?>><?php esc_attr_e( 'Standard Dropdown', 'thim' ); ?></option>
									<option value="multicolumn" <?php echo ( $value == 'multicolumn' ) ? ' selected="selected" ' : ''; ?>><?php esc_attr_e( 'Multicolumn Dropdown', 'thim' ); ?></option>
									<option value="widget_area" <?php echo ( $value == 'widget_area' ) ? ' selected="selected" ' : ''; ?>><?php esc_attr_e( 'Widget area', 'thim' ); ?></option>
								</select>
							</label>
						</p>

						<?php
						$key      = "menu-item-tp-widget-area";
						$value    = get_post_meta( $item->ID, '_' . $key, true );
						$sidebars = $GLOBALS['wp_registered_sidebars'];
						$style    = ( $submenu_type_value == 'widget_area' ) ? '' : ' style="display:none;" ';
						?>
						<p class="description description-wide description_width_100 el_widget_area"<?php echo ent2ncr( $style ); ?>>
							<?php esc_attr_e( 'Widget Area', 'thim' ); ?><br />
							<label for="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>">
								<select id="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>" class=" <?php echo esc_attr( $key ); ?>" name="<?php echo ent2ncr( $key . "[" . $item_id . "]" ); ?>">
									<option value="" <?php echo ( $value == '' ) ? ' selected="selected" ' : ''; ?>><?php esc_attr_e( 'Select Widget Area', 'thim' ); ?></option>
									<?php
									foreach ( $sidebars as $sidebar ) {
										echo '<option value="widget_area_' . $sidebar['id'] . '" ' . ( ( $value == "widget_area_" . $sidebar['id'] ) ? ' selected="selected" ' : '' ) . '>[' . $sidebar['id'] . '] - ' . $sidebar['name'] . '</option>';
									}
									?>
								</select>
							</label>
						</p>


						<?php
						$key   = "menu-item-tp-submenu-columns";
						$value = get_post_meta( $item->ID, '_' . $key, true );
						$style = ( $submenu_type_value == 'standard' ) ? ' style="display:none;" ' : '';
						?>
						<p class="description description-wide description_width_100 el_multicolumn"<?php echo ent2ncr( $style ); ?>>
							<?php esc_attr_e( 'Submenu Columns (Not For Standard Drops)', 'thim' ); ?><br />
							<label for="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>">
								<select id="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>" class=" <?php echo esc_attr( $key ); ?>" name="<?php echo ent2ncr( $key . "[" . $item_id . "]" ); ?>">
									<?php
									for ( $i = 1; $i <= 5; $i ++ ) {
										?>
										<option value="<?php echo esc_attr( $i ); ?>" <?php echo ( $value == $i ) ? ' selected="selected" ' : ''; ?>><?php echo esc_attr( $i ); ?></option>
										<?php
									}
									?>
								</select>
							</label>
						</p>

						<?php
						$key   = "menu-item-tp-side-dropdown-elements";
						$value = get_post_meta( $item->ID, '_' . $key, true );
						?>
						<p class="description description-wide description_width_100">
							<?php esc_attr_e( 'Side of Dropdown Elements', 'thim' ); ?><br />
							<label for="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>">
								<select id="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>" class=" <?php echo esc_attr( $key ); ?>" name="<?php echo ent2ncr( $key . "[" . $item_id . "]" ); ?>">
									<option value="drop_to_left" <?php echo ( $value == 'drop_to_left' ) ? ' selected="selected" ' : ''; ?>><?php esc_attr_e( 'Drop To Left Side', 'thim' ); ?></option>
									<option value="drop_to_right" <?php echo ( $value == 'drop_to_right' ) ? ' selected="selected" ' : ''; ?>><?php esc_attr_e( 'Drop To Right Side', 'thim' ); ?></option>
									<option value="drop_to_center" <?php echo ( $value == 'drop_to_center' ) ? ' selected="selected" ' : ''; ?>><?php esc_attr_e( 'Drop To Center', 'thim' ); ?></option>
								</select>
							</label>
						</p>

						<?php
						$key   = "menu-item-tp-full-width-dropdown";
						$value = get_post_meta( $item->ID, '_' . $key, true );
						$value = ( $value == 'active' ) ? ' checked="checked" ' : '';
						$style = ( $submenu_type_value == 'standard' ) ? ' style="display:none;" ' : '';
						?>
						<p class="description description-wide el_fullwidth"<?php echo ent2ncr( $style ); ?>>
							<label for="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>">
								<input type="checkbox" value="active" id="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>" class=" <?php echo esc_attr( $key ); ?>" name="<?php echo ent2ncr( $key . "[" . $item_id . "]" ); ?>" <?php echo esc_html( $value ); ?> /><?php esc_attr_e( 'Enable Full Width Dropdown ()', 'thim' ); ?>
							</label>
						</p>

						<?php
						$key   = "menu-item-tp-bg-color";
						$value = get_post_meta( $item->ID, '_' . $key, true );
						$style = '';
						?>

						<p class="description description-wide tp_checkbox tp_mega_menu tp_mega_menu_d2"<?php echo ent2ncr( $style ); ?>>
							<label for="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>">
								<span class='tp_long_desc'><?php esc_attr_e( 'DropDown Background Color', 'thim' ); ?></span><br />
								<input class="megamenu-colorpicker" type="text" name="<?php echo ent2ncr( $key . "[" . $item_id . "]" ); ?>" id="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>" value="<?php echo esc_html( $value ); ?>" data-default-color="<?php echo esc_html( $value ); ?>" />
							</label>
						</p>

						<?php
						$key   = "menu-item-tp-bg-image";
						$value = get_post_meta( $item->ID, '_' . $key, true );
						$style = '';
						?>

						<p class="description description-wide tp_checkbox tp_mega_menu tp_mega_menu_d2"<?php echo ent2ncr( $style ); ?>>
							<label for="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>">
								<span class='tp_long_desc'><?php esc_attr_e( 'DropDown Background Image', 'thim' ); ?></span><br />
								<input type="text" value="<?php echo esc_html( $value ); ?>" id="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>" class=" <?php echo esc_attr( $key ); ?>" name="<?php echo ent2ncr( $key . "[" . $item_id . "]" ); ?>" />
								<button id="browse-edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>"
										class="set_custom_images button button-secondary submit-add-to-menu"
										onclick="open_media_browser('edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>'); return false;"><?php esc_attr_e( 'Browse Image', 'thim' ); ?></button>
							</label>
						</p>

						<p class="description description-wide description_width_25">
							<?php
							$key     = "menu-item-tp-bg-image-repeat";
							$value   = get_post_meta( $item->ID, '_' . $key, true );
							$options = array( 'repeat', 'no-repeat', 'repeat-x', 'repeat-y' );
							?>
							<label for="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>">
								<select id="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>" class=" <?php echo esc_attr( $key ); ?>" name="<?php echo ent2ncr( $key . "[" . $item_id . "]" ); ?>">
									<?php
									foreach ( $options as $option ) {
										?>
										<option value="<?php echo esc_attr( $option ); ?>" <?php echo ( $value == $option ) ? ' selected="selected" ' : ''; ?>><?php echo esc_attr( $option ); ?></option>
										<?php
									}
									?>
								</select>
							</label>
							<?php
							$key     = "menu-item-tp-bg-image-attachment";
							$value   = get_post_meta( $item->ID, '_' . $key, true );
							$options = array( 'scroll', 'fixed' );
							?>
							<label for="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>">
								<select id="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>" class=" <?php echo esc_attr( $key ); ?>" name="<?php echo ent2ncr( $key . "[" . $item_id . "]" ); ?>">
									<?php
									foreach ( $options as $option ) {
										?>
										<option value="<?php echo esc_attr( $option ); ?>" <?php echo ( $value == $option ) ? ' selected="selected" ' : ''; ?>><?php echo esc_attr( $option ); ?></option>
										<?php
									}
									?>
								</select>
							</label>

							<?php
							$key     = "menu-item-tp-bg-image-position";
							$value   = get_post_meta( $item->ID, '_' . $key, true );
							$options = array(
								'center',
								'center left',
								'center right',
								'top left',
								'top center',
								'top right',
								'bottom left',
								'bottom center',
								'bottom right'
							);
							?>
							<label for="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>">
								<select id="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>" class=" <?php echo esc_attr( $key ); ?>" name="<?php echo ent2ncr( $key . "[" . $item_id . "]" ); ?>">
									<?php
									foreach ( $options as $option ) {
										?>
										<option value="<?php echo esc_attr( $option ); ?>" <?php echo ( $value == $option ) ? ' selected="selected" ' : ''; ?>><?php echo esc_attr( $option ); ?></option>
										<?php
									}
									?>
								</select>
							</label>

							<?php
							$key     = "menu-item-tp-bg-image-size";
							$value   = get_post_meta( $item->ID, '_' . $key, true );
							$options = array(
								"auto"      => "Keep original",
								"100% auto" => "Stretch to width",
								"auto 100%" => "Stretch to height",
								"cover"     => "cover",
								"contain"   => "contain"
							);
							?>
							<label for="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>">
								<select id="edit-<?php echo esc_attr( $key . '-' . $item_id ); ?>" class=" <?php echo esc_attr( $key ); ?>" name="<?php echo ent2ncr( $key . "[" . $item_id . "]" ); ?>">
									<?php
									foreach ( $options as $op_value => $op_text ) {
										?>
										<option value="<?php echo esc_attr( $op_value ); ?>" <?php echo ( $value == $op_value ) ? ' selected="selected" ' : ''; ?>><?php echo esc_attr( $op_text ); ?></option>
										<?php
									}
									?>
								</select>
							</label>
						</p>
						<!-- ***************  end item *************** -->
						<?php
					}
					?>
				</div>


				<?php do_action( 'tp_mega_menu_option_fields', $output, $item, $depth, $args ); ?>

				<!-- ################# end tp custom code here ################# -->

				<div class="menu-item-actions description-wide submitbox">
					<?php if ( 'custom' != $item->type ) : ?>
						<p class="link-to-original">
							<?php printf( __( 'Original: %s', 'thim' ), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
						</p>
					<?php endif; ?>
					<a class="item-delete submitdelete deletion" id="delete-<?php echo esc_attr( $item_id ); ?>" href="<?php
					echo wp_nonce_url(
						add_query_arg(
							array(
								'action'    => 'delete-menu-item',
								'menu-item' => $item_id,
							), remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
						), 'delete-menu_item_' . $item_id
					);
					?>"><?php esc_attr_e( 'Remove', 'thim' ); ?></a> <span class="meta-sep"> | </span>
					<a class="item-cancel submitcancel" id="cancel-<?php echo esc_attr( $item_id ); ?>" href="<?php echo esc_url( add_query_arg( array(
						'edit-menu-item' => $item_id,
						'cancel'         => time()
					), remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) ) ) );
					?>#menu-item-settings-<?php echo esc_attr( $item_id ); ?>">Cancel</a>
				</div>

				<input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item_id ); ?>" />
				<input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->object_id ); ?>" />
				<input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->object ); ?>" />
				<input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->menu_item_parent ); ?>" />
				<input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->menu_order ); ?>" />
				<input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->type ); ?>" />
			</div>
			<!-- .menu-item-settings-->
			<ul class="menu-item-transport"></ul>
			<?php
			$output .= ob_get_clean();
		}

	}

}


if ( !function_exists( 'tp_fallback_menu' ) ) {

	/**
	 * Create a navigation out of pages if the user didnt create a menu in the backend
	 *
	 */
	function tp_fallback_menu() {
		$current = "";
		$exclude = tp_get_option( 'frontpage' );
		if ( is_front_page() ) {
			$current = "class='current-menu-item'";
		}
		if ( $exclude ) {
			$exclude = "&exclude=" . $exclude;
		}

		echo "<div class='fallback_menu'>";
		echo "<ul class='tp_mega menu'>";
		echo "<li $current><a href='" . esc_url( home_url() ) . "'>" . esc_attr__( 'Home', 'tp_framework' ) . "</a></li>";
		wp_list_pages( 'title_li=&sort_column=menu_order' . $exclude );
		echo apply_filters( 'avf_fallback_menu_items', "", 'fallback_menu' );
		echo "</ul></div>";
	}

}

global $wpdb;
$menu_has_items = $wpdb->get_var( $wpdb->prepare(
	"
		SELECT COUNT(*)
		FROM $wpdb->term_taxonomy
		WHERE taxonomy = %s AND count != %d
	",
	'nav_menu',
	0
) );

if ( $menu_has_items ) {
	new tp_megamenu();
}

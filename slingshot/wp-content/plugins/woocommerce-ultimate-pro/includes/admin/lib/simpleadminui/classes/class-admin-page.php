<?php
/**
 *	Prepare a sinlge menu or submenu page with meta boxes and settings
 */

/** Example use:

	$settings_page = new \UltimateWoo\Admin_Page( array(
		'slug' => 'my-settings-page', // slug identifier for this page
		'page_title' => __( 'My Settings Page', 'text-domain' ), // page title
		'menu_title' => __( 'My Settings Page', 'text-domain' ), // menu title
		'capabilities' => 'manage_options', // capability a user must have to see the page
		'priority' => 99, // priority for menu positioning
		'icon' => '', // URL to an icon, or name of a Dashicons helper class to use a font icon
		'default_columns' => 2, // Number of default columns (1 or 2)
		'body_content' => 'body_content_callback', // callback that prints to the page, above the metaboxes
		'parent_slug' => 'edit.php', // slug of the 'parent' if subpage; leave empty for a top-level page
		'sortable' => true, // whether the meta boxes should be sortable
		'collapsable' => true, // whether the meta boxes should be collapsable
		'contains_media' => true, // whether the page utilizes the media uploader
		'tabs' => apply_filters( 'my_settings_page_tabs', array(
			// settings tabs
			'tab1' => __( 'Tab One', 'text-domain' ),
			'tab2' => __( 'Tab Two', 'text-domain' ),
			'tab3' => __( 'Tab Three', 'text-domain' ),
		)),
		'help_section' => array(
			'tabs' => array(
				'some-tab' => array(
					'title' => 'Tab Title',
					'content' => 'Some text'
				),
			),
			'sidebar' => 'Sidebar content'
		)
	) );

 */

namespace UltimateWoo\AdminPage;

if ( ! class_exists( 'Admin_Page' ) ) :

class Admin_Page {

	private $page,
			$page_hook,
			$page_title,
			$menu_title,
			$capabilities,
			$slug,
			$priority,
			$icon,
			$default_columns,
			$body_content,
			$parent_sluf,
			$sortable,
			$collapsable,
			$contains_media,
			$tabs,
			$help_section;

	/**
	 *	@param $args (array)
	 *		$slug (string) - slug identifier for this page
	 *		$page_title (string) - page title
	 *		$menu_title (string) - menu title
	 *		$icon (string) - URL to an icon, or name of a Dashicons helper class to use a font icon
	 *		$default_columns (int) - number of default columns (1 or 2)
	 *		$capabilities (string; default = manage_options) - capability a user must have to see the page
	 *		$priority (int) - priority for menu positioning
	 *		$body_content (string; optional) - callback that prints to the page, above the metaboxes
	 *		$parent_slug (string; optional) - slug of the 'parent' if subpage
	 *		$sortable (boolean; optional; default = true) - whether the meta boxes should be sortable
	 *		$collapsable (boolean; optional; default = true) - whether the meta boxes should be collapsable
	 *		$contains_media (boolean; optional; default = true) - whether the page utilizes the media uploader
	 *		$tabs (array; optional) - settings tabs
	 *		$help_section (array; optional) - help section
	 */
	public function __construct( array $args ) {

		// Setup
		$this->slug = $args['slug'];
		$this->page_title = $args['page_title'];
		$this->menu_title = $args['menu_title'];
		$this->capabilities = $args['capabilities'];
		$this->priority = $args['priority'];
		$this->icon = $args['icon'];
		$this->default_columns = $args['default_columns'];
		$this->body_content = $args['body_content'];
		$this->parent_slug = $args['parent_slug'];
		$this->sortable = $args['sortable'];
		$this->collapsable = $args['collapsable'];
		$this->contains_media = $args['contains_media'];
		$this->tabs = $args['tabs'];
		$this->help_section = $args['help_section'];

		$this->hooks();

		require_once 'class-register-meta-boxes.php';

		// Register page's meta boxes
		$meta_boxes = new \UltimateWoo\RegisterMetaBoxes\Register_Meta_Boxes( $this->slug );
	}

	/**
	 *	Run hooks
	 */
	public function hooks() {

		// Activate first tab when tabs are enabled
		add_action( 'admin_init', array( $this, 'activate_default_tab' ) );

		// Add the page
		add_action( 'admin_menu', array( $this, 'add_page' ) );

		// Add JavaScript
		if ( $this->contains_media === true ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_js' ) );
		}
	}

	/**
	 *	If tabs are enabled, and no tab is selected, automatically activate the first tab
	 */
	public function activate_default_tab() {

		// Bail if no tabs are set
		if ( empty( $this->tabs ) ) {
			return;
		}

		// Bail if not on the proper page
		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== $this->slug ) {
			return;
		}

		// Bail if tab is set
		if ( isset( $_GET['tab'] ) ) {
			return;
		}

		$keys = array_keys( $this->tabs );

		// Redirect to activate the first tab
		wp_redirect( add_query_arg( 'tab', $keys[0] ) );
		exit;
	}

	/**
	 *	Add the appropriate page (top or sub)
	 *	Add the appropriate callbacks to the page hooks (load-{suffix} and admin_footer-{suffix})
	 */
	public function add_page(){

		// Add the page
		if ( $this->parent_slug ) {
			$this->page_hook = add_submenu_page( $this->parent_slug, $this->page_title, $this->menu_title, $this->capabilities, $this->slug, array( $this, 'render_page'), $this->priority );
		} else {
			$this->page_hook = add_menu_page( $this->page_title, $this->menu_title, $this->capabilities, $this->slug, array( $this, 'render_page' ), $this->icon, $this->priority );
		}

		// Get the screen object
		$this->page = \WP_Screen::get( $this->page_hook );

		// Add callbacks for this page
		add_action( "load-{$this->page_hook}", array( $this, 'admin_load_page' ), 5 );
		add_action( "admin_footer-{$this->page_hook}", array( $this, 'admin_footer' ) );

		// Register page's help tabs
		if ( is_array( $this->help_section ) && isset( $this->help_section['tabs'] ) ) {
			$sidebar = isset( $this->help_section['sidebar'] ) && ! empty( $this->help_section['sidebar'] ) ? $this->help_section['sidebar'] : array();
			new \UltimateWoo\HelpTabs\Help_Tabs( $this->page, $this->help_section['tabs'], $sidebar );
		}
	}

	/**
	 *	Add JS files for media uploads (if enabled)
	 */
	public function enqueue_admin_js() {
		wp_enqueue_media();
		wp_enqueue_script( 'media-upload' ); // Provides all the functions needed to upload, validate and give format to files.
		wp_enqueue_script( 'thickbox' ); // Responsible for managing the modal window.
		wp_enqueue_style( 'thickbox' ); // Provides the styles needed for this window.
		// wp_enqueue_script( 'script', plugins_url( 'upload.js', __FILE__), array( 'jquery' ), '', true ); // It will initialize the parameters needed to show the window properly.
	}

	/**
	 *	jQuery to initialize meta boxes and media uploads (if enabled); runs on admin_footer-{suffix}
	 */
	public function admin_footer(){ ?>
		
		<script>

			jQuery(document).ready( function($) {

				<?php if ( $this->sortable === false ) : // Sortable disabled ?>

				$('.meta-box-sortables').sortable({
					disabled: true
				});

				$('.postbox .hndle').css('cursor', 'pointer');

				<?php endif; ?>

				<?php if ( $this->collapsable === true ) : // Collapsing enabled (default) ?>

				postboxes.add_postbox_toggles(pagenow);

				<?php else : ?>

				$('.postbox .hndle').css('cursor', 'default');
				$('.handlediv.button-link').css({
					cursor: 'default',
					display: 'none'
				});

				<?php endif; ?>
			});

		</script>

	<?php if ( $this->contains_media === true ) : // Add media uploader (default) ?>

		<?php
			/**
			 *	@link https://mikejolley.com/2012/12/21/using-the-new-wordpress-3-5-media-uploader-in-plugins/
			 *	@link https://codestag.com/how-to-use-wordpress-3-5-media-uploader-in-theme-options/
			 */
		?>

		<script>
			
			jQuery(document).ready(function($) {

				// Uploading files
				var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
				var set_to_post_id = 10; // Set this

				$('.media-uploader').live('click', function(event){

					event.preventDefault();

					var file_frame;
					var button = $(this);
					var id = button.attr('id').replace('_button', '');

					// If the media frame already exists, reopen it.
					if (file_frame) {

						// Set the post ID to what we want
						// file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
						
						// Open frame
						file_frame.open();

						alert(id);
						
						return;

					} else {
						// Set the wp.media post id so the uploader grabs the ID we want when initialised
						// wp.media.model.settings.post.id = set_to_post_id;
					}

					// Create the media frame.
					file_frame = wp.media.frames.file_frame = wp.media({

						title: button.data('uploader-title'),
						button: {
							text: button.data('uploader-button-text'),
						},
						multiple: false  // Set to true to allow multiple files to be selected
					});

					// When an image is selected, run a callback.
					file_frame.on( 'select', function() {

						// We set multiple to false so only get one image from the uploader
						attachment = file_frame.state().get('selection').first().toJSON();

						// Do something with attachment.id and/or attachment.url here
						$("#" + id).val(attachment.url);

						// Restore the main post ID
						wp.media.model.settings.post.id = wp_media_post_id;
					});

					// Finally, open the modal
					file_frame.open();
				});

				// Restore the main ID when the add media button is pressed
				$('a.add_media').on('click', function() {
					wp.media.model.settings.post.id = wp_media_post_id;
				});
			});

		</script>

	<?php endif;

	}


	/*
	 *	Add meta boxes, screen options and enqueues the postbox.js script.   
	 */
	public function admin_load_page(){

		// Do the meta boxes
		do_action( "add_meta_boxes_{$this->page_hook}", null );
		do_action( 'add_meta_boxes', $this->page_hook, null );

		// One or two column layout option
		add_screen_option( 'layout_columns', array(
			'max' => 2,
			'default' => $this->default_columns
		) );

		// Handle meta boxes
		wp_enqueue_script( 'postbox' ); 
	}


	/**
	 *	Renders the settings page
	 */
	public function render_page(){ ?>

		 <div class="wrap">

		 	<?php do_action( "ultimatewoo_settings_page_top" ); ?>

			<h1>
				<?php echo esc_html( $this->page_title );?>
				<?php do_action( 'ultimatewoo_settings_page_title_action' ); ?>
			</h1>

			<?php do_action( "ultimatewoo_settings_page_before_module_settings" ); ?>

			<?php $this->render_tabs( isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '' ); ?>

			<form name="<?php echo $this->slug; ?>_admin_form" id="<?php echo $this->slug; ?>_admin_form" action="?page=<?php echo $this->slug; ?><?php if ( isset( $_GET['tab'] ) ) echo '&tab=' . $_GET['tab']; ?>&save-settings=true" method="post">
				
				<?php wp_nonce_field( $this->slug . '_admin_nonce', $this->slug . '_admin_nonce' );

				// Used to save closed metaboxes and their order
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>

				<div id="poststuff">
		
					 <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>"> 

					 	<?php do_action( "ultimatewoo_settings_page_body_start" ); ?>

						<?php if ( is_callable( $this->body_content ) ) : ?>

							<div id="post-body-content" class="postbox-container">
								<?php call_user_func( $this->body_content ); ?>
								<?php do_action( "ultimatewoo_settings_page_after_body_content" ); ?>
							</div>

						<?php endif; ?>

						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes( '', 'side', null ); ?>
						</div>    

						<div id="postbox-container-2" class="postbox-container">
							<?php do_meta_boxes( '', 'normal', null );  ?>
							<?php do_meta_boxes( '', 'advanced', null ); ?>
						</div>

						<?php do_action( "ultimatewoo_settings_page_body_end" ); ?>	     					

					 </div> <!-- #post-body -->
				
				</div> <!-- #poststuff -->

	      	</form>

			<?php do_action( "ultimatewoo_settings_page_after_module_settings" ); ?>

		 </div><!-- .wrap -->

		<?php
	}

	/**
	 *	Renders the tabs
	 */
	public function render_tabs( $current = '' ) {

		if ( ! empty( $this->tabs ) ) : ?>

			<?php do_action( "ultimatewoo_settings_page_before_tabs" ); ?>

			<h2 class="nav-tab-wrapper">

				<?php foreach ( $this->tabs as $key => $tab ) : ?>

					<?php $active = ( $key == $current ) ? ' nav-tab-active' : ''; ?>

					<a href="<?php echo add_query_arg( 'tab', $key, ULTIMATEWOO_SETTINGS_PAGE_URL ); ?>" class="nav-tab<?php echo $active; ?>"><?php echo $tab; ?></a>

				<?php endforeach; ?>

			</h2>

			<?php do_action( "ultimatewoo_settings_page_after_tabs" ); ?>

		<?php endif;
	}

	/**
	 *	Get $this->page
	 */
	public function get_page_object() {
		return $this->page;
	}

	/**
	 *	Rertieve $this->slug
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 *	Rertieve $this->tabs
	 */
	public function get_tabs() {
		return $this->tabs;
	}

	/**
	 *	Retrieve $this->parent_slug
	 */
	public function get_hook() {
		return $this->parent_slug;
	}

	/**
	 *	Retrieve $this->page_title
	 */
	public function get_page_title() {
		return $this->page_title;
	}

	/**
	 *	Retrieve $this->menu_title
	 */
	public function get_menu_title() {
		return $this->menu_title;
	}

	/**
	 *	Retrieve $this->icon
	 */
	public function get_icon() {
		return $this->icon;
	}

	/**
	 *	Retrieve $this->capabilities
	 */
	public function get_capabilities() {
		return $this->capabilities;
	}

	/**
	 *	Retrieve $this->priority
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 *	Get it all!
	 */
	public function get_it_all() {
		return array(
			'page_object' => $this->get_page_object(),
			'slug' => $this->get_slug(),
			'tabs' => $this->get_tabs(),
			'hook' => $this->get_hook(),
			'page_title' => $this->get_page_title(),
			'menu_title' => $this->get_menu_title(),
			'icon' => $this->get_icon(),
			'capabilities' => $this->get_capabilities(),
			'priority' => $this->get_priority(),
		);
	}

}

endif;
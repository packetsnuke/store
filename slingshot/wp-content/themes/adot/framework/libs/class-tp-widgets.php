<?php

/**
 * Class Thim_Wigets
 *
 * @author Tuannv
 */
abstract class Thim_Widget extends WP_Widget {

	protected $form_options;
	protected $base_folder;
	protected $repeater_html;
	protected $field_ids;

	/**
	 * @var array The array of registered frontend scripts
	 */
	protected $frontend_scripts = array();

	/**
	 * @var array The array of registered frontend styles
	 */
	protected $frontend_styles = array();

	protected $current_instance;
	protected $instance_storage;

	/**
	 * @var int How many seconds a CSS file is valid for.
	 */
	static $css_expire = 604800; // 7 days

	/**
	 *
	 * @param string $id
	 * @param string $name
	 * @param array  $widget_options  Optional Normal WP_Widget widget options and a few extras.
	 *                                - help: A URL which, if present, causes a help link to be displayed on the Edit Widget modal.
	 *                                - instance_storage: Whether or not to temporarily store instances of this widget.
	 * @param array  $control_options Optional Normal WP_Widget control options.
	 * @param array  $form_options    Optional An array describing the form fields used to configure SiteOrigin widgets.
	 * @param mixed  $base_folder     Optional
	 *
	 */
	function __construct( $id, $name, $widget_options = array(), $control_options = array(), $form_options = array(), $base_folder = false ) {
		$this->form_options  = $form_options;
		$this->base_folder   = $base_folder;
		$this->repeater_html = array();
		$this->field_ids     = array();

		$control_options = wp_parse_args( $widget_options, array(
			'width' => 600,
		) );
		parent::__construct( $id, $name, $widget_options, $control_options, $form_options, $base_folder );
	}

	/**
	 * Get the form options and allow child widgets to modify that form.
	 *
	 * @return mixed
	 */
	function form_options() {
		return $this->modify_form( $this->form_options );
	}

	/**
	 * Display the widget.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$instance               = $this->modify_instance( $instance );
		$this->current_instance = $instance;

		$args = wp_parse_args( $args, array(
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '',
			'after_title'   => '',
		) );

		$style = $this->get_style_name( $instance );

		// Add any missing default values to the instance
		$instance = $this->add_defaults( $this->form_options, $instance );

		$upload_dir = wp_upload_dir();

		if ( $style !== false ) {
			$hash     = $this->get_style_hash( $instance );
			$css_name = $this->id_base . '-' . $style . '-' . $hash;

			if ( isset( $instance['is_preview'] ) && $instance['is_preview'] ) {
				thim_widget_add_inline_css( $this->get_instance_css( $instance ) );
			} else {
				if ( !file_exists( $upload_dir['basedir'] . '/thim-widgets/' . $css_name . '.css' ) || ( defined( 'SITEORIGIN_WIDGETS_DEBUG' ) && SITEORIGIN_WIDGETS_DEBUG ) ) {
					// Attempt to recreate the CSS
					$this->save_css( $instance );
				}

				if ( file_exists( $upload_dir['basedir'] . '/thim-widgets/' . $css_name . '.css' ) ) {
					wp_enqueue_style(
						$css_name, $upload_dir['baseurl'] . '/thim-widgets/' . $css_name . '.css'
					);
				} else {
					// Fall back to using inline CSS if we can't find the cached CSS file.
					thim_widget_add_inline_css( $this->get_instance_css( $instance ) );
				}
			}
		} else {
			$css_name = $this->id_base . '-base';
		}


		$this->enqueue_frontend_scripts();
		$this->enqueue_instance_frontend_scripts( $instance );
		extract( $this->get_template_variables( $instance, $args ) );

		$widget_template       = TP_THEME_DIR . 'inc/widgets/' . $this->id_base . '/tpl/' . $this->get_template_name( $instance ) . '.php';
		$child_widget_template = TP_CHILD_THEME_DIR . 'inc/widgets/' . $this->id_base . '/' . $this->get_template_name( $instance ) . '.php';
		if ( file_exists( $child_widget_template ) ) {
			$widget_template = $child_widget_template;
		}

		echo ent2ncr( $args['before_widget'] );
		echo '<div class="thim-widget-' . $this->id_base . ' thim-widget-' . $css_name . '">';
		@ include $widget_template;
		echo '</div>';
		echo ent2ncr( $args['after_widget'] );
	}

	/**
	 * By default, just return an array. Should be overwritten by child widgets.
	 *
	 * @param $instance
	 * @param $args
	 *
	 * @return array
	 */
	public function get_template_variables( $instance, $args ) {
		return array();
	}

	public function sub_widget( $class, $args, $instance ) {
		if ( !class_exists( $class ) ) {
			return;
		}
		$widget = new $class;

		$args['before_widget'] = '';
		$args['after_widget']  = '';

		$widget->widget( $args, $instance );
	}

	/**
	 * Add default values to the instance.
	 *
	 * @param $form
	 * @param $instance
	 */
	function add_defaults( $form, $instance, $level = 0 ) {
		if ( $level > 10 ) {
			return $instance;
		}

		foreach ( $form as $id => $field ) {

			if ( $field['type'] == 'repeater' && !empty( $instance[$id] ) ) {

				foreach ( array_keys( $instance[$id] ) as $i ) {
					$instance[$id][$i] = $this->add_defaults( $field['fields'], $instance[$id][$i], $level + 1 );
				}

			} else {
				if ( !isset( $instance[$id] ) && isset( $field['default'] ) ) {
					$instance[$id] = $field['default'];
				}
			}
		}

		return $instance;
	}

	/**
	 * Display the widget form.
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		$this->enqueue_scripts();
		$instance = $this->modify_instance( $instance );

		$form_id    = 'thim_widget_form_' . md5( uniqid( rand(), true ) );
		$class_name = str_replace( '_', '-', strtolower( get_class( $this ) ) );
		?>
		<div class="thim-widget-form thim-widget-form-main thim-widget-form-main-<?php echo esc_attr( $class_name ) ?>" id="<?php echo esc_attr( $form_id ) ?>" data-class="<?php echo get_class( $this ) ?>">
			<?php
			foreach ( $this->form_options() as $field_name => $field ) {
				$this->render_field(
					$field_name,
					$field,
					isset( $instance[$field_name] ) ? $instance[$field_name] : null,
					false
				);
			}
			?>
		</div>

		<?php if ( !empty( $this->widget_options['help'] ) ) : ?>
			<a href="<?php echo esc_url( $this->widget_options['help'] ) ?>" class="thim-widget-help-link thim-panels-help-link" target="_blank"><?php esc_attr_e( 'Help', 'tp' ) ?></a>
		<?php endif; ?>

		<script type="text/javascript">
			(function ($) {
				if (typeof window.ob_repeater_html == 'undefined')
					window.ob_repeater_html = {};
				window.ob_repeater_html["<?php echo get_class($this) ?>"] = <?php echo json_encode($this->repeater_html) ?>;
				if (typeof $.fn.obSetupForm != 'undefined') {
					$('#<?php echo esc_attr($form_id) ?>').obSetupForm();
				} else {
					// Init once admin scripts have been loaded
					$(window).load(function () {
						$('#<?php echo esc_attr($form_id) ?>').obSetupForm();
					});
				}
				if (!$('#thim-widget-admin-css').length && $.isReady) {
					alert('<?php esc_attr_e('Please refresh this page to start using this widget.', 'tp') ?>')
				}
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Enqueue the admin scripts for the widget form.
	 */
	function enqueue_scripts() {

		if ( !wp_script_is( 'thim-widget-admin' ) ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'thim-widget-admin', TP_THEME_FRAMEWORK_URI . 'css/admin/widget-admin.css', array( 'media-views' ), TP_FRAMEWORK_VERSION );
			wp_enqueue_style( 'thim-awesome', TP_THEME_FRAMEWORK_URI . 'css/font-awesome.min.css', array() );
			wp_enqueue_style( 'thim-7-stroke', TP_THEME_FRAMEWORK_URI . 'css/pe-icon-7-stroke.css', array() );

			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_media();

			wp_enqueue_script( 'thim-widget-admin', TP_THEME_FRAMEWORK_URI . 'js/admin/widget-admin.min.js', array(
				'jquery',
				'jquery-ui-sortable',
				'editor'
			), TP_FRAMEWORK_VERSION, true );

			wp_localize_script( 'thim-widget-admin', 'soWidgets', array(
				'ajaxurl' => wp_nonce_url( admin_url( 'admin-ajax.php' ), 'widgets_action', '_widgets_nonce' ),
				'sure'    => __( 'Are you sure?', 'tp' )
			) );
		}

		if ( !wp_script_is( 'thim-widget-admin-posts-selector' ) && $this->using_posts_selector() ) {

			wp_enqueue_script( 'thim-widget-admin-posts-selector', plugin_dir_url( SITEORIGIN_WIDGETS_BASE_PARENT_FILE ) . 'base/js/posts-selector.min.js', array(
				'jquery',
				'jquery-ui-sortable',
				'jquery-ui-autocomplete',
				'underscore',
				'backbone'
			), TP_FRAMEWORK_VERSION, true );

			wp_localize_script( 'thim-widget-admin-posts-selector', 'obPostsSelectorTpl', array(
				'modal'       => thim_file_get_contents( plugin_dir_path( __FILE__ ) . 'tpl/posts-selector/modal.html' ),
				'postSummary' => thim_file_get_contents( plugin_dir_path( __FILE__ ) . 'tpl/posts-selector/post.html' ),
				'foundPosts'  => '<div class="ob-post-count-message">' . sprintf( __( 'This query returns <a href="#" class="preview-query-posts">%s posts</a>.', 'tp' ), '<%= foundPosts %>' ) . '</div>',
				'fields'      => thim_widget_post_selector_form_fields(),
				'selector'    => thim_file_get_contents( plugin_dir_path( __FILE__ ) . 'tpl/posts-selector/selector.html' ),
			) );

			wp_localize_script( 'thim-widget-admin-posts-selector', 'obPostsSelectorVars', array(
				'modalTitle' => __( 'Select Posts', 'tp' ),
			) );
		}

		$this->enqueue_admin_scripts();
	}

	/**
	 * Checks if the current widget is using a posts selector
	 *
	 * @return bool
	 */
	function using_posts_selector() {
		foreach ( $this->form_options as $field ) {
			if ( !empty( $field['type'] ) && $field['type'] == 'posts' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Update the widget instance.
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array|void
	 */
	public function update( $new_instance, $old_instance ) {
		$new_instance = $this->sanitize( $new_instance, $this->form_options() );
		$this->delete_css( $this->modify_instance( $new_instance ) );

		return $new_instance;
	}

	/**
	 * Save the CSS to the filesystem
	 *
	 * @param $instance
	 *
	 * @return bool|string
	 */
	public function save_css( $instance ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		if ( WP_Filesystem() ) {
			global $wp_filesystem;
			$upload_dir = wp_upload_dir();

			if ( !$wp_filesystem->is_dir( $upload_dir['basedir'] . '/thim-widgets/' ) ) {
				$wp_filesystem->mkdir( $upload_dir['basedir'] . '/thim-widgets/' );
			}

			$style = $this->get_style_name( $instance );
			$hash  = $this->get_style_hash( $instance );

			$name = $this->id_base . '-' . $style . '-' . $hash . '.css';

			$css = $this->get_instance_css( $instance );

			if ( !empty( $css ) ) {
				$wp_filesystem->delete( $upload_dir['basedir'] . '/thim-widgets/' . $name );
				$wp_filesystem->put_contents(
					$upload_dir['basedir'] . '/thim-widgets/' . $name, $css
				);
			}

			return $hash;
		} else {
			return false;
		}
	}

	/**
	 * Clears CSS for a specific instance
	 */
	private function delete_css( $instance ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		if ( WP_Filesystem() ) {
			global $wp_filesystem;
			$upload_dir = wp_upload_dir();

			$style = $this->get_style_name( $instance );
			$hash  = $this->get_style_hash( $instance );
			$name  = $this->id_base . '-' . $style . '-' . $hash . '.css';

			$wp_filesystem->delete( $upload_dir['basedir'] . '/thim-widgets/' . $name );
		}
	}

	/**
	 * Clear all old CSS files
	 *
	 * @var bool $force Must we force a cache refresh.
	 */
	public static function clear_file_cache( $force_delete = false ) {
		// Use this variable to ensure this only runs once
		static $done = false;
		if ( $done && !$force_delete ) {
			return;
		}

		if ( !get_transient( 'ob:cleared' ) || $force_delete ) {

			require_once ABSPATH . 'wp-admin/includes/file.php';
			if ( WP_Filesystem() ) {
				global $wp_filesystem;
				$upload_dir = wp_upload_dir();

				$list = $wp_filesystem->dirlist( $upload_dir['basedir'] . '/thim-widgets/' );
				foreach ( $list as $file ) {
					if ( $file['lastmodunix'] < time() - self::$css_expire || $force_delete ) {
						// Delete the file
						$wp_filesystem->delete( $upload_dir['basedir'] . '/thim-widgets/' . $file['name'] );
					}
				}
			}

			set_transient( 'ob:cleared', true, self::$css_expire );
		}

		$done = true;
	}

	/**
	 * Generate the CSS for the widget.
	 *
	 * @param $instance
	 *
	 * @return string
	 */
	public function get_instance_css( $instance ) {
		if ( !class_exists( 'lessc' ) ) //require plugin_dir_path(__FILE__) . 'inc/lessc.inc.php';

		{
			$style_name = $this->get_style_name( $instance );
		}
		if ( empty( $style_name ) ) {
			return '';
		}

		$less = thim_file_get_contents( TP_THEME_THIM_DIR . 'inc/widgets/' . $this->id_base . '/style/' . $style_name . '.less' );

		$vars = $this->get_less_variables( $instance );
		if ( !empty( $vars ) ) {
			foreach ( $vars as $name => $value ) {
				if ( empty( $value ) ) {
					continue;
				}
				$less = preg_replace( '/\@' . preg_quote( $name ) . ' *\:.*?;/', '@' . $name . ': ' . $value . ';', $less );
			}
		}
		//get mixins
		$mixins = thim_file_get_contents( TP_THEME_THIM_DIR . 'less/mixins.less' );
		$less   = preg_replace( '/@import \".*mixins\";/', $mixins . "\n\n", $less );

		$style    = $this->get_style_name( $instance );
		$hash     = $this->get_style_hash( $instance );
		$css_name = $this->id_base . '-' . $style . '-' . $hash;

		$less = '.so-widget-' . $css_name . ' { ' . $less . ' } ';

		$c = new lessc();

		return $c->compile( $less );
	}

	/**
	 * @param $instance
	 * @param $fields
	 */
	public function sanitize( $instance, $fields = false ) {

		if ( $fields === false ) {
			$fields = $this->form_options();
		}

		foreach ( $fields as $name => $field ) {
			if ( empty( $instance[$name] ) ) {
				$instance[$name] = false;
			}

			switch ( $field['type'] ) {
				case 'select' :
				case 'radio' :
					$keys = array_keys( $field['options'] );
					if ( !in_array( $instance[$name], $keys ) ) {
						$instance[$name] = isset( $field['default'] ) ? $field['default'] : false;
					}
					break;

				case 'number' :
				case 'slider':
					$instance[$name] = (float) $instance[$name];
					break;

				case 'textarea':
					if ( empty( $field['allow_html_formatting'] ) ) {
						$instance[$name] = sanitize_text_field( $instance[$name] );
					} else {
						$instance[$name] = wp_kses( $instance[$name], $field['allow_html_formatting'] );
					}
					break;

				case 'text' :
					if ( empty( $field['allow_html_formatting'] ) ) {
						$instance[$name] = sanitize_text_field( $instance[$name] );
					} else {
						$instance[$name] = wp_kses( $instance[$name], $field['allow_html_formatting'] );
					}
					break;

				case 'color':
					if ( !preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $instance[$name] ) ) {
						$instance[$name] = false;
					}
					break;

				case 'media' :
					// Media values should be integer
					$instance[$name] = intval( $instance[$name] );
					break;

				case 'checkbox':
					$instance[$name] = !empty( $instance[$name] );
					break;

				case 'widget':
					if ( !empty( $field['class'] ) && class_exists( $field['class'] ) ) {
						$the_widget = new $field['class'];

						if ( is_a( $the_widget, 'SiteOrigin_Widget' ) ) {
							$instance[$name] = $the_widget->update( $instance[$name], $instance[$name] );
						}
					}
					break;

				case 'repeater':
					if ( !empty( $instance[$name] ) ) {
						foreach ( $instance[$name] as $i => $sub_instance ) {
							$instance[$name][$i] = $this->sanitize( $sub_instance, $field['fields'] );
						}
					}
					break;

				case 'section':
					$instance[$name] = $this->sanitize( $instance[$name], $field['fields'] );
					break;

				default:
					$instance[$name] = sanitize_text_field( $instance[$name] );
					break;
			}

			if ( isset( $field['sanitize'] ) ) {
				// This field also needs some custom sanitization
				switch ( $field['sanitize'] ) {
					case 'url':
						$instance[$name] = esc_url_raw( $instance[$name] );
						break;

					case 'email':
						$instance[$name] = sanitize_email( $instance[$name] );
						break;
				}
			}
		}

		return $instance;
	}

	/**
	 * @param        $field_name
	 * @param array  $repeater
	 * @param string $repeater_append
	 *
	 * @return mixed|string
	 */
	public function so_get_field_name( $field_name, $repeater = array(), $repeater_append = '[]' ) {
		if ( empty( $repeater ) ) {
			return $this->get_field_name( $field_name );
		} else {

			$repeater_extras = '';
			foreach ( $repeater as $r ) {
				$repeater_extras .= '[' . $r['name'] . ']';
				if( isset( $r['type'] ) && $r['type'] === 'repeater' )
				{
					$repeater_extras .= '[#' . $r['name'] . '#]';
				}
			}

			$name = $this->get_field_name( '{{{FIELD_NAME}}}' );

			$name = str_replace( '[{{{FIELD_NAME}}}]', $repeater_extras . '[' . esc_attr( $field_name ) . ']', $name );

			return $name;
		}
	}

	/**
	 * Get the ID of this field.
	 *
	 * @param         $field_name
	 * @param array   $repeater
	 * @param boolean $is_template
	 *
	 * @return string
	 */
	public function so_get_field_id( $field_name, $repeater = array(), $is_template = false ) {
		if ( empty( $repeater ) ) {
			return $this->get_field_id( $field_name );
		} else {
			// $name          = $repeater; fix
			$name = array();
			foreach ($repeater as $key => $val) {
				$name[] = $val['name'];
			}
			$name[]        = $field_name;
			$field_id_base = $this->get_field_id( implode( '-', $name ) );
			if ( $is_template ) {
				return $field_id_base . '-{id}';
			}
			if ( !isset( $this->field_ids[$field_id_base] ) ) {
				$this->field_ids[$field_id_base] = 1;
			}
			$curId = $this->field_ids[$field_id_base]++;

			return $field_id_base . '-' . $curId;
		}
	}

	/**
	 * Render a form field
	 *
	 * @param       $name
	 * @param       $field
	 * @param       $value
	 * @param array $repeater
	 */
	function render_field( $name, $field, $value, $repeater = array(), $is_template = false ) {
		if ( is_null( $value ) && isset( $field['default'] ) ) {
			$value = $field['default'];
		}
		$extra_class = '';
		if ( !empty( $field['class'] ) ) {
			$extra_class = $field['class'];
		}
		$wrapper_attributes = array(
			'class' => array(
				'thim-widget-field',
				'thim-widget-field-type-' . $field['type'],
				'thim-widget-field-' . $name,
				$extra_class
			)
		);

		if ( !empty( $field['state_name'] ) ) {
			$wrapper_attributes['class'][] = 'thim-widget-field-state-' . $field['state_name'];
		}
		if ( !empty( $field['hidden'] ) ) {
			$wrapper_attributes['class'][] = 'thim-widget-field-is-hidden';
		}
		if ( !empty( $field['optional'] ) ) {
			$wrapper_attributes['class'][] = 'thim-widget-field-is-optional';
		}
		$wrapper_attributes['class'] = implode( ' ', array_map( 'sanitize_html_class', $wrapper_attributes['class'] ) );

		if ( !empty( $field['state_emitter'] ) ) {
			// State emitters create new states for the form
			$wrapper_attributes['data-state-emitter'] = json_encode( $field['state_emitter'] );
		}

		if ( !empty( $field['state_handler'] ) ) {
			// State handlers decide what to do with form states
			$wrapper_attributes['data-state-handler'] = json_encode( $field['state_handler'] );
		}

		if ( !empty( $field['state_handler_initial'] ) ) {
			// Initial state handlers are only run when the form is first loaded
			$wrapper_attributes['data-state-handler-initial'] = json_encode( $field['state_handler_initial'] );
		}


		?>
		<div <?php foreach ( $wrapper_attributes as $attr => $attr_val ) {
			echo ent2ncr( $attr . '="' . esc_attr( $attr_val ) . '" ' );
		} ?>><?php

		$field_id = $this->so_get_field_id( $name, $repeater, $is_template );

		if ( $field['type'] != 'repeater' && $field['type'] != 'checkbox' && $field['type'] != 'separator' && !empty( $field['label'] ) ) {
			?>
			<label for="<?php echo esc_attr( $field_id ) ?>" class="thim-widget-field-label <?php if ( empty( $field['hide'] ) ) {
				echo 'thim-widget-section-visible';
			} ?>">
				<?php
				echo ent2ncr( $field['label'] );
				if ( !empty( $field['optional'] ) ) {
					echo ' <span class="field-optional">(' . __( 'Optional', 'tp' ) . ')</span>';
				}
				?>
			</label>
			<?php
		}

		switch ( $field['type'] ) {
			case 'text' :
				?>
				<input type="text" name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>" id="<?php echo esc_attr( $this->so_get_field_id( $name, $repeater ) ) ?>" value="<?php echo esc_attr( $value ) ?>" class="widefat thim-widget-input" /><?php
				break;

			case 'color' :
				?>
				<input type="text" name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>" id="<?php echo esc_attr( $field_id ) ?>" value="<?php echo esc_attr( $value ) ?>" class="widefat thim-widget-input thim-widget-input-color" /><?php
				break;

			case 'number' :
				?>
				<input type="number" name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>" id="<?php echo esc_attr( $this->so_get_field_id( $name, $repeater ) ) ?>" value="<?php echo esc_attr( $value ) ?>" class="widefat thim-widget-input thim-widget-input-number" /><?php
				if ( !empty( $field['suffix'] ) ) {
					echo ' (' . $field['suffix'] . ') ';
				}
				break;

			case 'radioimage' :
				foreach ( $field['options'] as $key => $imageURL ) {
					// Get the correct value, we might get a blank if index / value is 0
					if ( $value == '' ) {
						$value = $key;
					}
					?>
					<label class='tp-radio-image'>
						<input type="radio" name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>" value="<?php echo esc_attr( $key ) ?>" <?php
						checked( $value, $key );
						?>/>
						<img src="<?php echo esc_attr( $imageURL ) ?>" />
					</label>
					<?php
				}
				break;
			case 'textarea' :
			$this->so_get_field_name( $name, $repeater );
				?>
				<textarea type="text" name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater, $is_template ) ) ?>" id="<?php echo esc_attr( $this->so_get_field_id( $name, $repeater ) ) ?>" class="widefat thim-widget-input" rows="<?php echo !empty( $field['rows'] ) ? intval( $field['rows'] ) : 4 ?>"><?php echo esc_textarea( $value ) ?></textarea><?php
				break;

			case 'extra_textarea' :
				$param_value = str_replace( ",", "\n", esc_textarea( $value ) );
				?>
				<textarea class="widefat" id="<?php echo esc_attr( $this->so_get_field_id( $name, $repeater ) ) ?>" name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>" rows="6"><?php echo ent2ncr( $param_value ); ?></textarea>
				<?php
				break;
			case 'editor' :
				// The editor field doesn't actually work yet, this is just a placeholder
				?>
				<textarea type="text" name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>" id="<?php echo esc_attr( $this->so_get_field_id( $name, $repeater ) ) ?>" class="widefat thim-widget-input thim-widget-input-editor" rows="<?php echo !empty( $field['rows'] ) ? intval( $field['rows'] ) : 4 ?>"><?php echo esc_textarea( $value ) ?></textarea><?php
				break;
			case 'radio':
				?>
				<?php foreach ( $field['options'] as $k => $v ) : ?>
				<label for="<?php echo esc_attr( $this->so_get_field_id( $name, $repeater ) ) . '-' . $k ?>">
					<input type="radio" name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>" id="<?php echo esc_attr( $this->so_get_field_id( $name, $repeater ) ) . '-' . $k ?>" class="ob-widget-input" value="<?php echo esc_attr( $k ) ?>" <?php checked( $k, $value ) ?>> <?php echo esc_html( $v ) ?>
				</label>
			<?php endforeach; ?>
				<?php
				break;


			case 'slider':
				?>
				<div class="thim-widget-slider-value"><?php echo !empty( $value ) ? $value : 0 ?></div>
				<div class="thim-widget-slider-wrapper">
					<div class="thim-widget-value-slider"></div>
				</div>
				<input
					type="number"
					name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>"
					id="<?php echo esc_attr( $field_id ) ?>"
					value="<?php echo !empty( $value ) ? esc_attr( $value ) : 0 ?>"
					min="<?php echo isset( $field['min'] ) ? intval( $field['min'] ) : 0 ?>"
					max="<?php echo isset( $field['max'] ) ? intval( $field['max'] ) : 100 ?>"
					data-integer="<?php echo !empty( $field['integer'] ) ? 'true' : 'false' ?>" />
				<?php
				break;
			case 'select':
				?>
				<select name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>" id="<?php echo esc_attr( $field_id ) ?>" class="thim-widget-input thim-widget-select ob-widget-input">
					<?php
					if ( isset( $field['prompt'] ) ) {
						?>
						<option value="default" disabled="disabled" selected="selected"><?php echo esc_html( $field['prompt'] ) ?></option>
						<?php
					}
					?>
					<?php foreach ( $field['options'] as $key => $val ) : ?>
						<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $key, $value ) ?>><?php echo esc_html( $val ) ?></option>
					<?php endforeach; ?>
				</select>
				<?php
				break;

			case 'checkbox':
				?>
				<label for="<?php echo esc_attr( $field_id ) ?>">
					<input type="checkbox" name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>" id="<?php echo esc_attr( $field_id ) ?>" class="thim-widget-input" <?php checked( !empty( $value ) ) ?> />
					<?php echo ent2ncr( $field['label'] ) ?>
				</label>
				<?php
				break;

			case 'radio':
				?>
				<?php if ( !isset( $field['options'] ) || empty( $field['options'] ) ) {
				return;
			} ?>
				<?php foreach ( $field['options'] as $k => $v ) : ?>
				<label for="<?php echo esc_attr( $field_id ) . '-' . $k ?>">
					<input type="radio" name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>" id="<?php echo esc_attr( $field_id ) . '-' . $k ?>" class="thim-widget-input" value="<?php echo esc_attr( $k ) ?>" <?php checked( $k, $value ) ?>> <?php echo esc_html( $v ) ?>
				</label>
			<?php endforeach; ?>
				<?php
				break;

			case 'media':
				if ( version_compare( get_bloginfo( 'version' ), '3.5', '<' ) ) {
					printf( __( 'You need to <a href="%s">upgrade</a> to WordPress 3.5 to use media fields', 'tp' ), admin_url( 'update-core.php' ) );
					break;
				}
				if ( !empty( $value ) ) {
					if ( is_array( $value ) ) {
						$src = $value;
					} else {
						$post = get_post( $value );
						$src  = wp_get_attachment_image_src( $value, 'thumbnail' );
						if ( empty( $src ) ) {
							$src = wp_get_attachment_image_src( $value, 'thumbnail', true );
						}
					}
				} else {
					$src = array( '', 0, 0 );
				}

				$choose_title  = empty( $args['choose'] ) ? __( 'Choose Media', 'tp' ) : $args['choose'];
				$update_button = empty( $args['update'] ) ? __( 'Set Media', 'tp' ) : $args['update'];
				$library       = empty( $field['library'] ) ? 'image' : $field['library'];
				?>
				<div class="media-field-wrapper">
					<div class="current">
						<div class="thumbnail-wrapper">
							<img src="<?php echo esc_url( $src[0] ) ?>" class="thumbnail"
								<?php if ( empty( $src[0] ) ) {
									echo "style='display:none'";
								} ?> />
						</div>
						<div class="title"><?php if ( !empty( $post ) ) {
								echo esc_attr( $post->post_title );
							} ?></div>
					</div>
					<a href="#" class="media-upload-button" data-choose="<?php echo esc_attr( $choose_title ) ?>" data-update="<?php echo esc_attr( $update_button ) ?>" data-library="<?php echo esc_attr( $library ) ?>">
						<?php echo esc_html( $choose_title ) ?>
					</a>

					<a href="#" class="media-remove-button"><?php esc_attr_e( 'Remove', 'tp' ) ?></a>
				</div>

				<input type="hidden" value="<?php echo esc_attr( is_array( $value ) ? '-1' : $value ) ?>" name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>" class="thim-widget-input" />
				<div class="clear"></div>
				<?php
				break;
			case 'multimedia':
				if ( version_compare( get_bloginfo( 'version' ), '3.5', '<' ) ) {
					printf( __( 'You need to <a href="%s">upgrade</a> to WordPress 3.5 to use media fields', 'tp' ), admin_url( 'update-core.php' ) );
					break;
				}

				$data_img = "";
				if ( $value ) {
					$data = explode( ",", $value );
					if ( is_array( $data ) ) {
						foreach ( $data as $v ) {
							$post = get_post( $v );
							$src  = wp_get_attachment_image_src( $v, 'thumbnail' );
							if ( empty( $src ) ) {
								$src = wp_get_attachment_image_src( $v, 'thumbnail', true );
							}
							$data_img .= '<li id ="' . $v . '" class="current">
							<div class="thumbnail-wrapper">
								<img src="' . esc_url( $src[0] ) . '" class="thumbnail"/>
								<a href="#" class="multimedia-remove-button">x</a>
							</div>
						</li> ';
						}
					} else {
						$post = get_post( $data );
						$src  = wp_get_attachment_image_src( $data, 'thumbnail' );
						if ( empty( $src ) ) {
							$src = wp_get_attachment_image_src( $data, 'thumbnail', true );
						}
						$data_img .= '<li class="current">
							<div class="thumbnail-wrapper">
								<img src="' . esc_url( $src[0] ) . '" class="thumbnail"/>
								<a href="#" class="multimedia-remove-button">x</a>
							</div>
						</li> ';
					}

				} else {
					$data_img = "";
				}

				$choose_title  = empty( $args['choose'] ) ? __( 'Choose Media', 'tp' ) : $args['choose'];
				$update_button = empty( $args['update'] ) ? __( 'Set Media', 'tp' ) : $args['update'];
				$library       = empty( $field['library'] ) ? 'image' : $field['library'];
				?>
				<div class="multi-media-field-wrapper">

					<ul class="media-content">
						<?php echo ent2ncr( $data_img ); ?>
					</ul>
					<a href="#" class="media-upload-button" data-choose="<?php echo esc_attr( $choose_title ) ?>" data-update="<?php echo esc_attr( $update_button ) ?>" data-library="<?php echo esc_attr( $library ) ?>">
						<?php echo esc_html( $choose_title ) ?>
					</a>

				</div>

				<input type="hidden" value="<?php echo esc_attr( is_array( $value ) ? '-1' : $value ) ?>" name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>" class="thim-widget-input" />
				<div class="clear"></div>
				<?php
				break;

			case 'posts' :
				?>
				<input type="hidden" value="<?php echo esc_attr( is_array( $value ) ? '' : $value ) ?>" name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>" class="thim-widget-input" />
				<a href="#" class="ob-select-posts button button-secondary">
					<span class="ob-current-count"><?php echo thim_widget_post_selector_count_posts( is_array( $value ) ? '' : $value ) ?></span>
					<?php esc_attr_e( 'Build Posts Query', 'tp' ) ?>
				</a>
				<?php
				break;

			case 'repeater':
				if ( !isset( $field['fields'] ) || empty( $field['fields'] ) ) {
					return;
				}

				if( ! $repeater )
					$repeater = array();
				$repeater[] = array( 'name' => $name, 'type' => 'repeater' ); // instead of $repeater[] = $name
				$html = array();
				foreach ( $field['fields'] as $sub_field_name => $sub_field ) {
					ob_start();
					$this->render_field(
						$sub_field_name,
						$sub_field,
						isset( $value[$sub_field_name] ) ? $value[$sub_field_name] : null,
						$repeater,
						true
					);
					$html[] = ob_get_clean();
				}

				$this->repeater_html[$name] = implode( '' , $html);

				$item_label = isset( $field['item_label'] ) ? $field['item_label'] : null;
				if ( !empty( $item_label ) ) {
					// convert underscore naming convention to camelCase for javascript
					// and encode as json string
					$item_label = $this->underscores_to_camel_case( $item_label );
					$item_label = json_encode( $item_label );
				}
				$item_name = !empty( $field['item_name'] ) ? $field['item_name'] : __( 'Item', 'thim-widgets' );
				?>
				<div class="thim-widget-field-repeater" data-item-name="<?php echo esc_attr( $field['item_name'] ) ?>" data-repeater-name="<?php echo esc_attr( $name ) ?>" <?php echo !empty( $item_label ) ? 'data-item-label="' . esc_attr( $item_label ) . '"' : '' ?>>
					<div class="thim-widget-field-repeater-top">
						<div class="thim-widget-field-repeater-expend"></div>
						<h3><?php echo ent2ncr( $field['label'] ) ?></h3>
					</div>
					<div class="thim-widget-field-repeater-items">
						<?php
						if ( !empty( $value ) ) {
							foreach ( $value as $v ) {
								?>
								<div class="thim-widget-field-repeater-item ui-draggable">
									<div class="thim-widget-field-repeater-item-top">
										<div class="thim-widget-field-expand"></div>
										<div class="thim-widget-field-remove"></div>
										<h4><?php echo esc_html( $field['item_name'] ) ?></h4>
									</div>
									<div class="thim-widget-field-repeater-item-form">
										<?php
										foreach ( $field['fields'] as $sub_field_name => $sub_field ) {
											$this->render_field(
												$sub_field_name,
												$sub_field,
												isset( $v[$sub_field_name] ) ? $v[$sub_field_name] : null,
												$repeater
											);
										}
										?>
									</div>
								</div>
								<?php
							}
						}
						?>
					</div>
					<div class="thim-widget-field-repeater-add"><?php esc_attr_e( 'Add', 'tp' ) ?></div>
				</div>
				<?php
				break;
			case 'widget' :
				// Create the extra form entries
				$sub_widget = new $field['class'];
				?>
				<div class="thim-widget-section <?php if ( !empty( $field['hide'] ) ) {
				echo 'thim-widget-section-hide';
			} ?>"><?php
					$new = $repeater;
					$new[] = array( 'name' => $name );
				foreach ( $sub_widget->form_options() as $sub_name => $sub_field ) {
					// wrong
					// $this->render_field(
					// 	$sub_name,
					// 	$sub_field,
					// 	isset( $value[$sub_name] ) ? $value[$sub_name] : null,
					// 	$repeater
					// );

					$this->render_field(
						$sub_name,
						$sub_field,
						isset( $value[$sub_name] ) ? $value[$sub_name] : null,
						$new
					);
				}
				?></div><?php
				break;

			case 'icon' :
				$icons  = array(
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
				$output = '<div class="wrapper_icon"><input type="hidden" name="' . $this->so_get_field_name( $name, $repeater ) . '" class="wpb_vc_param_value" value="' . esc_attr( $value ) . '" id="trace"/>
					<div class="icon-preview"><i class=" fa fa-' . esc_attr( $value ) . '"></i></div>';
				$output .= '<input class="search" type="text" placeholder="Search" />';
				$output .= '<div id="icon-dropdown">';
				$output .= '<ul class="icon-list">';
				$n = 1;
				foreach ( $icons as $icon ) {
					$selected = ( $icon == esc_attr( $value ) ) ? 'class="selected"' : '';
					$output .= '<li ' . $selected . ' data-icon="' . $icon . '"><i class="icon fa fa-' . $icon . '"></i><label class="icon">' . $icon . '</label></li>';
					$n ++;
				}
				$output .= '</ul>';
				$output .= '</div></div>';
				$output .= '<script type="text/javascript">
                    jQuery(document).ready(function(){
                        jQuery(".search").keyup(function(){
                            // Retrieve the input field text and reset the count to zero
                            var filter = jQuery(this).val(), count = 0;
                            // Loop through the icon list
                            jQuery(".icon-list li").each(function(){
                                    // If the list item does not contain the text phrase fade it out
                                    if (jQuery(this).text().search(new RegExp(filter, "i")) < 0) {
                                            jQuery(this).fadeOut();
                                    } else {
                                            jQuery(this).show();
                                            count++;
                                    }
                            });
                        });
                    });

                    jQuery("#icon-dropdown li").click(function() {
                        jQuery(this).attr("class","selected").siblings().removeAttr("class");
                        var icon = jQuery(this).attr("data-icon");
                        jQuery(this).closest(".wrapper_icon").find(".wpb_vc_param_value").val(icon);
                        jQuery(this).closest(".wrapper_icon").find(".icon-preview").html("<i class=\'icon fa fa-"+icon+"\'></i>");
				});
				</script>';
				echo ent2ncr( $output );
				?>
				<?php
				break;
			case 'icon-7-stroke' :
				$icons  = array(
					'album',
					'arc',
					'back-2',
					'bandaid ',
					'car',
					'diamond',
					'door-lock',
					'eyedropper',
					'female',
					'gym',
					'hammer',
					'headphones',
					'helm',
					'hourglass',
					'leaf',
					'magic-wand',
					'male',
					'map-2',
					'next-2',
					'paint-bucket',
					'pendrive',
					'photo',
					'piggy',
					'plugin',
					'refresh-2',
					'rocket',
					'settings',
					'shield',
					'smile',
					'usb',
					'vector',
					'wine',
					'cloud-upload',
					'cash',
					'close',
					'bluetooth',
					'cloud-download',
					'way',
					'close-circle',
					'id',
					'angle-up',
					'wristwatch',
					'angle-up-circle',
					'world',
					'angle-right',
					'volume',
					'angle-right-circle',
					'users',
					'angle-left',
					'user-female',
					'angle-left-circle',
					'up-arrow',
					'angle-down',
					'switch',
					'angle-down-circle',
					'scissors',
					'wallet',
					'safe',
					'volume2',
					'volume1',
					'voicemail',
					'video',
					'user',
					'upload',
					'unlock',
					'umbrella',
					'trash',
					'tools',
					'timer',
					'ticket',
					'target',
					'sun',
					'study',
					'stopwatch',
					'star',
					'speaker',
					'signal',
					'shuffle',
					'shopbag',
					'share',
					'server',
					'search',
					'film',
					'science',
					'disk',
					'ribbon',
					'repeat',
					'refresh',
					'add-user',
					'refresh-cloud',
					'paperclip',
					'radio',
					'note2',
					'print',
					'network',
					'prev',
					'mute',
					'power',
					'medal',
					'portfolio',
					'like2',
					'plus',
					'left-arrow',
					'play',
					'key',
					'plane',
					'joy',
					'photo-gallery',
					'pin',
					'phone',
					'plug',
					'pen',
					'right-arrow',
					'paper-plane',
					'delete-user',
					'paint',
					'bottom-arrow',
					'notebook',
					'note',
					'next',
					'news-paper',
					'musiclist',
					'music',
					'mouse',
					'more',
					'moon',
					'monitor',
					'micro',
					'menu',
					'map',
					'map-marker',
					'mail',
					'mail-open',
					'mail-open-file',
					'magnet',
					'loop',
					'look',
					'lock',
					'lintern',
					'link',
					'like',
					'light',
					'less',
					'keypad',
					'junk',
					'info',
					'home',
					'help2',
					'help1',
					'graph3',
					'graph2',
					'graph1',
					'graph',
					'global',
					'gleam',
					'glasses',
					'gift',
					'folder',
					'flag',
					'filter',
					'file',
					'expand1',
					'exapnd2',
					'edit',
					'drop',
					'drawer',
					'download',
					'display2',
					'display1',
					'diskette',
					'date',
					'cup',
					'culture',
					'crop',
					'credit',
					'copy-file',
					'config',
					'compass',
					'comment',
					'coffee',
					'cloud',
					'clock',
					'check',
					'chat',
					'cart',
					'camera',
					'call',
					'calculator',
					'browser',
					'box2',
					'box1',
					'bookmarks',
					'bicycle',
					'bell',
					'battery',
					'ball',
					'back',
					'attention',
					'anchor',
					'albums',
					'alarm',
					'airplay'
				);
				$output = '<div class="wrapper_icon"><input type="hidden" name="' . $this->so_get_field_name( $name, $repeater ) . '" class="wpb_vc_param_value" value="' . esc_attr( $value ) . '" id="trace"/>
					<div class="icon-preview"><span class="pe-7s-' . esc_attr( $value ) . '"></span></div>';
				$output .= '<input class="search" type="text" placeholder="Search" />';
				$output .= '<div id="icon-dropdown_1">';
				$output .= '<ul class="icon-list">';
				$n = 1;
				foreach ( $icons as $icon ) {
					$selected = ( $icon == esc_attr( $value ) ) ? 'class="selected"' : '';
					$output .= '<li ' . $selected . ' data-icon="' . $icon . '"><span class="pe-7s-' . $icon . '"></span><label class="icon">' . $icon . '</label></li>';
					$n ++;
				}
				$output .= '</ul>';
				$output .= '</div></div>';
				$output .= '<script type="text/javascript">
                    jQuery(document).ready(function(){
                        jQuery(".search").keyup(function(){
                            // Retrieve the input field text and reset the count to zero
                            var filter = jQuery(this).val(), count = 0;
                            // Loop through the icon list
                            jQuery(".icon-list li").each(function(){
                                    // If the list item does not contain the text phrase fade it out
                                    if (jQuery(this).text().search(new RegExp(filter, "i")) < 0) {
                                            jQuery(this).fadeOut();
                                    } else {
                                            jQuery(this).show();
                                            count++;
                                    }
                            });
                        });
                    });

                    jQuery("#icon-dropdown_1 li").click(function() {
                        jQuery(this).attr("class","selected").siblings().removeAttr("class");
                        var icon = jQuery(this).attr("data-icon");
                        jQuery(this).closest(".wrapper_icon").find(".wpb_vc_param_value").val(icon);
                        jQuery(this).closest(".wrapper_icon").find(".icon-preview").html("<span class=\'pe-7s-"+icon+"\'></span>");
				});
				</script>';
				echo ent2ncr( $output );
				?>
				<?php
				break;

			case 'section' :
				?>
				<div class="thim-widget-section <?php if ( !empty( $field['hide'] ) ) {
				echo 'thim-widget-section-hide';
			} ?>"><?php
				if ( !isset( $field['fields'] ) || empty( $field['fields'] ) ) {
					return;
				}

				foreach ( (array) $field['fields'] as $sub_name => $sub_field ) {
					// wrong
					// $this->render_field(
					// 	$sub_name,
					// 	$sub_field,
					// 	isset( $value[$sub_name] ) ? $value[$sub_name] : null,
					// 	$repeater,
					// 	false
					// );

					$new = $repeater;
					$new[] = array( 'name' => $name );
					$this->render_field(
						$sub_name,
						$sub_field,
						isset( $value[$sub_name] ) ? $value[$sub_name] : null,
						$new,
						false
					);
				}
				?></div><?php
				break;

			case 'bucket' :
				// A bucket select and explore field
				?>
				<input type="text" name="<?php echo esc_attr( $this->so_get_field_name( $name, $repeater ) ) ?>" id="<?php echo esc_attr( $field_id ) ?>" value="<?php echo esc_attr( $value ) ?>" class="widefat thim-widget-input" /><?php
				break;

			default:
				?><?php esc_attr_e( 'Unknown Field', 'tp' ) ?><?php
				break;
		}

		if ( !empty( $field['description'] ) ) {
			?>
			<div class="thim-widget-field-description"><?php echo esc_html( $field['description'] ) ?></div><?php
		}
		?></div><?php
	}

	/**
	 * Parse markdown
	 *
	 * @param $markdown
	 *
	 * @return string The HTML
	 */
	function parse_markdown( $markdown ) {
		if ( !class_exists( 'Markdown_Parser' ) ) {
			include plugin_dir_path( __FILE__ ) . 'inc/markdown.php';
		}
		$parser = new Markdown_Parser();

		return $parser->transform( $markdown );
	}

	/**
	 * Get a hash that makes the design unique
	 *
	 * @param $instance
	 *
	 * @return string
	 */
	function get_style_hash( $instance ) {
		return substr( md5( serialize( $this->get_less_variables( $instance ) ) ), 0, 12 );
	}

	/**
	 * Get the template name that we'll be using to render this widget.
	 *
	 * @param $instance
	 *
	 * @return mixed
	 */
	abstract function get_template_name( $instance );

	/**
	 * Get the template name that we'll be using to render this widget.
	 *
	 * @param $instance
	 *
	 * @return mixed
	 */
	abstract function get_style_name( $instance );

	/**
	 * Get any variables that need to be substituted by
	 *
	 * @param $instance
	 *
	 * @return array
	 */
	function get_less_variables( $instance ) {
		return array();
	}

	/**
	 * This function can be overwritten to modify form values in the child widget.
	 *
	 * @param $form
	 *
	 * @return mixed
	 */
	function modify_form( $form ) {
		return $form;
	}

	/**
	 * This function should be overwritten by child widgets to filter an instance. Run before rendering form and widget.
	 *
	 * @param $instance
	 *
	 * @return mixed
	 */
	function modify_instance( $instance ) {
		return $instance;
	}

	/**
	 * Can be overwritten by child themes to enqueue scripts and styles for the frontend
	 */
	function enqueue_frontend_scripts() {

	}

	/**
	 * Enqueue all the registered scripts
	 */
	function enqueue_registered_scripts() {
		foreach ( $this->frontend_scripts as $f_script ) {
			if ( !wp_script_is( $f_script[0] ) ) {
				wp_enqueue_script(
					$f_script[0],
					isset( $f_script[1] ) ? $f_script[1] : false,
					isset( $f_script[2] ) ? $f_script[2] : array(),
					isset( $f_script[3] ) ? $f_script[3] : false,
					isset( $f_script[4] ) ? $f_script[4] : false
				);
			}
		}
	}

	/**
	 * Used by child widgets to register styles to be enqueued for the frontend.
	 *
	 * @param array $styles an array of styles. Each element is an array that corresponds to wp_enqueue_style arguments
	 */

	public function register_frontend_styles( $styles ) {
		foreach ( $styles as $style ) {
			if ( !isset( $this->frontend_styles[$style[0]] ) ) {
				$this->frontend_styles[$style[0]] = $style;
			}
		}
	}

	/**
	 * Enqueue any frontend styles that were registered
	 */
	function enqueue_registered_styles() {
		foreach ( $this->frontend_styles as $f_style ) {
			if ( !wp_style_is( $f_style[0] ) ) {
				wp_enqueue_style(
					$f_style[0],
					isset( $f_style[1] ) ? $f_style[1] : false,
					isset( $f_style[2] ) ? $f_style[2] : array(),
					isset( $f_style[3] ) ? $f_style[3] : false,
					isset( $f_style[4] ) ? $f_style[4] : "all"
				);
			}
		}
	}

	function enqueue_instance_frontend_scripts( $instance ) {
		$this->enqueue_registered_scripts();
		$this->enqueue_registered_styles();

		// Give plugins a chance to enqueue additional frontend scripts
		do_action( 'thim_widgets_enqueue_frontend_scripts_' . $this->id_base, $instance, $this );
	}

	/**
	 * Can be overwritten by child widgets to enqueue admin scripts and styles if necessary.
	 */
	function enqueue_admin_scripts() {

	}

	/**
	 * Initialize this widget in whatever way we need to. Run before rendering widget or form.
	 */
	function initialize() {

	}

}

/**
 * Register a plugin
 *
 * @param $name
 * @param $path
 */
function thim_widget_register_self( $name, $path ) {
	global $thim_widgets_registered;
	$thim_widgets_registered[$name] = realpath( $path );
}

/**
 * Get the base file of a widget plugin
 *
 * @param $name
 *
 * @return bool
 */
function thim_widget_get_plugin_path( $name ) {
	global $thim_widgets_registered;

	return isset( $thim_widgets_registered[$name] ) ? $thim_widgets_registered[$name] : false;
}

/**
 * Get the base path folder of a widget plugin.
 *
 * @param $name
 *
 * @return string
 */
function thim_widget_get_plugin_dir_path( $name ) {
	if ( strpos( $name, 'ob-' ) === 0 ) {
		$name = substr( $name, 4 );
	} // Handle raw widget IDs, assuming they're prefixed with ob-
	return plugin_dir_path( thim_widget_get_plugin_path( $name ) );
}

/**
 * Get the base path URL of a widget plugin.
 *
 * @param $name
 *
 * @return string
 */
function thim_widget_get_plugin_dir_url( $name ) {
	return plugin_dir_url( thim_widget_get_plugin_path( $name ) );
}

/**
 * Render a preview of the widget.
 */
function thim_widget_render_preview() {
	$class = $_GET['class'];
	if ( isset( $_POST['widgets'] ) ) {
		$instance = array_pop( $_POST['widgets'] );
	} else {

		foreach ( $_POST as $n => $v ) {
			if ( strpos( $n, 'widget-' ) === 0 ) {
				$instance = array_pop( $_POST[$n] );
				break;
			}
		}
	}

	if ( !class_exists( $class ) ) {
		exit();
	}
	$widget_obj = new $class();
	if ( !$widget_obj instanceof SiteOrigin_Widget ) {
		exit();
	}

	$instance               = $widget_obj->update( $instance, $instance );
	$instance['style_hash'] = 'preview';
	include plugin_dir_path( __FILE__ ) . '/inc/preview.tpl.php';
	exit();
}

add_action( 'wp_ajax_thim_widget_preview', 'thim_widget_render_preview' );

/**
 * @param $css
 */
function thim_widget_add_inline_css( $css ) {
	global $thim_widgets_inline_styles;
	if ( empty( $thim_widgets_inline_styles ) ) {
		$thim_widgets_inline_styles = '';
	}

	$thim_widgets_inline_styles .= $css;
}

/**
 * Print any inline styles that have been added with thim_widget_add_inline_css
 */
function thim_widget_print_styles() {
	global $thim_widgets_inline_styles;
	if ( !empty( $thim_widgets_inline_styles ) ) {
		?>
		<style type="text/css"><?php echo( $thim_widgets_inline_styles ) ?></style><?php
	}

	$thim_widgets_inline_styles = '';
}

add_action( 'wp_head', 'thim_widget_print_styles' );
add_action( 'wp_footer', 'thim_widget_print_styles' );
function thim_widget_preview_widget_action() {
	if ( !class_exists( $_POST['class'] ) ) {
		exit();
	}
	$widget = new $_POST['class'];
	if ( !is_a( $widget, 'SiteOrigin_Widget' ) ) {
		exit();
	}

	$instance               = json_decode( stripslashes_deep( $_POST['data'] ), true );
	$instance['is_preview'] = true;

	// The theme stylesheet will change how the button looks
	wp_enqueue_style( 'theme-css', get_stylesheet_uri(), array(), rand( 0, 65536 ) );
	wp_enqueue_style( 'so-widget-preview', plugin_dir_url( __FILE__ ) . '/css/preview.css', array(), rand( 0, 65536 ) );

	$widget->widget( array(
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '',
		'after_title'   => '',
	), $instance );

	// Print all the scripts and styles
	wp_print_scripts();
	wp_print_styles();
	thim_widget_print_styles();
	?>
	<script type="text/javascript">
		if (typeof jQuery != 'undefined') {
			// So that the widget still has access to the document ready event.
			jQuery(document).ready();
		}
	</script>
	<?php
	exit();
}

add_action( 'wp_ajax_so_widgets_preview', 'thim_widget_preview_widget_action' );

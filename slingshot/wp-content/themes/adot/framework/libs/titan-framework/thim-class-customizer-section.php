<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of newPHPClass
 *
 * @author Tuannv
 */
class ThimCustomizerSection extends TitanFrameworkThemeCustomizerSection {
	private $defaultSettings = array(
		'name'       => '', // Name of the menu item
		// 'parent' => null, // slug of parent, if blank, then this is a top level menu
		'id'         => '', // Unique ID of the menu item
		'capability' => 'edit_theme_options', // User role
		// 'icon' => 'dashicons-admin-generic', // Menu icon for top level menus only
		'desc'       => '', // Description
		'position'   => 30 // Menu position for top level menus only
	);
	public $settings;
	public $options = array();
	public $owner;

	public $set; // parent section
	public $subSec = array(); // subsection
	// Makes sure we only load live previewing CSS only once
	private static $generatedHeadCSSPreview = false;

	function __construct( $settings, $owner ) {
		$this->owner = $owner;

		$this->set = array_merge( $this->defaultSettings, $settings );

		if (empty($this->set['name'])) {
            $this->set['name'] = __("More Options", 'thim' );
        }

        if (empty($this->set['id'])) {
            $this->set['id'] = str_replace(' ', '-', trim(strtolower($this->set['name'])));
        }

		add_action( 'customize_register', array( $this, 'register' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'loadUploaderScript' ) );
	}

	public function loadUploaderScript() {
		wp_enqueue_media();
		wp_enqueue_script( 'tf-theme-customizer-admin', TitanFramework::getURL( 'js/admin.js', __FILE__ ) );
		wp_enqueue_style( 'tf-admin-theme-customizer-styles', TitanFramework::getURL( 'css/admin.css', __FILE__ ) );
		wp_enqueue_script( 'tf-theme-customizer-jquery.fileDownload', TitanFramework::getURL( 'js/jquery.fileDownload.js', __FILE__ ) );


		wp_enqueue_script( 'tf-theme-customizer-serialize', TitanFramework::getURL( 'js/serialize.js', __FILE__ ) );
		wp_enqueue_style( 'tf-admin-theme-customizer-styles', TitanFramework::getURL( 'css/admin-theme-customizer-styles.css', __FILE__ ) );
	}

	public function getID() {
		return $this->settings['id'];
	}

	public function livePreview() {
		?>
		<script>
			jQuery(document).ready(function ($) {
				<?php
				foreach ($this->options as $option):
					if (empty($option->settings['livepreview'])):
						continue;
					endif;
					?>
				wp.customize('<?php echo esc_attr($option->getID()) ?>', function (v) {
					v.bind(function (value) {
						<?php
						// Some options may want to insert custom jQuery code before manipulation of live preview
						if (!empty($option->settings['id'])) {
							do_action('tf_livepreview_pre_' . $this->owner->optionNamespace, $option->settings['id'], $option->settings['type'], $option);
						}

						echo ent2ncr($option->settings['livepreview']);
						?>
					});
				});
				<?php
			endforeach;
			?>
			});
		</script>
	<?php
	}

	/**
	 * Prints out CSS styles for refresh previewing
	 *
	 * @return  void
	 * @since   1.3
	 */
	public function printPreviewCSS() {
		if ( self::$generatedHeadCSSPreview ) {
			return;
		}
		self::$generatedHeadCSSPreview = true;
		echo "<style>" . $this->owner->cssInstance->generateCSS() . "</style>";
	}

    /**
     * Customize Register
     *
     * @return  void
     */
	public function register( $wp_customize ) {
		add_action( 'wp_head', array( $this, 'printPreviewCSS' ), 1000 );
		$icon = "";
		if (empty($this->subSec)) {
			$this->settings = $this->set;
			if (isset($this->set['icon'])) {
				$icon .= '<i class="customizer-icon fa fa-fw fa-lg '.$this->set['icon'].'"></i>';
			}
			$wp_customize->add_section($this->set['id'], array(
	            'title' => $this->set['name'],
	            'priority' => $this->set['position'],
	            'description' => $icon.$this->set['desc'],
	            'capability' => $this->set['capability'],
	        ));

	        // Unfortunately we have to call each option's register from here
	        foreach ($this->options as $index => $option) {
	            if (!empty($option->settings['id'])) {
	                $wp_customize->add_setting($option->getID(), array(
	                    'default' => $option->settings['default'],
	                    'transport' => empty($option->settings['livepreview']) ? 'refresh' : 'postMessage',
	                    'sanitize_callback' => array($this, 'sanitizeLayout'),
	                ));
	            }

	            // We add the index here, this will be used to order the controls because of this minor bug:
	            // https://core.trac.wordpress.org/ticket/20733
	            $option->registerCustomizerControl($wp_customize, $this, $index + 1);
	        }
		}else {
			if (isset($this->set['icon'])) {
				$icon .= '<i class="customizer-icon fa fa-fw fa-lg '.$this->set['icon'].'"></i>';
			}
			$wp_customize->add_panel( $this->set['id'], array(
				'priority'       => $this->set['position'],
				'capability'     => $this->set['capability'],
				'theme_supports' => '',
				'title'          => $this->set['name'],
				'description'    => $icon.$this->set['desc'],
			) );

			foreach ( $this->subSec as $sindex => $soption ) {
				$this->settings = $soption->settings;
				$sets           = array();
				$sets           = $soption->settings;
				$wp_customize->add_section( $sets['id'], array(
					'title'       => $sets['name'],
					'priority'    => $sets['position'],
					'description' => $sets['desc'],
					'capability'  => $sets['capability'],
					'panel'       => $this->set['id'],
				) );

				// Unfortunately we have to call each option's register from here
				foreach ( $this->options as $index => $option ) {
					if ( $option->settings['sub'] == $this->settings['id'] ) {
						if ( ! empty( $option->settings['id'] ) ) {
							$wp_customize->add_setting( $option->getID(), array(
								'default'   => $option->settings['default'],
								'transport' => empty( $option->settings['livepreview'] ) ? 'refresh' : 'postMessage',
								'sanitize_callback' => array($this, 'sanitizeLayout'),
							) );
						}

						// We add the index here, this will be used to order the controls because of this minor bug:
						// https://core.trac.wordpress.org/ticket/20733
						$option->registerCustomizerControl( $wp_customize, $this, $index + 1 );
					}
				}
			}
		}
		add_action( 'wp_footer', array( $this, 'livePreview' ) );
	}

	public function createOption( $settings ) {
		if (isset($this->subID)) {
        	$settings['sub'] = $this->subID;
		}
		if ( ! apply_filters( 'tf_create_option_continue_' . $this->owner->optionNamespace, true, $settings ) ) {
			return null;
		}

		$obj             = TitanFrameworkOption::factory( $settings, $this );
		$this->options[] = $obj;

		do_action( 'tf_create_option_' . $this->owner->optionNamespace, $obj );

		return $obj;
	}

	/**
     * Store Subsection
     *
     * @return  void
     */
	public function addSubSection( $settings ) {
        $this->subID = $settings['id'];
		$settings['capability'] = 'edit_theme_options';

		if ( ! apply_filters( 'tf_create_option_continue_' . $this->owner->optionNamespace, true, $settings ) ) {
			return null;
		}

		$objx           = TitanFrameworkOption::factory( $settings, $this );
		$this->subSec[] = $objx;

		do_action( 'tf_create_option_' . $this->owner->optionNamespace, $objx );

		return $objx;
	}

	public function sanitizeLayout( $value ) {
	    return $value;
	}
}

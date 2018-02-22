<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class TitanFrameworkOptionColorOpacity extends TitanFrameworkOption {

    private static $firstLoad = true;
    public $defaultSecondarySettings = array(
        'placeholder' => '', // show this when blank
    );

    /*
     * Display for options and meta
     */

    public function display() {
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');

        $this->echoOptionHeader();
        ?>
        <input class="thim-color-control color-picker-hex wp-color-picker" type="text" name="<?php echo esc_attr($this->getID() )?>" id="<?php echo esc_attr($this->getID() )?>" value="<?php echo ent2ncr($this->getValue()) ?>"  data-default-color="<?php echo ent2ncr($this->getValue()) ?>"/>
        <?php
        // load the javascript to init the colorpicker
        if (self::$firstLoad):
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    "use strict";
                    $('.tf-colorpicker').wpColorPicker();
                });
            </script>
            <?php
        endif;

        $this->echoOptionFooter();

        self::$firstLoad = false;
    }

    /*
     * Display for theme customizer
     */

    public function registerCustomizerControl($wp_customize, $section, $priority = 1) {
        // $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $this->getID(), array(
        $wp_customize->add_control(new TitanFrameworkOptionColorOpacityControl($wp_customize, $this->getID(), array(
            'label' => $this->settings['name'],
            'section' => $section->getID(),
            'settings' => $this->getID(),
            'description' => $this->settings['desc'],
            'priority' => $priority,
        )));
    }

}

/*
 * We create a new control for the theme customizer
 */
add_action('customize_register', 'registerTitanFrameworkOptionColorOpacityControl', 1);

function registerTitanFrameworkOptionColorOpacityControl() {

	class TitanFrameworkOptionColorOpacityControl extends WP_Customize_Control {
		public $description;
		public function render_content() {
			$id = 'customize-control-' . str_replace( '[', '-', str_replace( ']', '', $this->id ) );
			$class = 'customize-control customize-control-color' ?>
			<label>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<input type="text" data-default-color="<?php echo ent2ncr($this->value()) ?>" value="<?php echo ent2ncr($this->value()); ?>" class="thim-color-control color-picker-hex wp-color-picker" <?php $this->link(); ?>  />
			</label>
		<?php
			echo ent2ncr($this->description);
		}
	}
}

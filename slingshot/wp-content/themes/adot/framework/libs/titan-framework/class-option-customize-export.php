<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class TitanFrameworkOptionCustomizeExport extends TitanFrameworkOption {
    /*
     * Display for theme customizer
     */

    public function registerCustomizerControl($wp_customize, $section, $priority = 1) {
        $wp_customize->add_control(new TitanFrameworkOptionCustomizeExportControl($wp_customize, $this->getID(), array(
            'name' => $this->settings['name'],
            'label' => $this->settings['name'],
            'section' => $section->settings['id'],
            'settings' => $this->getID(),
            'type' => 'customizeexport',
            'description' => $this->settings['desc'],
            'priority' => $priority,
        )));
    }
}

/*
 * WP_Customize_Control with description
 */
add_action('customize_register', 'registerTitanFrameworkOptionCustomizeExportControl', 1);

function registerTitanFrameworkOptionCustomizeExportControl() {

    class TitanFrameworkOptionCustomizeExportControl extends WP_Customize_Control {

        public $description;
        public $is_code;

        public function render_content() {
            ?>
            <label>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
                <a href="#" id="thim-customizer-settings-download" class="button-primary export-customize-settings"><?php echo esc_html($this->label); ?></a>
            </label>
            <?php
            echo "<p class='description'>{$this->description}</p>";
        }

    }

}
    
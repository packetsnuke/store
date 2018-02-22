<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class TitanFrameworkOptionCustomizeImport extends TitanFrameworkOption {
    /*
     * Display for theme customizer
     */

    public function registerCustomizerControl($wp_customize, $section, $priority = 1) {
        $wp_customize->add_control(new TitanFrameworkOptionCustomizeImportControl($wp_customize, $this->getID(), array(
            'name' => $this->settings['name'],
            'label' => $this->settings['name'],
            'section' => $section->settings['id'],
            'settings' => $this->getID(),
            'type' => 'customizeimport',
            'description' => $this->settings['desc'],
            'priority' => $priority,
        )));
    }
}

/*
 * WP_Customize_Control with description
 */
add_action('customize_register', 'registerTitanFrameworkOptionCustomizeImportControl', 1);

function registerTitanFrameworkOptionCustomizeImportControl() {

    class TitanFrameworkOptionCustomizeImportControl extends WP_Customize_Control {

        public $description;
        public $is_code;

        public function render_content() {
            ?>
            <label>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
               <!--  <input style="position:absolute;z-index:-1;" type="file" name="import-customize" class="import-customize-settings"> -->
                <a href="#" id="import-customize-settings" class="button-primary"><?php echo esc_html($this->label); ?></a>
            </label>
            <?php
            echo "<p class='description'>{$this->description}</p>";
        }
    }
}
    
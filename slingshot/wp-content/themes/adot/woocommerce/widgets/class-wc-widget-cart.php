<?php
/**
 * Shopping Cart Widget
 *
 * Displays shopping cart widget
 *
 * @author        WooThemes
 * @category      Widgets
 * @package       WooCommerce/Widgets
 * @version       2.0.0
 * @extends       WP_Widget
 */

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class Custom_WC_Widget_Cart extends WC_Widget
{

    var $woo_widget_cssclass;
    var $woo_widget_description;
    var $woo_widget_idbase;
    var $woo_widget_name;

    function __construct()
    {
        $this->widget_cssclass    = 'woocommerce widget_shopping_cart';
        $this->widget_description = __( "Display the user's cart in the sidebar.", 'adot' );
        $this->widget_id          = 'woocommerce_widget_cart';
        $this->widget_name        = __( 'WooCommerce cart', 'adot' );
        //Constructor
        add_action('load-widgets.php', array(&$this, 'thim_script'));
        parent::__construct();
    }

    function thim_script()
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }

    function widget($args, $instance)
    {
        extract($args);

        if (is_cart() || is_checkout()) {
            return;
        }
        $style = $style_css = $class = $style_arrow = $background_color = $icon_color = $title = $hide_if_empty = '';
        $background_color = $instance['background_color'];
        $icon_color = $instance['icon_color'];
        $style .= ($background_color != '') ? 'background: ' . $background_color . ';' : 'background:#1dae84;';
        $style .= ($icon_color != '') ? 'color: ' . $icon_color : 'color:#fff;';
        $style_css .= ($style != '') ? ' style="' . $style . '"' : '';
        $style_arrow = ($background_color != '') ? 'style="border-bottom: 6px solid  ' . $background_color . ';"' : '';
        $bg_color_icon = empty($instance['bg_color_icon']) ? 0 : 1;
        if ($bg_color_icon == 1) {
            $class = ' background-primary';
        }
        if ($style) {
            $title = apply_filters('widget_title', empty($instance['title']) ? __('Cart', 'woocommerce') : $instance['title'], $instance, $this->id_base);
        }
        $hide_if_empty = empty($instance['hide_if_empty']) ? 0 : 1;

        echo $before_widget;
        if ($title) {
            echo '<div class="minicart_hover" id="header-mini-cart">';
        }
        list($cart_items) = thim_get_current_cart_info();
        echo $before_title . '<span class="cart-items-number' . $class . '"><i class="fa fa-fw fa-shopping-cart"></i><span class="wrapper-items-number" ' . $style_css . '><i class="icon-arrow" ' . $style_arrow . '></i><span class="items-number">' . $cart_items . '</span></span></span>' . $after_title;

        echo '<div class="clear"></div></div>';
        if ($hide_if_empty) {
            echo '<div class="hide_cart_widget_if_empty">';
        }
        // Insert cart widget placeholder - code in woocommerce.js will update this on page load
        echo '<div class="widget_shopping_cart_content" style="display: none;"></div>';
        if ($hide_if_empty) {
            echo '</div>';
        }
        echo $after_widget;
    }


    /**
     * update function.
     *
     * @see    WP_Widget->update
     * @access public
     *
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    function update($new_instance, $old_instance)
    {
        $instance['background_color'] = $new_instance['background_color'];
        $instance['icon_color'] = $new_instance['icon_color'];
        $instance['title'] = strip_tags(stripslashes($new_instance['title']));
        $instance['hide_if_empty'] = empty($new_instance['hide_if_empty']) ? 0 : 1;
        $instance['bg_color_icon'] = empty($new_instance['bg_color_icon']) ? 0 : 1;

        return $instance;
    }


    /**
     * form function.
     *
     * @see    WP_Widget->form
     * @access public
     *
     * @param array $instance
     *
     * @return void
     */
    function form($instance)
    {
        $hide_if_empty = empty($instance['hide_if_empty']) ? 0 : 1;
        $bg_color_icon = empty($instance['bg_color_icon']) ? 0 : 1;
        $background_color = isset($instance['background_color']) ? $instance['background_color'] : "";
        $icon_color = isset($instance['icon_color']) ? $instance['icon_color'] : "";
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'woocommerce') ?></label>
            <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   value="<?php if (isset ($instance['title'])) {
                       echo esc_attr($instance['title']);
                   } ?>"/></p>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('.thim-picker-color').wpColorPicker();
                $("body").bind("ajaxComplete", function () {
                    $('.thim-picker-color').wpColorPicker();
                });
            });
        </script>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo esc_attr($this->get_field_id('bg_color_icon')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('bg_color_icon')); ?>"<?php checked($bg_color_icon); ?> />
            <label for="<?php echo $this->get_field_id('bg_color_icon'); ?>"><?php _e('Using Primary color for Background Widget', 'thim'); ?></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('background_color'); ?>"
                   style="vertical-align: top; width: 120px; display: inline-block;"><?php _e('Background Number', 'thim'); ?></label>
            <input class="thim-picker-color" type="text" id="<?php echo $this->get_field_id('background_color'); ?>"
                   name="<?php echo $this->get_field_name('background_color'); ?>"
                   value="<?php echo esc_attr($background_color); ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('icon_color'); ?>"
                   style="vertical-align: top; width: 120px;  display: inline-block;"><?php _e('Color Number', 'thim'); ?></label>
            <input class="thim-picker-color" type="text" id="<?php echo $this->get_field_id('icon_color'); ?>"
                   name="<?php echo $this->get_field_name('icon_color'); ?>"
                   value="<?php echo esc_attr($icon_color); ?>"/>
        </p>

        <p>
            <input type="checkbox" class="checkbox" id="<?php echo esc_attr($this->get_field_id('hide_if_empty')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('hide_if_empty')); ?>"<?php checked($hide_if_empty); ?> />
            <label for="<?php echo $this->get_field_id('hide_if_empty'); ?>"><?php _e('Hide if cart is empty', 'woocommerce'); ?></label>
        </p>

        <?php
    }

}
<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SOD_Widget_Ajax_Layered_Nav_Clear extends WP_Widget {

    var $woo_widget_cssclass;
    var $woo_widget_description;
    var $woo_widget_idbase;
    var $woo_widget_name;

    /**
     * constructor
     *
     * @access public
     * @return void
     */
    function SOD_Widget_Ajax_Layered_Nav_Clear() {

        /* Widget variable settings. */
        $this->woo_widget_cssclass      = 'woocommerce widget_ajax_layered_nav_clear widget_layered_nav_clear widget_layered_nav_filters';
        $this->woo_widget_description   = __( 'Displays a "Clear All" Link. Should be used with the Ajax Layered Nav.', 'woocommerce' );
        $this->woo_widget_idbase        = 'sod_ajax_layered_nav_clear';
        $this->woo_widget_name          = __( 'WooCommerce Ajax Layered Nav Clear All', 'woocommerce' );
        $this->settings           = array(
            'title'  => array(
                'type'  => 'text',
                'std'   => __( 'Clear All Filters', 'woocommerce' ),
                'label' => __( 'Title', 'woocommerce' )
            ),
            'link_text'  => array(
                'type'  => 'text',
                'std'   => __( 'Clear All', 'woocommerce' ),
                'label' => __( 'Link Text', 'woocommerce' )
            )
        );
        /* Widget settings. */
        $widget_ops = array( 'classname' => $this->woo_widget_cssclass, 'description' => $this->woo_widget_description );

        /* Create the widget. */
        parent::__construct( 'sod_ajax_layered_nav_clear', $this->woo_widget_name, $widget_ops );
    }

    /**
     * widget function.
     *
     * @see WP_Widget
     * @access public
     * @param array $args
     * @param array $instance
     * @return void
     */
    function widget( $args, $instance ) {
        global $_chosen_attributes, $woocommerce;

        extract( $args );

         if ( ! is_post_type_archive( 'product' ) && ! is_tax( get_object_taxonomies( 'product' ) ) )
            return;

        $current_term   = is_tax() ? get_queried_object()->term_id : '';
        $current_tax   = is_tax() ? get_queried_object()->taxonomy : '';

        $title = ( ! isset( $instance['title'] ) ) ? __( 'Clear All Filters', 'woocommerce' ) : $instance['title'];
        $title    = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
        $link_text  = ( ! isset( $instance['link_text'] ) ) ? __( 'Clear All', 'woocommerce' ) : $instance['link_text'];
        // Price
        $post_min = isset( $woocommerce->session->min_price ) ? $woocommerce->session->min_price : 0;
        $post_max = isset( $woocommerce->session->max_price ) ? $woocommerce->session->max_price : 0;

        if ( count( $_chosen_attributes ) > 0 || $post_min > 0 || $post_max > 0 ) {

            echo $before_widget;
            if ( $title ) {
                echo $before_title . $title . $after_title;
            }
            $link = false;    
            echo '<ul>';

            // Attributes
            foreach ( $_chosen_attributes as $taxonomy => $data ) {
                
                foreach ( $data['terms'] as $term_id ) {
                    $term               = get_term( $term_id, $taxonomy );
                    
                    $taxonomy_filter = str_replace( 'pa_', '', $taxonomy );
                    $current_filter  = ! empty( $_GET[ 'filter_' . $taxonomy_filter ] ) ? $_GET[ 'filter_' . $taxonomy_filter ] : '';
                    if ( !$link ){
                       $link =  remove_query_arg( 'filter_' . $taxonomy_filter ) ;  
                     }else{
                       $link = remove_query_arg( 'filter_' . $taxonomy_filter, $link  );
                     }
                    if($data['query_type'] == 'or'){
                        $link = esc_url( remove_query_arg( 'query_type_' . $taxonomy_filter, $link ) );     
                    }
                    //echo '<li class="chosen"><a title="' . __( 'Remove filter', 'woocommerce' ) . '" href="#"  href="#" data-filter="'.esc_url($link).'" data-link="'.esc_url($link).'">' . $term->name . '</a></li>';
                }
            }

            if ( $post_min ) {
                $link = esc_url( remove_query_arg( 'min_price', $link ) );
          }

            if ( $post_max ) {
                $link = esc_url( remove_query_arg( 'max_price', $link ) );
            }
            echo '<li><a href="#" data-filter="'. esc_url(urldecode($link)) . '" data-link="'. esc_url( urldecode($link) ) . '" >'. $link_text .'</a></li>';
            echo "</ul>";

            echo $after_widget;
        }else{
            echo $before_widget;
            echo "<div></div>";
            echo $after_widget;
        }
    }
    function update( $new_instance, $old_instance ) {
        global $woocommerce;
        $instance['title'] = strip_tags(stripslashes($new_instance['title']));
        $instance['link_text'] = strip_tags(stripslashes($new_instance['link_text']));
        
        return $instance;
    }

    /** @see WP_Widget->form */
    function form( $instance ) {
        
        if (!isset($instance['title'])) $instance['title'] = 'Clear All Filters';
        if (!isset($instance['link_text'])) $instance['link_text'] = 'Clear All';
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'ultimatewoo-pro') ?></label>
            <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if (isset ( $instance['title'])) {echo esc_attr( $instance['title'] );} ?>" /></p>
          <p><label for="<?php echo $this->get_field_id('link_text'); ?>"><?php _e('Link Text:', 'ultimatewoo-pro') ?></label>
            <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('link_text') ); ?>" name="<?php echo esc_attr( $this->get_field_name('link_text') ); ?>" value="<?php if (isset ( $instance['link_text'])) {echo esc_attr( $instance['link_text'] );} ?>" /></p>
        
    <?php 
    }
}
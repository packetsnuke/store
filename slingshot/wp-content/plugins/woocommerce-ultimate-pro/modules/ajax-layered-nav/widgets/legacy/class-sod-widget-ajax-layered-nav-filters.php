<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SOD_Widget_Ajax_Layered_Nav_Filters extends WP_Widget {

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
    function SOD_Widget_Ajax_Layered_Nav_Filters() {

        /* Widget variable settings. */
        $this->woo_widget_cssclass      = 'woocommerce widget_ajax_layered_nav_filters widget_layered_nav_filters';
        $this->woo_widget_description   = __( 'Shows active layered nav filters so users can see and deactivate them. Should be used with the Ajax Layered Nav.', 'woocommerce' );
        $this->woo_widget_idbase        = 'sod_ajax_layered_nav_filters';
        $this->woo_widget_name          = __( 'WooCommerce Ajax Layered Nav Filters', 'woocommerce' );
        $this->settings           = array(
            'title'  => array(
                'type'  => 'text',
                'std'   => __( 'Active Filters', 'woocommerce' ),
                'label' => __( 'Title', 'woocommerce' )
            )
        );
        /* Widget settings. */
        $widget_ops = array( 'classname' => $this->woo_widget_cssclass, 'description' => $this->woo_widget_description );

        /* Create the widget. */
        parent::__construct( 'sod_ajax_layered_nav_filters', $this->woo_widget_name, $widget_ops );
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

        $title = ( ! isset( $instance['title'] ) ) ? __( 'Active filters', 'woocommerce' ) : $instance['title'];
        $title    = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

        // Price
        $post_min = isset( $woocommerce->session->min_price ) ? $woocommerce->session->min_price : 0;
        $post_max = isset( $woocommerce->session->max_price ) ? $woocommerce->session->max_price : 0;

        if ( count( $_chosen_attributes ) > 0 || $post_min > 0 || $post_max > 0 ) {

            echo $before_widget;
            if ( $title ) {
                echo $before_title . $title . $after_title;
            }

            echo "<ul>";

            // Attributes
            foreach ( $_chosen_attributes as $taxonomy => $data ) {
                
                foreach ( $data['terms'] as $term_id ) {
                    $term               = get_term( $term_id, $taxonomy );
                    
                    $taxonomy_filter = str_replace( 'pa_', '', $taxonomy );
                    $current_filter  = ! empty( $_GET[ 'filter_' . $taxonomy_filter ] ) ? $_GET[ 'filter_' . $taxonomy_filter ] : '';
                    $new_filter      = array_map( 'absint', explode( ',', $current_filter ) );
                    $new_filter      = array_diff( $new_filter, array( $term_id ) );
                    
                    //$link = remove_query_arg( array( 'add-to-cart', 'filter_' . $taxonomy_filter ) );
                    $link = remove_query_arg( 'filter_' . $taxonomy_filter );
                    //$link = remove_query_arg( 'query_type_' . $taxonomy_filter );
                    if ( sizeof( $new_filter ) > 0 ) {
                        $link = add_query_arg( 'filter_' . $taxonomy_filter, implode( ',', $new_filter ), $link );
                    }else{
                        if($data['query_type'] == 'or'){
                            $link = remove_query_arg( 'query_type_' . $taxonomy_filter, $link );        
                        }
                    }
                    
                    $link = urldecode($link);
                    
                    echo '<li class="chosen"><a title="' . __( 'Remove filter', 'woocommerce' ) . '" href="#"  href="#" data-filter="'.esc_url($link).'" data-link="'.esc_url($link).'">' . $term->name . '</a></li>';
                }
            }

            if ( $post_min ) {
                $link = esc_url( remove_query_arg( 'min_price' ) );
                echo '<li class="chosen"><a title="' . __( 'Remove filter', 'woocommerce' ) . '" href="#" data-filter="'.urldecode(esc_url($link)).'" data-link="'.urldecode(esc_url($link)).'">' . __( 'Min', 'woocommerce' ) . ' ' . woocommerce_price( $post_min ) . '</a></li>';
            }

            if ( $post_max ) {
                $link = esc_url( remove_query_arg( 'max_price' ) );
                echo '<li class="chosen"><a title="' . __( 'Remove filter', 'woocommerce' ) . '"  href="#" data-filter="'.urldecode(esc_url($link)).'" data-link="'.urldecode(esc_url($link)).'">' . __( 'Max', 'woocommerce' ) . ' ' . woocommerce_price( $post_max ) . '</a></li>';
            }

            echo "</ul>";

            echo $after_widget;
        }else{
            echo $before_widget;
            echo "<ul></ul>";
            echo $after_widget;
        }
    }
    function update( $new_instance, $old_instance ) {
        global $woocommerce;
        $instance['title'] = strip_tags(stripslashes($new_instance['title']));
        return $instance;
    }

    /** @see WP_Widget->form */
    function form( $instance ) {
        
        if (!isset($instance['title'])) $instance['title'] = 'Active Filters';
        
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'ultimatewoo-pro') ?></label>
            <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if (isset ( $instance['title'])) {echo esc_attr( $instance['title'] );} ?>" /></p>
        
    <?php 
    }
}
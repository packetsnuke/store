<?php

class Woocommerce_Widget_Products_Of_The_Day extends WP_Widget
{
    /**
     * Constructor
     *
     * Setup the widget with the available options
     * Add actions to clear the cache whenever a post is saved|deleted or a theme is switched
     */
    public function __construct()
    {
        $options = array(
            'classname'		=> 'woocommerce_products_of_the_day',
            'description'	=> __( 'Lists the products of the day', 'ultimatewoo-pro' )
        );

        // Create the widget
        parent::__construct( 'woocommerce_products_of_the_day', __( 'WooCommerce Products Of The Day', 'ultimatewoo-pro' ), $options );

        // Flush cache after every save
        add_action( 'save_post',	array( &$this, 'flush_widget_cache' ) );
        add_action( 'deleted_post', array( &$this, 'flush_widget_cache' ) );
        add_action( 'switch_theme', array( &$this, 'flush_widget_cache' ) );
    }

    /**
     * Widget
     *
     * Display the widget in the sidebar
     * Save output to the cache if empty
     *
     * @param	array	sidebar arguments
     * @param	array	instance
     */
    public function widget( $args, $instance )
    {
        // Get the best selling products from the transient
        $cache = get_transient( 'woocommerce_widget_cache' );

        // If cached get from the cache
        if ( isset( $cache[$args['widget_id']] ) )
        {
            echo $cache[$args['widget_id']];
            return false;
        }

        // Start buffering
        ob_start();
        extract( $args );

        // Set the widget title
        $title = apply_filters(
            'widget_title',
            ( $instance['title'] ) ? $instance['title'] : __( 'Products Of The Day', 'ultimatewoo-pro' ),
            $instance,
            $this->id_base
        );

        // Set number of products to fetch
        $number = filter_var( $instance['number'], FILTER_VALIDATE_INT ) ? absint($instance['number']) : 10;
        $day = strtolower(date('D'));

        $q = new WP_Query( array(
            'posts_per_page'    => $number,
            'post_type'         => 'product',
            'post_status'       => 'publish',
            'meta_key'          => 'product_of_the_day_' . $day,
            'orderby'           => 'meta_value_num',
            'order'             => 'ASC',
            'nopaging'          => false,
            'tax_query'        => array(array(
                                  'taxonomy' => 'product_visibility',
                                  'field'    => 'name',
                                  'terms'    => 'exclude-from-catalog',
                                  'operator' => 'NOT IN',
            )) 
        ) );

        // If there are products
        if( $q->have_posts() )
        {
            // Print the widget wrapper & title
            echo $before_widget;
            echo $before_title . $title . $after_title;

            // Open the list
            echo '<ul class="product_list_widget">';

            // Print out each product
            while( $q->have_posts() ) : $q->the_post();

                echo '<li class="product">';
                // Print the product image & title with a link to the permalink
                echo '<a href="' . esc_attr( get_permalink() ) . '" title="' . esc_attr( get_the_title() ) . '">';

                if (isset($instance['show_thumbs']) && $instance['show_thumbs']):
                    // Print the product image
                    echo ( has_post_thumbnail() )
                    ? the_post_thumbnail( 'shop_tiny' )
                    : woocommerce_placeholder_img( 'shop_tiny' );
                endif;

                echo '<span class="js_widget_product_title">' . get_the_title() . '</span>';
                echo '</a>';

                // Print the price with html wrappers
                echo '<span class="js_widget_product_price">';
                do_action('woocommerce_after_shop_loop_item_title');
                echo '</span>';

                if (isset($instance['show_add_to_cart']) && $instance['show_add_to_cart']):
                woocommerce_template_loop_add_to_cart();
                endif;

                echo '</li>';
            endwhile;

            echo '</ul>'; // Close the list

            // Print closing widget wrapper
            echo $after_widget;

            // Reset the global $the_post as this query will have stomped on it
            wp_reset_postdata();
        }

        // Flush output buffer and save to transient cache
        $cache[$args['widget_id']] = ob_get_flush();
        set_transient( 'woocommerce_widget_cache', $cache, 3600*3 ); // 3 hours ahead
    }

    /**
     * Update
     *
     * Handles the processing of information entered in the wordpress admin
     * Flushes the cache & removes entry from options array
     *
     * @param	array	new instance
     * @param	array	old instance
     * @return	array	instance
     */
    public function update( $new_instance, $old_instance )
    {
        $instance = $old_instance;

        // Save the new values
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['number'] = absint( $new_instance['number'] );
        $instance['show_thumbs'] = (bool) isset($new_instance['show_thumbs']) ? $new_instance['show_thumbs'] : false;
        $instance['show_add_to_cart'] = (bool) isset($new_instance['show_add_to_cart']) ? $new_instance['show_add_to_cart'] : false;

        // Flush the cache
        $this->flush_widget_cache();

        return $instance;
    }

    /**
    * Flush Widget Cache
    *
    * Flushes the cached output
    */
    public static function flush_widget_cache()
    {
        delete_transient( 'woocommerce_widget_cache' );
    }

    /**
    * Form
    *
    * Displays the form for the wordpress admin
    *
    * @param	array	instance
    */
    public function form( $instance )
    {
        // Get instance data
        $title 		= isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : null;
        $number 	= isset( $instance['number'] ) ? absint( $instance['number'] ) : 10;
        $show_thumbs 	      = isset( $instance['show_thumbs'] ) ? (bool) $instance['show_thumbs'] : true;
        $show_add_to_cart 	= isset( $instance['show_add_to_cart'] ) ? (bool) $instance['show_add_to_cart'] : true;

        // Widget Title
        echo "
        <p>
        <label for='{$this->get_field_id( 'title' )}'>" . __( 'Title:', 'woocommerce' ) . "</label>
        <input class='widefat' id='{$this->get_field_id( 'title' )}' name='{$this->get_field_name( 'title' )}' type='text' value='{$title}' />
        </p>";

        // Number of posts to fetch
        echo "
        <p>
        <label for='{$this->get_field_id( 'number' )}'>" . __( 'Number of products to show:', 'woocommerce' ) . "</label>
        <input id='{$this->get_field_id( 'number' )}' name='{$this->get_field_name( 'number' )}' type='number' min='1' value='{$number}' />
        </p>";

        // Show thumbnails
        echo "
        <p>
        <input class='checkbox' type='checkbox' ".checked($show_thumbs, true, false)." id='{$this->get_field_id('show_thumbs')}' name='{$this->get_field_name('show_thumbs')}' />
        <label for='{$this->get_field_id('show_thumbs')}'>". __('Show thumbnails', 'ultimatewoo-pro') ."</label>
        </p>";

        // Show thumbnails
        echo "
        <p>
        <input class='checkbox' type='checkbox' ".checked($show_add_to_cart, true, false)." id='{$this->get_field_id('show_add_to_cart')}' name='{$this->get_field_name('show_add_to_cart')}' />
        <label for='{$this->get_field_id('show_add_to_cart')}'>". __('Show add to cart', 'ultimatewoo-pro') ."</label>
        </p>";
    }
} 
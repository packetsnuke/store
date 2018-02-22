<?php
/**
 * Template renders the product of a day page which is displayed under admin settings
 * @param array $controls
 */
?>

<div class="wrap">
    <div class="icon32 icon32-products-of-the-day" id="icon-edit"><br/></div>
    <h2><?php _e('Products Of The Day', 'ultimatewoo-pro') ?></h2>
    <p><?php _e('You can see here all choosen products of the day for each day of week. Each list of products is sortable using drag&drop, so you can define different products order for each day.', 'ultimatewoo-pro')?></p>

    <div id="sortable-lists">

            <?php foreach ( $controls as $set ): ?>

                <div class="<?php print self::CSS_PREFIX; ?>days-row">
            
                    <?php foreach( $set as $day => $name ) : ?>

                            <div class="day-box" data-day="<?php print $day; ?>">
                                <h3><?php echo $name ?><span>Saved</span></h3>

                                <?php

                                $q = new WP_Query( array(
                                    'posts_per_page'    => -1,
                                    'post_type'         => 'product',
                                    'post_status'       => 'publish',
                                    'meta_key'          => 'product_of_the_day_' . $day,
                                    'orderby'           => 'meta_value_num',
                                    'order'             => 'ASC',
                                    'nopaging'          => true,
                                    'tax_query'        => array(array(
                                            'taxonomy' => 'product_visibility',
                                            'field'    => 'name',
                                            'terms'    => 'exclude-from-catalog',
                                            'operator' => 'NOT IN',
                                    ))
                                ) );

                                $products_assigned = array(); ?>
                                <ul class="sortable widefat potd-sortable-list" data-day="<?php print $day; ?>">
                                    <?php while( $q->have_posts() )
                                    {
                                        $q->the_post();
                                        $post_id    = get_the_ID();
                                        $post_title = get_the_title();

                                        // render the list element
                                        self::product_list_element( $day, $post_id, $post_title );

                                        $products_assigned[] = get_the_ID();
                                    } ?>
                                </ul>
                                <?php
                                
                                // render the list of products
                                ?>
                                <div class="<?php print self::CSS_PREFIX ?>product-add-container">
                                    <div class="<?php print self::CSS_PREFIX ?>container-input">
                                        <input type="text" placeholder="<?php _e( 'Add product...', 'ultimatewoo-pro' );?>" class="ajax-product-list"/>
                                    </div>
                                </div>                                
                                <?php
                                wp_reset_postdata();
                                ?>
                            </div>

                    <?php endforeach; ?>
                    
                </div>
                
        <?php endforeach; ?>

    </div>
</div>
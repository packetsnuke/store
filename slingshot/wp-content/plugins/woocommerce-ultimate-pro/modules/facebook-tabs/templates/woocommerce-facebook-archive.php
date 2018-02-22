<div class="fb_product_wrap<?php echo is_active_sidebar('facebook-archive') ? ' has_left_sidebar' :'';?>">
    <?php if(is_active_sidebar('facebook-archive')){ ?>
    <div class="fb_sidebar">
        <?php dynamic_sidebar('facebook-archive'); ?>
    </div>
    <?php } ?>
    <div class="fb_products">
        <?php if ( have_posts() ) :

        global $woocommerce_loop;
        // Store column count for displaying the grid
//	        $woocommerce_loop['columns'] = 4;
//        if ( empty( $woocommerce_loop['columns'] ) )
//            $woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
        ?>

        <?php
            /**
             * woocommerce_before_shop_loop hook
             *
             * @hooked woocommerce_result_count - 20
             * @hooked woocommerce_catalog_ordering - 30
             */
           // do_action( 'woocommerce_before_shop_loop' );
    //        woocommerce_show_messages();
            woocommerce_result_count();
            woocommerce_catalog_ordering();

	        $column_count = 0;
	        $column_limit = 4;
        ?>

        <?php //woocommerce_product_loop_start(); ?>
        <ul class="products"> <!-- woocommerce_product_loop_start() -->


            <?php woocommerce_product_subcategories(); ?>

	        <!-- here -->

            <?php while ( have_posts() ) : the_post(); ?>

                <?php //woocommerce_get_template_part( 'content', 'product' ); ?>

	            <!-- loop -->

	            <?php
                global $product;

                if(!$product)continue;

                // Store loop count we're currently on
//                if ( empty( $woocommerce_loop['loop'] ) )
//                    $woocommerce_loop['loop'] = 0;

                // Ensure visibility
                if ( ! $product->is_visible() )continue;

                // Increase loop count
                //$woocommerce_loop['loop']++;
	            $column_count++;

                // Extra post classes
                $classes = array();
	            if ( 0 === ( $column_count - 1 ) % $column_limit || 1 === $column_limit ) {
		            $classes[] = 'first';
	            }
	            if ( 0 === $column_count % $column_limit ) {
		            $classes[] = 'last';
	            }
	            $classes[] = 'column-c-'.$column_count;
                ?>
	            <!-- <?php echo var_export($classes,true);?> -->
                <li <?php post_class( $classes ); ?>>

                    <?php
                    // stop these actions, they break facebook layout with custom plugins.
                    //do_action( 'woocommerce_before_shop_loop_item' ); ?>

                    <a href="<?php the_permalink(); ?>">

                        <?php
                            /**
                             * woocommerce_before_shop_loop_item_title hook
                             *
                             * @hooked woocommerce_show_product_loop_sale_flash - 10
                             * @hooked woocommerce_template_loop_product_thumbnail - 10
                             */
                            //do_action( 'woocommerce_before_shop_loop_item_title' );
                            //woocommerce_show_product_loop_sale_flash();
                            echo woocommerce_get_product_thumbnail();
                        ?>

                        <h3><?php the_title(); ?></h3>

                        <?php
                            /**
                             * woocommerce_after_shop_loop_item_title hook
                             *
                             * @hooked woocommerce_template_loop_price - 10
                             */
                            //do_action( 'woocommerce_after_shop_loop_item_title' );
                        ?>
                        <!-- woocommerce_after_shop_loop_item_title -->
                        <?php if ( $price_html = $product->get_price_html() ) : ?>
                            <span class="price"><?php echo $price_html; ?></span>
                        <?php endif; ?>
                        <!-- /woocommerce_after_shop_loop_item_title -->

                    </a>

                    <?php //do_action( 'woocommerce_after_shop_loop_item' );

                    if ( ! $product->is_in_stock() ) : ?>

                        <a href="<?php echo apply_filters( 'out_of_stock_add_to_cart_url', get_permalink( $product->id ) ); ?>" class="button"><?php echo apply_filters( 'out_of_stock_add_to_cart_text', __( 'Read More', 'woocommerce' ) ); ?></a>

                    <?php else : ?>

                        <?php
                            $link = array(
                                'url'   => '',
                                'label' => '',
                                'class' => ''
                            );

                            $handler = apply_filters( 'woocommerce_add_to_cart_handler', $product->product_type, $product );

                            switch ( $handler ) {
                                case "variable" :
                                    $link['url'] 	= apply_filters( 'variable_add_to_cart_url', get_permalink( $product->id ) );
                                    $link['label'] 	= apply_filters( 'variable_add_to_cart_text', __( 'Select options', 'woocommerce' ) );
                                break;
                                case "grouped" :
                                    $link['url'] 	= apply_filters( 'grouped_add_to_cart_url', get_permalink( $product->id ) );
                                    $link['label'] 	= apply_filters( 'grouped_add_to_cart_text', __( 'View options', 'woocommerce' ) );
                                break;
                                case "external" :
                                    $link['url'] 	= apply_filters( 'external_add_to_cart_url', get_permalink( $product->id ) );
                                    $link['label'] 	= apply_filters( 'external_add_to_cart_text', __( 'Read More', 'woocommerce' ) );
                                break;
                                default :
                                    if ( $product->is_purchasable() ) {
                                        $link['url'] 	= apply_filters( 'add_to_cart_url', esc_url( $product->add_to_cart_url() ) );
                                        $link['label'] 	= apply_filters( 'add_to_cart_text', __( 'Add to cart', 'woocommerce' ) );
                                        $link['class']  = apply_filters( 'add_to_cart_class', 'add_to_cart_button' );
                                    } else {
                                        $link['url'] 	= apply_filters( 'not_purchasable_url', get_permalink( $product->id ) );
                                        $link['label'] 	= apply_filters( 'not_purchasable_text', __( 'Read More', 'woocommerce' ) );
                                    }
                                break;
                            }

                            echo apply_filters( 'woocommerce_loop_add_to_cart_link', sprintf('<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="%s button product_type_%s">%s</a>', esc_url( $link['url'] ), esc_attr( $product->id ), esc_attr( $product->get_sku() ), esc_attr( $link['class'] ), esc_attr( $product->product_type ), esc_html( $link['label'] ) ), $product, $link );

                        ?>

                    <?php endif; ?>

                </li>
                <!-- woocommerce_get_template_part() -->

            <?php endwhile; // end of the loop. ?>

        <?php //woocommerce_product_loop_end(); ?>

    </ul>

        <?php
            /**
             * woocommerce_after_shop_loop hook
             *
             * @hooked woocommerce_pagination - 10
             */
            //do_action( 'woocommerce_after_shop_loop' );
    //        woocommerce_pagination();
        ?>

            <?php
            global $wp_query;
    if ( $wp_query->max_num_pages > 1 ){
    ?>
    <nav class="woocommerce-pagination">
        <?php
            echo paginate_links( apply_filters( 'woocommerce_pagination_args', array(
                'base' 			=> str_replace( 999999999, '%#%', get_pagenum_link( 999999999 ) ),
                'format' 		=> '',
                'current' 		=> max( 1, get_query_var('paged') ),
                'total' 		=> $wp_query->max_num_pages,
                'prev_text' 	=> '&larr;',
                'next_text' 	=> '&rarr;',
                'type'			=> 'list',
                'end_size'		=> 3,
                'mid_size'		=> 3
            ) ) );
        ?>
    </nav>
        <?php } ?>

    <?php endif; ?>
    </div>
</div>
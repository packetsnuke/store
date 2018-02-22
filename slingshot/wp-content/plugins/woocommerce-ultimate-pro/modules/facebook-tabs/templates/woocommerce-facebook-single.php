<div class="fb_product_wrap<?php echo is_active_sidebar('facebook-single') ? ' has_left_sidebar' :'';?>">
    <?php if(is_active_sidebar('facebook-single')){ ?>
    <div class="fb_sidebar">
        <?php dynamic_sidebar('facebook-single'); ?>
    </div>
    <?php } ?>
    <div class="fb_product">

    <?php
    if ( post_password_required() ) {
        echo get_the_password_form();

    }else{
    if(have_posts())the_post();
    global $woocommerce, $product, $post, $woocommerce;
    ?>
    <div itemscope itemtype="http://schema.org/Product" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>

        <?php
            /**
             * woocommerce_show_product_images hook
             *
             * @hooked woocommerce_show_product_sale_flash - 10
             * @hooked woocommerce_show_product_images - 20
             */
//        woocommerce_show_product_sale_flash();
//        woocommerce_show_product_images();
            //do_action( 'woocommerce_before_single_product_summary' );
        ?>

    <?php if ($product->is_on_sale()) : ?>
        <?php
        //echo apply_filters('woocommerce_sale_flash', '<span class="onsale">'.__( 'Sale!', 'woocommerce' ).'</span>', $post, $product);
        echo '<span class="onsale">'.__( 'Sale!', 'woocommerce' ).'</span>';
        // ?>
    <?php endif; ?>

        <div class="images">

            <?php
            if ( has_post_thumbnail() ) {

                $image_title 	= esc_attr( get_the_title( get_post_thumbnail_id() ) );
                $image_caption 	= get_post( get_post_thumbnail_id() )->post_excerpt;
                $image_link  	= wp_get_attachment_url( get_post_thumbnail_id() );
                $image       	= get_the_post_thumbnail( $post->ID, 'shop_single', array(
                    'title'	=> $image_title,
                    'alt'	=> $image_title
                ) );
                /*$image       	= get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ), array(
                    'title'	=> $image_title,
                    'alt'	=> $image_title
                ) );*/

                $attachment_count = count( $product->get_gallery_attachment_ids() );

                if ( $attachment_count > 0 ) {
                    $gallery = '[product-gallery]';
                } else {
                    $gallery = '';
                }

                //echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<a href="%s" itemprop="image" class="woocommerce-main-image zoom" title="%s" data-rel="prettyPhoto' . $gallery . '">%s</a>', $image_link, $image_caption, $image ), $post->ID );
                echo sprintf( '<a href="%s" itemprop="image" class="woocommerce-main-image zoom" title="%s" data-rel="prettyPhoto' . $gallery . '">%s</a>', $image_link, $image_caption, $image );

            } else {

                //echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<img src="%s" alt="%s" />', wc_placeholder_img_src(), __( 'Placeholder', 'woocommerce' ) ), $post->ID );
                echo sprintf( '<img src="%s" alt="%s" />', wc_placeholder_img_src(), __( 'Placeholder', 'woocommerce' ) );

            }
            ?>

            <?php
//            woocommerce_show_product_thumbnails();
//            do_action( 'woocommerce_product_thumbnails' );
            $attachment_ids = $product->get_gallery_attachment_ids();

            if ( $attachment_ids ) {
                $loop 		= 0;
                $columns 	= 3;// apply_filters( 'woocommerce_product_thumbnails_columns', 3 );
                ?>
                <div class="thumbnails <?php echo 'columns-' . $columns; ?>"><?php

                    foreach ( $attachment_ids as $attachment_id ) {

                        $classes = array( 'zoom' );

                        if ( $loop == 0 || $loop % $columns == 0 )
                            $classes[] = 'first';

                        if ( ( $loop + 1 ) % $columns == 0 )
                            $classes[] = 'last';

                        $image_link = wp_get_attachment_url( $attachment_id );

                        if ( ! $image_link )
                            continue;

                        $image       = wp_get_attachment_image( $attachment_id, 'shop_thumbnail' );
                        //$image       = wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' ) );
                        $image_class = esc_attr( implode( ' ', $classes ) );
                        $image_title = esc_attr( get_the_title( $attachment_id ) );

                        //echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', sprintf( '<a href="%s" class="%s" title="%s" data-rel="prettyPhoto[product-gallery]">%s</a>', $image_link, $image_class, $image_title, $image ), $attachment_id, $post->ID, $image_class );
                        echo sprintf( '<a href="%s" class="%s" title="%s" data-rel="prettyPhoto[product-gallery]">%s</a>', $image_link, $image_class, $image_title, $image );

                        $loop++;
                    }

                    ?></div>
                <?php
            }
            ?>

        </div>

        <div class="summary entry-summary">

            <?php
                /**
                 * woocommerce_single_product_summary hook
                 *
                 * @hooked woocommerce_template_single_title - 5
                 * @hooked woocommerce_template_single_price - 10
                 * @hooked woocommerce_template_single_excerpt - 20
                 * @hooked woocommerce_template_single_add_to_cart - 30
                 * @hooked woocommerce_template_single_meta - 40
                 * @hooked woocommerce_template_single_sharing - 50
                 */
                //do_action( 'woocommerce_single_product_summary' );
            //woocommerce_template_single_title();
            ?>
            <h1 itemprop="name" class="product_title entry-title"><?php the_title(); ?></h1>
            <?php
            //woocommerce_template_single_price();
            ?>

            <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">

                <p class="price"><?php echo $product->get_price_html(); ?></p>

                <meta itemprop="price" content="<?php echo $product->get_price(); ?>" />
                <meta itemprop="priceCurrency" content="<?php echo get_woocommerce_currency(); ?>" />
                <link itemprop="availability" href="http://schema.org/<?php echo $product->is_in_stock() ? 'InStock' : 'OutOfStock'; ?>" />

            </div>
            <?php
            //woocommerce_template_single_excerpt();
            ?>

            <div itemprop="description">
                <?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ) ?>
            </div>

            <?php
            woocommerce_template_single_add_to_cart();
            /*switch($product->product_type){
                case 'external':
                    ?>
                    <p class="cart">
                    <a href="<?php echo esc_url( $product->get_product_url() ); ?>" rel="nofollow" class="single_add_to_cart_button button alt"><?php echo $product->single_add_to_cart_text(); ?></a>
                    </p> <?php
                    break;
                case 'grouped':
                    $grouped_product = $product;
                    $grouped_products = $product->get_children();
                    $quantites_required = false;
                    ?>
                    <form class="cart" method="post" enctype='multipart/form-data'>
                        <table cellspacing="0" class="group_table">
                            <tbody>
                            <?php
                            foreach ( $grouped_products as $product_id ) :
                                $product = wc_get_product( $product_id );

                                if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $product->is_in_stock() ) {
                                    continue;
                                }

                                $post    = $product->post;
                                setup_postdata( $post );
                                ?>
                                <tr>
                                    <td>
                                        <?php if ( $product->is_sold_individually() || ! $product->is_purchasable() ) : ?>
                                            <?php woocommerce_template_loop_add_to_cart(); ?>
                                        <?php else : ?>
                                            <?php
                                            $quantites_required = true;
                                            woocommerce_quantity_input( array(
                                                'input_name'  => 'quantity[' . $product_id . ']',
                                                'input_value' => ( isset( $_POST['quantity'][$product_id] ) ? wc_stock_amount( $_POST['quantity'][$product_id] ) : 0 ),
                                                'min_value'   => apply_filters( 'woocommerce_quantity_input_min', 0, $product ),
                                                'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product )
                                            ) );
                                            ?>
                                        <?php endif; ?>
                                    </td>

                                    <td class="label">
                                        <label for="product-<?php echo $product_id; ?>">
                                            <?php echo $product->is_visible() ? '<a href="' . get_permalink() . '">' . get_the_title() . '</a>' : get_the_title(); ?>
                                        </label>
                                    </td>

                                    <?php // do_action ( 'woocommerce_grouped_product_list_before_price', $product ); ?>

                                    <td class="price">
                                        <?php
                                        echo $product->get_price_html();

                                        if ( $availability = $product->get_availability() ) {
                                            $availability_html = empty( $availability['availability'] ) ? '' : '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>';
                                            echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability['availability'], $product );
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            endforeach;

                            // Reset to parent grouped product
                            $post    = $parent_product_post;
                            $product = wc_get_product( $parent_product_post->ID );
                            setup_postdata( $parent_product_post );
                            ?>
                            </tbody>
                        </table>

                        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->id ); ?>" />

                        <?php if ( $quantites_required ) : ?>

                            <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

                            <button type="submit" class="single_add_to_cart_button button alt"><?php echo $product->single_add_to_cart_text(); ?></button>

                            <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

                        <?php endif; ?>
                    </form>
                    <?php
                    break;
                case 'simple':
                    if($product->is_purchasable()) {
                        // Availability
                        $availability      = $product->get_availability();
                        $availability_html = empty( $availability['availability'] ) ? '' : '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>';

                        echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability['availability'], $product );
                        ?>

                        <?php if ( $product->is_in_stock() ) : ?>

                            <form class="cart" method="post" enctype='multipart/form-data'>

                                <?php
                                if ( ! $product->is_sold_individually() ) {
                                    woocommerce_quantity_input( array(
                                        'min_value'   => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
                                        'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product ),
                                        'input_value' => ( isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : 1 )
                                    ) );
                                }
                                ?>

                                <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->id ); ?>" />

                                <button type="submit" class="single_add_to_cart_button button alt"><?php echo $product->single_add_to_cart_text(); ?></button>

                            </form><?php
                        endif;
                    }
                    break;
                case 'variable':
                    $available_variations = $product->get_available_variations();
                    $attributes = $product->get_variation_attributes();
                    $selected_attributes = $product->get_variation_default_attributes();
                    ?>
                <form class="variations_form cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo $post->ID; ?>" data-product_variations="<?php echo esc_attr( json_encode( $available_variations ) ) ?>">
                    <?php if ( ! empty( $available_variations ) ) : ?>
                    <table class="variations" cellspacing="0">
                        <tbody>
                        <?php $loop = 0; foreach ( $attributes as $name => $options ) : $loop++; ?>
                            <tr>
                                <td class="label"><label for="<?php echo sanitize_title( $name ); ?>"><?php echo wc_attribute_label( $name ); ?></label></td>
                                <td class="value"><select id="<?php echo esc_attr( sanitize_title( $name ) ); ?>" name="attribute_<?php echo sanitize_title( $name ); ?>" data-attribute_name="attribute_<?php echo sanitize_title( $name ); ?>">
                                        <option value=""><?php echo __( 'Choose an option', 'woocommerce' ) ?>&hellip;</option>
                                        <?php
                                        if ( is_array( $options ) ) {

                                            if ( isset( $_REQUEST[ 'attribute_' . sanitize_title( $name ) ] ) ) {
                                                $selected_value = $_REQUEST[ 'attribute_' . sanitize_title( $name ) ];
                                            } elseif ( isset( $selected_attributes[ sanitize_title( $name ) ] ) ) {
                                                $selected_value = $selected_attributes[ sanitize_title( $name ) ];
                                            } else {
                                                $selected_value = '';
                                            }

                                            // Get terms if this is a taxonomy - ordered
                                            if ( taxonomy_exists( $name ) ) {

                                                $terms = wc_get_product_terms( $post->ID, $name, array( 'fields' => 'all' ) );

                                                foreach ( $terms as $term ) {
                                                    if ( ! in_array( $term->slug, $options ) ) {
                                                        continue;
                                                    }
                                                    echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $selected_value ), sanitize_title( $term->slug ), false ) . '>' . apply_filters( 'woocommerce_variation_option_name', $term->name ) . '</option>';
                                                }

                                            } else {

                                                foreach ( $options as $option ) {
                                                    echo '<option value="' . esc_attr( sanitize_title( $option ) ) . '" ' . selected( sanitize_title( $selected_value ), sanitize_title( $option ), false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
                                                }

                                            }
                                        }
                                        ?>
                                    </select> <?php
                                    if ( sizeof( $attributes ) === $loop ) {
                                        echo '<a class="reset_variations" href="#reset">' . __( 'Clear selection', 'woocommerce' ) . '</a>';
                                    }
                                    ?></td>
                            </tr>
                        <?php endforeach;?>
                        </tbody>
                    </table>

                    <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

                    <div class="single_variation_wrap" style="display:none;">
                        <?php do_action( 'woocommerce_before_single_variation' ); ?>

                        <div class="single_variation"></div>

                        <div class="variations_button">

                            <?php woocommerce_quantity_input( array(
                                'input_value' => ( isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : 1 )
                            ) ); ?>
                            <button type="submit" class="single_add_to_cart_button button alt"><?php echo $product->single_add_to_cart_text(); ?></button>
                        </div>

                        <input type="hidden" name="add-to-cart" value="<?php echo $product->id; ?>" />
                        <input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" />
                        <input type="hidden" name="variation_id" class="variation_id" value="" />

                        <?php do_action( 'woocommerce_after_single_variation' ); ?>
                    </div>

                    <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

                <?php else : ?>

                    <p class="stock out-of-stock"><?php _e( 'This product is currently out of stock and unavailable.', 'woocommerce' ); ?></p>

                <?php endif; ?>

                    </form><?php
                    break;
                default:
                    woocommerce_template_single_add_to_cart();
            }*/
            woocommerce_template_single_meta();
            ?>

        </div><!-- .summary -->

        <?php
            /**
             * woocommerce_after_single_product_summary hook
             *
             * @hooked woocommerce_output_product_data_tabs - 10
             * @hooked woocommerce_output_related_products - 20
             */
            //do_action( 'woocommerce_after_single_product_summary' );
            woocommerce_output_product_data_tabs();
            //woocommerce_output_related_products();
        ?>

    </div><!-- #product-<?php the_ID(); ?> -->

    <?php } ?>
    </div>
</div>
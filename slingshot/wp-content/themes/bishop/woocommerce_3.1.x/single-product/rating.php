<?php
/**
 * Single Product Rating
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/rating.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $product;

if ( 'no' === get_option( 'woocommerce_enable_review_rating' ) ) {
    return;
}

$rating_count = $product->get_rating_count();
$review_count = $product->get_review_count();
$average      = $product->get_average_rating();
$rating_html  = wc_get_rating_html( $product->get_average_rating() );

if ( $rating_count > 0 ) : ?>

    <div class="rating-single-product" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
        <?php
        // if we have some rating we'll show the div content.
        if ( $rating_html != '' ) {
            echo $rating_html ." <span class='rating-text'> <span itemprop='reviewCount'>".$review_count." </span>". _n("REVIEW","REVIEWS",$review_count,"yit")." </span>";
        }
        ?>
        <meta itemprop="ratingValue" content="<?php echo $average; ?>" />
    </div>
    <div class="clearfix"></div>

<?php endif; ?>




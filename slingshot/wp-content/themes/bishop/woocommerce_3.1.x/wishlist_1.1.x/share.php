<?php
/**
 * Share template
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Wishlist
 * @version 1.1.2
 */

global $yith_wcwl;

if ( get_option( 'yith_wcwl_share_fb' ) == 'yes' || get_option( 'yith_wcwl_share_twitter' ) == 'yes'
    || get_option( 'yith_wcwl_share_pinterest' ) == 'yes' || get_option( 'yith_wcwl_share_googleplus' ) == 'yes' ) {
    $share_url = $yith_wcwl->get_wishlist_url();
    $share_url .= get_option( 'permalink-structure' ) != '' ? '&amp;user_id=' : '?user_id=';
    $share_url .= get_current_user_id();
    ?>
    <div id="wishlist-share">
        <div class="share-text">
            <span class="fa fa-plus"></span>
            <h4><?php _e( 'share it', 'yit' ); ?></h4>
        </div>
        <div class="share-link-container">
            <div class="share-link arrow-right">
                <?php echo YITH_WCWL_UI::get_share_links( $share_url ); ?>
            </div>
        </div>
    </div>
<?php
}

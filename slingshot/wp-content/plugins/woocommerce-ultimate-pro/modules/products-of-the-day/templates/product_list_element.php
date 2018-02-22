<?php
/**
 * Template renders the list element for given product
 * @string $day
 * @int $post_id
 * @string $post_title
 */
?>
<li class="<?php print self::CSS_PREFIX ?>day" id="<?php print $day . '_' . $post_id; ?>"
    data-post-id="<?php print $post_id; ?>">
    <div class="<?php print self::CSS_PREFIX ?>day-label">
        <a class="row-title" href="<?php print get_edit_post_link( $post_id ); ?>">
            <?php print $post_title; ?>
        </a>        
    </div>
    <div class="<?php print self::CSS_PREFIX ?>day-remove-icon">
        <a title="<?php _e( 'Remove item', 'ultimatewoo-pro' ); ?>" class="<?php print self::CSS_PREFIX ?>product-remove" href="#"></a>
    </div>
</li>
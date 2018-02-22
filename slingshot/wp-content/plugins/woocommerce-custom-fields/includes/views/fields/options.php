<?php

/**
 * View for field settings panel
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<style type="text/css">
    #submitdiv {
        display: none;
    }
</style>

<div class="wccf_post wccf_post_options">

    <div class="wccf_post_options_list">
        <div class="wccf_post_no_options"><?php _e('No options configured.', 'rp_wccf'); ?></div>
    </div>

    <div class="wccf_post_add_option">
        <button type="button" class="button" value="<?php _e('Add Option', 'rp_wccf'); ?>">
            <i class="fa fa-plus"></i>&nbsp;&nbsp;<?php _e('Add Option', 'rp_wccf'); ?>
        </button>
    </div>

</div>

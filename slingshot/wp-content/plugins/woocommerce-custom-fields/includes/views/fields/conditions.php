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

<div class="wccf_post wccf_post_conditions">

    <div class="wccf_post_conditions_list">
        <div class="wccf_post_no_conditions"><?php _e('No conditions configured.', 'rp_wccf'); ?></div>
    </div>

    <div class="wccf_post_add_condition">
        <button type="button" class="button" value="<?php _e('Add Condition', 'rp_wccf'); ?>">
            <i class="fa fa-plus"></i>&nbsp;&nbsp;<?php _e('Add Condition', 'rp_wccf'); ?>
        </button>
    </div>

</div>

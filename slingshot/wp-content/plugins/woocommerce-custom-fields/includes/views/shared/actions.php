<?php

/**
 * View for field edit page Actions block
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="wccf_post wccf_post_actions">

    <div class="wccf_post_action_select">
        <select name="wccf_<?php echo $post_type_short; ?>_actions">
            <?php foreach ($actions as $action_key => $action_title): ?>
                <option value="<?php echo $action_key; ?>"><?php echo $action_title; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="wccf_post_actions_footer submitbox">
        <?php if (!empty($id)): ?>
            <div class="wccf_post_delete">
                <?php if (WCCF::is_authorized('manage_posts')): ?>
                    <?php if ($object && $object->is_archived()): // Special handling for archived fields ?>
                        <a class="submitdelete wccf_delete_permanently" href="<?php echo esc_url(WCCF_Field_Controller::get_delete_permanently_url($object->get_id(), $object->get_post_type())); ?>"><?php _e('Delete Permanently', 'rp_wccf') ?></a>
                    <?php else: ?>
                        <a class="submitdelete deletion" href="<?php echo esc_url(get_delete_post_link($id)); ?>"><?php echo (!EMPTY_TRASH_DAYS ? __('Delete Permanently', 'rp_wccf') : __('Move to Trash', 'rp_wccf')); ?></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <button type="submit" class="button button-primary" title="<?php _e('Submit', 'rp_wccf'); ?>" name="wccf_<?php echo $post_type_short; ?>_button" value="actions"><?php _e('Submit', 'rp_wccf'); ?></button>
    </div>
    <div style="clear: both;"></div>

</div>

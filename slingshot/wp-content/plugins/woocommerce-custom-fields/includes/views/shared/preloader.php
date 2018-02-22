<?php

/**
 * View for preloader
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div id="wccf_preloader">
    <div id="wccf_preloader_content">

        <p class="wccf_preloader_icon">
            <i class="fa fa-cog fa-spin fa-3x fa-fw" aria-hidden="true"></i>
        </p>

        <p class="wccf_preloader_header">
            <?php _e('<strong>User Interface Loading</strong>', 'rp_wccf'); ?>
        </p>

        <p class="wccf_preloader_text">
            <?php printf(__('This plugin uses a JavaScript-driven user interface. If this notice does not disappear in a few seconds, you should check Console for any JavaScript errors or get in touch with <a href="%s">RightPress Support</a>.', 'rp_wccf'), 'http://support.rightpress.net'); ?><br>
        </p>

    </div>
</div>

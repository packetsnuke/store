<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

$id = $field['id'];
$name = $field['name'];
$custom_attributes = $field['custom_attributes'];

$is_multiple = isset( $field['multiple'] ) && $field['multiple'];
$multiple = ( $is_multiple ) ? ' multiple' : '';
$db_value = $field['value'];
$db_value = ( $is_multiple && ! is_array( $db_value ) ) ? array() : $db_value;
?>

<div id="<?php echo $id ?>-container" class="chosen yit_options rm_option rm_input rm_text" <?php if ( isset( $field['deps'] ) ): ?>data-field="<?php echo $id ?>" data-dep="<?php echo  $field['deps']['ids']  ?>" data-value="<?php echo $field['deps']['values'] ?>" <?php endif ?>>
    <div class="option">
        <div class="select_wrapper">
            <select name="<?php echo $name ?><?php if( $is_multiple ) echo "[]" ?>" class="chosen" id="<?php echo $id ?>" <?php echo $multiple ?> <?php echo $custom_attributes ?> >
                <?php foreach ( $field['options'] as $key => $value ) : ?>
                    <option value="<?php echo esc_attr( $key ) ?>"<?php ($is_multiple) ? selected( true, in_array( $key, $db_value) ) : selected( $key, $db_value ) ?>><?php echo $value ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="clear"></div>
</div>
<style>#yit_newsletter_options_newsletter_popup_pages-container .chosen .select_wrapper .chosen-container {
        width: 100% !important;
    }

    #yit_newsletter_options_newsletter_popup_pages-container .chosen .select_wrapper .chosen-choices span {
        position: relative;
    }

    #yit_newsletter_options_newsletter_popup_pages-container .chosen .search-field {
        display: none;
    }

    #yit_newsletter_options_newsletter_popup_pages-container .chosen .select_wrapper {
        background-image: none;
        border: none;
    }

    #yit_newsletter_options_newsletter_popup_pages_chosen .chosen-choices {
        min-height: 30px;
    }
</style>
<script>
    jQuery.noConflict();
    jQuery( document ).ready(function( $ ) {
        $('#yit_newsletter_options_newsletter_popup_pages-container.chosen .select_wrapper select').chosen();
    });
</script>
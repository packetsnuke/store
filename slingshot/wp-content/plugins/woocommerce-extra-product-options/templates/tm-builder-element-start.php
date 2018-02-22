<?php
/**
 * The template for displaying the start of an element in the builder mode options
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-builder-element-start.php
 *
 * NOTE that we may need to update template files and you
 * (the plugin or theme developer) will need to copy the new files
 * to your theme or plugin to maintain compatibility.
 *
 * @author 		themeComplete
 * @package 	WooCommerce Extra Product Options/Templates
 * @version 	4.0
 */
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

do_action( 'tm_before_builder_element', isset( $tm_element_settings ) ? $tm_element_settings : array() ); 

if ( !empty( $class ) ) {
	$class = " " . $class;
	$divclass = $class . "-div";
	$ulclass = $class . "-ul";
	$class = "";
} else {
	$class = "";
	$divclass = "";
	$ulclass = "";
}

if ( !$haslogic ) {
	$logic = "";
}
if ( !empty( $exactlimit ) ) {
	$exactlimit = " " . $exactlimit;
} else {
	$exactlimit = "";
}
if ( !empty( $minimumlimit ) ) {
	$minimumlimit = " " . $minimumlimit;
} else {
	$minimumlimit = "";
}
$tm_product_id_class = "";
if ( !empty( $tm_product_id ) ) {
	$tm_product_id_class = " tm-product-id-" . $tm_product_id;
}
$cid = "";
if ( !empty( $container_id ) ) {
	$cid = ' id="' . $container_id . '"';
}
if ( !empty( TM_EPO()->tm_builder_elements[ $tm_element_settings['type'] ]["no_frontend_display"] ) ) {
	$divclass .= " tm-hidden";
}
$fb_label_show = isset( $tm_element_settings['hide_element_label_in_floatbox'] ) ? $tm_element_settings['hide_element_label_in_floatbox'] : '';
$fb_value_show = isset( $tm_element_settings['hide_element_value_in_floatbox'] ) ? $tm_element_settings['hide_element_value_in_floatbox'] : '';
?>
<div data-uniqid="<?php echo $uniqid; ?>"
     data-logic="<?php echo $logic; ?>"
     data-haslogic="<?php echo $haslogic; ?>"
     data-fblabelshow="<?php echo $fb_label_show; ?>"
     data-fbvalueshow="<?php echo $fb_value_show; ?>"
     class="cpf_hide_element tm-cell <?php echo $column; ?> cpf-type-<?php echo $type . $divclass . $tm_product_id_class; ?>"<?php echo $cid; ?>>
<?php
$use = " " . $class_id;
if ( !empty( $use_images ) ) {
	switch ( $use_images ) {
		case "images":
			$use .= " use_images_container";
			break;
	}
}
if ( !empty( $use_colors ) ) {
	switch ( $use_colors ) {
		case "color":
		case "start":
		case "end":
			$use .= " use_colors_container";
			break;
	}
}
if ( !empty( $use_url ) ) {
	switch ( $use_url ) {
		case "url":
			$use .= " use_url_container";
			break;
	}
}
if ( $tm_element_settings['type'] == 'radio' ) {
	switch ( TM_EPO()->tm_epo_global_radio_undo_button ) {
		case 'enable':
			$clear_options = 'yes';
			break;
		case 'disable':
			$clear_options = '';
			break;
	}
}
$description = apply_filters( "wc_epo_content", $description );
$description = apply_filters( "wc_epo_subtitle", $description );
if ( !empty( $tm_undo_button ) || !empty( $clear_options ) ) {
	$class .= " " . 'tm-has-undo-button';
}
if ( $required ) {
	$class .= " " . 'tm-has-required';
}
if ( empty( $title ) && !empty( $required ) ) {
	$title = '&nbsp;';
}
if ( $element != "divider" ) {

	if ( (!empty( $title ) && ($title_position == "left" || $title_position == "right")) && (!empty( $description ) && ($description_position !== "below")) ) {
		echo '<div';
		if ( !empty( $description_color ) ) {
			echo ' style="color:' . $description_color . '"';
		}
		echo ' class="tm-description' . ($description_position == "tooltip" || $description_position == "icontooltipright" || $description_position == "icontooltipleft" ? " tm-tip-html" : "") . '">' . do_shortcode( $description ) . '</div>';
	}

	if ( (!empty( $title ) && $title_position != "disable") || !empty( $required ) || !empty( $clear_options ) || (!empty( $description ) && ($description_position == "icontooltipright" | $description_position == "icontooltipleft")) ) {

		echo '<' . $title_size;

		if ( !empty( $title_color ) ) {
			echo ' style="color:' . $title_color . '"';
		}
		if ( $element == 'header' && !empty( $class ) ) {
			$class = " " . $class;
		}
		if ( !empty( $description ) && $description_position == "tooltip" ) {
			$class = " tm-tooltip";
		}
		if ( !empty( $title_position ) ) {
			$class .= " tm-" . $title_position;
		}
		if ( !empty( $description ) && !empty( $description_position ) && $description_position == "tooltip" ) {
			echo ' data-tm-tooltip-swatch="on"';
		}
		echo ' class="tm-epo-field-label' . $class . '">';
		if ( $required && !empty( TM_EPO()->tm_epo_global_required_indicator ) && TM_EPO()->tm_epo_global_required_indicator_position == 'left' ) {
			echo '<span class="tm-epo-required">' . TM_EPO()->tm_epo_global_required_indicator . '</span>';
		}
		if ( $description_position == "icontooltipleft" ) {
			echo '<i data-tm-tooltip-swatch="on" class="tc-icontooltipleft tm-tooltip tc-tooltip tcfa tcfa-question-circle"></i>';
		}

		if ( !empty( $title ) && $title_position != "disable" ) {
			echo $title;
		} else {
			echo "&nbsp;";
		}
		if ( $required && !empty( TM_EPO()->tm_epo_global_required_indicator ) && TM_EPO()->tm_epo_global_required_indicator_position == 'right' ) {
			echo '<span class="tm-epo-required">' . TM_EPO()->tm_epo_global_required_indicator . '</span>';
		}

		if ( !empty( $tm_undo_button ) ) {
			echo $tm_undo_button;
		}
		if ( !empty( $clear_options ) ) {
			echo '<span class="tm-epo-reset-radio">' . apply_filters( 'tm_undo_radio_text', '<i class="tcfa tcfa-undo"></i>' ) . '</span>';
		}
		if ( $description_position == "icontooltipright" ) {
			echo '<i data-tm-tooltip-swatch="on" class="tc-icontooltipright tm-tooltip tc-tooltip tcfa tcfa-question-circle"></i>';
		}

		echo '</' . $title_size . '>';
	}
	if ( !($title_position == "left" || $title_position == "right") && !empty( $description ) && (empty( $description_position ) || $description_position == "tooltip" || $description_position == "icontooltipright" | $description_position == "icontooltipleft") ) {
		echo '<div';
		if ( !empty( $description_color ) ) {
			echo ' style="color:' . $description_color . '"';
		}
		echo ' class="tm-description' . ($description_position == "tooltip" || $description_position == "icontooltipright" || $description_position == "icontooltipleft" ? " tm-tip-html" : "") . '">' . do_shortcode( $description ) . '</div>';

	}


}
echo $divider;
if ( !in_array( $element, array( 'header', 'divider' ) ) && empty( TM_EPO()->tm_builder_elements[ $tm_element_settings['type'] ]["no_frontend_display"] ) ) {
	?>
    <div class="tm-extra-product-options-container">
<ul data-rules="<?php echo $rules; ?>"
    data-original-rules="<?php echo $original_rules; ?>"
    data-rulestype="<?php echo $rules_type; ?>"
	<?php if (!empty( $tm_validation )){ ?>data-tm-validation="<?php echo $tm_validation; ?>" <?php } ?>
    class="tmcp-ul-wrap tmcp-elements tm-extra-product-options-<?php echo $type . $use . $ulclass . $exactlimit . $minimumlimit; ?>">
	<?php
}
?>
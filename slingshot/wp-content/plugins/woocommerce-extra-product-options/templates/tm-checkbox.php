<?php
/**
 * The template for displaying the checkbox element for the builder/local modes
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-checkbox.php
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
if ( empty( $image ) ) {
	$image = '';
}
if ( empty( $imagec ) ) {
	$imagec = '';
}
if ( empty( $imagep ) || empty( $changes_product_image ) ) {
	$imagep = '';
}
if ( empty( $imagel ) ) {
	$imagel = '';
}
if ( empty( $exactlimit ) ) {
	$exactlimit = '';
}
if ( empty( $minimumlimit ) ) {
	$minimumlimit = '';
}
if ( !isset( $checked ) ) {
	$checked = FALSE;
}
if ( !isset( $fieldtype ) ) {
	$fieldtype = "tmcp-field";
}
?>
<?php
if ( !isset( $border_type ) ) {
	$border_type = "";
}
$use = "";
if ( !empty( $use_images ) ) {
	switch ( $use_images ) {
		case "images":
			$use = " use_images";
			if ( empty($use_colors) && !empty( $image ) ) {
				$swatch = "";
				$swatch_class = "";
				if ( $swatchmode == 'swatch' ) {
					$swatch_class = " tm-tooltip";
					$swatch = ' ' . 'data-tm-tooltip-swatch="on"';
				} elseif ( $swatchmode == 'swatch_desc' ) {
					$swatch_class = " tm-tooltip";
					$swatch = ' ' . 'data-tm-tooltip-swatch-desc="on"';
				} elseif ( $swatchmode == 'swatch_lbl_desc' ) {
					$swatch_class = " tm-tooltip";
					$swatch = ' ' . 'data-tm-tooltip-swatch-lbl-desc="on"';
				} elseif ( $swatchmode == 'swatch_img' ) {
					$swatch_class = " tm-tooltip";
					$swatch = ' ' . 'data-tm-tooltip-swatch-img="on"';
				} elseif ( $swatchmode == 'swatch_img_lbl' ) {
					$swatch_class = " tm-tooltip";
					$swatch = ' ' . 'data-tm-tooltip-swatch-img-lbl="on"';
				} elseif ( $swatchmode == 'swatch_img_desc' ) {
					$swatch_class = " tm-tooltip";
					$swatch = ' ' . 'data-tm-tooltip-swatch-img-desc="on"';
				} elseif ( $swatchmode == 'swatch_img_lbl_desc' ) {
					$swatch_class = " tm-tooltip";
					$swatch = ' ' . 'data-tm-tooltip-swatch-img-lbl-desc="on"';
				}

				if ( $tm_epo_no_lazy_load == 'no' ) {
					if ( $checked && !empty( $imagec ) ) {
						$altsrc = 'data-original="' . $imagec . '"';
					} else {
						$altsrc = 'data-original="' . $image . '"';
					}
				} else {
					if ( $checked && !empty( $imagec ) ) {
						$altsrc = 'src="' . $imagec . '"';
					} else {
						$altsrc = 'src="' . $image . '"';
					}
				}
				if ( !empty( $use_lightbox ) && $use_lightbox == "lightbox" ) {
					$swatch_class .= " tc-lightbox-image";
				}
				$label = '<img class="tmlazy ' . $border_type . ' checkbox_image' . $swatch_class . '" alt="' . esc_attr( $label ) . '" ' . $altsrc . $swatch . ' />' . '<span class="tc-label checkbox_image_label">' . $label . '</span>';
			} else {
				// check for hex color
				$search_for_color = $label;
				if ( isset( $color ) ) {
					if ( ! is_array( $color ) ){
						$search_for_color = $color;	
					}else{
						if ( isset( $color[ $choice_counter ] ) ){
							$search_for_color = $color[ $choice_counter ];		
						}
					}
					if ( empty( $search_for_color ) ) {
						$search_for_color = 'transparent';
					}
				}
				if ( $search_for_color == 'transparent' || preg_match( '/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', $search_for_color ) ) { //hex color is valid
					$swatch = "";
					$swatch_class = "";
					if ( $swatchmode == 'swatch' ) {
						$swatch_class = " tm-tooltip";
						$swatch = ' ' . 'data-tm-tooltip-swatch="on"';
					}
					if ( $search_for_color == 'transparent' ) {
						$swatch_class .= " tm-transparent-swatch";
					}
					$label = '<span class="tmhexcolorimage ' . $border_type . ' checkbox_image' . $swatch_class . '" alt="' . esc_attr( (!isset( $color )) ? $search_for_color : $label ) . '" ' . $swatch . '></span>' . '<span class="tc-label checkbox_image_label">' . ((!isset( $color )) ? $search_for_color : $label) . '</span>';
				}
			}
			break;
		case "start":
			if ( !empty( $image ) ) {
				if ( $tm_epo_no_lazy_load == 'no' ) {
					$altsrc = 'data-original="' . $image . '"';
				} else {
					$altsrc = 'src="' . $image . '"';
				}
				$label = '<img class="tmlazy tc-checkbox-image" alt="' . esc_attr( $label ) . '" ' . $altsrc . ' /><span class="tc-label">' . $label . '</span>';
			}else {
				// check for hex color
				$search_for_color = $label;
				if ( isset( $color ) ) {
					if ( ! is_array( $color ) ){
						$search_for_color = $color;	
					}else{
						if ( isset( $color[ $choice_counter ] ) ){
							$search_for_color = $color[ $choice_counter ];		
						}
					}
					if ( empty( $search_for_color ) ) {
						$search_for_color = 'transparent';
					}
				}
				if ( $search_for_color == 'transparent' || preg_match( '/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', $search_for_color ) ) { //hex color is valid
					$swatch = "";
					$swatch_class = "";
					if ( $swatchmode == 'swatch' ) {
						$swatch_class = " tm-tooltip";
						$swatch = ' ' . 'data-tm-tooltip-swatch="on"';
					}
					if ( $search_for_color == 'transparent' ) {
						$swatch_class .= " tm-transparent-swatch";
					}
					$label = '<span class="tmhexcolorimage ' . $border_type . ' checkbox_image' . $swatch_class . '" alt="' . esc_attr( (!isset( $color )) ? $search_for_color : $label ) . '" ' . $swatch . '></span>' . '<span class="tc-label checkbox_image_label">' . ((!isset( $color )) ? $search_for_color : $label) . '</span>' ;
				}
			}
			break;

		case "end":
			if ( !empty( $image ) ) {
				if ( $tm_epo_no_lazy_load == 'no' ) {
					$altsrc = 'data-original="' . $image . '"';
				} else {
					$altsrc = 'src="' . $image . '"';
				}
				$label = '<span class="tc-label">' . $label . '</span><img class="tmlazy tc-checkbox-image" alt="' . esc_attr( $label ) . '" ' . $altsrc . ' />';
			}else {
				// check for hex color
				$search_for_color = $label;
				if ( isset( $color ) ) {
					if ( ! is_array( $color ) ){
						$search_for_color = $color;	
					}else{
						if ( isset( $color[ $choice_counter ] ) ){
							$search_for_color = $color[ $choice_counter ];		
						}
					}
					if ( empty( $search_for_color ) ) {
						$search_for_color = 'transparent';
					}
				}
				if ( $search_for_color == 'transparent' || preg_match( '/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', $search_for_color ) ) { //hex color is valid
					$swatch = "";
					$swatch_class = "";
					if ( $swatchmode == 'swatch' ) {
						$swatch_class = " tm-tooltip";
						$swatch = ' ' . 'data-tm-tooltip-swatch="on"';
					}
					if ( $search_for_color == 'transparent' ) {
						$swatch_class .= " tm-transparent-swatch";
					}
					$label = '<span class="tc-label checkbox_image_label">' . ((!isset( $color )) ? $search_for_color : $label) . '</span>' . '<span class="tmhexcolorimage ' . $border_type . ' checkbox_image' . $swatch_class . '" alt="' . esc_attr( (!isset( $color )) ? $search_for_color : $label ) . '" ' . $swatch . '></span>' ;
				}
			}
			break;
	}
}
if ( !empty( $li_class ) ) {
	$li_class = " " . $li_class;
} else {
	$li_class = "";
}
if ( !empty( $items_per_row ) ) {
	$li_class .= " tm-per-row";
}

if ( !empty( $class ) ) {
	$fieldtype .= " " . $class;
}
if ( !empty( $changes_product_image ) ) {
	$fieldtype .= " tm-product-image";
}
if ( !empty( $changes_product_image ) && $changes_product_image == "images" ) {
	$imagep = '';
}

if ( empty( $limit ) ) {
	$limit = "";
}

if ( isset( $textbeforeprice ) && $textbeforeprice != '' ) {
	$textbeforeprice = '<span class="before-amount' . (!empty( $hide_amount ) ? " " . $hide_amount : "") . '">' . $textbeforeprice . '</span>';
} else {
	$textbeforeprice = '';
}

if ( isset( $textafterprice ) && $textafterprice != '' ) {
	$textafterprice = '<span class="after-amount' . (!empty( $hide_amount ) ? " " . $hide_amount : "") . '">' . $textafterprice . '</span>';
}

$element_data_attr_html = array();
if ( !empty( $element_data_attr ) && is_array( $element_data_attr ) ) {
	foreach ( $element_data_attr as $k => $v ) {
		$element_data_attr_html[] = $k . '="' . esc_attr( $v ) . '"';
	}
}
if ( !empty( $element_data_attr_html ) ) {
	$element_data_attr_html = " " . implode( " ", $element_data_attr_html ) . " ";
} else {
	$element_data_attr_html = "";
}

if ( empty( $original_rules ) ) {
	$original_rules = '';
}

$image_variations = array();
if (!empty($changes_product_image)){
	$image_link = $image;
	$attachment_id = TM_EPO_HELPER()->get_attachment_id( $image_link );
	$attachment_id = ($attachment_id) ? $attachment_id : 0;
	$attachment_object = get_post( $attachment_id );
	$full_src = wp_get_attachment_image_src( $attachment_id, 'large' );
	$image_title = get_the_title( $attachment_id );
	$image_alt = trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', TRUE ) ) );
	$image_srcset = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, 'shop_single' ) : FALSE;
	$image_sizes = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, 'shop_single' ) : FALSE;
	$image_caption = $attachment_object->post_excerpt;
	$image_variations['image'] = array(
		'image_link'    => $image_link,
		'image_title'   => $image_title,
		'image_alt'     => $image_alt,
		'image_srcset'  => $image_srcset,
		'image_sizes'   => $image_sizes,
		'image_caption' => $image_caption,
		'image_id'      => $attachment_id,
		'full_src'      => $full_src[0],
		'full_src_w'    => $full_src[1],
		'full_src_h'    => $full_src[2],
	);

	$image_link = $imagep;
	$attachment_id = TM_EPO_HELPER()->get_attachment_id( $image_link );
	$attachment_object = get_post( $attachment_id );
	$full_src = wp_get_attachment_image_src( $attachment_id, 'large' );
	$image_title = get_the_title( $attachment_id );
	$image_alt = trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', TRUE ) ) );
	$image_srcset = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, 'shop_single' ) : FALSE;
	$image_sizes = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, 'shop_single' ) : FALSE;
	$image_caption = $attachment_object->post_excerpt;
	$image_variations['imagep'] = array(
		'image_link'    => $image_link,
		'image_title'   => $image_title,
		'image_alt'     => $image_alt,
		'image_srcset'  => $image_srcset,
		'image_sizes'   => $image_sizes,
		'image_caption' => $image_caption,
		'image_id'      => $attachment_id,
		'full_src'      => $full_src[0],
		'full_src_w'    => $full_src[1],
		'full_src_h'    => $full_src[2],
	);
}
/*
$image_link = $imagec;
$attachment_id = TM_EPO_HELPER()->get_attachment_id( $image_link );
$attachment_object = get_post( $attachment_id );
$full_src = wp_get_attachment_image_src( $attachment_id, 'large' );
$image_title = get_the_title( $attachment_id );
$image_alt = trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', TRUE ) ) );
$image_srcset = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, 'shop_single' ) : FALSE;
$image_sizes = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, 'shop_single' ) : FALSE;
$image_caption = $attachment_object->post_excerpt;
$image_variations['imagec'] = array(
	'image_link'    => $image_link,
	'image_title'   => $image_title,
	'image_alt'     => $image_alt,
	'image_srcset'  => $image_srcset,
	'image_sizes'   => $image_sizes,
	'image_caption' => $image_caption,
	'image_id'      => $attachment_id,
	'full_src'      => $full_src[0],
	'full_src_w'    => $full_src[1],
	'full_src_h'    => $full_src[2],
);

$image_link = $imagel;
$attachment_id = TM_EPO_HELPER()->get_attachment_id( $image_link );
$attachment_id = ($attachment_id) ? $attachment_id : 0;
$attachment_object = get_post( $attachment_id );
$full_src = wp_get_attachment_image_src( $attachment_id, 'large' );
$image_title = get_the_title( $attachment_id );
$image_alt = trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', TRUE ) ) );
$image_srcset = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, 'shop_single' ) : FALSE;
$image_sizes = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, 'shop_single' ) : FALSE;
$image_caption = $attachment_object->post_excerpt;
$image_variations['imagel'] = array(
	'image_link'    => $image_link,
	'image_title'   => $image_title,
	'image_alt'     => $image_alt,
	'image_srcset'  => $image_srcset,
	'image_sizes'   => $image_sizes,
	'image_caption' => $image_caption,
	'image_id'      => $attachment_id,
	'full_src'      => $full_src[0],
	'full_src_w'    => $full_src[1],
	'full_src_h'    => $full_src[2],
);*/

$labelclass = '';
$labelclass_start = '';
$labelclass_end = '';
if ( TM_EPO()->tm_epo_css_styles == "on" && (empty( $use_images ) || (isset( $use_images ) && $use_images != "images")) ) {
	$labelclass = ' class="tc-label tm-epo-style ' . TM_EPO()->tm_epo_css_styles_style . '"';
	$labelclass_start = '<span class="tm-epo-style-wrapper ' . TM_EPO()->tm_epo_css_styles_style . '">';
	$labelclass_end = '</span>';
}

?>
<li class="tmcp-field-wrap<?php echo $grid_break . $li_class; ?>">
	<?php include(TM_EPO_TEMPLATE_PATH .'_quantity_start.php'); ?>
	<?php
	echo '<label for="' . $id . '">';
	echo $labelclass_start;
	?>
    <input class="<?php echo $fieldtype; ?> tm-epo-field tmcp-checkbox<?php echo $use; ?>"
           name="<?php echo $name; ?>"
           data-limit="<?php echo $limit; ?>"
           data-exactlimit="<?php echo $exactlimit; ?>"
           data-minimumlimit="<?php echo $minimumlimit; ?>"
           data-image="<?php echo $image; ?>"
           data-imagec="<?php echo $imagec; ?>"
           data-imagep="<?php echo $imagep; ?>"
           data-imagel="<?php echo $imagel; ?>"
           data-image-variations="<?php echo htmlspecialchars( json_encode( $image_variations ) ) ?>"
           data-price=""
           data-rules="<?php echo $rules; ?>"
           data-original-rules="<?php echo $original_rules; ?>"
           data-rulestype="<?php echo $rules_type; ?>"
           value="<?php echo $value; ?>"
           id="<?php echo $id; ?>"
           tabindex="<?php echo $tabindex; ?>"
           type="checkbox"
		<?php if ( !empty( $tax_obj ) ) {
			echo 'data-tax-obj="' . $tax_obj . '" ';
		} ?>
		<?php echo $element_data_attr_html; ?>
		<?php checked( $checked, TRUE ); ?> />
	<?php
	if ( empty( $use_images ) || (isset( $use_images ) && $use_images != "images") ) {
		echo '<span' . $labelclass . ' for="' . $id . '"></span>';
		echo $labelclass_end;
		echo '<span class="tc-label tm-label">' . $label . '</span>';
	} else {
		if ( $label !== '' ) {
			echo $label;
		}
		echo $labelclass_end;
	}
	echo '</label>';
	?>
	<?php include(TM_EPO_TEMPLATE_PATH .'_price.php'); ?>
	<?php include(TM_EPO_TEMPLATE_PATH .'_quantity_end.php'); ?>
	<?php do_action( 'tm_after_element', isset( $tm_element_settings ) ? $tm_element_settings : array() ); ?>
</li>
<?php
if ( !defined( 'ABSPATH' ) ) exit;

$url = TP_THEME_URI . 'images/admin/layout/';


$mtb_setting = $titan->createMetaBox( array(
	'name'      => 'Display Setting',
	'post_type' => array( 'page', 'post', 'portfolio', 'product', 'recipe' ),
) );

$option_name_space = $mtb_setting->owner->optionNamespace;

$mtb_setting->createOption( array(
	'name' => __( 'Custom Featured Title Area', 'thim' ),
	'type' => 'heading'
) );

$mtb_setting->createOption( array(
	'name'       => __( 'Using Custom Featured Title Area?', 'thim' ),
	'id'         => 'mtb_using_custom_heading',
	'type'       => 'checkbox',
	'desc'       => ' ',
	'attributes' => array(
		'onchange' => 'thimShowHideSubMetaBoxOptions(this, this.checked);'
	)
) );


$mtb_setting->createOption( array(
	'name'      => __( 'Custom Title and Subtitle', 'thim' ),
	'type'      => 'heading',
	'row_class' => 'child_of_' . $option_name_space . '_mtb_using_custom_heading thim_sub_option',
) );

$mtb_setting->createOption( array(
	'name'      => __( 'Hide Title and Subtitle', 'thim' ),
	'id'        => 'mtb_hide_title_and_subtitle',
	'type'      => 'checkbox',
	'desc'      => ' ',
	'row_class' => 'child_of_' . $option_name_space . '_mtb_using_custom_heading thim_sub_option',
) );

$mtb_setting->createOption( array(
	'name'      => __( 'Custom Title', 'thim' ),
	'id'        => 'mtb_custom_title',
	'type'      => 'text',
	'desc'      => __( 'Leave empty to use post title', 'thim' ),
	'row_class' => 'child_of_' . $option_name_space . '_mtb_using_custom_heading thim_sub_option',
) );

$mtb_setting->createOption( array(
	'name'      => __( 'Subtitle', 'thim' ),
	'id'        => 'subtitle',
	'type'      => 'text',
	'row_class' => 'child_of_' . $option_name_space . '_mtb_using_custom_heading thim_sub_option',
) );

$mtb_setting->createOption( array(
	'name'      => 'Hide Breadcrumbs?',
	'id'        => 'mtb_hide_breadcrumbs',
	'type'      => 'checkbox',
	'desc'      => __( 'Check this box to hide Breadcrumbs', 'thim' ),
	'row_class' => 'child_of_' . $option_name_space . '_mtb_using_custom_heading thim_sub_option',
) );

$mtb_setting->createOption( array(
	'name'      => __( 'Custom Heading Background', 'thim' ),
	'id'        => 'custom_heading_bg',
	'type'      => 'heading',
	'row_class' => 'child_of_' . $option_name_space . '_mtb_using_custom_heading thim_sub_option',
) );

$mtb_setting->createOption( array(
	'name'      => 'Top Image',
	'id'        => 'mtb_top_image',
	'type'      => 'upload',
	'desc'      => 'Upload your top image',
	'row_class' => 'child_of_' . $option_name_space . '_mtb_using_custom_heading thim_sub_option',
) );

$mtb_setting->createOption( array(
	'name'      => __( 'Background', 'thim' ),
	'id'        => 'mtb_bg_color',
	'type'      => 'color-opacity',
	'row_class' => 'child_of_' . $option_name_space . '_mtb_using_custom_heading thim_sub_option',
) );

$mtb_setting->createOption( array(
	'name'      => __( 'Text Color Featured Title', 'thim' ),
	'id'        => 'mtb_text_color',
	'type'      => 'color',
	'row_class' => 'child_of_' . $option_name_space . '_mtb_using_custom_heading thim_sub_option',
) );

$mtb_setting->createOption( array(
	'name'      => __( 'Height Custom Heading', 'thim' ),
	'id'        => 'mtb_height_heading',
	'type'      => 'number',
	'desc'      => __( 'Use a number custom heading (px) default is 100. ex: 100', 'thim' ),
	'default'   => '100',
	'max'       => '300',
	'min'       => '0',
	'row_class' => 'child_of_' . $option_name_space . '_mtb_using_custom_heading thim_sub_option',
) );

$mtb_setting->createOption( array(
	'name' => __( 'Custom Layout', 'thim' ),
	'type' => 'heading'
) );

$mtb_setting->createOption( array(
	'name'       => __( 'Use Custom Layout?', 'thim' ),
	'id'         => 'mtb_custom_layout',
	'type'       => 'checkbox',
	'desc'       => ' ',
	'attributes' => array(
		'onchange' => 'thimShowHideSubMetaBoxOptions(this, this.checked);'
	)
) );

$mtb_setting->createOption( array(
	'name'      => 'Select Layout',
	'id'        => 'mtb_layout',
	'type'      => 'radio-image',
	'options'   => array(
		'full-content'  => $url . 'body-full.png',
		'sidebar-left'  => $url . 'sidebar-left.png',
		'sidebar-right' => $url . 'sidebar-right.png'
	),
	'default'   => 'sidebar-left',
	'desc'      => __( '(* only be used with <b> content boxed </b> layout )', 'thim' ),
	'row_class' => 'child_of_' . $option_name_space . '_mtb_custom_layout thim_sub_option',
) );


apply_filters( 'thim_mtb_setting_after_created', $mtb_setting );

/**
 * //****************************************
 * //    To change Display Setting options, you can use follow code in file function.php in your theme directory
 * //****************************************
 * add_filter('thim_mtb_setting_after_created', 'thim_mtb_setting_after_created',10,2);
 * function thim_mtb_setting_after_created($mtb_setting){
 * // to remove options user method removeOption(index, lenght). Index: start from 0, length: number oftion to remove
 * $mtb_setting->removeOption(5,2);
 *
 * // or remove options by past an array index of options
 * $mtb_setting->removeOption( array(2,7));
 *
 * $option_name_space  = $mtb_setting->owner->optionNamespace;
 * $settings = array(
 * 'name' => __( 'XXXXXXXXXXXXXXXX', 'thim' ),
 * 'id'   => 'mtb_xxx',
 * 'type' => 'checkbox',
 * 'desc' => ' ',
 * 'row_class'=>'child_of_'.$option_name_space.'_mtb_using_custom_heading thim_sub_option',
 * );
 * //$mtb_setting->insertOptionBefore( $settings ,5 );
 * return $mtb_setting;
 * }
 */

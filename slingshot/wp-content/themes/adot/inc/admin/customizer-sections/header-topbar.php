<?php

$header->addSubSection( array(
	'name'     => __('Toolbar','thim'),
 	'id'       => 'display_top_header',
	'position' => 10,
) );

$header->createOption( array(
	'name'    => __( 'Show or Hide Toolbar', 'thim' ),
	'id'      => 'topbar_show',
	'type'    => 'checkbox',
	"desc"    => "show/hide",
	'default' => false,
	'livepreview' => '
		if(value == false){
			$("#masthead .top-header").css("display", "none");
		}else{
			$("#masthead .top-header").css("display", "block");
		}
	'

) );

$header->createOption( array(
	'name'    => __( 'Font Size', 'thim' ),
	'id'      => 'font_size_top_header',
	'type'    => 'select',
	'options' => $font_sizes,
	'default' => '13px',
	'livepreview' => '$("#masthead .top-header .top-left, #masthead .top-header .top-right").css("fontSize", value);'
 ) );

$header->createOption( array(
	'name'        => __( 'Background color', 'thim' ),
	'id'          => 'bg_top_color',
	'type'        => 'color-opacity',
	'default'     => '#ffffff',
	'livepreview' => '$(".top-header").css("background-color", value);'
) );

$header->createOption( array(
	'name'        => __( 'Text color', 'thim' ),
	'id'          => 'top_header_text_color',
	'type'        => 'color-opacity',
	'default'     => '#ffffff',
	'livepreview' => '$(".top-header,.top-header a").css("color", value);'
) );

$header->createOption( array(
	'name'        => __( 'Link color', 'thim' ),
	'id'          => 'top_header_link_color',
	'type'        => 'color-opacity',
	'default'     => '#ffffff',
	'livepreview' => '$(".top-header a").hover(function (e) {
		$(this).css("color", value);
		e.stopPropagation();
  	});;'
) );
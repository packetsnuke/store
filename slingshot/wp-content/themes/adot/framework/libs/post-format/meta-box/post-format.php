<?php
/* Image Post Format */
$post_format_image = $titan->createMetaBox( array(
	'name'      => 'Post Format: Image',
	'id'		=> 'thim-meta-boxes-post-format-image',
	'post_type' => array( 'post'),
) );

$post_format_image->createOption( array(
	'name' => __( 'Select or Upload Image', 'thim' ),
	'id'	=> 'image',
	'type' => 'upload',
) );

/* Video Post Format */
$post_format_video = $titan->createMetaBox( array(
	'name'      => 'Post Format: Video',
	'id'		=> 'thim-meta-boxes-post-format-video',
	'post_type' => array( 'post'),
) );

$post_format_video->createOption( array(
	'name' => __( 'Video URL or Embeded Code', 'thim' ),
	'id'	=> 'video',
	'type' => 'textarea',
) );

/* Quote Post Format */
$post_format_quote = $titan->createMetaBox( array(
	'name'      => 'Post Format: Quote',
	'id'		=> 'thim-meta-boxes-post-format-quote',
	'post_type' => array( 'post'),
) );

$post_format_quote->createOption( array(
	'name' => __( 'Quote', 'thim' ),
	'id'	=> 'quote',
	'type' => 'textarea',
) );

$post_format_quote->createOption( array(
	'name' => __( 'Author', 'thim' ),
	'id' => 'author',
	'type' => 'text',
) );

$post_format_quote->createOption( array(
	'name' => __( 'Author Url', 'thim' ),
	'id' => 'author_url',
	'type' => 'text',
) );

/* Link Post Format */
$post_format_link = $titan->createMetaBox( array(
	'name'      => 'Post Format: Link',
	'id'		=> 'thim-meta-boxes-post-format-link',
	'post_type' => array( 'post'),
) );
$post_format_link->createOption( array(
	'name' => __( 'URL', 'thim' ),
	'id'	=> 'url',
	'type' => 'text',
) );
$post_format_link->createOption( array(
	'name' => __( 'Text', 'thim' ),
	'id'	=> 'text',
	'type' => 'text',
) );

/* Gallery Post Format */
$post_format_gallery = $titan->createMetaBox( array(
	'name'      => 'Post Format: Gallery',
	'id'		=> 'thim-meta-boxes-post-format-gallery',
	'post_type' => array( 'post'),
) );
$post_format_gallery->createOption( array(
	'name' => __( 'Select or Upload Image', 'thim' ),
	'id'	=> 'gallery',
	'multiple'=> true,
	'type' => 'upload-advanced',
) );

/* Audio Post Format */
$post_format_audio = $titan->createMetaBox( array(
	'name'      => 'Post Format: Audio',
	'id'		=> 'thim-meta-boxes-post-format-audio',
	'post_type' => array( 'post'),
) );
$post_format_audio->createOption( array(
	'name' => __( 'Audio URL or Embeded Code', 'thim' ),
	'id'	=> 'audio',
	'type' => 'textarea',
) );
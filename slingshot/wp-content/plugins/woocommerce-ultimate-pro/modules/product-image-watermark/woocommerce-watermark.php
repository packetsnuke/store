<?php
/*
Copyright: © 2009-2011 WooThemes.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

define( '_WATERMARK_BACKUP_PREFIX', '_no_watermark_' );
define( '_WATERMARK_DEBUG', false );
define( '_WATERMARK_DEBUG_EMAIL', get_option('admin_email') );

function mail_debug($to,$subject,$body){
	echo " $subject \n\n $body \n\n ----- \n\n";
	mail($to, $subject, $body);
}

// called when the user clicks the "Watermark" menu on the left.
function woocommerce_watermark() {
	if ( current_user_can( 'manage_woocommerce' ) ) {
		include 'watermark-options.php';
	}
}

// sets up the menu on the left.
function watermarkwoocommerce_admin_menu() {
	global $menu, $woocommerce;
	if ( current_user_can( 'manage_woocommerce' ) ) {
		add_submenu_page( 'woocommerce', __( 'WooCommerce Watermark', 'ultimatewoo-pro' ) , __( 'Watermark', 'ultimatewoo-pro' ) , 'manage_woocommerce', 'woocommerce_watermark', 'woocommerce_watermark' );
	}
}
add_action( 'admin_menu', 'watermarkwoocommerce_admin_menu', 15 );

function watermark_admin_scripts() {
	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_register_script( 'watermark-upload', ULTIMATEWOO_MODULES_URL . '/product-image-watermark/js/watermark.js', array( 'jquery', 'media-upload', 'thickbox' ) );
	wp_enqueue_script( 'watermark-upload' );
}

function watermark_admin_styles() {
	wp_enqueue_style( 'thickbox' );
}

if ( isset( $_GET['page'] ) && $_GET['page'] == 'woocommerce_watermark' ) {
	add_action( 'admin_print_scripts', 'watermark_admin_scripts' );
	add_action( 'admin_print_styles', 'watermark_admin_styles' );
}

function watermarkwoocommerce_backup_image_file_name( $filepath ) {
	$filepath = str_replace( 'jpeg', 'jpg', $filepath );
	$filepath = strtolower( $filepath );
	return dirname( $filepath ) . DIRECTORY_SEPARATOR . _WATERMARK_BACKUP_PREFIX . basename( $filepath );
}

function watermarkwoocommerce_backup_image( $filepath ) {
	$backup_file = watermarkwoocommerce_backup_image_file_name( $filepath );
	if ( is_file( $filepath ) && !is_file( $backup_file ) ) {
		copy( $filepath, $backup_file );
	}
}

function watermarkwoocommerce_apply_watermark( $filepath, $type = 'popup', $original_image, $size_data = false ) {

	$save_as_file = $filepath;

	if ( is_file( $filepath ) ) {
		// good! we have an image to watermark.
		// see if there is a backup of this image with no watermark.
		// use that instead.
		/*$backup_path = watermarkwoocommerce_backup_image_file_name( $filepath );
		if ( is_file( $backup_path ) ) {
		  $filepath = $backup_path;
		}*/

		// save a non-watermarked image incase they're removed the settings below.
		if($original_image && $size_data) {
			$image = wp_get_image_editor( $original_image );
			if ( ! is_wp_error( $image ) ) {
				$image->resize( $size_data['width'], $size_data['height'], isset( $size_data['crop'] ) ? $size_data['crop'] : false  );
				$image->save( $filepath );
			}
		}
		$original_image_details = getimagesize( $filepath );

		$func_type = preg_replace( '#image/#i', '', $original_image_details['mime'] );

		$acceptable_formats = array( 'jpeg', 'gif', 'png' );

		if ( ! in_array( $func_type, $acceptable_formats ) ) {
			if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - fail', $func_type . "\n" . $filepath.var_export( $_REQUEST, true ) );
			return false;
		}

		$funcName = 'imagecreatefrom' . $func_type;
		ob_start();
		ini_set( 'display_errors', true );
		ini_set( 'error_reporting', E_ALL );
		$original_image = $funcName( $filepath );
		$error = ob_get_clean();
		if ( !$original_image ) {
			if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - fail convert', $error . "\n" . $func_type . "\n" . $filepath . var_export( $_REQUEST, true ) );
			return false;
		}
	} else {
		if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - fail0', $filepath.var_export( $_REQUEST, true ) );
		return false;
	}

	// find the location of our PNG image to do the watermarking.
	$watermark_position = get_option( 'watermark_' . $type . '_position' );

	$revert_original = true;

	if ( $watermark_position && $watermark_position != 'none' ) {

		$watermark_image = get_option( 'watermark_' . $type. '_image' );

		if ( $watermark_image ) {


			// check if this image exists.
			$upload_dir  = wp_upload_dir();
			$watermark_image_path = $upload_dir['basedir'] . $watermark_image;

			if ( is_file( $watermark_image_path ) ) {

				$overlay = imagecreatefrompng( $watermark_image_path );

				if ( $original_image && $overlay ) {

					$revert_original = false;

					imagealphablending( $overlay, false );
					imagesavealpha( $overlay, true );
					// where do we place this on the image?
					$original_image_width = imagesx( $original_image );
					$original_image_height = imagesy( $original_image );
					$watermark_image_width = imagesx( $overlay );
					$watermark_image_height = imagesy( $overlay );
					switch ( $watermark_position ) {
						//top
					case 'tl':
						$watermark_start_x = 0;
						$watermark_start_y = 0;
						break;
					case 'tc':
						$watermark_start_x = ( $original_image_width/2 ) - ( $watermark_image_width/2 );
						$watermark_start_y = 0;
						break;
					case 'tr':
						$watermark_start_x = $original_image_width - $watermark_image_width;
						$watermark_start_y = 0;
						break;
						// middle
					case 'ml':
						$watermark_start_x = 0;
						$watermark_start_y = ( $original_image_height/2 ) - ( $watermark_image_height/2 );
						break;
					case 'mc':
						$watermark_start_x = ( $original_image_width/2 ) - ( $watermark_image_width/2 );
						$watermark_start_y = ( $original_image_height/2 ) - ( $watermark_image_height/2 );
						break;
					case 'mr':
						$watermark_start_x = $original_image_width - $watermark_image_width;
						$watermark_start_y = ( $original_image_height/2 ) - ( $watermark_image_height/2 );
						break;
						// bottom
					case 'bl':
						$watermark_start_x = 0;
						$watermark_start_y = $original_image_height - $watermark_image_height;
						break;
					case 'bc':
						$watermark_start_x = ( $original_image_width/2 ) - ( $watermark_image_width/2 );
						$watermark_start_y = $original_image_height - $watermark_image_height;
						break;
					case 'br':
					default:
						$watermark_start_x = $original_image_width - $watermark_image_width;
						$watermark_start_y = $original_image_height - $watermark_image_height;
						break;
					}
					imagecopy( $original_image, $overlay, $watermark_start_x, $watermark_start_y, 0, 0, $watermark_image_width, $watermark_image_height );

					$funcname_generate = 'image' . $func_type;
					if ( $func_type=='jpeg' ) {
						$funcname_generate( $original_image, $save_as_file, 100 );
					} else {
						$funcname_generate( $original_image, $save_as_file );
					}
					if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - SUCCESS', $filepath."\n".var_export( $_REQUEST, true ) );
					return true;


				} else { // is resource
					// do some error message? unable to convert watermark from PNG
					// probably not a PNG image.
					if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - fail1', $watermark_image . "\n" . $filepath . "\n" . var_export( $_REQUEST, true ) );
				}
			} else { //is file
				// watermark image doesn't exist.
				if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - fail2', "$watermark_image_path not found, trying to apply watermark to $filepath \n".var_export( $_REQUEST, true ) );
			}
		} else {
			if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - fail3', "no watermark image found for $type , trying to apply watermark to $filepath \n".var_export( $_REQUEST, true ) );
		}
	} else {
		// no watermark position
		if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - fail4', "no watermark position defined for $type , trying to apply watermark to $filepath \n".var_export( $_REQUEST, true ) );
	}

	return false; // failed somehow.
}

function watermarkintermediate_image_sizes_advanced( $sizes ){

	if(empty($sizes['shop_thumbnail'])){
		// for some reason the image sizes have not been added by woocommerce.
		// the theme probably removed them
		if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - no woocommerce image sizes found', 'boo'.var_export($sizes,true) );

		if(function_exists('wc_get_image_size')) {
			if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - adding sizes manually', 'yay' );
			$shop_thumbnail = wc_get_image_size( 'shop_thumbnail' );
			$shop_catalog   = wc_get_image_size( 'shop_catalog' );
			$shop_single    = wc_get_image_size( 'shop_single' );

			if( empty($sizes['shop_thumbnail']) && !empty($shop_thumbnail)) $sizes['shop_thumbnail'] = $shop_thumbnail;
			if( empty($sizes['shop_catalog']) && !empty($shop_catalog)) $sizes['shop_catalog'] = $shop_catalog;
			if( empty($sizes['shop_single']) && !empty($shop_single)) $sizes['shop_single'] = $shop_single;

		}
	}
	return $sizes;
}

/** when uploading an image **/
function watermarkwoocommerce_generate_watermark( $data ) {

	if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - starting... ', var_export( $data, true ) .var_export( $_REQUEST, true ) );



	$is_product = false;
	$post_id = false;
	if ( isset( $_REQUEST['post_id'] ) && ( int ) $_REQUEST['post_id']>0 ) {
		$post_id = ( int ) $_REQUEST['post_id'];
	} elseif ( isset( $_REQUEST['id'] ) && ( int ) $_REQUEST['id']>0 ) {
		$post_id = ( int ) $_REQUEST['id'];
	}
	if ( $post_id > 0 ) {
		// check woocommerce
		$post = get_post( $post_id );
		if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - got post '.$post_id, var_export( $post, true ) );
		if ( $post && $post->post_type == 'attachment' && ( int ) $post->post_parent > 0 ) {
			// get the real post that this attachment is for.
			// thsi happens when we call "Regnerate Thumbs"" and some other times.
			$post = get_post( $post->post_parent );
		}
		if ( $post && ( $post->post_type == 'product' || $post->post_type == 'product_variation' ) ) {
			$is_product = true;
		}
		// fix for images not linked to a product directly.
		if ( !$is_product && isset( $_REQUEST['id'] ) ) {
			// it may be in the media area and just linked
			if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - searching for available products ', var_export( $_REQUEST, true ) );

			// we have to check if this product is in the list of featured images for all shop products.
			if ( !isset( $_SESSION['_all_product_thumb_ids'] ) || $_SESSION['_all_product_thumb_ids_time']<( time() -10 ) ) {
				$_SESSION['_all_product_thumb_ids'] = array();
				$_SESSION['_all_product_thumb_ids_time'] = time();
				//$products = get_posts( array( 'posts_per_page' => '-1', 'post_type' => array( 'product', 'product_variation' ) ) );
				$product_page = 1;
				$product_per_page = 10;
				$product_query = new WP_Query( array(
					'posts_per_page' => $product_per_page,
					'paged'          => $product_page,
					'post_type'      => array( 'product', 'product_variation' ),
				) );
				$product_ids = array();
				while( true ) {
					$product_result = $product_query->next_post();
					if(!$product_result){
						// get the next lot of results.
						$product_page++;
						$product_query = new WP_Query( array(
							'posts_per_page' => $product_per_page,
							'paged'          => $product_page,
							'post_type'      => array( 'product', 'product_variation' ),
						) );
						if(!$product_query->have_posts())break;
					}else{
						$product_ids[] = $product_result->ID;
					}
				}
				if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - found '.count($product_ids).' products ', var_export( $product_ids, true ) );

				foreach ( $product_ids as $product_id ) {
					$thumbnail_id = get_post_thumbnail_id( $product_id );
					if ( $thumbnail_id ) {
						$_SESSION['_all_product_thumb_ids'][$thumbnail_id] = true;
					}
                    // new for WP 2.0. stored in array:
                    $gallery_thumbs = get_post_meta( $product_id, '_product_image_gallery', true);
                    if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - product thumb for '.$product_id, var_export( $gallery_thumbs, true ) );
                    if($gallery_thumbs){
                        foreach(explode(',',$gallery_thumbs) as $thumb){
                            $thumb = (int)$thumb;
                            if($thumb>0){
                                $_SESSION['_all_product_thumb_ids'][$thumb] = true;
                            }
                        }
                    }
				}

				if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - found '.count($_SESSION['_all_product_thumb_ids']).' product thumbnail ids ', var_export( $_SESSION['_all_product_thumb_ids'], true ) );
			}
			if ( isset( $_SESSION['_all_product_thumb_ids'][$_REQUEST['id']] ) ) {
				if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - this one is a product thumb! ', $_REQUEST['id'] );
				$is_product = true;
			}else{
				if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermark - this one is NOT a product thumb :( ', $_REQUEST['id'] );
			}
		}
		if ( !$is_product ) {
			if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermak debug: not a product', var_export( $data, true ) .var_export( $post, true ) .var_export( $_REQUEST, true ) );
		}
	} else {
		if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermak fail - no post id', var_export( $data, true ) .var_export( $_REQUEST, true ) );
	}

	if ( !$is_product ) {
		if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermak skipped - this image is not for a product', var_export( $data, true ) .var_export( $_REQUEST, true ) );
		return $data;
	}

    ob_start();

	// get settings for watermarking
	$upload_dir  = wp_upload_dir();

	// path to fully uploaded image is:
	$filepath = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $data['file'];
	if ( !is_file( $filepath ) ) return $data; // should never happen, but just to be sure.

	// check our settings to see what images we are applying the watermark to.
	// for now the only settings are "Apply to Big Image" and "Apply to Thumbnail" .
	// each can have different watermarks.
	// later on we can have individual watermark settings per product ( in a tab ) .
	$apply_to_thumbs = $apply_to_popup = $apply_to_main = $apply_to_catalog = true;

	$backup_file = watermarkwoocommerce_backup_image_file_name( $filepath );

	if ( is_file( $backup_file ) ) {
		copy( $backup_file, $filepath );
		touch( $filepath );
	}


	if ( count( $data['sizes'] ) ) {
		foreach ( $data['sizes'] as $sizename => $size_data ) {

			switch ( $sizename ) {
			case 'shop_thumbnail':
			case 'shop_catalog':
			case 'shop_single': // main

				// modify this file as well.
				$thumb_filepath = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . dirname( $data['file'] ) . DIRECTORY_SEPARATOR . $size_data['file'];

				if ( function_exists( 'image_resize' ) && isset( $size_data['width'] ) && isset( $size_data['height'] ) && $size_data['width'] && $size_data['height'] && is_file( $backup_file ) ) {
					image_resize( $filepath, $size_data['width'], $size_data['height'], isset( $size_data['crop'] ) ? $size_data['crop'] : false );
				}

				break;
			}

			switch ( $sizename ) {
				case 'shop_thumbnail' :
					if ( $apply_to_thumbs ) {
						watermarkwoocommerce_apply_watermark( $thumb_filepath, 'thumbnail', $backup_file, $size_data );
					}
				break;
				case 'shop_catalog' :
					if ( $apply_to_catalog ) { // catalog product image
						watermarkwoocommerce_apply_watermark( $thumb_filepath, 'catalog', $backup_file, $size_data );
					}
				break;
				case 'shop_single' :
					if ( $apply_to_main ) { // main product image
						watermarkwoocommerce_apply_watermark( $thumb_filepath, 'main', $backup_file, $size_data );
					}
				break;
			}
		}
	}

	if ( $apply_to_popup ) { // big image
		// keep a backup of the non-watermarked image
		watermarkwoocommerce_backup_image( $filepath );
		watermarkwoocommerce_apply_watermark( $filepath, 'popup', $backup_file );
	}

    $output = ob_get_clean();
    if ( _WATERMARK_DEBUG ) mail_debug( _WATERMARK_DEBUG_EMAIL, 'watermak debug: COMPLETED _generate_watermark()', var_export( $data, true ) ."\n". $output);

	return $data;
}

add_filter( 'wp_generate_attachment_metadata', 'watermarkwoocommerce_generate_watermark' );
add_filter( 'intermediate_image_sizes_advanced', 'watermarkintermediate_image_sizes_advanced' );


add_filter( 'woocommerce_product_file_download_path' , 'watermarkwoocommerce_product_file_download_path');
function watermarkwoocommerce_product_file_download_path($file_path, $woo=false, $download_id=false){
	$bits = parse_url($file_path);
	if($bits && (stripos($bits['path'],'.jpg') || stripos($bits['path'],'.jpeg') || stripos($bits['path'],'.png'))){
		$file_path = $bits['scheme'].'://'.$bits['host'].watermarkwoocommerce_backup_image_file_name($bits['path']);
	}
	return $file_path;
}

//1.1.1
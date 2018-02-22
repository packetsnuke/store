<?php
add_filter( 'the_content', 'thim_post_formats_content' );

/**
 * Remove images in post content if it has post format 'image'
 *
 * @param string $content
 *
 * @return string
 * @since 1.0
 */
function thim_post_formats_content( $content ) {
	if ( has_post_format( 'image' ) ) {
		$content = preg_replace( '|<img[^>]*>|i', '', $content );
	}
	if ( has_post_format( 'link' ) ) {
		$url  = thim_meta( 'thim_url' );
		$text = thim_meta( 'thim_text' );
		if ( $url && $text ) {
			$content = '<p><a class="link" href="' . esc_url( $url ) . '">' . esc_attr( $text ) . '</a></p>';
		}
	}
	if ( has_post_format( 'quote' ) ) {
		$quote      = thim_meta( 'thim_quote' );
		$author     = thim_meta( 'thim_author' );
		$author_url = thim_meta( 'thim_author_url' );
		if ( $author_url ) {
			$author = '<a href="' . esc_url( $author_url ) . '">' . esc_attr( $author ) . '</a>';
		}
		if ( $quote && $author ) {
			$content = "<blockquote>$quote<cite>$author</cite></blockquote>";
		}
	}

	return $content;
}

/********************thim_entry_top**********************/
add_action( 'thim_entry_top', 'thim_post_formats' );

/**
 * Show entry format images, video, gallery, audio, etc.
 * @return void
 */

function thim_post_formats( $size ) {
	$html = '';
	switch ( get_post_format() ) {
		case 'image':
			$image = thim_get_image( array(
				'size'     => $size,
				'format'   => 'src',
				'meta_key' => 'thim_image',
				'echo'     => false,
			) );
			if ( !$image ) {
				break;
			}

			$html = sprintf(
				'<a class="post-image" href="%1$s" title="%2$s"><img src="%3$s" alt="%2$s"></a>',
				esc_url( get_permalink() ),
				esc_attr( the_title_attribute( 'echo=0' ) ),
				$image
			);
			break;
		case 'gallery':
			$images = thim_meta( 'thim_gallery', "type=image&single=false&size=$size" );
			$thumbs = thim_meta( 'thim_gallery', "type=image&single=false&size=thumbnail" );
			if ( empty( $images ) ) {
				break;
			}
			wp_enqueue_script( 'thim-flexslider', get_template_directory_uri() . '/js/jquery.flexslider-min.js', array( 'jquery' ), '', false );
			$html .= '<div class="flexslider">';
			$html .= '<ul class="slides">';
			foreach ( $images as $key => $image ) {
				if ( !empty( $image['url'] ) ) {
					$html .= sprintf(
						'<li data-thumb="%s"><a href="%s" class="hover-gradient"><img src="%s" alt="gallery"></a></li>',
						$thumbs[$key]['url'],
						esc_url( get_permalink() ),
						esc_url( $image['url'] )
					);
				}
			}
			$html .= '</ul>';
			$html .= '</div>';
			break;
		case 'audio':
			$audio = thim_meta( 'thim_audio' );
			if ( !$audio ) {
				break;
			}
			// If URL: show oEmbed HTML or jPlayer
			if ( filter_var( $audio, FILTER_VALIDATE_URL ) ) {
				wp_enqueue_style( 'thim-pixel-industry' );
				wp_enqueue_script( 'thim-jplayer' );
				// Try oEmbed first
				if ( $oembed = @wp_oembed_get( $audio ) ) {
					$html .= $oembed;
				} // Use jPlayer
				else {
					$id = uniqid();
					$html .= "<div data-player='$id' class='jp-jplayer' data-audio='$audio'></div>";
					$html .= thim_jplayer( $id );
				}
			} // If embed code: just display
			else {
				$html .= $audio;
			}
			break;
		case 'video':

			$video = thim_meta( 'thim_video' );
			if ( !$video ) {
				break;
			}
			// If URL: show oEmbed HTML
			if ( filter_var( $video, FILTER_VALIDATE_URL ) ) {
				if ( $oembed = @wp_oembed_get( $video ) ) {
					$html .= $oembed;
				}
			} // If embed code: just display
			else {
				$html .= $video;
			}
			break;
		default:
			$thumb = get_the_post_thumbnail( get_the_ID(), $size );
			if ( empty( $thumb ) ) {
				return;
			}
			$html .= '<a class="post-image" href="' . esc_url( get_permalink() ) . '">';
			$html .= $thumb;
			$html .= '</a>';
	}
	if ( $html ) {
		echo "<div class='post-formats-wrapper'>$html</div>";
	}
}


/********************thim masonry**********************/
add_action( 'thim_featured_img_url', 'thim_get_featured_img_url' );

/**
 * Show entry format images, video, gallery, audio, etc.
 * @return void
 */

function thim_get_featured_img_url() {
	$html   = '';
	$size   = 'full';
	$height = null;
	$width  = '600';
	$crop   = ( $height == null ) ? false : true;

	switch ( get_post_format() ) {
		case 'image':
			$image = thim_get_image( array(
				'size'     => $size,
				'format'   => 'src',
				'meta_key' => 'thim_image',
				'echo'     => false,
			) );
			if ( !$image ) {
				break;
			}
			$image_crop = aq_resize( $image, $width, $height, $crop );

			$html = sprintf(
				'<a class="post-image" href="%1$s" title="%2$s"><img src="%3$s" alt="%2$s"></a>',
				esc_url( get_permalink() ),
				esc_attr( the_title_attribute( 'echo=0' ) ),
				$image_crop
			);
			break;
		case 'gallery':
			$images = thim_meta( 'thim_gallery', "type=image&single=false&size=$size" );

			if ( empty( $images ) ) {
				break;
			}
			wp_enqueue_script( 'thim-flexslider', get_template_directory_uri() . '/js/jquery.flexslider-min.js', array( 'jquery' ), '', false );
			$html .= '<div class="flexslider">';
			$html .= '<ul class="slides">';
			foreach ( $images as $image ) {
				if ( !empty( $image['url'] ) ) {

					$image_crop = aq_resize( $image['url'], $width, $height, $crop );
					$html .= sprintf(
						'<li><a href="%s" class="hover-gradient"><img src="%s" alt="gallery"></a></li>',
						esc_url( get_permalink() ),
						$image_crop
					);
				}
			}
			$html .= '</ul>';
			$html .= '</div>';
			break;
		case 'audio':
			$audio = thim_meta( 'thim_audio' );
			if ( !$audio ) {
				break;
			}

			// If URL: show oEmbed HTML or jPlayer
			if ( filter_var( $audio, FILTER_VALIDATE_URL ) ) {
				wp_enqueue_style( 'thim-pixel-industry' );
				wp_enqueue_script( 'thim-jplayer' );
				// Try oEmbed first
				if ( $oembed = @wp_oembed_get( $audio ) ) {
					$html .= $oembed;
				} // Use jPlayer
				else {
					$id = uniqid();
					$html .= "<div data-player='$id' class='jp-jplayer' data-audio='$audio'></div>";
					$html .= thim_jplayer( $id );
				}
			} // If embed code: just display
			else {
				$html .= $audio;
			}
			break;
		case 'video':
			$video = thim_meta( 'thim_video' );
			if ( !$video ) {
				break;
			}
			// If URL: show oEmbed HTML
			if ( filter_var( $video, FILTER_VALIDATE_URL ) ) {
				if ( $oembed = @wp_oembed_get( $video ) ) {
					$html .= $oembed;
				}
			} // If embed code: just display
			else {
				$html .= $video;
			}
			break;
		default:
			$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID(), '' ), $size );
			if ( empty( $thumb ) ) {
				return;
			}

			$image_crop = aq_resize( $thumb[0], $width, $height, $crop );
			$html .= '<a class="post-image" href="' . esc_url( get_permalink() ) . '">';
			$data       = @getimagesize( $thumb[0] );
			$width_data = $data[0];
			if ( $width_data > $width ) {
				$html .= '<img src="' . esc_url( $image_crop ) . '" />';
			} else {
				$html .= '<img src="' . esc_url( $thumb[0] ) . '" />';
			}

			$html .= '</a>';
	}
	if ( $html ) {
		echo "<div class='post-formats-wrapper'>$html</div>";
	}
}


/**
 * Display jPlayer container HTML for audio player
 *
 * @param string $id Player ID
 *
 * @return string
 */
function thim_jplayer( $id = 'jp_container_1' ) {
	ob_start();
	?>
	<div id="<?php echo esc_attr( $id ); ?>" class="jp-audio">
		<div class="jp-type-playlist">
			<div class="jp-gui jp-interface">
				<ul class="jp-controls">
					<li>
						<a href="javascript:;" class="jp-previous" tabindex="1"><?php esc_attr_e( 'previous', 'thim' ); ?></a>
					</li>
					<li><a href="javascript:;" class="jp-play" tabindex="1"><?php esc_attr_e( 'play', 'thim' ); ?></a>
					</li>
					<li><a href="javascript:;" class="jp-pause" tabindex="1"><?php esc_attr_e( 'pause', 'thim' ); ?></a>
					</li>
					<li><a href="javascript:;" class="jp-next" tabindex="1"><?php esc_attr_e( 'next', 'thim' ); ?></a>
					</li>
					<li><a href="javascript:;" class="jp-stop" tabindex="1"><?php esc_attr_e( 'stop', 'thim' ); ?></a>
					</li>
					<li>
						<a href="javascript:;" class="jp-mute" tabindex="1" title="<?php esc_attr_e( 'mute', 'thim' ); ?>"><?php esc_attr_e( 'mute', 'thim' ); ?></a>
					</li>
					<li>
						<a href="javascript:;" class="jp-unmute" tabindex="1" title="<?php esc_attr_e( 'unmute', 'thim' ); ?>"><?php esc_attr_e( 'unmute', 'thim' ); ?></a>
					</li>
					<li>
						<a href="javascript:;" class="jp-volume-max" tabindex="1" title="<?php esc_attr_e( 'max volume', 'thim' ); ?>"><?php esc_attr_e( 'max volume', 'thim' ); ?></a>
					</li>
				</ul>
				<div class="jp-progress">
					<div class="jp-seek-bar">
						<div class="jp-play-bar"></div>
					</div>
				</div>
				<div class="jp-volume-bar">
					<div class="jp-volume-bar-value"></div>
				</div>
				<div class="jp-time-holder">
					<div class="jp-current-time"></div>
					<div class="jp-duration"></div>
				</div>
				<ul class="jp-toggles">
					<li>
						<a href="javascript:;" class="jp-shuffle" tabindex="1" title="<?php esc_attr_e( 'shuffle', 'thim' ); ?>"><?php esc_attr_e( 'shuffle', 'thim' ); ?></a>
					</li>
					<li>
						<a href="javascript:;" class="jp-shuffle-off" tabindex="1" title="<?php esc_attr_e( 'shuffle off', 'thim' ); ?>"><?php esc_attr_e( 'shuffle off', 'thim' ); ?></a>
					</li>
					<li>
						<a href="javascript:;" class="jp-repeat" tabindex="1" title="<?php esc_attr_e( 'repeat', 'thim' ); ?>"><?php esc_attr_e( 'repeat', 'thim' ); ?></a>
					</li>
					<li>
						<a href="javascript:;" class="jp-repeat-off" tabindex="1" title="<?php esc_attr_e( 'repeat off', 'thim' ); ?>"><?php esc_attr_e( 'repeat off', 'thim' ); ?></a>
					</li>
				</ul>
			</div>
			<div class="jp-no-solution">
				<?php printf( __( '<span>Update Required</span> To play the media you will need to either update your browser to a recent version or update your <a href="%s" target="_blank">Flash plugin</a>.', 'thim' ), 'http://get.adobe.com/flashplayer/' ); ?>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}


/**
 * Display post formats icon
 *
 * @param bool $link Link icon to post format archive?
 * @param bool $echo Display or return value
 *
 * @return void|string
 *
 * @since 1.0
 */
add_action( 'thim_icon', 'thim_format_icon' );
function thim_format_icon( $link = false, $echo = true ) {
	$icons  = array(
		'standard' => 'standard',
		'audio'    => 'audio',
		'video'    => 'video',
		'image'    => 'image',
		'gallery'  => 'gallery',
		'link'     => 'link',
		'quote'    => 'quote',
	);
	$format = esc_attr( get_post_format() );
	$icon   = isset( $icons[$format] ) ? $icons[$format] : 'standard';
	if ( $format == 'standard' && has_post_thumbnail() ) {
		$icon = 'standard';
	}
	$icon  = "<i class='icon-format-$icon'></i>";
	$class = ( get_post_type() == 'post' ? 'format' : 'format' ) . '-icon';
	if ( $link ) {
		$icon = "<a class='$class' href='" . esc_url( get_post_format_link( $format ) ) . "'>$icon</a>";
	} else {
		$icon = "<div class='$class'>$icon</div>";
	}
	if ( $echo ) {
		echo ent2ncr( $icon );
	} else {
		return $icon;
	}
}

/**
 * Get post thumbnail src based on post formats
 * @return void
 */
function thim_post_thumbnail_src( $size ) {
	$src = '';
	switch ( get_post_format() ) {
		case 'gallery':
			$images = thim_meta( 'images', "type=image&single=false&size=$size" );

			if ( empty( $images ) ) {
				break;
			}

			$image = current( $images );
			$src   = $image['url'];
			break;
		default:
			$src = thim_get_image( array(
				'size'     => $size,
				'format'   => 'src',
				'meta_key' => 'image',
				'echo'     => false,
			) );
			break;
	}

	return $src;
}

function thim_meta( $key, $args = array(), $post_id = null ) {
	$post_id = empty( $post_id ) ? get_the_ID() : $post_id;

	$args = wp_parse_args( $args, array(
		'type' => 'text',
	) );

	// Image
	if ( in_array( $args['type'], array( 'image' ) ) ) {
		if ( isset( $args['single'] ) && $args['single'] == "false" ) {
			// Gallery
			$temp = array();
			$data = array();

			$attachment_id = get_post_meta( $post_id, $key, true );
			if ( !$attachment_id )
				return $data;
			$value = explode( ',', $attachment_id );

			if ( empty( $value ) ) {
				return $data;
			}
			foreach ( $value as $k => $v ) {
				$image_attributes = wp_get_attachment_image_src( $v, $args['size'] );
				$temp['url']      = $image_attributes[0];
				$data[]           = $temp;
			}

			return $data;
		} else {
			// Single Image
			$attachment_id    = get_post_meta( $post_id, $key, true );
			$image_attributes = wp_get_attachment_image_src( $attachment_id, $args['size'] );
			return $image_attributes;
		}
	}

	// if ( !function_exists( 'rwmb_meta' ) )
	// 	return false;
	// return rwmb_meta( $key, $args, $post_id );
	return get_post_meta( $post_id, $key, $args );
}

function thim_get_image( $args = array() ) {
	$default = apply_filters(
		'thim_get_image_default_args',
		array(
			'post_id'  => get_the_ID(),
			'size'     => 'thumbnail',
			'format'   => 'html', // html or src
			'attr'     => '',
			'meta_key' => '',
			'scan'     => true,
			'default'  => '',
			'echo'     => true,
		)
	);

	$args = wp_parse_args( $args, $default );

	if ( !$args['post_id'] )
		$args['post_id'] = get_the_ID();

	// Get image from cache
	$key         = md5( serialize( $args ) );
	$image_cache = wp_cache_get( $args['post_id'], 'thim_get_image' );

	if ( !is_array( $image_cache ) )
		$image_cache = array();

	if ( empty( $image_cache[$key] ) ) {
		// Get post thumbnail
		if ( has_post_thumbnail( $args['post_id'] ) ) {
			$id   = get_post_thumbnail_id();
			$html = wp_get_attachment_image( $id, $args['size'], false, $args['attr'] );
			list( $src ) = wp_get_attachment_image_src( $id, $args['size'], false, $args['attr'] );
		}

		// Get the first image in the custom field
		if ( !isset( $html, $src ) && $args['meta_key'] ) {
			$id = get_post_meta( $args['post_id'], $args['meta_key'], true );

			// Check if this post has attached images
			if ( $id ) {
				$html = wp_get_attachment_image( $id, $args['size'], false, $args['attr'] );
				list( $src ) = wp_get_attachment_image_src( $id, $args['size'], false, $args['attr'] );
			}
		}

		// Get the first attached image
		if ( !isset( $html, $src ) ) {
			$image_ids = array_keys( get_children( array(
				'post_parent'    => $args['post_id'],
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			) ) );

			// Check if this post has attached images
			if ( !empty( $image_ids ) ) {
				$id   = $image_ids[0];
				$html = wp_get_attachment_image( $id, $args['size'], false, $args['attr'] );
				list( $src ) = wp_get_attachment_image_src( $id, $args['size'], false, $args['attr'] );
			}
		}

		// Get the first image in the post content
		if ( !isset( $html, $src ) && ( $args['scan'] ) ) {
			preg_match( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', get_post_field( 'post_content', $args['post_id'] ), $matches );

			if ( !empty( $matches ) ) {
				$html = $matches[0];
				$src  = $matches[1];
			}
		}

		// Use default when nothing found
		if ( !isset( $html, $src ) && !empty( $args['default'] ) ) {
			if ( is_array( $args['default'] ) ) {
				$html = @$args['html'];
				$src  = @$args['src'];
			} else {
				$html = $src = $args['default'];
			}
		}

		// Still no images found?
		if ( !isset( $html, $src ) )
			return false;

		$output = 'html' === strtolower( $args['format'] ) ? $html : $src;

		$image_cache[$key] = $output;
		wp_cache_set( $args['post_id'], $image_cache, 'thim_get_image' );
	} // If image already cached
	else {
		$output = $image_cache[$key];
	}

	$output = apply_filters( 'thim_get_image', $output, $args );

	if ( !$args['echo'] )
		return $output;

	echo ent2ncr( $output );
}



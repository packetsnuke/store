<?php
$link_before = $after_link = $image = $css_animation = $number = $paged = $nav = $data = $type = $grid_column = '';

if($instance['title']){
	echo '<h3 class="widget-title">'.$instance['title'].'</h3>';
}

if ( $instance['image'] ) {
	if ( $instance['link_target'] == '_blank' ) {
		$t = 'target="_blank"';
	} else {
		$t = '';
	}
	$paged       = $instance['pagination'];
	$nav         = $instance['navigation'];
	$nav         = $instance['navigation'];
	$type        = $instance['display_type'];
	$grid_column = $instance['column'];
	$data .= ' data-show-paged="' . $paged . '"';
	$data .= ' data-show-nav="' . $nav . '"';
	if ( $instance['number'] ) {
		$data .= ' data-column-slider="' . $instance['number'] . '"';
	}

	$img_id = explode( ",", $instance['image'] );
	if ( $instance['image_link'] ) {
		$img_url = explode( ",", $instance['image_link'] );
	}

	$css_animation = $instance['css_animation'];
	$css_animation = thim_getCSSAnimation( $css_animation );


	if ( $type == 'slider' ):
		echo '<div class="thim-gallery-images gallery-img ' . esc_attr( $css_animation ) . '" ' . $data . '>';
		$i = 0;
		foreach ( $img_id as $id ) {
			$src = wp_get_attachment_image_src( $id, $instance['image_size'] );
			if ( $src ) {
				$img_size = '';
				$src_size = @getimagesize( $src['0'] );
				$image    = '<img src ="' . esc_url( $src['0'] ) . '" ' . $src_size[3] . ' alt=""/>';
			}
			if ( $instance['image_link'] ) {
				$link_before = '<a ' . $t . ' href="' . esc_url( $img_url[$i] ) . '">';
				$after_link  = "</a>";
			}
			echo '<div class="item">' . $link_before . $image . $after_link . "</div>";
			$i ++;
		}
		echo "</div>";
	else:
		echo '<ul class="thim-gallery-images-grid col-' . $grid_column . ' ">';
		$i     = 0;
		$j     = 1;
		$total = sizeof( $img_id );
		$row   = (int) ( $total / $grid_column );

		foreach ( $img_id as $id ) {
			$class   = '';
			$class_b = '';
			$src     = wp_get_attachment_image_src( $id, $instance['image_size'] );
			if ( $src ) {
				$img_size = '';
				$src_size = @getimagesize( $src['0'] );
				$image    = '<img src ="' . esc_url( $src['0'] ) . '" ' . $src_size[3] . ' alt=""/>';
			}
			if ( $instance['image_link'] ) {
				$link_before = '<a ' . $t . ' href="' . esc_url( $img_url[$i] ) . '">';
				$after_link  = "</a>";
			}
			if ( $j % $grid_column == 0 ) {
				$class .= 'last';
			}
			if ( $total % $grid_column == 0 ) {
				if ( $j > ( $grid_column * ( $row - 1 ) ) ) {
					$class_b .= ' bottom';
				}
			} else {
				if ( $j > ( $grid_column * $row ) ) {
					$class_b .= ' bottom';
				}
			}
			echo '<li class="' . $class . $class_b . ' ' . esc_attr( $css_animation ) . '">' . $link_before . $image . $after_link . "</li>";
			$i ++;
			$j ++;
		}
		echo '</ul>';
	endif;

}
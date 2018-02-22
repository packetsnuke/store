<div class="wrapper-time-line">
	<div class="box-time-line">
		<?php if ( have_posts() ):
			$date_posts = array();
			while ( have_posts() ) : the_post();
				if ( $cat == '' ) {
					$cat = 'all';
				};
				$date_post = get_the_date( 'F Y' );
				if ( !in_array( $date_post, $date_posts ) ) {
					$date_posts[] = $date_post;
					echo '<article class="time-line full month-year" id="date-' . str_replace( ' ', '-', $date_post ) . '"><h4><span>' . $date_post . '</span></h4></article>';
				};
				get_template_part( 'inc/blog-templates/content', 'timeline' );
			endwhile;
		//print_r($date_posts);
		else: get_template_part( 'templates/content', 'none' );
		endif ?>
	</div>
	<?php
	echo '<div class="btn_time_line_load_more"><a href="javascript:;" data-ajax_url="' . admin_url( 'admin-ajax.php' ) . '"  data-size="full"  data-post-date="' . base64_encode( json_encode( $date_posts ) ) . '"  data-cat="' . $cat . '" data-offset="' . get_option( 'posts_per_page' ) . '" >' . __( 'Load More', 'thim' ) . '</a></div>';
	?>
</div>
<ul class="date-time">
	<?php
	if ( $cat == '' ) {
		query_posts( 'posts_per_page=-1' );
	} else {
		query_posts( 'posts_per_page=-1&cat=' . $cat );
	}
	$dates = $date_time = array();
	while ( have_posts() ) : the_post();
		$disable = $class = '';
		$date    = get_the_date( 'F Y' );
		if ( !in_array( $date, $dates ) ) {
			$dates[] = $date;
			echo '<li><a href="javascript:;"  ' . $disable . ' class="date-scoll' . $class . '" data-target="date-' . str_replace( ' ', '-', $date ) . '">' . $date . '</a></li>';
		}
	endwhile;
	wp_reset_query();
	?>
</ul>

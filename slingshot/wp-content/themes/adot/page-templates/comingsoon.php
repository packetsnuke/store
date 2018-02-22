<?php
/**
 * Template Name:  Coming Soon Mode
 *
 **/
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
	<meta name="viewport" content="width=device-width" />
        <title><?php bloginfo('name'); wp_title(); ?></title>
        <?php 
        global $theme_options_data;
	$class_header = '';
	if ( isset( $theme_options_data['thim_favicon'] ) ) {
		$thim_favicon     = $theme_options_data['thim_favicon'];
		$thim_favicon_src = $thim_favicon; // For the default value
		if ( is_numeric( $thim_favicon ) ) {
			$favicon_attachment = wp_get_attachment_image_src( $thim_favicon, 'full' );
			$thim_favicon_src   = $favicon_attachment[0];
		}
	} else {
		$thim_favicon_src = get_template_directory_uri() . "/images/favicon.ico";
	}        
        ?>
        <link rel="shortcut icon" href=" <?php echo esc_url( $thim_favicon_src ); ?>" type="image/x-icon" />
        <?php //wp_head(); ?>
        <link href="<?php echo get_template_directory_uri() ?>/css/coming-soon.css" rel="stylesheet" />
	<link href='http://fonts.googleapis.com/css?family=Roboto:400,100,500,700' rel='stylesheet' type='text/css'>

</head>
<body>
<?php while ( have_posts() ) : the_post(); ?>
	<?php
	$style_css = $style = '';
	if ( has_post_thumbnail() ):
		$image = wp_get_attachment_url( get_post_thumbnail_id( get_the_ID() ), 'full' );
		$style_css .= ( $image != '' ) ? 'background-image: url(' . $image . ');' : '';
	endif;

	$text_color = get_post_meta( get_the_ID(), 'thim_text_color', true );
	$style_css .= ( $text_color != '' ) ? 'color: ' . $text_color . ';' : '';

	if ( $style_css ) {
		$style = 'style="' . $style_css . '"';
	}

	$date = get_post_meta( get_the_ID(), 'thim_coming_soon_date', true );

	$thim_cover_color = get_post_meta( get_the_ID(), 'thim_cover_color', true );
	$link_mp4         = get_post_meta( get_the_ID(), 'thim_link_mp4', true );
	$link_ogg         = get_post_meta( get_the_ID(), 'thim_link_ogg', true );

	$text_copyright        = get_post_meta( get_the_ID(), 'thim_text_copyright', true );
	$thim_title_form       = get_post_meta( get_the_ID(), 'thim_title_form', true );
	$thim_form_mail_letter = get_post_meta( get_the_ID(), 'thim_form_mail_letter', true );
	?>
	<div class="main" <?php echo ent2ncr($style); ?>>
		<?php if ( $link_mp4 <> '' || $link_ogg <> '' ) { ?>
			<video id="video_background" preload="auto" autoplay="true" loop="loop" muted="muted" volume="0">
				<?php
				if ( $link_ogg ) {
					echo '<source src="' . $link_ogg . '" type="video/webm">';
				}
				if ( $link_mp4 ) {
					echo '<source src="' . $link_mp4 . '" type="video/mp4">';
				}
				?>
				Video not supported
			</video>
		<?php } ?>
		<div class="cover <?php echo esc_attr( $thim_cover_color ) ?>" data-color="<?php echo esc_attr( $thim_cover_color ) ?>"></div>
		<div class="container">
			<h1 class="logo">
				<?php
				$coming_soon_logo = get_post_meta( get_the_ID(), 'thim_coming_soon_logo', true );
				if ( $coming_soon_logo ) {
					$coming_soon_logo_attachment = wp_get_attachment_image_src( $coming_soon_logo, 'full' );
					echo '<img src="' . $coming_soon_logo_attachment[0] . '">';
				}
				?>
			</h1>

			<div class="content">
				<?php the_content(); ?>
				<div class="row text-center" id="coming-soon-counter"></div>
				<?php
				if ( $thim_form_mail_letter ) {
					echo '	<div class="subscribe">';
					if ( $thim_title_form ) {
						echo '<h5 class="info-text">' . $thim_title_form . ' </h5>';
					}
					echo '<div class="row" align="center">' . do_shortcode( $thim_form_mail_letter ) . '</div>';
					echo '</div>';
				}
				?>
			</div>
		</div>
		<?php if ( $text_copyright ) {
			echo '<div class="footer">
				<div class="container" style="text-align: center; font-size: 12px;">' . $text_copyright . '</div>
			</div>';
		} ?>

	</div>
<?php endwhile; ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo get_template_directory_uri() ?>/js/jquery.mb-comingsoon.min.js"></script>
<script type="text/javascript">
	<?php echo '
			 $(function () {
 					$("#coming-soon-counter").mbComingsoon({ expiryDate:  new Date(' . date( "Y",  $date ) . ', ' . ( date( "m", $date ) - 1 ) . ', ' . date( "d", $date ) . ', ' . date( "G", $date ) . ',' . date( "i", $date ) . ', ' . date( "s", $date ) . '), speed:100 });
					setTimeout(function () {
						$(window).resize();
					}, 200);
				});
			 '
	 ?>
</script>
</body>
</html>
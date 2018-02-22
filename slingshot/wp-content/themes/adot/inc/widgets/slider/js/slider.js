var siteoriginSlider = {};
jQuery(function ($) {

	var playSlideVideo = siteoriginSlider.playSlideVideo = function (el) {
		$(el).find('video').each(function () {
			if (typeof this.play != 'undefined') {
				this.play();
			}
		});
	}

	var pauseSlideVideo = siteoriginSlider.pauseSlideVideo = function (el) {
		$(el).find('video').each(function () {
			if (typeof this.pause != 'undefined') {
				this.pause();
			}
		});
	}

	var setupActiveSlide = siteoriginSlider.setupActiveSlide = function (slider, newActive, speed) {
		// Start by setting up the active sentinal
		var
			sentinel = $(slider).find('.cycle-sentinel'),
			active = $(newActive),
			video = active.find('video.ob-background-element');

		//if (speed == undefined) sentinel.css('height', active.outerHeight());
		//else sentinel.animate({height: active.outerHeight()}, speed);

		if (video.length) {

			// Resize the video so it fits in the current slide
			var
				slideRatio = active.outerWidth() / active.outerHeight(),
				videoRatio = video.outerWidth() / video.outerHeight();

			if (slideRatio > videoRatio) {
				video.css({
					'width' : '100%',
					'height': 'auto'
				});
			}
			else {
				video.css({
					'width' : 'auto',
					'height': '100%'
				});
			}

			video.css({
				'margin-left': -Math.ceil(video.width() / 2),
				'margin-top' : -Math.ceil(video.height() / 2)
			});
		}

	}

	$('.ob-slider-images').each(function () {
		var $$ = $(this);
		var $p = $$.siblings('.ob-slider-pagination');
		var $base = $$.closest('.ob-slider-base');
		var $n = $base.find('.ob-slide-nav');
		var $slides = $$.find('.ob-slider-image');
		var settings = $$.data('settings');
		var transfromY = function () {
			var temp = 0;
			$('.site-badge-perspective').animate(
				{textIndent: 0}, {
					step    : function (now, fx) {
						temp = temp + (90 / 46);
						if (temp > 90) temp = 90;
						$(this).css('transform', 'rotateY(' + temp + 'deg)');
					},
					duration: 'slow',
					complete: function () {
						$('.site-badge-perspective').animate(
							{textIndent: 0}, {
								step    : function (now, fx) {
									temp = temp - (90 / 46);
									if (temp < 0) temp = 0;
									$(this).css('transform', 'rotateY(' + temp + 'deg)');
								},
								duration: 'slow'
							}, 'linear'
						);
					}
				}, 'linear'
			);
		}

		var setupSlider = function () {
			// Show everything for this slider
			$base.show();

			// Setup each of the slider frames
			$$.find('.ob-slider-image').each(function () {
				var $i = $(this);

				$(window)
					.resize(function () {
						//$i.height( $i.find('.ob-slider-image-wrapper').outerHeight() );
						$i.css('height', $i.find('.ob-slider-image-wrapper').outerHeight());
					})
					.resize();
			});

			// Set up the Cycle
			$$
				.on({
					'cycle-after': function (event, optionHash, outgoingSlideEl, incomingSlideEl, forwardFlag) {
						var $$ = $(this);
						playSlideVideo(incomingSlideEl);
						setupActiveSlide($$, incomingSlideEl);
					},

					'cycle-before': function (event, optionHash, outgoingSlideEl, incomingSlideEl, forwardFlag) {
						var $$ = $(this);
						$p.find('> li').removeClass('ob-active').eq(optionHash.slideNum - 1).addClass('ob-active');
						var $badge_current_1 = $('.ob-slider-pagination > li.ob-active a').data('goto') + 1;
						$('.site-badge-content .badge-current').html($badge_current_1);
						//transfromY();
						transfromYLoad = false;
						pauseSlideVideo(outgoingSlideEl);
						setupActiveSlide($$, incomingSlideEl, optionHash.speed);
					},

					'cycle-initialized': function (event, optionHash) {
						playSlideVideo($(this).find('.cycle-slide-active'));
						setupActiveSlide($$, optionHash.slides[0]);
						//$p.find('>li').removeClass('ob-active').eq(0).addClass('ob-active');
						//alert(eq(0));
						if (optionHash.slideCount <= 1) {
							// Special case when there is only one slide
							$p.hide();
							$n.hide();
						}

						$(window).resize();
					}
				})
				.cycle({
					'slides'  : '> .ob-slider-image',
					'speed'   : settings.speed,
					'timeout' : settings.timeout,
					'swipe'   : true,
					'swipe-fx': 'scrollHorz',
					'height'  : false
				});

			$$.find('video.ob-background-element').on('loadeddata', function () {
				setupActiveSlide($$, $$.find('.cycle-slide-active'));
			});


			// Resize the sentinel when ever the window is resized
			$(window).resize(function () {
				setupActiveSlide($$, $$.find('.cycle-slide-active'));
			});

			// Setup clicks on the pagination
			$p.find('> li > a').click(function (e) {
				e.preventDefault();
				$$.cycle('goto', $(this).data('goto'));
				var $badge_current = $(this).data('goto') + 1;
				$('.site-badge-content .badge-current').html($badge_current);
				//transfromY();
			});

			// Clicking on the next and previous navigation buttons
			$n.find('> a').click(function (e) {
				e.preventDefault();
				$$.cycle($(this).data('action'));
				var $badge_current = $('.ob-slider-pagination > li.ob-active a').data('goto') + 1;
				$('.site-badge-content .badge-current').html($badge_current);
			});
			$(".ob-slide-nav-next").hover(function () {
					$(".site-badge-perspective").addClass('transform-left');
				}, function () {
					$(".site-badge-perspective").removeClass('transform-left');
				}
			);
			$(".ob-slide-nav-prev").hover(function () {
					$(".site-badge-perspective").addClass('transform-right');
				}, function () {
					$(".site-badge-perspective").removeClass('transform-right');
				}
			);
			$(".site-badge-perspective").click(function () {
				$(this).removeClass('transform-right');
				$(this).removeClass('transform-left');
				transfromY();
			});
		}

		var images = $$.find('img');
		var imagesLoaded = 0;
		var sliderLoaded = false;

		// Preload all the images, when they're loaded, then display the slider
		images.each(function () {
			var $i = $(this);

			if (this.complete) imagesLoaded++;
			else {
				$(this).one('load', function () {
					imagesLoaded++;

					if (imagesLoaded == images.length && !sliderLoaded) {
						setupSlider();
						sliderLoaded = true;
					}
				})
			}

			if (imagesLoaded == images.length && !sliderLoaded) {
				setupSlider();
				sliderLoaded = true;
			}
		});

		if (images.length == 0) {
			setupSlider();
		}
		$('.header_default .site-header.header_v1 .navigation,.header_default .site-header.header_v2 .navigation').imagesLoaded(function () {
			var height_header = $('.header_default .site-header.header_v1 .navigation,.header_default .site-header.header_v2 .navigation').innerHeight();
			var wp_admin_bar = $('#wpadminbar').height();
			var height_hd = height_header + wp_admin_bar;
			if (settings.full_screen == '1') {
				$('.ob-slider-images').css({'height': ($(window).height() - height_hd) + 'px'});
				$(window).resize(function () {
					$('.ob-slider-images').css({'height': ($(window).height() - height_hd) + 'px'});
				});
			}
			if ($(window).innerWidth() < 768) {
				$('.ob-slider-images').css({'height': ($(window).height() - height_hd) + 'px'});
				$(window).resize(function () {
					$('.ob-slider-images').css({'height': ($(window).height() - height_hd) + 'px'});
				});
			}
			if ($(window).innerHeight() < 480) {
				$('.site-badge-content').css({'display': 'none'});
			}
		})
	});
});
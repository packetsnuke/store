/**
 * Created by Tien Cong on 4/22/2015.
 */
jQuery(function($) {
	$(".button-search").on('click',function(e) {
		e.preventDefault();
		$('#header-search-form-input').addClass('open');
		$('#header-search-form-input').find('.woocommerce-product-search').css('margin-left',-$(window).width()/4);
		$('#header-search-form-input #s').focus();
	});
	$(".main-header-search-form-input").on('click',function(e) {
		if ( $(e.target).attr('class') == 'form-control ob-search-input' || $(e.target).attr('class') == 'button-on-search') {
			return;
		} else {
			$('#header-search-form-input').removeClass('open');
		}
	});

	$('.ob-search-input').on('keyup', function (event) {

		clearTimeout($.data(this, 'timer'));
		if (event.which == 27) {
			$('#header-search-form-input').removeClass('open');
			$('.navigation .sm-logo,.navigation .table_right').css({'opacity': 1});
			$('.header-search-close').html('<i class="fa fa-times"></i>');
			$('.ob_list_search').html('');
			$(this).val('');
			$(this).stop();
		} else {
			$(this).data('timer', setTimeout(search, 1000));
		}
	});
});
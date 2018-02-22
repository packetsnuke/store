(function ($) {
	"use strict";
	/* Social login popup */
	var thimLoginSocialPopup = function () {
		jQuery('.thim-link-login a').click(function (event) {
			var popupWrapper = '#thim-popup-login-wrapper';
			jQuery.ajax({
				type   : 'POST',
				data   : 'action=thim_social_login',
				url    : thim_ob_ajax_url,
				success: function (html) {
					if (jQuery(popupWrapper).length) {
						jQuery(popupWrapper).remove();
					}
					jQuery('body').append(html);
					jQuery('ul.the_champ_login_ul li i', popupWrapper).show();
					jQuery('.thim-popup-login-close', popupWrapper).click(function () {
						jQuery(this).parent().parent().parent().parent().remove();
					});
					jQuery(document).mouseup(function (e) {
						var container = jQuery(".thim-popup-login-container-inner");

						if (!container.is(e.target) // if the target of the click isn't the container...
							&& container.has(e.target).length === 0) // ... nor a descendant of the container
						{
							jQuery("#thim-popup-login-wrapper").remove();
						}
					});

					jQuery(document).keyup(function (e) {
						if (e.keyCode == 27) {
							jQuery("#thim-popup-login-wrapper").remove();
						}
					});

					jQuery('#thim-popup-login-form').submit(function (event) {
						var input_data = jQuery('#thim-popup-login-form').serialize();

						jQuery.ajax({
							type   : 'POST',
							data   : input_data,
							url    : thim_ob_ajax_url,
							success: function (html) {
								var response_data = jQuery.parseJSON(html);
								jQuery('.login-message', '#thim-popup-login-form').html(response_data.message);
							},
							error  : function (html) {
							}
						});
						event.preventDefault();
						return false;
					});
				},
				error  : function (html) {
				}
			});
			event.preventDefault();
		});
	}

	/* thim Login Widget*/
	var thimLoginWidget = function () {
		jQuery('.thim-login-widget-form').each(function () {
			jQuery(this).submit(function (event) {
				if (this.checkValidity()) {
					var $form = jQuery(this);
					var input_data = jQuery($form).serialize();
					jQuery.ajax({
						type   : 'POST',
						data   : input_data,
						url    : thim_ob_ajax_url,
						success: function (html) {
							var response_data = jQuery.parseJSON(html);
							jQuery('.thim-login-widget-message', $form).html(response_data.message);
						},
						error  : function (html) {
						}
					});
				}
				event.preventDefault();
				return false;
			});
		});
	}

	// DOMReady event
	$(function () {
		thimLoginSocialPopup();
		thimLoginWidget();
	});
})(jQuery);


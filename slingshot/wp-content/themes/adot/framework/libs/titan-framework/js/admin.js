jQuery(document).ready(function ($) {
	"use strict";

	jQuery(window).load(function() {
		// Section Icon
		jQuery('.customizer-icon').each(function(){
			var selector =jQuery(this);
			if (selector.length) {
				var parent =  selector.closest('ul');
				var html =  jQuery('<div>').append(selector.clone()).remove().html();
				parent.prev("h3").prepend(html);
				selector.remove();
			}
		});
	});

	jQuery(document).on("click", "#import-customize-settings", function (e) {
		e.preventDefault();
		jQuery('#thim-customizer-settings-upload').trigger('click');
	});

	// Add Import Settings form and Upload button
	jQuery('form#customize-controls').after(
		jQuery('<form></form>').attr('id', 'thim-import-form').append(
			jQuery('<input/>')
				.attr('type', 'file')
				.attr('id', 'thim-customizer-settings-upload')
				.attr('name', "thim-customizer-settings-upload")
				.css('position', 'absolute')
				.css('top', '-100px'), // hack sercurity
			jQuery('<input/>').attr('type','hidden').attr('name', 'action').val('thim_cusomizer_upload_settings')
		)
	);

	jQuery( '#thim-customizer-settings-upload' ).change( function () {
		var $this = jQuery( this );
		var formData = new FormData(jQuery('#thim-import-form')[0]);
		jQuery.ajax({
				url: ajax_url,
				type: 'POST',
				//Ajax events
				// Form data
				data: formData,
				//Options to tell JQuery not to process data or worry about content-type
				cache: false,
				contentType: false,
				processData: false
			},
			'json'
		).done(function(data) {
        	var settings = '';

			try {
				settings = JSON.parse(data);
			} catch (e) {
				return;
			}
			var nodes  = jQuery('[data-customize-setting-link]');
			var radios = {};

			// Normal option type
			nodes.each( function() {
				var node = jQuery(this),
					name,
					key = node.attr('data-customize-setting-link');

				if( settings.hasOwnProperty(key)) {

					wp.customize(key, function (obj) {
						if(jQuery.type( settings[key]) === "object" ) {
							var $ = jQuery;
							// Initialize the option
							var $container = node.parent(".tf-font");
							jQuery.each(settings[key],function(index, item){
						        if (index == 'font-family') {
						        	$container.find(".tf-font-sel-family").val(item);
						        	$container.find(".tf-font-sel-family").trigger("chosen:updated");
						        }
						        if (index == 'font-type') {
						        	$container.find(".tf-font-sel-type").val(item);
						        }
						        if (index == 'color-opacity') {
						        	var rgba=item;
									var alpha=rgba.replace(/^.*,(.+)\)/,'$1')*100;

						        	$container.find(".tf-font-sel-color").val(item);
						        	var $alpha_slider = $container.find('.slider-alpha');

						        	$alpha_slider.slider( "option", "value", alpha );
						        	$alpha_slider.find("a.ui-slider-handle").text(alpha);
						        	//$alpha_slider.slider("value", $alpha_slider.slider("value"));
						        	//$alpha_slider.slider('refresh');

						        	$container.find(".tf-font-sel-color").wpColorPicker('color', item);
						        	wp.customize.trigger('change');
						        }
						        if (index == 'font-size') {
						        	$container.find(".tf-font-sel-size").val(item);
						        }
						        if (index == 'font-weight') {
						        	$container.find(".tf-font-sel-weight").val(item);
						        }
						        if (index == 'font-style') {
						        	$container.find(".tf-font-sel-style").val(item);
						        }
						        if (index == 'line-height') {
						        	
						        	$container.find(".tf-font-sel-height").val(item);
						        }
						        if (index == 'letter-spacing') {
						        	$container.find(".tf-font-sel-spacing").val(item);
						        }
						        if (index == 'text-transform') {
						        	$container.find(".tf-font-sel-transform").val(item);
						        }
						        if (index == 'font-variant') {
						        	$container.find(".tf-font-sel-variant").val(item);
						        }
						        if (index == 'text-shadow-location') {
						        	$container.find(".tf-font-sel-location").val(item);
						        }
						        if (index == 'text-shadow-distance') {
						        	$container.find(".tf-font-sel-distance").val(item);
						        }if (index == 'text-shadow-blur') {
						        	$container.find(".tf-font-sel-blur").val(item);
						        }
						        if (index == 'text-shadow-color') {
						        	$container.find(".tf-font-sel-shadow-color").val(item);
						        }
						        if (index == 'text-shadow-opacity') {
						        	$container.find(".tf-font-sel-opacity").val(item);
						        }
						        if (index == 'dark') {
						        	$container.find(".tf-font-sel-dark").val(item);
						        }
						        if (index == 'text') {

						        }
						    });
							tf_select_font_update_preview( $container, true );
						}else {
							obj.set(settings[key]);
							wp.customize.trigger('change');
						}
					});
					node.trigger(	"toggle_children");
				}else {

				}
			});
			alert("Done!");
        });
    });

	//Export settings
	jQuery(document).on("click", "a#thim-customizer-settings-download", function () {
		jQuery.fileDownload(export_url, {
			failCallback: function () {
				alert('fail');
			}
		});
		return false; //this is critical to stop the click event which will trigger a normal file download!
	});
});

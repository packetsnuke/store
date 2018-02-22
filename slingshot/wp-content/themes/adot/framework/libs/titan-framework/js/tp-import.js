/**
 * File: tp import
 * Description: Action import data demo, demo files to make site as demo site
 * Author: Andy Ha (tu@wpbriz.com)
 * Copyright 2007-2014 wpbriz.com. All rights reserved.
 */

/**
 * Function import
 * Call ajax to process
 * @constructor
 */
jQuery(document).ready(function ($) {
	$('.tp_process_messase').css('font-size','smaller');
	$('#demodata-selecter').on('change', function(event){
		event.preventDefault();
		var thumbnail_url = $('#'+this.id+' option[value="'+$(this).val()+'"]').attr('data-thumbnail-url');
		$('#demodata-thumbnail').attr('src',thumbnail_url);
		$('#demodata-thumbnail').show('slow');
	});
	jQuery('.tp-import-action').on('click', function (event) {
		console.log( !$('#demodata-selecter').length );
		
		if( !$('#demodata-selecter').length ) {
			import_type('woo_setting', 0);
			jQuery('.tpimport_core').show();
			jQuery('.tpimport_core .meter > span').animate({width: '20px'});
			jQuery('.tp_process_bar').slideDown('fast');
			return;
		}
		if($('#demodata-selecter').attr('disabled')=='disabled'){
			return;
		}
		jQuery('.tp_process_bar').show();
		var demodata = jQuery('#demodata-selecter').val();
		$('#demodata-selecter').attr('disabled','disabled');
		import_type('download&demodata='+demodata, 0);
		jQuery(".tpimport_download").show();
		jQuery(".tpimport_download .meter > span").animate({width: '20px'});
		jQuery(".tp_process_bar").slideDown('fast');
	})
})


function import_type(type, method) {
		jQuery.ajax({
			type   : 'POST',
			data   : 'action=tp_make_site&method=' + method + '&type=' + type,
			url    : ajaxurl,
			'dataType':'json',
			success: function (response) {
				var next_step	= response.next;
				var step		= response.step;
				var msg_style="";
				if(response.return){
					msg_style=' style="color:green;" ';
				}else{
					msg_style=' style="color:red;" ';
				}
				if( next_step == 'error' ) {
					if(response.message){
						jQuery(".tpimport_"+step+" .tp_process_messase").append('<div'+msg_style+'>'+response.message+'</div>');
					}
					if(response.logs){
						jQuery(".tpimport_"+step+" .tp_process_messase").append('<div'+msg_style+'>'+response.logs+'</div>');
					}
					return;
				} else if ((next_step == 'setting') || (next_step == 'menus') || (next_step == 'slider') || (next_step == 'widgets') || (next_step == 'core') || (next_step== 'extract') || (next_step=='woo_setting') || (next_step=='done') ) {
					if( next_step == 'core' ) {
						if( step != next_step ) {
							jQuery(".tpimport_"+next_step).show();
						}
						if ( parseInt( jQuery('.tpimport_core .meter > span').width() ) < 320 ) {
							jQuery(".tpimport_core .meter > span").animate({width: parseInt(jQuery('.tpimport_core .meter > span').width()) + 50 + 'px'}, 'slow');							
							if( response.message ) {
								jQuery(".tpimport_core .tp_process_messase").append('<div'+msg_style+'>'+response.message+'</div>');
							}
							if( response.logs ) {
								jQuery(".tpimport_core .tp_process_messase").append('<div'+msg_style+'>'+response.logs+'</div>');
							}
						}
					} else if ((next_step == 'setting') || (next_step == 'menus') || (next_step == 'slider') || (next_step == 'widgets') || (next_step== 'extract') || (next_step=='woo_setting') || (next_step=='done')){
						jQuery(".tpimport_"+step+" .meter > span").animate({width: '345px'},'slow');
						if(response.message){
							jQuery(".tpimport_"+step+" .tp_process_messase").append('<div'+msg_style+'>'+response.message+'</div>');
						}
						if(response.logs){
							jQuery(".tpimport_"+step+" .tp_process_messase").append('<div'+msg_style+'>'+response.logs+'</div>');
						}
						if (next_step == 'done') {
							alert("Import demo data success!");
							return;
						}
						jQuery(".tpimport_"+next_step).show();
						jQuery(".tpimport_"+next_step+" .meter > span").animate({width: '20px'});
					} else {
						jQuery(".tpimport_core .meter > span").animate({width: '345px'}, 'slow');
						if(response.message){
							jQuery(".tpimport_core .tp_process_messase").append('<div'+msg_style+'>'+response.message+'</div>');
						}
						if(response.logs){
							jQuery(".tpimport_core .tp_process_messase").append('<div'+msg_style+'>'+response.logs+'</div>');
						}
					}
					import_type( next_step, method );
				}
                else if(next_step=='revolution_error')
                {
					var msg = 'Import finish without revolution sliders.You can import manual, please view http://thimpress.com/knowledge-base/import-revolution-sliders/.';
					jQuery('#tp_process_error_messase').append( msg );
					alert( msg );
                }
				else {
					if( response.message ) {
						jQuery('#tp_process_error_messase').append(response.message);
						alert(response.message);
					}else{
						jQuery('#tp_process_error_messase').append('Import error. Please go to http://thimpress.com/forums to get the best support.');
						alert('Import error. Please go to http://thimpress.com/forums to get the best support.');
					}
				}
			},
			error  : function (html) {
				jQuery('#tp_process_error_messase').append('Import error. Please go to http://thimpress.com/forums to get the best support.');
				alert('Import error 02. Please go to http://thimpress.com/forums to get the best support.');
			}
		});
}
/**
 * Function remove demo data
 * @constructor
 */
function tp_remove() {

}

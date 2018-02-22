jQuery( function ( $ ) {
	$('#post-query-submit').remove();
	
	// Color Picker
	$( document.body ).on( 'wc-sa-init-colorpicker', function() {
		$( '.color-picker-field, .color-picker' ).wpColorPicker({
            change: function(b, c) {
            	$('.wc-sa-icon-color, .wc-sa-text-color').css({
            		'background-color': c.color.toString(),
            		'border-color'    : c.color.toString()
            	});
            	$('.wc-sa-icon-outline, .wc-sa-text-outline').css({
            		'color'         : c.color.toString(),
            		'border-color'  : c.color.toString()
            	});
            }
        }); 
	}).trigger( 'wc-sa-init-colorpicker' );

	// Color Picker
	$( document.body ).on( 'wc-sa-init-iconpicker', function() {
		$( '.icon-picker-field, .icon-picker' ).fontIconPicker({
			theme: 'fip-ae',
			source: wc_sa_icons_array,
			prefix_class: 'wc-sa-icon-uni',
			emptyIcon: false,
			hasSearch: false,
			onchange : function(a, b){
				if( a.hasClass('status_icon') ){
					$('.wc-sa-icon-color i, .wc-sa-icon-outline i').attr('class', 'wc-sa-icon-uni'+b);					
				}
		    }
		});
	}).trigger( 'wc-sa-init-iconpicker' );

	$('#wc-sa-options #status_label').change(function(event) {
		var title = $(this).val();
			title = title.trim();
		if( title != ''){
			$('.wc-sa-text-color, .wc-sa-text-outline').text(title);
		}
	}).trigger('change');

	$('#status_email_notification').change(function(event) {
		if( $(this).is(':checked') ){
			$('.show_if_email_notification').show();
		}else{
			$('.show_if_email_notification').hide();
		}
	}).trigger('change');

	$('#status_email_message').change(function(event) {
		if( $(this).is(':checked') ){
			$('.show_if_custom_message').show();
		}else{
			$('.show_if_custom_message').hide();
		}
	}).trigger('change');

	$('#status_customer_account').change(function(event) {
		if( $(this).is(':checked') ){
			$('.show_if_customer_account').show();
		}else{
			$('.show_if_customer_account').hide();
		}
	}).trigger('change');

	$('#status_automatic_trigger').change(function(event) {
		if( $(this).is(':checked') ){
			$('.show_if_automatic_trigger').show();
		}else{
			$('.show_if_automatic_trigger').hide();
		}
	}).trigger('change');

	$('#status_email_recipients').change(function(event) {
		if( $(this).val() == 'custom' ){
			$('.show_if_email_recipients_custom').show();
		}else{
			$('.show_if_email_recipients_custom').hide();
		}
	}).trigger('change');

	$('#status_customer_cancel_orders').change(function(event) {
		if( $(this).is(':checked') ){
			$('.show_if_cancel_orders').show();
		}else{
			$('.show_if_cancel_orders').hide();
		}
	}).trigger('change');

	if( $('form#post').length && $('#post_type').val() == 'wc_custom_statuses' ){
		$('form#post').submit(function(event) {
			var error = '';
			$('#status_validation').remove();
			if( $('#title').val() == ''){
				error += '<p>' + wc_sa_error_validation.name + '</p>';
			}
			if( $('#status_label').val() == ''){
				error += '<p>' + wc_sa_error_validation.label + '</p>';
			}
			if( $('#status_email_notification').is(':checked') ){

				if( $('#status_email_from_name').val() == ''){
					error += '<p>' + wc_sa_error_validation.fromname + '</p>';
				}
				if( $('#status_email_from_address').val() == ''){
					error += '<p>' + wc_sa_error_validation.fromemail + '</p>';
				}
				if( $('#status_email_subject').val() == ''){
					error += '<p>' + wc_sa_error_validation.email + '</p>';
				}
				if( $('#status_email_heading').val() == ''){
					error += '<p>' + wc_sa_error_validation.emailhead + '</p>';
				}

			}

			if( $('#status_customer_account').is(':checked') ){
				if( $('#status_customer_account_visibility').val() == ''){
					error += '<p>' + wc_sa_error_validation.visibility_rule + '</p>';
				}
				if( $('#status_customer_account_button_label').val() == ''){
					error += '<p>' + wc_sa_error_validation.buttonlabel + '</p>';
				}				
			}
			if( $('#status_automatic_trigger').is(':checked') ){
				if( $('#status_triggered_status').val() == ''){
					error += '<p>' + wc_sa_error_validation.triggeredstatus + '</p>';
				}
				if( $('#status_time_period').val() == ''){
					error += '<p>' + wc_sa_error_validation.timeperiod + '</p>';
				}				
			}

			if( error != '' ){
				var html = '<div id="status_validation" class="notice notice-error">'+error+'</div>';
				$('.wp-header-end').after(html);
				return false;
			}
			
		});
	}


	
});

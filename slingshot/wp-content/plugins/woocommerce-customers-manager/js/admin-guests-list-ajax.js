jQuery(document).ready(function()
{
	var error = null;
	var current_iteration_num;
	/* var max_iterations; */
	var max_guest_orders_iterations;
	var max_registered_users_iterations;
	var per_page;
	var default_per_page = 70;
	var sleep_period = 800;
	var guest_customers = {};
	var guest_customers_to_export = {};
	var csv_data;
	var customers_list_table;
	var csv_data_columns = ["ID", //0
				"Password hash", 
				"Name", 
				"Surname", 
				"Roles",
				"Login", 
				"Email", 
				"Notes", 
				"Registration date", 
				"First order date",
				"Last order date", //10
				"# Orders", 
				"Total amount spent",
				"Billing name",
				"Billing surname",
				"Billing email", //15
				"Billing phone",
				"Billing company",
				"VAT number", //18
				"Billing address",
				"Billing address 2",
				"Billing postcode",
				"Billing city", //22
				"Billing state",
				"Billing country",
				"Shipping name",
				"Shipping surname",
				"Shipping company",
				"Shipping address", //28
				"Shipping address 2",
				"Shipping postcode",
				"Shipping city",
				"Shipping state",
				"Shipping country"]; //33
	
	(function start_computing()
	{
		per_page = default_per_page;
		max_guest_orders_iterations/*  = max_iterations */ = 0;
		current_iteration_num = 1; //0
		jQuery('#guest-customer-list').fadeOut(0);
		wccm_get_guest_orders_max_iterations();
		return false;
	}());
	function wccm_get_guest_orders_max_iterations()
	{
		//Guest customers
		var formData = new FormData();
		formData.append('action', 'wccm_export_get_max_guest_orders_iterations'); 
		formData.append('filter_by_product', wccm_product_filter_id);
		formData.append('customer_emails', wccm_filter_by_emails);
		jQuery.ajax({
			url: ajaxurl, 
			type: 'POST',
			data: formData,
			async: true,
			cache: false,
			contentType: false,
			processData: false,
			success: function (data) 
			{
				max_guest_orders_iterations = Math.ceil(data/per_page);
				wccm_get_guest_customers_data();
			}
		});
	}
	function wccm_get_guest_customers_data()
	{
		var formData = new FormData();
		formData.append('action', 'wccm_export_guests_csv'); 
		formData.append('per_page', per_page); 
		formData.append('page_num', current_iteration_num); 
		formData.append('get_last_order_id', 'yes'); 
		formData.append('reverse_order', 'yes'); 
		formData.append('filter_by_product', wccm_product_filter_id); 
		formData.append('customer_emails', wccm_filter_by_emails);
		var perc_num = ((current_iteration_num/max_guest_orders_iterations)*100);
		perc_num = perc_num > 100 ? 100:perc_num;
		
		var perc = Math.floor(perc_num);
		jQuery('#ajax-progress').html("<p>computing data, please wait...<strong>"+perc+"% done</strong></p>");
		jQuery( "#progressbar" ).progressbar({
					  value: perc
					});
					
		jQuery.ajax({
			url: ajaxurl, //defined in php
			type: 'POST',
			data: formData,//{action: 'upload_csv', csv: data_to_send},
			async: true,
			success: function (data) {
				//alert(data);
				wccm_check_guest_data_response(data);
			},
			error: function (request, error)  {
				if(per_page > 10)
				{
					per_page -= 10;
					wccm_get_guest_customers_data();
				}
				else
				{
					//alert("Server error, response: "+error);
				}
		
			},
			cache: false,
			contentType: false,
			processData: false
		});
	}
	function wccm_check_guest_data_response(data)
	{
		if(data != null)
		{
			var result = jQuery.parseJSON(data);
			var prev_value;
			var was_already_defined;
			jQuery.each(result, function(email_value, data_array)
			{
			
				was_already_defined = false;
				prev_value = null;
				if(typeof guest_customers[email_value] !== 'undefined')
				{
					prev_value = guest_customers[email_value];
					was_already_defined = true;
				}
				guest_customers[email_value] = [];
				jQuery.each(data_array, function( key, value )
				{
					if(was_already_defined)
					{
						if(key == 'total_spent' ) //total_spent
						{
							value += prev_value[key]
						}
						else if(key == 'first_order_date' && prev_value[key] < value)
							value = prev_value[key];
						else if(key == 'last_order_date' && prev_value[key] > value)
							value = prev_value[key];
					}
					guest_customers[email_value].push(value);
				});
				//11: total_spent
				if(hide_not_purchasing_guest_customers && guest_customers[email_value][11] == 0)
					delete guest_customers[email_value];
			});
		}
		if(current_iteration_num < max_guest_orders_iterations)
		{
			current_iteration_num++;
			//wccm_get_guest_customers_data();
			setTimeout(function(){ wccm_get_guest_customers_data(data); }, sleep_period);
		}
		else
		{
			var html_string = "";
			jQuery.each(guest_customers, function( key, value_array )
				{
					//csv_data.push(value_array);
					//console.log(value_array);
					if(typeof value_array !== 'string')
					{
						var total_spent = value_array[12];
						if(isNaN(total_spent))
							total_spent = 0;
						
						html_string += '<tr class="guest-table-row">';
						html_string += '<td><input type="checkbox" class="guest-checkbox" data-customer-email="'+value_array[6]+'"></input><span class="hidden_data">'+JSON.stringify( value_array )+'</span></td>';
						html_string += '<td class="conversion column-conversion"><button data-lastorderid="'+value_array[34]+'" class="conversion-button">Guest to Registered</button><input class="wccm_email_notification" type="checkbox" value="yes" checked="checked"><span class="wccm_email_notification_label">Send an email with login?</span></input>';
						html_string +=		'<div class="wccm-conversion-loader"></div>';
						html_string +=		'<div class="wccm-conversion-result">';
						html_string +=			'<h3>Customer successful converted!</h3>';
						html_string +=			'<p class="wccm-conversion-text-result"></p>';
						html_string +=		'</div></td>';
						html_string += '<td class="name column-name"> <a href="?page=woocommerce-customers-manager&customer_email='+value_array[6]+'&action=customer_details" target="_blank">'+value_array[2]+'</a></td>';
						html_string += '<td class="surname column-surname">'+value_array[3]+'</td>';
						html_string += '<td class="address column-address">'+value_array[13]+' '+value_array[14]+' '+value_array[17]+' '+value_array[19]+' '+value_array[21]+' '+value_array[22]+' '+value_array[23]+' '+value_array[24]+'</td>';
						html_string += '<td class="address column-address">'+value_array[25]+' '+value_array[26]+' '+value_array[27]+' '+value_array[28]+' '+value_array[30]+' '+value_array[31]+' '+value_array[32]+' '+value_array[33]+'</td>';
						html_string += '<td class="email column-phone">'+value_array[16]+'</td>';
						html_string += '<td class="email column-email">'+value_array[6]+'</td>';
						html_string += '<td class="orders column-orders">'+value_array[11]+'</td>';
						html_string += '<td class="total_spent column-total_spent">'+total_spent+'</td>';
						html_string += '<td class="first_order_date column-first_order_date">'+value_array[9]+'</td>';
						html_string += '<td class="last_order_date column-last_order_date">'+value_array[10]+'</td>';
						html_string += '<td class="last_order_date column-last_order_date"><a class="" target="_blank" href="'+wccm_admin_url+'?s='+value_array[6]+'&post_status=all&post_type=shop_order" ><span class="dashicons dashicons-list-view"></span></a></td>';
						html_string += '</tr>';
					}
				}); 
			jQuery("#table-body").html(html_string);
			customers_list_table = jQuery('#guests-table').DataTable( {
						"pagingType": "full_numbers",
						 "pageLength": 50,
						 "order": [[ 11, "desc" ]],
						 "aoColumnDefs": [
							  { 'bSortable': false, 'aTargets': [ 0,1,12 ] }
						   ]
					} );
			jQuery("#guests-table").fadeIn(800);
			jQuery( "#progressbar" ).progressbar({
			  value: 100
			});
			
			//End
			jQuery('.all-customers-select').prop('checked', false);
			jQuery('.all-guest-customer-email-option-toggle').prop('checked', false);
			jQuery('#ajax-progress').append("<p>100% done</p> <h3>end!</h3>");
			jQuery('.conversion-button').click(wccm_convert_user);			
			//jQuery('.guest-checkbox').live('change', wccm_select_guest_customer);
			jQuery('.guest-checkbox').on('change', wccm_select_guest_customer);
			jQuery('.all-guest-customer-email-option-toggle').on('change', wccm_check_if_check_all_guesto_to_registered_sending_email_option);
			jQuery('#progress-container').delay(200).fadeOut(800);
			jQuery('#guest-customer-list').delay(1000).fadeIn(800);
			jQuery('#export-button').click(wccm_export_selected);
			jQuery('#convert-button').click(wccm_convert_selected);
			jQuery('.all-customers-select').click(wccm_select_all_visible_customers);
			jQuery('.all-guest-customer-email-option-toggle').click(wccm_select_sending_registration_email_to_all_visible_customers);
			customers_list_table.on('page.dt',wccm_page_changed);
		}
	}
	
	function wccm_convert_user()
	{
		wcc_start_conversion(jQuery(this));
		
	}
	function wcc_start_conversion(html_element)
	{
		html_element.fadeOut();
		html_element.parent().find('.wccm-conversion-loader').fadeIn();
		html_element.parent().find('.wccm_email_notification').fadeOut();
		html_element.parent().find('.wccm_email_notification_label').fadeOut();
		var elem = html_element;
		setTimeout(function(){wccm_delayed_wccm_convert_user(elem)}, 1000);
	}
	function wccm_delayed_wccm_convert_user(elem)
	{
		var formData = new FormData();
		var notification_email = elem.parent().find('.wccm_email_notification').attr('checked') ? 'yes':'no';
		formData.append('action', 'wccm_convert_guest_to_registered'); 
		formData.append('order-id', elem.data('lastorderid')); 
		formData.append('notification-email', notification_email); 
		jQuery.ajax({
			url: ajaxurl, 
			type: 'POST',
			data: formData,
			async: true,
			cache: false,
			contentType: false,
			processData: false,
			success: function (data) 
			{
				elem.parent().find('.wccm-conversion-loader').fadeOut();
				elem.parent().find('.wccm-conversion-result').fadeIn();
				elem.parent().find('.wccm-conversion-text-result').html(data);
			}
		});
	}
	function wccm_page_changed(event)
	{
		jQuery('.guest-checkbox').on('change', wccm_select_guest_customer)
		setTimeout(wccm_check_if_check_all_customers_checkbox, 200);
	}
	function wccm_select_all_visible_customers(event)
	{
		//console.log(jQuery(event.currentTarget).prop('checked'));
		
		var is_checked = jQuery(event.currentTarget).prop('checked');
		jQuery('.all-customers-select').prop('checked', is_checked);
		
		var current_email;
		jQuery.each(jQuery('#guests-table tr.guest-table-row'), function(index, value)
		{
			jQuery(value).find('.guest-checkbox').prop('checked', is_checked);
			current_email = jQuery(value).find('.column-email').html();
			if(typeof current_email !== 'undefined')
			{
				if(is_checked && !(current_email in guest_customers_to_export))
					guest_customers_to_export[current_email] = jQuery(value).find('.hidden_data').html();
				else
				{
					//guest_customers_to_export.splice(exists, 1);
					delete guest_customers_to_export[current_email];
				}
			}
		});
	}
	function wccm_select_sending_registration_email_to_all_visible_customers(event)
	{
		var is_checked = jQuery(event.currentTarget).prop('checked');
		jQuery('#guests-table tr.guest-table-row').each(function(index, value)
		{
			jQuery(value).find('.wccm_email_notification').prop('checked', is_checked);	
		});
	}
	function wccm_select_guest_customer(event)
	{
		//console.log(jQuery(event.currentTarget).data('customer-email'));
		var guest_email = jQuery(event.currentTarget).data('customer-email');
		//var exists = guest_customers_to_export.indexOf(guest_email);
		if(!(guest_email in guest_customers_to_export))
			guest_customers_to_export[guest_email] = jQuery(event.currentTarget).parent().find('.hidden_data').html();
		else
		{
			//guest_customers_to_export.splice(exists, 1);
			delete guest_customers_to_export[guest_email];
		}
		
		wccm_check_if_check_all_customers_checkbox();
	}
	function wccm_check_if_check_all_customers_checkbox()
	{
		select_all = true;
		jQuery('#guests-table tr.guest-table-row').each(function(index, value)
		{
			select_all = jQuery(value).find('.guest-checkbox').prop('checked');
			if(!select_all)
				return;
		});
		jQuery('.all-customers-select').prop('checked', select_all);
	}
	function wccm_check_if_check_all_guesto_to_registered_sending_email_option(event)
	{
		select_all = true;
		jQuery('#guests-table tr.guest-table-row').each(function(index, value)
		{
			select_all = jQuery(value).find('.wccm_email_notification').prop('checked');
			if(select_all)
				return;
			
		});
		jQuery('.all-guest-customer-email-option-toggle').prop('checked', select_all);
	}
	function wccm_convert_selected(event)
	{
		select_all = true;
		jQuery('#guests-table tr.guest-table-row').each(function(index, value)
		{
			is_selected = jQuery(value).find('.guest-checkbox').prop('checked');
			if(is_selected)
			{
				//console.log(jQuery(value).find(".conversion-button"));
				wcc_start_conversion(jQuery(value).find(".conversion-button"));
			}
			
		});
	}
	function wccm_export_selected(event)
	{
		csv_data = []
		csv_data.push(csv_data_columns);
		jQuery.each(guest_customers_to_export, function(index, value)
		{
			var result = jQuery.parseJSON(value);
			csv_data.push(result);
		});
		wccm_download_csv_file();
	}
	function wccm_download_csv_file()
	{
		var ids_to_export = [];
		var csvRows = [];
		var d = new Date();
		for(var i=0,l=csv_data.length; i<l; ++i)
		{
			csvRows.push(csv_data[i].join(',')); 
		}
		var csvString = csvRows.join("\r\n");
		var a         = document.createElement('a');
		a.href        = 'data:attachment/csv,' + encodeURIComponent(csvString);
		a.target      = '_blank';
		a.download    = 'WCCM-customers_list_'+d.yyyymmdd()+'.csv';

		document.body.appendChild(a);
		a.click();
	}
	 Date.prototype.yyyymmdd = function() {
	   var yyyy = this.getFullYear().toString();
	   var mm = (this.getMonth()+1).toString(); // getMonth() is zero-based
	   var dd  = this.getDate().toString();
	   return yyyy + (mm[1]?mm:"0"+mm[0]) + (dd[1]?dd:"0"+dd[0]); // padding
	};
});
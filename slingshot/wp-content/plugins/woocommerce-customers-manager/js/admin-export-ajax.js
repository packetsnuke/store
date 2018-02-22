jQuery(document).ready(function()
{
	var error = null;
	var current_iteration_num;
	var max_iterations;
	var max_guest_orders_iterations;
	var max_registered_users_iterations;
	var per_page;
	var default_per_page = 70;
	var sleep_period = 800;
	var guest_customers = {};
	var csv_data = [["ID", 
				"Password hash", 
				"Name", 
				"Surname", 
				"Role", 
				"Login", 
				"Email", 
				"Notes", 
				"Registration date", 
				"First order date",
				"Last order date", 
				"# Orders", 
				"Total amount spent",
				"Billing name",
				"Billing surname",
				"Billing email",
				"Billing phone",
				"Billing company",
				"VAT number",
				"Billing address",
				"Billing address 2",
				"Billing postcode",
				"Billing city",
				"Billing state",
				"Billing country",
				"Shipping name",
				"Shipping surname",
				"Shipping company",
				"Shipping address",
				"Shipping address 2",
				"Shipping postcode",
				"Shipping city",
				"Shipping state",
				"Shipping country"]]
	
	if(wpuef_addition_columns.length != 0)
		for(var i = 0; i < wpuef_addition_columns.length; i++)
			csv_data[0].push(wpuef_addition_columns[i]);
	//console.log(csv_data);
	jQuery('#export-start-button').click(transition_out);
	
	if(ids_to_export != "")
	{
		jQuery('#export-start-button').trigger('click');
	}
	function transition_out(e)
	{
		per_page = default_per_page;
		e.preventDefault();
		e.stopImmediatePropagation();
		max_guest_orders_iterations = max_iterations = 0;
		current_iteration_num = 1; //0
		jQuery('#ajax-progress-title').delay(600).fadeIn(500);
		jQuery('#upload-istruction-box').fadeOut(500, function(){setTimeout(function()
			{
				if(jQuery("#export-guest-user-select-box").val() == "yes")
					wccm_get_guest_orders_max_iterations();
				else
					wcc_get_registered_orders_max_iterations();
			}, 1000); });
		return false;
	}
	
	function wcc_get_registered_orders_max_iterations()
	{
		//Registered customers
		var formData = new FormData();
		formData.append('action', 'wccm_export_get_max_regiesterd_users'); 
		if(ids_to_export != "")
			formData.append('ids_to_export', ids_to_export); //export from discover by order
		jQuery.ajax({
			url: ajax_url, 
			type: 'POST',
			data: formData,
			async: true,
			cache: false,
			contentType: false,
			processData: false,
			success: function (data) 
			{
				max_registered_users_iterations = Math.ceil(data/per_page);
				if(max_guest_orders_iterations == 0 && max_registered_users_iterations == 0)
				{
					window.alert("There are no customers to export");
					return;
				}
				max_iterations = max_guest_orders_iterations + max_registered_users_iterations;
				
				if(jQuery("#export-guest-user-select-box").val() == "yes" && max_guest_orders_iterations != 0)
				{
					per_page = default_per_page;
					wccm_get_guest_customers_data();
				}
				else
				{
					current_iteration_num = 0;
					wccm_export_csv();
				}
			}
		});
	}
	function wccm_get_guest_orders_max_iterations()
	{
		//Guest customers
		var formData = new FormData();
		formData.append('action', 'wccm_export_get_max_guest_orders_iterations');  
		jQuery.ajax({
			url: ajax_url, 
			type: 'POST',
			data: formData,
			async: true,
			cache: false,
			contentType: false,
			processData: false,
			success: function (data) 
			{
				max_guest_orders_iterations = Math.ceil(data/per_page);
				wcc_get_registered_orders_max_iterations();
			}
		});
	}
	function wccm_get_guest_customers_data()
	{
		var formData = new FormData();
		formData.append('action', 'wccm_export_guests_csv');
		formData.append('per_page', per_page); 
		formData.append('page_num', current_iteration_num); 
		var perc_num = ((current_iteration_num/max_iterations)*100);
		perc_num = perc_num > 100 ? 100:perc_num;
		
		var perc = Math.floor(perc_num);
		jQuery('#ajax-progress').html("<p>computing data, please wait...<strong>"+perc+"% done</strong></p>");
		jQuery( "#progressbar" ).progressbar({
					  value: perc
					});
					
		jQuery.ajax({
			url: ajax_url, //defined in php
			type: 'POST',
			data: formData,//{action: 'upload_csv', csv: data_to_send},
			async: true,
			success: function (data) {
				//alert(data);
				wccm_check_guest_data_response(data);
			},
			error: function (request, error) {
				if(per_page > 10)
				{
					per_page -= 10;
					wccm_get_guest_customers_data();
				}
				else
					alert("Server error, response: "+error);
			},
			cache: false,
			contentType: false,
			processData: false
		});
	}
	function wccm_export_csv()
	{
		var formData = new FormData();
		formData.append('action', 'wccm_export_csv'); 
		if(ids_to_export != "")
			formData.append('ids_to_export', ids_to_export); //export from discover by order		
		/* if(current_iteration_num == 0)
		{
			if(jQuery("#export-guest-user-select-box").val() == "yes")
				formData.append('action', 'wccm_export_guests_csv'); 
			else
				current_iteration_num++;
		}  */
		formData.append('per_page', per_page); 
		formData.append('page_num', (current_iteration_num - max_guest_orders_iterations)+1); 
		var perc_num = (current_iteration_num/max_iterations)*100;
		perc_num = perc_num > 100 ? 100:perc_num;
		
		var perc = Math.floor(perc_num);
		jQuery('#ajax-progress').html("<p>computing data, please wait...<strong>"+perc+"% done</strong></p>");
		jQuery( "#progressbar" ).progressbar({
					  value: perc
					});
					
		jQuery.ajax({
			url: ajax_url, //defined in php
			type: 'POST',
			data: formData,//{action: 'upload_csv', csv: data_to_send},
			async: true,
			success: function (data) {
				//alert(data);
				wccm_check_response(data);
			},
			error: function (request, error) {
				if(per_page > 10)
				{
					per_page -= 10;
					wccm_export_csv();
				}
				else
					alert("Server error, response: "+error);
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
			setTimeout(function(){ wccm_get_guest_customers_data(); }, sleep_period);
		}
		else
		{
			jQuery.each(guest_customers, function( key, value_array )
				{
					csv_data.push(value_array)
				}); 
			//wccm_export_csv();
			setTimeout(function(){ wccm_export_csv(); }, sleep_period);
		}
	}
	function wccm_check_response(data)
	{
		if(data != null)
		{
			var result = jQuery.parseJSON(data);
			jQuery.each(result, function(index, value)
			{
				var tmp_array = [];
				jQuery.each(value, function( key, value )
				{
					tmp_array.push(value);
				});
				csv_data.push(tmp_array);
			});
		}
		if(current_iteration_num < max_iterations)
		{
			current_iteration_num++;
			//wccm_export_csv();
			setTimeout(function(){ wccm_export_csv(); }, sleep_period);
		}
		else
		{
			jQuery( "#progressbar" ).progressbar({
			  value: 100
			});
			jQuery('#ajax-progress').append("<p>100% done</p> <h3>end!</h3>");
			download_csv_file();
		}
	}
	
	function download_csv_file()
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
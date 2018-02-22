var wccm_per_page = 100;
var sleep_period = 500;
var wccm_current_iteration_num = 1;
var wccm_max_iterations = 0;
var wccm_picker_start_date;
var wccm_start_datewccm_start_date;
var wccm_picker_end_date;
var wccm_end_date;
var wccm_customer_ids;
var wccm_product_ids;
var wccm_product_category_ids;
var wccm_csv_separator;
var wccm_csv_line_breaker;
var wccm_statuses = [];
var wccm_customers_id = [];				
var wccm_customers_id_temp = [];				
var wccm_customers_emails = [];				
var wccm_customers_emails_temp = [];				
				
jQuery(document).ready(function()
{
	wccm_picker_start_date =  jQuery( "#picker_start_date" ).pickadate({formatSubmit: 'yyyy/mm/dd'});
	wccm_picker_end_date = jQuery( "#picker_end_date" ).pickadate({formatSubmit: 'yyyy/mm/dd'});
	jQuery('#view_results_button').fadeOut(0);
	wccm_set_range_slider();
	
	jQuery('#start-export-button').click(wccm_start_exporting);
	//jQuery('#view_results_button').click(wccm_load_customers_list);
	
	Date.prototype.yyyymmdd = function() {
	   var yyyy = this.getFullYear().toString();
	   var mm = (this.getMonth()+1).toString(); // getMonth() is zero-based
	   var dd  = this.getDate().toString();
	   return yyyy + (mm[1]?mm:"0"+mm[0]) + (dd[1]?dd:"0"+dd[0]); // padding
	};
});
function wccm_start_exporting()
{
	var picker_start_date = wccm_picker_start_date.pickadate('picker');
	var picker_end_date = wccm_picker_end_date.pickadate('picker'); 
	wccm_start_date = picker_start_date.get('select', 'yyyy-mm-dd'); 
	wccm_end_date = picker_end_date.get('select', 'yyyy-mm-dd');
	
	wccm_statuses = [];
	jQuery.each(jQuery('.status-checkbox'),function(index,value)
	{
		if(this.checked)
			wccm_statuses.push(jQuery(this).val());
	});
	
	if(wccm_statuses.length == 0)
		alert(statuses_error);
	else if(wccm_start_date > wccm_end_date)
		alert(date_error);
	/* else if(jQuery('#csv-separator').val() == "" || jQuery('#csv-line-breaker').val() == "")
	{
		alert(csv_error);
	} */
	else
	{
		/* wccm_csv_line_breaker = jQuery('#csv-line-breaker').val();
		wccm_csv_separator = jQuery('#csv-separator').val(); */
		wccm_customer_ids = jQuery('#customer_ids').val();
		wccm_product_ids = jQuery('#product_ids').val();
		wccm_product_category_ids = jQuery('#category_ids').val();
		jQuery("html, body").animate({ scrollTop: 0 }, 800);
		jQuery('#option-box').delay('900').fadeOut('800');
		jQuery('#progress-container').delay('1800').fadeIn('800', wccm_get_order_tot);
	}
}
function wccm_set_range_slider()
{
	 jQuery( "#slider-range" ).slider({
      range: true,
      min: 0,
      max: Math.ceil(max_total_sale),
      values: [ 0, Math.ceil(max_total_sale) ],
      slide: function( event, ui ) {
       jQuery( "#amount" ).val( "" + ui.values[ 0 ] + " -  " + ui.values[ 1 ] );
      }
    });
    jQuery( "#amount" ).val( "" + jQuery( "#slider-range" ).slider( "values", 0 ) +
      " - " + jQuery( "#slider-range" ).slider( "values", 1 ) );
	  
	jQuery( "#slider-range-total" ).slider({
      range: true,
      min: 0,
      max: Math.ceil(max_user_total_sale),
      values: [ 0, Math.ceil(max_user_total_sale) ],
      slide: function( event, ui ) {
       jQuery( "#amount-total" ).val( "" + ui.values[ 0 ] + " -  " + ui.values[ 1 ] );
      }
    });
    jQuery( "#amount-total" ).val( "" + jQuery( "#slider-range-total" ).slider( "values", 0 ) +
      " - " + jQuery( "#slider-range-total" ).slider( "values", 1 ) );
}

function wccm_get_order_tot()
{
	var formData = new FormData();
	formData.append('action', 'wccm_get_orders_tot_num');
	if(wccm_start_date != "")
		formData.append('start_date', wccm_start_date);
	if(wccm_end_date != "")
		formData.append('end_date', wccm_end_date);
	if(wccm_customer_ids)
		formData.append('customer_ids', wccm_customer_ids.join());
	if(wccm_product_ids)
		formData.append('product_ids', wccm_product_ids.join());
	if(wccm_product_category_ids)
		formData.append('category_ids', wccm_product_category_ids.join());
	formData.append('min_amount', jQuery( "#slider-range" ).slider( "values", 0 ));
	formData.append('max_amount', jQuery( "#slider-range" ).slider( "values", 1 ));
	formData.append('min_amount_total', jQuery( "#slider-range-total" ).slider( "values", 0 ));
	formData.append('max_amount_total', jQuery( "#slider-range-total" ).slider( "values", 1 ));
	formData.append('product_relationship', jQuery( "#product_relationship" ).val());
	formData.append('product_category_relationship', jQuery( "#product_category_relationship" ).val());
	formData.append('product_category_filters_relationship', jQuery( "#product_category_filters_relationship" ).val());
	formData.append('statuses', wccm_statuses.join());
	
	jQuery.ajax({
		url: ajaxurl, 
		type: 'POST',
		data: formData,
		async: true,
		success: function (data) {
			wccm_max_iterations =  Math.ceil(data/wccm_per_page);
			wccm_get_orders_data();
		},
		error: function (data,error) 
		{
			//wccm_check_response(data);
		},
		cache: false,
		contentType: false, 
		processData: false
	});
}
function wccm_get_orders_data()
{
	var formData = new FormData();
	formData.append('action', 'wccm_get_orders_data'); 
	if(wccm_start_date != "")
		formData.append('start_date', wccm_start_date);
	if(wccm_end_date != "")
		formData.append('end_date', wccm_end_date);
	if(wccm_customer_ids)
		formData.append('customer_ids', wccm_customer_ids.join());
	if(wccm_product_ids)
		formData.append('product_ids', wccm_product_ids.join());
	if(wccm_product_category_ids)
		formData.append('category_ids', wccm_product_category_ids.join());
	formData.append('min_amount', jQuery( "#slider-range" ).slider( "values", 0 ));
	formData.append('max_amount', jQuery( "#slider-range" ).slider( "values", 1 ));
	formData.append('min_amount_total', jQuery( "#slider-range-total" ).slider( "values", 0 ));
	formData.append('max_amount_total', jQuery( "#slider-range-total" ).slider( "values", 1 ));
	formData.append('product_relationship', jQuery( "#product_relationship" ).val());
	formData.append('product_category_relationship', jQuery( "#product_category_relationship" ).val());
	formData.append('product_category_filters_relationship', jQuery( "#product_category_filters_relationship" ).val());
	formData.append('statuses', wccm_statuses.join());
	formData.append('per_page', wccm_per_page); 
	formData.append('page_num', wccm_current_iteration_num); 
	
	var perc_num = ((wccm_current_iteration_num/wccm_max_iterations)*100);
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
			wccm_process_response_data(data);
		},
		error: function (data,error) {
			if(wccm_per_page > 10)
			{
				wccm_per_page -=10;
				wccm_get_orders_data();
			}
			else
				alert("Error: "+error);
			//wccm_check_response(data);
		},
		cache: false,
		contentType: false,
		processData: false
	});
}
function wccm_process_response_data(data)
{
	if(data != null)
	{
		var result = jQuery.parseJSON(data);
		//console.log(result);
		//wccm_customers_emails += result['emails']; 
		jQuery.each(result['emails'], function(index, value)
		{
			if(typeof wccm_customers_emails_temp[value] === 'undefined')
			{
				wccm_customers_emails.push(value);
				wccm_customers_emails_temp[value] = 1;
			}
		});
		jQuery.each(result['ids'], function(index, value)
		{
			if(typeof wccm_customers_id_temp[value] === 'undefined')
			{
				wccm_customers_id.push(value);
				wccm_customers_id_temp[value] = 1;
			}
			
		}); 
		//console.log(wccm_customers_id);
	}
	if(wccm_current_iteration_num < wccm_max_iterations)
	{
		wccm_current_iteration_num++;
		setTimeout(function(){ wccm_get_orders_data(data); }, sleep_period);
	}
	else
	{
		
		jQuery('#wccm_customers_ids').val(wccm_customers_id.join());
		jQuery('#wccm_customers_emails').val(wccm_customers_emails.join());
		jQuery('#wccm_start_date').val(wccm_start_date);
		jQuery('#wccm_end_date').val(wccm_end_date);
		jQuery('#view_results_button').fadeIn(800);
		//wccm_download_csv_file();
	}
}

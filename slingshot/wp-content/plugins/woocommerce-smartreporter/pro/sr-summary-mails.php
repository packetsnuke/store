<?php 

global $wpdb;

if ( empty( $wpdb ) || !is_object( $wpdb ) ) {
    if ( ! defined('ABSPATH') ) {
        include_once ('../../../../wp-load.php');
    }
    require_once ABSPATH . 'wp-includes/wp-db.php';
}

include_once (WP_PLUGIN_DIR . "/smart-reporter-for-wp-e-commerce/sr/json-woo.php");

function extra_reccurences() {
	$curr_time_gmt = date('H:i:s',time()- date("Z"));
	$new_date = date('Y-m-d') ." " . $curr_time_gmt;
	$today = date('Y-m-d',((int)strtotime($new_date)) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS )) ;
	$days_in_this_month = date('t', mktime(0, 0, 0, date('m', strtotime($today)), 1, date('Y', strtotime($today))));
	$month_interval = 60*60*24*$days_in_this_month;

	return array(
		'weekly' => array('interval' => 604800, 'display' => 'Once Weekly'),
		'monthly' => array('interval' => $month_interval, 'display' => 'Once Monthly'),
	);
}

if (get_option('sr_send_summary_mails') == "yes") {

	wp_clear_scheduled_hook( 'sr_send_summary_mails' ); // only for v2.5

	add_filter('cron_schedules', 'extra_reccurences'); // for adding the occurance in set_timeout

	$curr_time_gmt = date('H:i:s',time()- date("Z"));

	if ( ! wp_next_scheduled( 'sr_send_summary_mails' ) ) {

		if (get_option('sr_summary_mail_interval') == 'monthly') {

			$new_date = date('Y-m-d' , strtotime(date('Y-m-d') .' +1 month')) ." " . $curr_time_gmt;
			$today = date('Y-m-d',((int)strtotime($new_date)) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS )) ;
			$monthly_start = date("Y-m-d H:i:s", mktime(0,0,0,date('m', strtotime($today)),get_option('sr_summary_month_start_day'),date('Y', strtotime($today))));
			wp_schedule_event( (strtotime( $monthly_start ) - (get_option( 'gmt_offset' ) * HOUR_IN_SECONDS)), 'monthly', 'sr_send_summary_mails');

		} if (get_option('sr_summary_mail_interval') == 'weekly') {

			$weekly_start = date('Y-m-d H:i:s',strtotime("next ". get_option('sr_summary_week_start_day'), strtotime('tomorrow',strtotime(date('Y-m-d')))));
			wp_schedule_event( (strtotime( $weekly_start ) - (get_option( 'gmt_offset' ) * HOUR_IN_SECONDS)), 'weekly', 'sr_send_summary_mails');

		} else {
			wp_schedule_event( (strtotime( 'tomorrow ') - (get_option( 'gmt_offset' ) * HOUR_IN_SECONDS)), 'daily', 'sr_send_summary_mails');
		}
		
	}

	add_action( 'sr_send_summary_mails', 'sr_send_summary_mails');
}

function sr_send_summary_mails() {

	global $wpdb;

	$_POST['SR_CURRENCY_SYMBOL'] = $sr_currency_symbol = get_woocommerce_currency_symbol();
	$_POST['SR_DECIMAL_PLACES'] = $sr_decimal_places = get_option( 'woocommerce_price_num_decimals' );
	$_POST['top_prod_option'] = "sr_opt_top_prod_price";

	$date = date('Y-m-d', strtotime(date('Y-m-d') .' -1 day'));

	if (get_option('sr_summary_mail_interval') == 'monthly') {
		$start_date = date('Y-m-d', strtotime($date .' -1 month')) . " 00:00:00";
	} else if (get_option('sr_summary_mail_interval') == 'weekly') {
		$start_date = date('Y-m-d', strtotime($date .' -1 week')) . " 00:00:00";
	} else {
		$start_date = $date . " 00:00:00";
	}

	$end_date = $date . " 23:59:59";
	
	$today_arr 		    = getdate();
	$this_month_start   = date("Y-m-d H:i:s", mktime(0,0,0,$today_arr['mon'],1,$today_arr['year']));
	$days_in_this_month = date('t', mktime(0, 0, 0, $today_arr['mon'], 1, $today_arr['year']));


	$cumm_sales_data = sr_get_sales ($start_date,$end_date,1,$_POST);
	$daily_kpi_data = sr_get_daily_kpi_data();

	$total_tax_shipping = (!empty($cumm_sales_data[13])) ? ($cumm_sales_data[13]['tax'] + $cumm_sales_data[13]['shipping_tax'] + $cumm_sales_data[13]['shipping']) : 0;
	$net_sales = (isset($cumm_sales_data[13]['net_sales'])) ? $cumm_sales_data[13]['net_sales'] : 0;

	$prod_add_to_cart = (isset($cumm_sales_data[16]['total_products_added_cart'])) ? $cumm_sales_data[16]['total_products_added_cart'] : 0;
	$prod_purchased = (isset($cumm_sales_data[16]['products_purchased_count'])) ? $cumm_sales_data[16]['products_purchased_count'] : 0;

	//code for forming the array for payment gateways

	$payment_gateway_data = array();

	if (!empty($cumm_sales_data[12])) {

		foreach ($cumm_sales_data[12] as $payment_gateway) {

			$payment_gateway_temp = "<tr>";
			$payment_gateway_temp .= "<td style='padding-left: 20px;'>" . $payment_gateway['payment_method'] . "</td>";
			$payment_gateway_temp .= "<td>" . $payment_gateway['gateway_sales_display'] . " • " . $payment_gateway['gateway_sales_percent'] . " • " . $payment_gateway['sales_count'] . "</td> </tr>";
			$payment_gateway_data [] = $payment_gateway_temp;
		}

	}
	 
	//code for forming the array for abandoned products

	$abandoned_products_data = array();

	if (!empty($cumm_sales_data[14])) {

		foreach ($cumm_sales_data[14] as $abandoned_products) {

			$abandoned_products_temp = "<tr>";
			$abandoned_products_temp .= "<td>" . $abandoned_products['prod_name'] . "</td>";
			$abandoned_products_temp .= "<td style='padding-left: 10px;'>" . $abandoned_products['price'] . " • " . $abandoned_products['abandoned_rate'] . " • " . $abandoned_products['abondoned_qty'] . "</td> </tr>";
			$abandoned_products_data [] = $abandoned_products_temp;
		}
	}

	//code for forming the array for Top Coupons

	$top_coupons_data = array();

	if (!empty($cumm_sales_data[9])) {

		foreach ($cumm_sales_data[9] as $top_coupons) {

			$top_coupons_temp = "<tr>";
			$top_coupons_temp .= "<td style='padding-left: 20px;'>" . $top_coupons['coupon_name'] . "</td>";
			$top_coupons_temp .= "<td>" . $top_coupons['coupon_amount'] . " • " . $top_coupons['coupon_count'] . "</td> </tr>";
			$top_coupons_data [] = $top_coupons_temp;
		}
	}

	//code for forming the array for Top Products

	$top_products_data = array();

	if (!empty($cumm_sales_data[2])) {

		foreach ($cumm_sales_data[2] as $top_products) {

			$top_products_temp = "<tr>";
			$top_products_temp .= "<td style='padding-left: 20px;'>" . $top_products['product_name'] . "</td>";
			$top_products_temp .= "<td>" . $top_products['product_sales_display'] . " • " . $top_products['product_qty'] . "</td> </tr>";
			$top_products_data [] = $top_products_temp;
		}
	}


	//Code for creating month to date and forecasted sales widget
	$query               = "SELECT SUM( postmeta.meta_value ) AS month_to_date 
		                    FROM `{$wpdb->prefix}postmeta` AS postmeta
		                    LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = postmeta.post_id )
		                    WHERE postmeta.meta_key IN ('_order_total')
	                        	AND posts.post_date between '$this_month_start' AND '$end_date'
	                        	$cond";
	$results 			 = $wpdb->get_results ( $query, 'ARRAY_A' );

	$month_to_date_sales = $results[0]['month_to_date'] ? $results[0]['month_to_date'] : 0;
	$avg_sales_per_day  = round(($results[0]['month_to_date']/$today_arr['mday']),2);
	$forcasted_sales 	= $avg_sales_per_day * $days_in_this_month;

	$month_to_date_sales = $sr_currency_symbol . sr_number_format($month_to_date_sales,$sr_decimal_places);
	$forcasted_sales = $sr_currency_symbol . sr_number_format($forcasted_sales,$sr_decimal_places);

	// $heading = 'Daily Summary Report - ' . date('F d, Y');
	if (get_option('sr_summary_mail_interval') == 'monthly') {
		$heading = 'Monthly Summary | ' . date('M d', strtotime($start_date)) .' - '.date('M d', strtotime($end_date));
	} else if (get_option('sr_summary_mail_interval') == 'weekly') {
		$heading = 'Weekly Summary | ' . date('M d', strtotime($start_date)) .' - '.date('M d', strtotime($end_date));
	} else {
		$heading = 'Daily Summary - ' . date('F d, Y', strtotime($end_date));
	}

	ob_start();

	woocommerce_get_template('emails/email-header.php', array( 'email_heading' => $heading ));

	// <div class='average_order_total_amt'>
	// 	            <div class='sr_cumm_small_widget_content sr_cumm_avg_order_value'>

	echo "
			<table style='width: 120px;height: 55px;border: 2px solid #557da1;float: right;text-align: center;color: #7C7C86;margin-right: 50px'>
				<tr> <td>
				        <p style='margin-top: 5px;font-size: 24px;margin-bottom: 1px;'> ". $month_to_date_sales ." </p>
			            <p style='font-size: 10px;font-weight: 500;'> Month To Date Sales </p>
					    
			    </td> </tr>
			</table>
			    
			<table >
				<tr> 
					<td> Sales </td> 
					<td style='padding-left: 50px'> <b>". $sr_currency_symbol . sr_number_format($cumm_sales_data[1],$sr_decimal_places) ." </b> </td>
				</tr>"  . 
				"<tr> 
					<td> Discounts </td> 
					<td style='padding-left: 50px'> ". $sr_currency_symbol . sr_number_format($cumm_sales_data[8],$sr_decimal_places) ." </td>
				</tr>" . 
				"<tr> 
					<td> Tax & Shipping   </td> 
					<td style='padding-left: 50px'> ". $sr_currency_symbol . sr_number_format($total_tax_shipping,$sr_decimal_places) ." </td>
				</tr>" . 
				"<tr> 
					<td> Refunds   </td> 
					<td style='padding-left: 50px'> ". $sr_currency_symbol . sr_number_format($daily_kpi_data['today_refund'],$sr_decimal_places) ." </td>
				</tr>" .
				"<tr> 
					<td> Net Sales   </td> 
					<td style='padding-left: 50px'> <b>". $sr_currency_symbol . sr_number_format($net_sales,$sr_decimal_places) ."</b> </td>
				</tr>" . 
			"</table> 


			<table style='color: #7C7C86;width: 120px;height: 55px;border: 2px solid #557da1;float: right;text-align: center;margin-right: 50px;margin-top: -25px'>
				<tr> <td>
			        <p style='margin-top: 5px;font-size: 24px;margin-bottom: 1px;'> ". $forcasted_sales ." </p>
		            <p style='font-size: 10px;font-weight: 500;'> Forecasted Sales </p>
		    	</td> </tr>
			</table>

		    <br />

			<table>

				<tr> 
					<td> New Customers   </td> 
					<td style='padding-left: 10px'> <b>". sr_number_format($daily_kpi_data['today_count_cust'],$sr_decimal_places) ." </b> </td>
				</tr>"  . 
				"<tr> 
					<td> Avg. Order Total   </td> 
					<td style='padding-left: 10px'> ". $sr_currency_symbol . sr_number_format($cumm_sales_data[4],$sr_decimal_places) ." </td>
				</tr>" . 
				"<tr> 
					<td> Avg. Items Per Order   </td> 
					<td style='padding-left: 10px'> ". $sr_currency_symbol . sr_number_format($cumm_sales_data[5],$sr_decimal_places) ." </td>
				</tr>" . 
				"<tr> 
					<td> Unfullfilled Orders   </td> 
					<td style='padding-left: 10px'> <b>". sr_number_format($daily_kpi_data['count_order_fulfillment_today'],$sr_decimal_places) ." </b> </td>
				</tr>" .

			"</table> 

			<span style='float:right'>
				<h3> Abandoned Products </h3>
					";

				if ( !empty($abandoned_products_data) ) {

					echo "<table style='margin-top:-15px;'>";

					foreach ($abandoned_products_data as $abandoned_products_data1) {
						echo $abandoned_products_data1;
					}	

					echo "</table>";

				} else {
					echo "<span style='margin-top:-15px;text-align: center;font-size: 15px;font-weight: 700;color: #DBDBDB;margin-top: 2.37em;font-family: Helvetica, Arial, sans-serif;'> 
							No Data
						</span>";
				}

				echo "
			</span>

			<h3> Abandonment Statistics </h3>
			<table style='margin-top:-15px;'>
				<tr> 
					<td> Add To Cart </td> 
					<td style='padding-left: 20px'> ". sr_number_format($prod_add_to_cart,$sr_decimal_places) ." </td>
				</tr>"  . 
				"<tr> 
					<td> Orders Placed </td> 
					<td style='padding-left: 20px'> ". sr_number_format($prod_purchased,$sr_decimal_places) ." </td>
					</tr>" . 
				"<tr> 
					<td> Abandonment Rate </td> 
					<td style='padding-left: 20px'> ". sr_number_format($cumm_sales_data[15],$sr_decimal_places) ."% </td>
				</tr>" . 
			"</table>

			<table width=100% style='margin-top:-15px;'>
			<tr><th width=60%></th><th width=40% style='text-align: right;'></th>

			<tr><th style='padding-bottom: 5px;padding-top: 20px;text-align: left;color: #7C7C86;'> Payment Gateways </th> </tr>

			";

			// style='margin-top:-15px;'

				if ( !empty($payment_gateway_data) ) {

					// echo "<table >";

					foreach ($payment_gateway_data as $payment_gateway_data1) {
						echo $payment_gateway_data1;
					}	

					// echo "</table>";

				} else {
					echo "<tr><td style='padding-left: 20px;><span style='margin-top:-15px;text-align: center;font-size: 15px;font-weight: 700;color: #DBDBDB;margin-top: 2.37em;font-family: Helvetica, Arial, sans-serif;'> 
							No Data
						</span></td></tr>";
				}

			echo "

				<tr><th style='padding-bottom: 5px;padding-top: 20px;text-align: left;color: #7C7C86;'> Top Coupons </th> </tr>

				";

				if ( !empty($top_coupons_data) ) {

					// echo "<table style='margin-top:-15px;'>";

					foreach ($top_coupons_data as $top_coupons_data1) {
						echo $top_coupons_data1;
					}	

					// echo "</table>";

				} else {
					echo "<tr><td style='padding-left: 20px;><span style='margin-top:-15px;text-align: center;font-size: 15px;font-weight: 700;color: #DBDBDB;margin-top: 2.37em;font-family: Helvetica, Arial, sans-serif;'> 
							No Data
						</span></td></tr>";
				}

			echo "

				<tr><th style='padding-bottom: 5px;padding-top: 20px;text-align: left;color: #7C7C86;'> Top Products </th> </tr>
			";

				if ( !empty($top_products_data) ) {

					// echo "<table style='margin-top:-15px;'>";

					foreach ($top_products_data as $top_products_data1) {
						echo $top_products_data1;
					}	

					// echo "</table>";

				} else {
					echo "<tr><td style='padding-left: 20px;><span style='margin-top:-15px;text-align: center;font-size: 15px;font-weight: 700;color: #DBDBDB;margin-top: 2.37em;font-family: Helvetica, Arial, sans-serif;'> 
							No Data
						</span></td></tr>";
				}
				
			echo "</table>";


	$message = ob_get_clean();

	$sr_send_summary_mails_email = get_option('sr_send_summary_mails_email');
	$email = (!empty($sr_send_summary_mails_email)) ? $sr_send_summary_mails_email : get_option('admin_email');
	
	if (get_option('sr_summary_mail_interval') == 'monthly') {
		$subject = 'Monthly Summary Report for ' . sanitize_title(get_bloginfo( 'name' )) .' | ' . date('M d', strtotime($start_date)) .' - '.date('M d', strtotime($end_date));
	} else if (get_option('sr_summary_mail_interval') == 'weekly') {
		$subject = 'Weekly Summary Report for ' . sanitize_title(get_bloginfo( 'name' )) .' | ' . date('M d', strtotime($start_date)) .' - '.date('M d', strtotime($end_date));
	} else {
		$subject = 'Daily Summary Report for ' . sanitize_title(get_bloginfo( 'name' )) .' | '. date('F d, Y', strtotime($end_date));
	}

	woocommerce_mail( $email, $subject, $message );
}


//Abandoned Products Export CSV Function

function sr_export_csv_woo ( $columns_header, $data, $widget ) {

	$getfield = '';

	foreach ( $columns_header as $key => $value ) {
		$getfield .= $value . ',';
	}

	$fields = substr_replace($getfield, '', -1);
	$each_field = array_keys( $columns_header );
	
	$csv_file_name = sanitize_title(get_bloginfo( 'name' )) . '_' . $widget . '_' . gmdate('d-M-Y_H:i:s') . ".csv";

	foreach( (array) $data as $row ){
		for($i = 0; $i < count ( $columns_header ); $i++){
			if($i == 0) $fields .= "\n";
            $row_each_field = $row[$each_field[$i]];
            $array_temp = str_replace(array("\n", "\n\r", "\r\n", "\r"), "\t", $row_each_field); 
			$array = str_replace("<br>", "\n", $array_temp); 
			$array = str_getcsv ( $array , ",", "\"" , "\\");
			$str = ( $array && is_array( $array ) ) ? implode( ', ', $array ) : '';
			$fields .= '"'. $str . '",'; 
		}			
		$fields = substr_replace($fields, '', -1); 
	}
	$upload_dir = wp_upload_dir();
	$file_data = array();
	$file_data['wp_upload_dir'] = $upload_dir['path'] . '/';
	$file_data['file_name'] = $csv_file_name;
	$file_data['file_content'] = $fields;
	return $file_data;
}

// Abandoned Products Export

// if (isset ( $_GET ['cmd'] ) && (($_GET ['cmd'] == 'top_ababdoned_products_export') )) {
function sr_top_ababdoned_products_export() {

	global $wpdb;

	$sr_domain = 'smart-reporter';
	$sr_is_woo22 = (!empty($_GET['SR_IS_WOO22'])) ? $_GET['SR_IS_WOO22'] : '';
	//Get the converted dates    
    $converted_dates = date_timezone_conversion($_GET);

    $start_date = $converted_dates ['start_date'];
    $end_date = $converted_dates ['end_date'];

    $terms_post = '';
    $group_by = '';

    if (empty($sr_is_woo22)) {
    	$query_terms = "SELECT id FROM {$wpdb->prefix}posts AS posts
                                JOIN {$wpdb->prefix}term_relationships AS term_relationships 
                                                            ON term_relationships.object_id = posts.ID 
                                            JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy 
                                                            ON term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id 
                                            JOIN {$wpdb->prefix}terms AS terms 
                                                            ON term_taxonomy.term_id = terms.term_id
                            WHERE terms.name IN ('completed','processing','on-hold')
                                AND posts.post_status IN ('publish')";
                  
	    $terms_post = $wpdb->get_col($query_terms);
	    $rows_terms_post =  $wpdb->num_rows;

	    if ($rows_terms_post > 0) {
	    	$terms_post = implode(",",$terms_post);
	    } else {
	    	$terms_post = ''; 
	    }
    }
    
    $abandoned_prod_data = json_decode(sr_get_abandoned_products($start_date,$end_date,$group_by,get_woocommerce_currency_symbol(),get_option( 'woocommerce_price_num_decimals' ),'','','',$terms_post,$sr_is_woo22),true);

    $columns_header = array();
    $columns_header['prod_name'] 				= __('Name', $sr_domain);
	$columns_header['abondoned_qty'] 			= __('Add To Cart', $sr_domain);
	$columns_header['orders_placed'] 			= __('Total Orders', $sr_domain);
	$columns_header['abandoned_rate'] 		    = __('Abandoment Rate', $sr_domain);
	$columns_header['price'] 				    = __('Price', $sr_domain);
	$columns_header['last_order_date'] 			= __('Last Order Date', $sr_domain);

	$file_data = sr_export_csv_woo ( $columns_header, $abandoned_prod_data, 'abandoned_products' );

	ob_clean();
    header("Content-type: text/x-csv; charset=UTF-8");
	header("Content-Transfer-Encoding: binary");
	header("Content-Disposition: attachment; filename=".$file_data['file_name']); 
	header("Pragma: no-cache");
	header("Expires: 0");
	
	echo $file_data['file_content'];	
	exit;
}



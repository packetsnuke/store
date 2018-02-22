<?php

ob_start();

if ( empty( $wpdb ) || !is_object( $wpdb ) ) {
    if ( ! defined('ABSPATH') ) {
        include_once ('../../../../wp-load.php');
    }
    require_once ABSPATH . 'wp-includes/wp-db.php';
}

// include_once ('../../../../wp-load.php');
// include_once ('../../../../wp-includes/wp-db.php');
include_once (ABSPATH . WPINC . '/functions.php');
// include_once ('reporter-console.php'); // Included for using the sr_number_format function


//Function to convert the Sales Figures
function sr_number_format($input, $places)
{

    $suffixes = array('', 'k', 'm', 'b', 't');
    $suffixIndex = 0;
    $mult = pow(10, $places);

    while(abs($input) >= 1000 && $suffixIndex < sizeof($suffixes))
    {
        $suffixIndex++;
        $input /= 1000;
    }

    return (
        $input > 0
            // precision of 3 decimal places
            
            ? floor($input * $mult) / $mult
            : ceil($input * $mult) / $mult
        )
        . $suffixes[$suffixIndex];
}


// =============================================================================
// Code For SR Beta
// =============================================================================

	$del = 3;
	$result  = array ();
	$encoded = array ();
	$months  = array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );
	$cat_rev = array ();

	global $wpdb;

	if (isset ( $_GET ['start'] ))
	    $offset = $_GET ['start'];
	else
	    $offset = 0;

	if (isset ( $_GET ['limit'] ))
	    $limit = $_GET ['limit'];

	// For pro version check if the required file exists
	// if (file_exists ( '../pro/sr-woo.php' )){
	//     define ( 'SRPRO', true );
	// } else {
	//     define ( 'SRPRO', false );
	// }

	//Function for sorting and getting the top 5 values
	function usort_callback($a, $b)
	{
	  if ( $a['calc_total'] == $b['calc_total'] )
	    return 0;

	  return ( $a['calc_total'] > $b['calc_total'] ) ? -1 : 1;
	}


	//Function to get the abandoned Products
	function sr_get_abandoned_products(&$start_date,&$end_date_query,&$group_by,$sr_currency_symbol,$sr_decimal_places,$date_series,$select_top_abandoned_prod,$limit,$terms_taxonomy_ids,$sr_is_woo22) {
		
		global $wpdb;

		//Query to get Top Abandoned Products

	    $current_time = current_time('timestamp');
		$cut_off_time = (get_option('sr_abandoned_cutoff_time')) ? get_option('sr_abandoned_cutoff_time') : 0;
		$cart_cut_off_time = $cut_off_time * 60;
		$compare_time = $current_time - $cart_cut_off_time;

		//Query to update the abandoned product status
	    $query_abandoned_status = "UPDATE {$wpdb->prefix}sr_woo_abandoned_items
	    							SET product_abandoned = 1
	    							WHERE order_id IS NULL
	    								AND abandoned_cart_time < ". $compare_time;

		$wpdb->query ( $query_abandoned_status );

		//Query to get the Top Abandoned Products

		$query_top_abandoned_products = "SELECT SUM(quantity) as abondoned_qty,
											GROUP_CONCAT(quantity order by id SEPARATOR '###') AS abandoned_quantity,
											product_id as id
											$select_top_abandoned_prod
										FROM {$wpdb->prefix}sr_woo_abandoned_items
										WHERE order_id IS NULL                            
											AND product_abandoned = 1
											AND abandoned_cart_time BETWEEN '".strtotime($start_date)."' AND '". strtotime($end_date_query)."'
										GROUP BY product_id
										ORDER BY abondoned_qty DESC
										$limit";

		$results_top_abandoned_products    = $wpdb->get_results ( $query_top_abandoned_products, 'ARRAY_A' );
	    $rows_top_abandoned_products 	  =  $wpdb->num_rows;

	    if ($rows_top_abandoned_products > 0) {

	    	$prod_id = array();

			foreach ($results_top_abandoned_products as $results_top_abandoned_product) {
				$prod_id[] = $results_top_abandoned_product['id'];
			}	    	

			$prod_id = implode(",", $prod_id);

			$query_prod_abandoned_rate = "SELECT SUM(quantity) as abondoned_rate,
												COUNT(order_id) as orders_placed
											FROM {$wpdb->prefix}sr_woo_abandoned_items
											WHERE product_abandoned = 1
												AND abandoned_cart_time BETWEEN '".strtotime($start_date)."' AND '". strtotime($end_date_query)."'
												AND product_id IN (". $prod_id .")
											GROUP BY product_id
											ORDER BY FIND_IN_SET(product_id,'$prod_id') ";

			$results_prod_abandoned_rate    = $wpdb->get_results ( $query_prod_abandoned_rate, 'ARRAY_A' );
		    $rows_prod_abandoned_rate 	  =  $wpdb->num_rows;

		    $j = 0;

		    $total_prod_abandoned_qty = 0;
		    $total_prod_qty = 0;

		    //Query to get the last_order_date

		    // if (empty($limit)) {

		    	if (!empty($sr_is_woo22) && $sr_is_woo22 == 'true') {
		    		$terms_post_join = '';
		    		$terms_post_cond = "AND posts.post_status IN ('wc-completed','wc-processing','wc-on-hold')";
		    	} else {
		    		$terms_post_join = (!empty($terms_taxonomy_ids)) ? ' JOIN '.$wpdb->prefix.'term_relationships AS term_relationships ON (term_relationships.object_id = posts.ID AND posts.post_status = "publish") ' : '';
        			$terms_post_cond = (!empty($terms_taxonomy_ids) && !empty($terms_post_join)) ? 'AND term_relationships.term_taxonomy_id IN ('.$terms_taxonomy_ids.')' : '';	
		    	}

		    	// $terms_post_cond = (!empty($terms_post)) ? 'AND posts.ID IN ('.$terms_post.')' : '';

		    	$query_last_order_date = "SELECT posts.id as order_id,
		    									SUM(sr.quantity) as tot_qty_sold,
		    									COUNT(posts.id) as order_count,
		    									MAX(sr.order_id) AS max_order_id,
		    									posts.post_date_gmt AS last_order_date
	    									FROM {$wpdb->prefix}sr_woo_order_items AS sr
	    										JOIN {$wpdb->prefix}posts AS posts 
	    													ON (posts.id = sr.order_id)
												$terms_post_join
											WHERE sr.product_id IN (". $prod_id .")
												AND posts.post_date_gmt BETWEEN '".$start_date."' AND '". $end_date_query."'
												$terms_post_cond
											GROUP BY sr.product_id
												HAVING order_id = MAX(sr.order_id)
											ORDER BY FIND_IN_SET(sr.product_id,'$prod_id')";

				$results_last_order_date  = $wpdb->get_results ( $query_last_order_date, 'ARRAY_A' );
		    	$rows_last_order_date 	  = $wpdb->num_rows;

		    // }


		    //Query to get the variation Attributes in a formatted manner

		    $query_attributes = "SELECT post_id,
		    							GROUP_CONCAT(meta_key order by meta_id SEPARATOR '###') AS meta_key,
		    							GROUP_CONCAT(meta_value order by meta_id SEPARATOR '###') AS meta_value
				    			FROM {$wpdb->prefix}postmeta
				    			WHERE meta_key like 'attribute_%'
				    				AND post_id IN ($prod_id)
				    			GROUP BY post_id";
		   	$results_attributes    = $wpdb->get_results ( $query_attributes, 'ARRAY_A' );
		    $rows_attributes 	  =  $wpdb->num_rows; 

		    $variation_attributes = array();

		    foreach ($results_attributes as $results_attribute) {
		    	$meta_key = explode('###', $results_attribute['meta_key']);
                $meta_value = explode('###', $results_attribute['meta_value']);

                if (count($meta_key) != count($meta_value))
                    continue;

                $variation_attributes[$results_attribute['post_id']] = woocommerce_get_formatted_variation( array_combine($meta_key,$meta_value), true );
                
		    }

	    	foreach ($results_top_abandoned_products as &$results_top_abandoned_product) {
	    		$abandoned_quantity = (!empty($results_top_abandoned_product['abandoned_quantity'])) ? explode('###', $results_top_abandoned_product['abandoned_quantity']) : array();
                $abandoned_dates = (!empty($results_top_abandoned_product['abandoned_dates'])) ? explode('###', $results_top_abandoned_product['abandoned_dates']) : array();
                $max = 0;

                if ($group_by == "display_date_time") {
					$abandoned_dates_comp = explode('###', $results_top_abandoned_product['comp_time']);                	
                }

                if (count($abandoned_quantity) != count($abandoned_dates) && !empty($limit))
                    continue;

                unset($results_top_abandoned_product['abandoned_quantity']);
                unset($results_top_abandoned_product['abandoned_dates']);

                if ($group_by == "display_date_time") {
                	unset($results_top_abandoned_product['comp_time']);
                }

                $abandoned_date_series = $date_series;

                if ($group_by == "display_date_time") {

                	for ($i=0; $i<sizeof($abandoned_dates_comp); $i++) {
                		$abandoned_date_series[$abandoned_dates_comp[$i]]['post_date'] = $abandoned_dates[$i];
						$abandoned_date_series[$abandoned_dates_comp[$i]]['sales'] = $abandoned_date_series[$abandoned_dates_comp[$i]]['sales'] + $abandoned_quantity[$i];
	                }

                } else if (!empty($limit)) {
                	for ($i=0; $i<sizeof($abandoned_dates); $i++) {
						$abandoned_date_series[$abandoned_dates[$i]]['sales'] = $abandoned_date_series[$abandoned_dates[$i]]['sales'] + $abandoned_quantity[$i];

						if ($max < $abandoned_date_series[$abandoned_dates[$i]]['sales']) {
							$max = $abandoned_date_series[$abandoned_dates[$i]]['sales'];
						}
	                }	
                }
                
                if (!empty($limit)) {
                	$results_top_abandoned_product ['graph_data'] = array();

	                foreach ($abandoned_date_series as $abandoned_date_series1) {
	                	$results_top_abandoned_product['graph_data'][] = $abandoned_date_series1;
	                }	
                }

                $results_top_abandoned_product['max_count'] = $max;

                $results_top_abandoned_product['price'] = get_post_meta($results_top_abandoned_product['id'],'_price',true) * $results_top_abandoned_product['abondoned_qty'];

                if (empty($limit)) {
                	$results_top_abandoned_product['price'] = sr_number_format($results_top_abandoned_product['price'] ,$sr_decimal_places);
                } else {
                	$results_top_abandoned_product['price'] = $sr_currency_symbol . sr_number_format($results_top_abandoned_product['price'] ,$sr_decimal_places);
                }

                $results_top_abandoned_product['abondoned_qty'] = sr_number_format($results_top_abandoned_product['abondoned_qty'] ,$sr_decimal_places);

                if (empty($limit)) {
                	// $results_top_abandoned_product['orders_placed'] = $results_prod_abandoned_rate[$j]['orders_placed'];
                	$results_top_abandoned_product['orders_placed'] = (!empty($results_last_order_date[$j]['order_count'])) ? sr_number_format( $results_last_order_date[$j]['order_count'],$sr_decimal_places) : '';
                	$results_top_abandoned_product['last_order_date'] = (!empty($results_last_order_date[$j]['last_order_date'])) ? $results_last_order_date[$j]['last_order_date'] : ''; 
                }

                // $abandoned_rate = ($results_top_abandoned_product['abondoned_qty']/$results_prod_abandoned_rate[$j]['abondoned_rate'])*100;
                $abandoned_rate = (!empty($results_last_order_date[$j]['tot_qty_sold']) && !empty($results_top_abandoned_product['abondoned_qty'])) ? ($results_top_abandoned_product['abondoned_qty']/($results_last_order_date[$j]['tot_qty_sold'] + $results_top_abandoned_product['abondoned_qty']))*100 : '';
                $results_top_abandoned_product['abandoned_rate'] = sr_number_format($abandoned_rate ,$sr_decimal_places) . "%";

                //Code for formatting the product name

                $post_parent = wp_get_post_parent_id($results_top_abandoned_product['id']);

                if ($post_parent  > 0) {
                	$results_top_abandoned_product ['prod_name'] = get_the_title($post_parent) . " (". $variation_attributes[$results_top_abandoned_product['id']] .")";

                } else {
                	$results_top_abandoned_product ['prod_name'] = get_the_title($results_top_abandoned_product['id']);
                }

                $results_top_abandoned_product ['prod_name'] = sanitize_title($results_top_abandoned_product ['prod_name']);

                $total_prod_abandoned_qty = $total_prod_abandoned_qty + $results_top_abandoned_product['abondoned_qty'];
                $total_prod_qty = $total_prod_qty + $results_prod_abandoned_rate[$j]['abondoned_rate'];

                $j++;

	    	}

	    }

	    return json_encode($results_top_abandoned_products);

	}

	//Function for formatting Graph data

	function sr_graph_data_formatting(&$graph_data,&$results,&$group_by,$index_flag,$graph_data_index_sales,$graph_data_index_count,$results_index_sales,$results_index_count ) {

		if (empty($results) || empty($index_flag) || empty($graph_data_index_sales) || empty($graph_data_index_count) || empty($results_index_sales) || empty($results_index_count))
			return;

		// $graph_data = array();

		for ($i=0, $j=0, $k=0; $i<sizeof($results);$i++) {

		            if ($i>0) {

		                if ($results [$i][$index_flag] == $flag) {
		                    $j++;

		                    $graph_data [$k][$j][$graph_data_index_sales] = $results [$i][$results_index_sales];
		                    $graph_data [$k][$j][$graph_data_index_count] = $results [$i][$results_index_count];
		                    $graph_data [$k][$j][$group_by] = $results [$i][$group_by];    

		                    if($group_by == "display_date_time") {
		                        $graph_data [$k][$j]['display_time'] = $results [$i]['display_time'];
		                        $graph_data [$k][$j]['comp_time'] = $results [$i]['comp_time'];
		                    } 

		                    $flag = $results [$i][$index_flag];


		                }
		                else {

		                    $k++;
		                    $j=0;
		                    $graph_data [$k] = array();

		                    $graph_data [$k][$j][$graph_data_index_sales] = $results [$i][$results_index_sales];
		                    $graph_data [$k][$j][$graph_data_index_count] = $results [$i][$results_index_count];
		                    $graph_data [$k][$j][$group_by] = $results [$i][$group_by];
		                    if($group_by == "display_date_time") {
		                        $graph_data [$k][$j]['display_time'] = $results [$i]['display_time'];
		                        $graph_data [$k][$j]['comp_time'] = $results [$i]['comp_time'];
		                    }

		                    $flag = $results [$i][$index_flag];
		                }
		            }
		            else {

		                $graph_data [$k] = array();
		                $graph_data [$k][$j][$graph_data_index_sales] = $results [$i][$results_index_sales];
		                $graph_data [$k][$j][$graph_data_index_count] = $results [$i][$results_index_count];
		                $graph_data [$k][$j][$group_by] = $results [$i][$group_by];
		                if($group_by == "display_date_time") {
		                    $graph_data [$k][$j]['display_time'] = $results [$i]['display_time'];
		                    $graph_data [$k][$j]['comp_time'] = $results [$i]['comp_time'];
		                }
		                
		                $flag = $results [$i][$index_flag];
		            }
		        }
	        return $graph_data;
	}

	//Cummulative sales Query function
	function sr_query_sales($start_date,$end_date_query,$date_series,$select,$group_by,$select_top_prod,$select_top_abandoned_prod,$terms_taxonomy_ids,$post) {

	    global $wpdb;

	    $monthly_sales = array();
	    $cumm_top_prod_graph_data = array();
	    $results_top_prod = array();
	    $top_prod_ids = array();
	    $top_prod_graph_data = array();
	    $top_gateway_graph_data = array();
	    $top_shipping_method_graph_data = array();

	    $sr_currency_symbol = isset($post['SR_CURRENCY_SYMBOL']) ? $post['SR_CURRENCY_SYMBOL'] : '';
	    $sr_decimal_places = isset($post['SR_DECIMAL_PLACES']) ? $post['SR_DECIMAL_PLACES'] : '';

	    
	    // $terms_postmeta_cond = (!empty($terms_post)) ? 'AND post_id IN ('.$terms_post.')' : '';

	    $sr_is_woo22 = (!empty($post['SR_IS_WOO22'])) ? $post['SR_IS_WOO22'] : '';

	    if (!empty($sr_is_woo22) && $sr_is_woo22 == 'true') {
	    	$terms_post_join = '';
	    	$terms_post_cond = "AND posts.post_status IN ('wc-completed','wc-processing','wc-on-hold')";
	    } else {
	    	$terms_post_join = (!empty($terms_taxonomy_ids)) ? ' JOIN '.$wpdb->prefix.'term_relationships AS term_relationships ON (term_relationships.object_id = posts.ID AND posts.post_status = "publish") ' : '';
        	$terms_post_cond = (!empty($terms_taxonomy_ids) && !empty($terms_post_join)) ? 'AND term_relationships.term_taxonomy_id IN ('.$terms_taxonomy_ids.')' : '';
	    }
	    

	    //Query for getting the cumm sales

	    $query_monthly_sales = "SELECT SUM( postmeta.meta_value ) AS todays_sales,
	    						COUNT(posts.ID) AS total_orders,
	    						$select
		                        FROM `{$wpdb->prefix}postmeta` AS postmeta
		                        LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = postmeta.post_id )
		                        $terms_post_join
		                        WHERE postmeta.meta_key IN ('_order_total')
		                            AND posts.post_date BETWEEN '$start_date' AND '$end_date_query'
		                            $terms_post_cond
	                            GROUP BY $group_by";
        $results_monthly_sales    = $wpdb->get_results ( $query_monthly_sales, 'ARRAY_A' );
	    $rows_monthly_sales 	  =  $wpdb->num_rows;

	    //Query for Top 5 Customers

	    $index = 0;

	    //Reg Customers
	    $query_reg_cumm = "SELECT ID FROM `$wpdb->users` 
	                        WHERE user_registered BETWEEN '$start_date' AND '$end_date_query'";
	    $reg_cumm_ids   = $wpdb->get_col ( $query_reg_cumm );
	    $rows_reg_cumm_ids =  $wpdb->num_rows;


	    $query_cumm_top_cust_guest ="SELECT postmeta1.meta_value AS billing_email,
	                                GROUP_CONCAT(DISTINCT postmeta2.post_id
                                                     ORDER BY postmeta2.meta_id DESC SEPARATOR ',' ) AS post_id,
	                                MAX(postmeta2.post_id) AS post_id_max,
	                                SUM(postmeta2.meta_value) as total
	                            
	                                FROM {$wpdb->prefix}postmeta AS postmeta1
	                                    JOIN {$wpdb->prefix}posts AS posts ON (posts.ID = postmeta1.post_id)
	                                    INNER JOIN {$wpdb->prefix}postmeta AS postmeta2
	                                       ON (postmeta2.post_ID = postmeta1.post_ID AND postmeta2.meta_key IN ('_order_total'))
	                                    $terms_post_join
	                                WHERE postmeta1.meta_key IN ('_billing_email')
	                                    AND posts.post_date BETWEEN '$start_date' AND '$end_date_query'
	                                    AND posts.id IN (SELECT post_id FROM {$wpdb->prefix}postmeta
	                                                        WHERE meta_key IN ('_customer_user')
	                                                            AND meta_value = 0)
										$terms_post_cond
	                                GROUP BY postmeta1.meta_value
	                                ORDER BY total DESC
	                                LIMIT 5";

	    $results_cumm_top_cust_guest   =  $wpdb->get_results ( $query_cumm_top_cust_guest, 'ARRAY_A' );    
	    $rows_cumm_top_cust_guest    =  $wpdb->num_rows;

	    $results_cumm_top_cust = array();

	    if($rows_cumm_top_cust_guest > 0) {

	        $post_id_max = array();
	        
	        foreach ($results_cumm_top_cust_guest as $results_cumm_top_cust_guest1) {
	            $post_id_max [] = $results_cumm_top_cust_guest1 ['post_id_max'];
	        }

	        $post_id_imploded = implode(",",$post_id_max);

	        $query_cumm_top_cust_guest_detail = "SELECT postmeta.post_id as post_id,
	                                                GROUP_CONCAT(postmeta.meta_key
	                                                             ORDER BY postmeta.meta_id DESC SEPARATOR '###' ) AS meta_key,
	                                                GROUP_CONCAT(postmeta.meta_value
	                                                             ORDER BY postmeta.meta_id DESC SEPARATOR '###' ) AS meta_value
	                                            FROM {$wpdb->prefix}postmeta AS postmeta
	                                            WHERE postmeta.post_id IN ($post_id_imploded)
	                                                AND postmeta.meta_key IN ('_billing_first_name' , '_billing_last_name')
	                                            GROUP BY postmeta.post_id";

	        $results_cumm_top_cust_guest_detail   =  $wpdb->get_results ( $query_cumm_top_cust_guest_detail, 'ARRAY_A' );    
	        $results_cumm_top_cust_guest_detail_rows = $wpdb->num_rows;

	        $top_cust_guest_detail = array();

	        if ($results_cumm_top_cust_guest_detail_rows > 0) {
	        	foreach ( $results_cumm_top_cust_guest_detail as $cumm_top_cust_guest_detail ) {
	        		$guest_meta_values = explode('###', $cumm_top_cust_guest_detail['meta_value']);
		            $guest_meta_key = explode('###', $cumm_top_cust_guest_detail['meta_key']);
		            if (count($guest_meta_values) != count($guest_meta_key))
		                continue;
		            unset($cumm_top_cust_guest_detail['meta_value']);
		            unset($cumm_top_cust_guest_detail['meta_key']);
		            $guest_meta_key_values = array_combine($guest_meta_key, $guest_meta_values);

	        		$top_cust_guest_detail [$cumm_top_cust_guest_detail['post_id']] = $guest_meta_key_values['_billing_first_name'] . " " . $guest_meta_key_values['_billing_last_name'];
	        	}
	        }

	        foreach ($results_cumm_top_cust_guest as $cumm_top_cust_guest) {

	            $results_cumm_top_cust[$index] = array();

	            $post_id = $cumm_top_cust_guest['post_id_max'];

	            $results_cumm_top_cust [$index]['total'] = $sr_currency_symbol . sr_number_format($cumm_top_cust_guest['total'],$sr_decimal_places);
	            $results_cumm_top_cust [$index]['calc_total'] = floatval($cumm_top_cust_guest['total']); // value used only for sorting purpose
	            $results_cumm_top_cust [$index]['name'] = ( !empty($top_cust_guest_detail[$post_id]) ) ? $top_cust_guest_detail[$post_id] : '-';
	            $results_cumm_top_cust [$index]['billing_email'] = $cumm_top_cust_guest['billing_email'];
	            $results_cumm_top_cust [$index]['post_ids'] = json_encode($cumm_top_cust_guest['post_id']);

	            $index++;
	        }
	    }

	    $query_cumm_top_cust_reg ="SELECT postmeta1.meta_value AS user_id,
	                                GROUP_CONCAT(DISTINCT postmeta1.post_id
	                                                             ORDER BY postmeta1.meta_id DESC SEPARATOR ',' ) AS post_id,
	                               SUM(postmeta2.meta_value) as total
	                            
	                                FROM {$wpdb->prefix}postmeta AS postmeta1
	                                    JOIN {$wpdb->prefix}posts AS posts ON (posts.ID = postmeta1.post_id)
	                                    INNER JOIN {$wpdb->prefix}postmeta AS postmeta2
	                                       ON (postmeta2.post_ID = postmeta1.post_ID AND postmeta2.meta_key IN ('_order_total'))
	                                    $terms_post_join
	                                WHERE postmeta1.meta_key IN ('_customer_user')
	                                    AND posts.post_date BETWEEN '$start_date' AND '$end_date_query'
	                                    AND posts.id IN (SELECT post_id FROM {$wpdb->prefix}postmeta
	                                                        WHERE meta_key IN ('_customer_user')
	                                                            AND meta_value > 0)
										$terms_post_cond
	                                GROUP BY postmeta1.meta_value
	                                ORDER BY total DESC
	                                LIMIT 5";

	    $results_cumm_top_cust_reg   =  $wpdb->get_results ( $query_cumm_top_cust_reg, 'ARRAY_A' );    
	    $rows_cumm_top_cust_reg    =  $wpdb->num_rows;

	    $user_id = array();

	    if ($rows_cumm_top_cust_reg > 0) {

	        foreach ($results_cumm_top_cust_reg as $results_cumm_top_cust_reg1) {
	            $user_id[] = $results_cumm_top_cust_reg1 ['user_id'];
	        }

	        if(!empty($user_id)) {
	        	$user_ids_imploded = implode(",",$user_id);	
	        }
	        
	        $query_reg_details = "SELECT users.ID as cust_id,
	                                users.user_email as email,
	                                GROUP_CONCAT(usermeta.meta_key
	                                             ORDER BY usermeta.umeta_id DESC SEPARATOR '###' ) AS meta_key,
	                                GROUP_CONCAT(usermeta.meta_value
	                                             ORDER BY usermeta.umeta_id DESC SEPARATOR '###' ) AS meta_value
	                              FROM $wpdb->users as users
	                                    JOIN $wpdb->usermeta as usermeta ON (users.ID = usermeta.user_id)
	                              WHERE users.ID IN ($user_ids_imploded)
	                                    AND usermeta.meta_key IN ('first_name','last_name')
	                              GROUP BY users.ID";

	        $results_reg_details =  $wpdb->get_results ( $query_reg_details, 'ARRAY_A' );
	        $results_reg_details_rows =  $wpdb->num_rows;

	        $reg_cust_details = array();

	        if ($results_reg_details_rows > 0) {
	        	foreach ( $results_reg_details as $result_reg_cust ) {

	        		$reg_meta_values = explode('###', $result_reg_cust['meta_value']);
		            $reg_meta_key = explode('###', $result_reg_cust['meta_key']);
		            if (count($reg_meta_values) != count($reg_meta_key))
		                continue;
		            unset($result_reg_cust['meta_value']);
		            unset($result_reg_cust['meta_key']);
		            $reg_meta_key_values = array_combine($reg_meta_key, $reg_meta_values);

	        		$reg_cust_details [$result_reg_cust['cust_id']] = array();
	        		$reg_cust_details [$result_reg_cust['cust_id']] ['name'] = $reg_meta_key_values['first_name'] . " " . $reg_meta_key_values['last_name'];
	        		$reg_cust_details [$result_reg_cust['cust_id']] ['email'] = $result_reg_cust['email'];
	        	}
	        }

	        foreach ($results_cumm_top_cust_reg as $result_cumm_top_cust_reg) {

	            $results_cumm_top_cust[$index] = array();
	            $user_id = $result_cumm_top_cust_reg['user_id'];

	            $results_cumm_top_cust [$index]['total'] = $sr_currency_symbol . sr_number_format($result_cumm_top_cust_reg['total'],$sr_decimal_places);
	            $results_cumm_top_cust [$index]['calc_total'] = floatval($result_cumm_top_cust_reg['total']); // value used only for sorting purpose
	            $results_cumm_top_cust [$index]['name'] = ( !empty($reg_cust_details[$user_id]) ) ? $reg_cust_details[$user_id]['name'] : '-';
	            $results_cumm_top_cust [$index]['billing_email'] = ( !empty($reg_cust_details[$user_id]) ) ? $reg_cust_details[$user_id]['email'] : '-';
	            $results_cumm_top_cust [$index]['post_ids'] = json_encode($result_cumm_top_cust_reg['post_id']);

	            $index++;
	        }

	    }

	    if(!empty($results_cumm_top_cust)) {
	        usort($results_cumm_top_cust, 'usort_callback');
	        $results_cumm_top_cust = array_slice($results_cumm_top_cust, 0, 5);    
	    }
	    else {
	        $results_cumm_top_cust = "";
	    }

	    //Top 5 Products

	    //Query to get the Top 5 Products

	    $query_top_prod      		= "SELECT order_item.product_id as product_id,
	                                    order_item.product_name as product_name,
	                                    SUM( order_item.sales ) AS product_sales ,
	                                    SUM( order_item.quantity ) AS product_qty
	                                    FROM `{$wpdb->prefix}sr_woo_order_items` AS order_item
	                                        LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = order_item.order_id )
	                                        $terms_post_join
	                                    WHERE posts.post_date BETWEEN '$start_date' AND '$end_date_query'
	                                        $terms_post_cond
	                                    GROUP BY order_item.product_id
	                                    ORDER BY product_sales DESC
	                                    LIMIT 5";
	    $results_top_prod    		= $wpdb->get_results ( $query_top_prod, 'ARRAY_A' );
	    $rows_top_prod   		  	= $wpdb->num_rows;

	    if($rows_top_prod > 0) {
	        foreach (array_keys($results_top_prod) as $results_top_prod1) {
	            $top_prod_ids[] = $results_top_prod [$results_top_prod1]['product_id'];
	        
	            if (isset($post['top_prod_option'])) {
                    $results_top_prod [$results_top_prod1]['product_sales_display'] = $sr_currency_symbol . sr_number_format($results_top_prod [$results_top_prod1]['product_sales'],$sr_decimal_places);
	            }

	        }

	        if (!empty($top_prod_ids)) {
	        	$top_prod_ids1 = implode(",", $top_prod_ids);	
	        }
	        

	        //Query to get the Top 5 Products graph related data

	        $query_top_prod_graph   = "SELECT order_item.product_id as product_id,
	                                        SUM( order_item.sales ) AS product_sales,
	                                        SUM( order_item.quantity ) AS product_qty,
	                                        $select
	                                    FROM `{$wpdb->prefix}sr_woo_order_items` AS order_item
	                                        LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = order_item.order_id )
	                                    WHERE posts.post_date BETWEEN '$start_date' AND '$end_date_query'
	                                        AND order_item.product_id IN ($top_prod_ids1)
	                                    GROUP BY order_item.product_id,$group_by
	                                    ORDER BY FIND_IN_SET(order_item.product_id,'$top_prod_ids1')";

	        $results_top_prod_graph = $wpdb->get_results ( $query_top_prod_graph, 'ARRAY_A' );
	        $rows_top_prod_graph	= $wpdb->num_rows;

	        if($rows_top_prod_graph > 0) {
	        	foreach ($results_top_prod_graph as $results_top_prod_graph1) {
		            $top_prod_graph_temp [] = $results_top_prod_graph1['product_id'];
		        }

				//call function for graph data formatting
		        sr_graph_data_formatting($top_prod_graph_data,$results_top_prod_graph,$group_by,'product_id','product_sales','product_qty','product_sales','product_qty' );
		    }

	    }


	    $monthly_sales_temp = $date_series;
	    $max_sales = 0;
	    $total_monthly_sales = 0;
	    $tot_cumm_orders = 0;
	    $tot_cumm_orders_qty = 0;
	    $total_orders = 0;

	    if ($rows_monthly_sales > 0) {
	        foreach ( $results_monthly_sales as $results_monthly_sale ) {
	            if($group_by == "display_date_time") {
	                    $monthly_sales_temp[$results_monthly_sale['comp_time']]['post_date'] = date ("Y-m-d", strtotime($start_date)) .' '. $results_monthly_sale['display_time'];
	                    $monthly_sales_temp[$results_monthly_sale['comp_time']]['sales'] = floatval($results_monthly_sale['todays_sales']); 
	            }
	            else {
	                $monthly_sales_temp[$results_monthly_sale[$group_by]]['sales'] = floatval($results_monthly_sale['todays_sales']); 
	            }

	            if ($max_sales < $results_monthly_sale['todays_sales']) {
	                $max_sales = $results_monthly_sale['todays_sales'];
	            }

	            $total_monthly_sales = $total_monthly_sales + $results_monthly_sale['todays_sales'];
	            $total_orders = $total_orders + $results_monthly_sale['total_orders'];
	        }

	        foreach ( $monthly_sales_temp as $monthly_sales_temp1 ) {
	            $monthly_sales[] = $monthly_sales_temp1;
	        }
	    }

	    //Top 5 Products Graph

	    $cumm_top_prod_graph_data = array();

	    $index = 0;
	    $max_values = array();

	    if(!empty($top_prod_graph_data)) {
	        foreach ( $top_prod_graph_data as $results_top_prod_graph1 ) {
	            $cumm_top_prod_graph_data[$index] = array();
	            $temp = array();
	            $cumm_date = $date_series;

	            $max=0;

	            for ( $j=0;$j<sizeof($results_top_prod_graph1);$j++ ) {

	                if($group_by == "display_date_time") {
	                    $cumm_date[$results_top_prod_graph1[$j]['comp_time']]['post_date'] = date ("Y-m-d", strtotime($start_date)) .' '. $results_top_prod_graph1[$j]['display_time'];
	                }


	                if (isset($post['top_prod_option'])) {
	                    if ($post['top_prod_option'] == 'sr_opt_top_prod_price') {

	                        if($results_top_prod_graph1[$j]['product_sales'] > $max) {
	                            $max = floatval($results_top_prod_graph1[$j]['product_sales']);
	                        }

	                        if($group_by == "display_date_time") {
	                            $cumm_date[$results_top_prod_graph1[$j]['comp_time']]['sales'] = floatval($results_top_prod_graph1[$j]['product_sales']);
	                        }
	                        else {
	                            $cumm_date[$results_top_prod_graph1[$j][$group_by]]['sales'] = floatval($results_top_prod_graph1[$j]['product_sales']);    
	                        }
	                        
	                    }
	                    else if($post['top_prod_option'] == 'sr_opt_top_prod_qty') {
	                        
	                        if($results_top_prod_graph1[$j]['product_qty'] > $max) {
	                            $max = intval($results_top_prod_graph1[$j]['product_qty']);
	                        }

	                        if($group_by == "display_date_time") {
	                            $cumm_date[$results_top_prod_graph1[$j]['comp_time']]['sales'] = intval($results_top_prod_graph1[$j]['product_qty']);
	                        }
	                        else {
	                            $cumm_date[$results_top_prod_graph1[$j][$group_by]]['sales'] = intval($results_top_prod_graph1[$j]['product_qty']);
	                        }
	                        
	                    }
	                }
	                else {

	                    if($results_top_prod_graph1[$j]['product_sales'] > $max) {
	                        $max = floatval($results_top_prod_graph1[$j]['product_sales']);
	                    }

	                    $cumm_date[$results_top_prod_graph1[$j][$group_by]]['sales'] = floatval($results_top_prod_graph1[$j]['product_sales']);
	                }

	                $product_sales_display = $results_top_prod_graph1[$j]['product_sales'];

	            }

	            foreach ($cumm_date as $cumm_date1) {
	                $temp [] = $cumm_date1;
	            }



	            if (isset($post['option'])) { // Condition to handle the change of graph on option select
	                $cumm_top_prod_graph_data[$index]['graph_data'] = $temp;
	                $cumm_top_prod_graph_data[$index]['max_value'] = $max;
	            }
	            else {
	                $results_top_prod[$index]['graph_data'] = $temp;    
	                $results_top_prod[$index]['max_value'] = $max;
	            }
	            $index++;
	        }    
	    }

	    //Query for Avg. Items Per Customer

	    $query_cumm_reg_cust_count 	="SELECT COUNT(DISTINCT postmeta.meta_value) AS cust_orders
		                                FROM {$wpdb->prefix}postmeta AS postmeta
		                                    JOIN {$wpdb->prefix}posts AS posts ON (posts.ID = postmeta.post_id)
		                                    $terms_post_join
		                                WHERE postmeta.meta_key IN ('_customer_user')
		                                    AND postmeta.meta_value > 0
		                                    AND posts.post_date BETWEEN '$start_date' AND '$end_date_query'
		                                    $terms_post_cond";

	    $results_cumm_reg_cust_count = $wpdb->get_col ( $query_cumm_reg_cust_count );
	    $rows_cumm_reg_cust_count	 = $wpdb->num_rows;

	    if($rows_cumm_reg_cust_count > 0) {
	        $reg_cust_count = $results_cumm_reg_cust_count[0];
	    }
	    else {
	        $reg_cust_count = 0;
	    }

	    $query_cumm_guest_cust_count 	="SELECT COUNT(DISTINCT postmeta1.meta_value) AS cust_orders
		                                    FROM {$wpdb->prefix}postmeta AS postmeta1
		                                        JOIN {$wpdb->prefix}posts AS posts ON (posts.ID = postmeta1.post_id)
		                                        INNER JOIN {$wpdb->prefix}postmeta AS postmeta2 
		                                            ON (postmeta2.post_ID = postmeta1.post_ID AND postmeta2.meta_key IN ('_customer_user'))
	                                            $terms_post_join
		                                    WHERE postmeta1.meta_key IN ('_billing_email')
		                                        AND postmeta2.meta_value = 0
		                                        AND posts.post_date BETWEEN '$start_date' AND '$end_date_query'
		                                        $terms_post_cond";

	    $results_cumm_guest_cust_count 	=  $wpdb->get_col ( $query_cumm_guest_cust_count );
	    $rows_cumm_guest_cust_count	 	= $wpdb->num_rows;

	    if($rows_cumm_guest_cust_count > 0) {
	        $guest_cust_count = $results_cumm_guest_cust_count[0];
	    }
	    else {
	        $guest_cust_count = 0;
	    }

	    $total_cumm_cust_count = $reg_cust_count + $guest_cust_count;


	    //Query for Avg. Order Total and Avg. Order Items

	    $query_cumm_avg_order_tot_items      = "SELECT COUNT(DISTINCT order_item.order_id) as no_orders,
				                                    SUM( order_item.quantity ) AS cumm_quantity
			                                    FROM `{$wpdb->prefix}sr_woo_order_items` AS order_item
			                                        LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = order_item.order_id )
			                                        $terms_post_join
			                                    WHERE posts.post_date BETWEEN '$start_date' AND '$end_date_query'
			                                    	$terms_post_cond";
	    $results_cumm_avg_order_tot_items    = $wpdb->get_results ( $query_cumm_avg_order_tot_items, 'ARRAY_A' );
	    $rows_cumm_avg_order_tot_items 	     = $wpdb->num_rows;

	    if ($rows_cumm_avg_order_tot_items > 0) {
			$tot_cumm_orders 	 = $results_cumm_avg_order_tot_items [0]['no_orders'];
			$tot_cumm_orders_qty = $results_cumm_avg_order_tot_items [0]['cumm_quantity'];	    	
	    }
	    else {
	    	$tot_cumm_orders 	 = 0;
			$tot_cumm_orders_qty = 0;
	    }

	    //Total Discount Sales Widget 

	    $query_cumm_discount_sales = "SELECT SUM( postmeta.meta_value ) AS discount_sales,
	    						$select
		                        FROM `{$wpdb->prefix}postmeta` AS postmeta
		                        	LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = postmeta.post_id )
		                        	$terms_post_join
		                        WHERE postmeta.meta_key IN ('_order_discount','_cart_discount')
		                            AND posts.post_date BETWEEN '$start_date' AND '$end_date_query'
		                            $terms_post_cond
	                            GROUP BY $group_by";
        $results_cumm_discount_sales    = $wpdb->get_results ( $query_cumm_discount_sales, 'ARRAY_A' );
	    $rows_cumm_discount_sales 	  =  $wpdb->num_rows;



	    $cumm_discount_sales_temp = $date_series;
	    $cumm_discount_sales = array();
	    $max_discount_total = 0;
	    $total_discount_sales = 0;

	    if ($rows_cumm_discount_sales > 0) {
	        foreach ( $results_cumm_discount_sales as $results_cumm_discount_sale ) {
	            if($group_by == "display_date_time") {
	                    $cumm_discount_sales_temp[$results_cumm_discount_sale['comp_time']]['post_date'] = date ("Y-m-d", strtotime($start_date)) .' '. $results_cumm_discount_sale['display_time'];
	                    $cumm_discount_sales_temp[$results_cumm_discount_sale['comp_time']]['sales'] = floatval($results_cumm_discount_sale['discount_sales']); 
	            }
	            else {
	                $cumm_discount_sales_temp[$results_cumm_discount_sale[$group_by]]['sales'] = floatval($results_cumm_discount_sale['discount_sales']); 
	            }

	            if ($max_discount_total < $results_cumm_discount_sale['discount_sales']) {
	                $max_discount_total = $results_cumm_discount_sale['discount_sales'];
	            }

	            $total_discount_sales = $total_discount_sales + $results_cumm_discount_sale['discount_sales'];
	        }

	        foreach ( $cumm_discount_sales_temp as $cumm_discount_sales_temp1 ) {
	            $cumm_discount_sales[] = $cumm_discount_sales_temp1;
	        }
	    }


	    //Top Coupons Widget

	    $query_cumm_coupon_count = "SELECT COUNT( order_items.order_item_name ) AS coupon_count,
	    							SUM(order_itemmeta.meta_value) AS coupon_amount,
	    							order_items.order_item_name AS coupon_name,
	    							GROUP_CONCAT(DISTINCT order_items.order_id
	                                                             ORDER BY order_items.order_item_id DESC SEPARATOR ',' ) AS order_ids
		                        FROM `{$wpdb->prefix}posts` AS posts
		                        	JOIN {$wpdb->prefix}woocommerce_order_items as order_items ON ( posts.ID = order_items.order_id )
		                        	JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_itemmeta 
		                        		ON (order_items.order_item_id = order_itemmeta.order_item_id 
		                        				AND order_itemmeta.meta_key IN ('discount_amount') )
									$terms_post_join
		                        WHERE posts.post_date BETWEEN '$start_date' AND '$end_date_query'
		                            $terms_post_cond
		                            AND order_items.order_item_type IN ('coupon')
	                            GROUP BY order_items.order_item_name
	                            ORDER BY coupon_count DESC, coupon_amount DESC
	                            LIMIT 5";

        $results_cumm_coupon_count    = $wpdb->get_results ( $query_cumm_coupon_count, 'ARRAY_A' );
	    $rows_cumm_coupon_count	  =  $wpdb->num_rows;

	    foreach ($results_cumm_coupon_count as &$results_cumm_coupon_count1) {
	    	$results_cumm_coupon_count1['coupon_amount'] = $sr_currency_symbol . sr_number_format($results_cumm_coupon_count1['coupon_amount'],$sr_decimal_places);
	    	$results_cumm_coupon_count1['coupon_count'] = sr_number_format($results_cumm_coupon_count1['coupon_count'],$sr_decimal_places);
	    }

	    // % Orders Containing Coupons

	    $sr_per_order_containing_coupons = '';

	    $query_cumm_orders_coupon_count 	= "SELECT COUNT( posts.ID ) AS total_coupon_orders
		    									FROM `{$wpdb->prefix}posts` AS posts
			                        				JOIN {$wpdb->prefix}woocommerce_order_items as order_items ON ( posts.ID = order_items.order_id )
			                        				$terms_post_join
			                        			WHERE posts.post_date BETWEEN '$start_date' AND '$end_date_query'
						                            $terms_post_cond
					                            	AND order_items.order_item_type IN ('coupon')";
		$results_cumm_orders_coupon_count 	= $wpdb->get_col ( $query_cumm_orders_coupon_count );
	    $rows_cumm_orders_coupon_count	  	= $wpdb->num_rows;		

	    if ($rows_cumm_orders_coupon_count > 0 && $total_orders > 0) {
	    	$sr_per_order_containing_coupons = ($results_cumm_orders_coupon_count[0] / $total_orders) * 100 ;	
	    }


	    //Orders By Payment Gateways

	    $query_top_payment_gateway = "SELECT postmeta1.meta_value AS payment_method,
		    							SUM(postmeta2.meta_value) AS sales_total,
		    							COUNT(posts.ID) AS sales_count,
		    							GROUP_CONCAT(posts.ID ORDER BY posts.ID DESC SEPARATOR ',' ) AS order_ids
				                        FROM {$wpdb->prefix}posts AS posts 
					                        LEFT JOIN `{$wpdb->prefix}postmeta` AS postmeta1 ON ( posts.ID = postmeta1.post_id )
					                        LEFT JOIN `{$wpdb->prefix}postmeta` AS postmeta2 ON ( posts.ID = postmeta2.post_id )
					                        $terms_post_join
				                        WHERE postmeta1.meta_key IN ('_payment_method')
				                        	AND postmeta2.meta_key IN ('_order_total')
				                            AND posts.post_date BETWEEN '$start_date' AND '$end_date_query'
				                            $terms_post_cond
			                            GROUP BY payment_method
			                            ORDER BY sales_total DESC
			                            LIMIT 5";
        $results_top_payment_gateway  = $wpdb->get_results ( $query_top_payment_gateway, 'ARRAY_A' );
	    $rows_top_payment_gateway 	  =  $wpdb->num_rows;

	    if ($rows_top_payment_gateway > 0) {
	    	foreach ($results_top_payment_gateway as &$results_top_payment_gateway1) {
	            $top_payment_gateway[] = $results_top_payment_gateway1 ['payment_method'];
	        
	            if (isset($post['top_prod_option'])) {
                    $results_top_payment_gateway1 ['gateway_sales_display'] = (!empty($results_top_payment_gateway1 ['sales_total'])) ? $sr_currency_symbol . sr_number_format($results_top_payment_gateway1 ['sales_total'],$sr_decimal_places) : $sr_currency_symbol . '0';
                    $results_top_payment_gateway1 ['gateway_sales_percent'] = sr_number_format((($results_top_payment_gateway1 ['sales_total'] / $total_monthly_sales) * 100),$sr_decimal_places) . '%';
	            }

	        }

	        if (!empty($top_payment_gateway)) {
	        	$top_payment_gateway_imploded = "'".implode("','", $top_payment_gateway)."'";
	        	$top_payment_gateway_cond = 'AND postmeta1.meta_value IN ('.$top_payment_gateway_imploded.')';
	        	// $top_payment_gateway_order_by = 'ORDER BY FIND_IN_SET(postmeta1.meta_value,\'".implode(",",$top_payment_gateway)."\')';
	        	$top_payment_gateway_order_by = "ORDER BY FIND_IN_SET(postmeta1.meta_value,'".implode(",", $top_payment_gateway)."')";

	        }
	    } else {
	    	$top_payment_gateway_cond = '';
	    	$top_payment_gateway_order_by = '';
	    }

        //Query to get the Top 5 Products graph related data

        $query_top_gateways_graph   = "SELECT postmeta1.meta_value AS payment_method,
	    							SUM(postmeta2.meta_value) AS sales_total,
	    							COUNT(posts.ID) AS sales_count,
	    							$select
			                        FROM {$wpdb->prefix}posts AS posts 
				                        LEFT JOIN `{$wpdb->prefix}postmeta` AS postmeta1 ON ( posts.ID = postmeta1.post_id )
				                        LEFT JOIN `{$wpdb->prefix}postmeta` AS postmeta2 ON ( posts.ID = postmeta2.post_id )
				                        $terms_post_join
			                        WHERE postmeta1.meta_key IN ('_payment_method')
			                        	AND postmeta2.meta_key IN ('_order_total')
			                            AND posts.post_date BETWEEN '$start_date' AND '$end_date_query'
			                            $terms_post_cond
			                            $top_payment_gateway_cond
		                            GROUP BY payment_method, $group_by
		                            $top_payment_gateway_order_by";

        $results_top_gateways_graph = $wpdb->get_results ( $query_top_gateways_graph, 'ARRAY_A' );
        $rows_top_gateways_graph	= $wpdb->num_rows;

	    $cumm_payment_gateway_temp = $date_series;
	    $cumm_payment_gateway_sales = array();

		if($rows_top_gateways_graph > 0) {

			//call function for graph data formatting
	        sr_graph_data_formatting($top_gateway_graph_data,$results_top_gateways_graph,$group_by,'payment_method','gateway_sales_amt','gateway_sales_count','sales_total','sales_count' );

        }

        //Query to get the Payment Gateway Title

        $query_gateway_title = "SELECT DISTINCT postmeta2.meta_value as gateway_title,
        							postmeta1.meta_value as gateway_method
	    						FROM `{$wpdb->prefix}postmeta` AS postmeta2
	    							JOIN `{$wpdb->prefix}postmeta` AS postmeta1 ON ( postmeta2.post_id = postmeta1.post_id )
    							WHERE postmeta2.meta_key IN ('_payment_method_title')
    								AND postmeta1.meta_key IN ('_payment_method')
    								$top_payment_gateway_cond
    							$top_payment_gateway_order_by";
    	$result_gateway_title = $wpdb->get_results ( $query_gateway_title, 'ARRAY_A' );

    	$gateway_title = array();

    	foreach($result_gateway_title as $result_gateway_title1) {
    		$gateway_title[strtolower($result_gateway_title1['gateway_method'])] = $result_gateway_title1['gateway_title'];
    	}

        //Top 5 Products Graph

	    $cumm_top_gateway_graph_data = array();

	    $index = 0;
	    $max_values = array();

	    if(!empty($top_gateway_graph_data)) {
	        foreach ( $top_gateway_graph_data as $top_gateway_graph_data1 ) {
	            $cumm_top_gateway_amt_graph_data[$index] = array();
	            $temp_gateway_sales_amt = array();
	            $temp_gateway_sales_count = array();
	            $cumm_date_amt = $date_series;
	            $cumm_date_count = $date_series;

	            $max_amt=0;
	            $max_count=0;

	            for ( $j=0;$j<sizeof($top_gateway_graph_data1);$j++ ) {

	                if($group_by == "display_date_time") {
	                    $cumm_date_amt[$top_gateway_graph_data1[$j]['comp_time']]['post_date'] = date ("Y-m-d", strtotime($start_date)) .' '. $top_gateway_graph_data1[$j]['display_time'];
	                    $cumm_date_count[$top_gateway_graph_data1[$j]['comp_time']]['post_date'] = date ("Y-m-d", strtotime($start_date)) .' '. $top_gateway_graph_data1[$j]['display_time'];

	                    $cumm_date_amt[$top_gateway_graph_data1[$j]['comp_time']]['sales'] = floatval($top_gateway_graph_data1[$j]['gateway_sales_amt']);
	                	$cumm_date_count[$top_gateway_graph_data1[$j]['comp_time']]['sales'] = floatval($top_gateway_graph_data1[$j]['gateway_sales_count']);
	                }
	                else {
	                	$cumm_date_amt[$top_gateway_graph_data1[$j][$group_by]]['sales'] = floatval($top_gateway_graph_data1[$j]['gateway_sales_amt']);
	                	$cumm_date_count[$top_gateway_graph_data1[$j][$group_by]]['sales'] = floatval($top_gateway_graph_data1[$j]['gateway_sales_count']);
	                }

	                //Payment Gateways Sales Amt

                    if($top_gateway_graph_data1[$j]['gateway_sales_amt'] > $max_amt) {
                        $max_amt = floatval($top_gateway_graph_data1[$j]['gateway_sales_amt']);
                    }

                   //Payment Gateways Sales Count

                    if($top_gateway_graph_data1[$j]['gateway_sales_count'] > $max_count) {
                        $max_count = floatval($top_gateway_graph_data1[$j]['gateway_sales_count']);
                    }

	            }

	            foreach ($cumm_date_amt as $cumm_date_amt1) {
	                $temp_gateway_sales_amt [] = $cumm_date_amt1;
	            }

	            foreach ($cumm_date_count as $cumm_date_count1) {
	                $temp_gateway_sales_count [] = $cumm_date_count1;
	            }

                $results_top_payment_gateway[$index]['graph_data_sales_amt'] = $temp_gateway_sales_amt;    
                $results_top_payment_gateway[$index]['max_value_sales_amt'] = $max_amt;

                $results_top_payment_gateway[$index]['graph_data_sales_count'] = $temp_gateway_sales_count;    
                $results_top_payment_gateway[$index]['max_value_sales_count'] = $max_count;

                $results_top_payment_gateway[$index]['payment_method'] = $gateway_title[strtolower($results_top_payment_gateway[$index]['payment_method'])];

	            $index++;
	        }    
	    }
	    

	    //Query for getting the cumm taxes

	    $query_cumm_taxes = "SELECT GROUP_CONCAT(postmeta.meta_key order by postmeta.meta_id SEPARATOR '###') AS prod_othermeta_key,
									GROUP_CONCAT(postmeta.meta_value order by postmeta.meta_id SEPARATOR '###') AS prod_othermeta_value
		                        FROM `{$wpdb->prefix}postmeta` AS postmeta
		                        	LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = postmeta.post_id )
		                        	$terms_post_join
		                        WHERE postmeta.meta_key IN ('_order_total','_order_shipping','_order_shipping_tax','_order_tax')
		                            AND posts.post_date BETWEEN '$start_date' AND '$end_date_query'
		                            $terms_post_cond
		                        GROUP BY posts.ID";
        $results_cumm_taxes    = $wpdb->get_results ( $query_cumm_taxes, 'ARRAY_A' );
	    $rows_cumm_taxes 	  =  $wpdb->num_rows;

	    $tax_data = array();

	    if ($rows_cumm_taxes > 0) {

	    	$tax = 0;
	    	$shipping_tax = 0;
	    	$shipping = 0;
	    	$order_total = 0;

	    	foreach($results_cumm_taxes as $results_cumm_tax) {
	    		$prod_meta_values = explode('###', $results_cumm_tax['prod_othermeta_value']);
                $prod_meta_key = explode('###', $results_cumm_tax['prod_othermeta_key']);

                if (count($prod_meta_values) != count($prod_meta_key))
                    continue;
                
                $prod_meta_key_values = array_combine($prod_meta_key, $prod_meta_values);

                $tax = $tax + $prod_meta_key_values['_order_tax'];
                $shipping_tax = $shipping_tax + $prod_meta_key_values['_order_shipping_tax'];
                $shipping = $shipping + $prod_meta_key_values['_order_shipping'];
                $order_total = $order_total + $prod_meta_key_values['_order_total'];

	    	}

	    	$tax_data['tax'] = $tax;
	    	$tax_data['shipping_tax'] = $shipping_tax;
	    	$tax_data['shipping'] = $shipping;
	    	$tax_data['net_sales'] = $order_total - ($tax + $shipping_tax + $shipping);
	    	$tax_data['total_sales'] = $order_total;
	    }

	    $query_min_abandoned_date = "SELECT MIN(abandoned_cart_time) AS min_abandoned_date
	    							FROM {$wpdb->prefix}sr_woo_abandoned_items";
		$results_min_abandoned_date = $wpdb->get_col ( $query_min_abandoned_date );
		$rows_min_abandoned_date   = $wpdb->num_rows;

		$min_abandoned_date = '';

		if ($results_min_abandoned_date[0] != '') {
			$min_abandoned_date = date('Y-m-d',(int)$results_min_abandoned_date[0]);
		}
		
	    //Cumm Cart Abandonment Rate

	    $query_total_cart = "SELECT COUNT(DISTINCT cart_id) as total_cart_count
			    			FROM {$wpdb->prefix}sr_woo_abandoned_items
			    			WHERE abandoned_cart_time >= ".strtotime($start_date)." AND abandoned_cart_time <=". strtotime($end_date_query);
		$total_cart_count    = $wpdb->get_col ( $query_total_cart );
		$rows_total_cart 	 = $wpdb->num_rows; 

		$query_total_abandoned_cart = "SELECT COUNT(DISTINCT cart_id) as total_cart_abandoned_count
						    			FROM {$wpdb->prefix}sr_woo_abandoned_items
						    			WHERE abandoned_cart_time >= ".strtotime($start_date)." AND abandoned_cart_time <=". strtotime($end_date_query)."
						    				AND product_abandoned = 1
						    				AND order_id IS NULL";
		$total_abandoned_cart_count    = $wpdb->get_col ( $query_total_abandoned_cart );
		$rows_total_abandoned_cart 	 = $wpdb->num_rows; 


	    if ( !empty($total_cart_count) && $total_cart_count[0] > 0) {
	    	$cumm_cart_abandoned_rate = round(($total_abandoned_cart_count[0]/$total_cart_count[0])*100, get_option( 'woocommerce_price_num_decimals' )); 		
	    } else {
	    	$cumm_cart_abandoned_rate = 0;
	    }

	    //Query for getting the countries wise sales

	    $query_cumm_sales_billing_country = "SELECT SUM( postmeta.meta_value ) AS sales,
	    							COUNT(posts.ID) AS total_orders,
	    							postmeta_country.meta_value AS billing_country,
	    							GROUP_CONCAT(DISTINCT postmeta.post_id
                                                 	ORDER BY postmeta.post_id DESC SEPARATOR ',' ) AS order_ids
		                        FROM `{$wpdb->prefix}postmeta` AS postmeta
		                        	LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = postmeta.post_id )
		                        	JOIN {$wpdb->prefix}postmeta AS postmeta_country ON ( postmeta_country.post_id = postmeta.post_id )
		                        	$terms_post_join
		                        WHERE postmeta.meta_key IN ('_order_total')
		                            AND posts.post_date BETWEEN '$start_date' AND '$end_date_query'
		                            AND postmeta_country.meta_key IN ('_billing_country')
		                            $terms_post_cond
	                            GROUP BY billing_country";
        $results_cumm_sales_billing_country   = $wpdb->get_results ( $query_cumm_sales_billing_country, 'ARRAY_A' );
	    $rows_cumm_sales_billing_country 	  =  $wpdb->num_rows;

	    $cumm_sales_billing_country_values = array();
	    $cumm_sales_billing_country_tooltip = array();

	    if ($rows_cumm_sales_billing_country > 0) {

	    	foreach( $results_cumm_sales_billing_country as $result_cumm_sales_billing_country ) {

	    		$cumm_sales_billing_country_values [$result_cumm_sales_billing_country['billing_country']] =  $result_cumm_sales_billing_country['sales'];
		    	$cumm_sales_billing_country_tooltip [$result_cumm_sales_billing_country['billing_country']] = array();
		    	$cumm_sales_billing_country_tooltip [$result_cumm_sales_billing_country['billing_country']] ['sales'] = $sr_currency_symbol.sr_number_format($result_cumm_sales_billing_country['sales'],$sr_decimal_places);
		    	$cumm_sales_billing_country_tooltip [$result_cumm_sales_billing_country['billing_country']] ['count'] = sr_number_format($result_cumm_sales_billing_country['total_orders'],$sr_decimal_places);
		    	$cumm_sales_billing_country_tooltip [$result_cumm_sales_billing_country['billing_country']] ['order_ids'] = $result_cumm_sales_billing_country['order_ids'];

	    	}	    	
	    }
	    
	    //Query to get the top shipping methods
	    $result_top_shipping_method = array();
	    $results_top_shipping_method = array();
	    $rows_top_shipping_method = 0;

	    if (get_option( 'woocommerce_calc_shipping') == 'yes') {

	    	$query_top_shipping_method = "SELECT COUNT( order_items.order_item_name ) AS shipping_count,
			    							SUM(order_itemmeta.meta_value) AS shipping_amount,
			    							order_items.order_item_name AS shipping_name,
			    							SUM(postmeta.meta_value) AS sales_total,
			    							GROUP_CONCAT(DISTINCT order_items.order_id
			                                        		ORDER BY order_items.order_item_id DESC SEPARATOR ',' ) AS order_ids
					                        FROM `{$wpdb->prefix}posts` AS posts
					                        	JOIN {$wpdb->prefix}woocommerce_order_items as order_items ON ( posts.ID = order_items.order_id )
					                        	JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_itemmeta 
					                        		ON (order_items.order_item_id = order_itemmeta.order_item_id 
					                        				AND order_itemmeta.meta_key IN ('cost') )
												LEFT JOIN `{$wpdb->prefix}postmeta` AS postmeta ON ( posts.ID = postmeta.post_id )
												$terms_post_join
					                        WHERE posts.post_date BETWEEN '$start_date' AND '$end_date_query'
					                            $terms_post_cond
					                            AND order_items.order_item_type IN ('shipping')
					                            AND postmeta.meta_key IN ('_order_total')
				                            GROUP BY order_items.order_item_name
				                            ORDER BY shipping_count DESC, shipping_amount DESC
				                            LIMIT 5";

	        $results_top_shipping_method = $wpdb->get_results ( $query_top_shipping_method, 'ARRAY_A' );
		    $rows_top_shipping_method	  =  $wpdb->num_rows;
	    }

	    $top_shipping_method = array();

	    if ($rows_top_shipping_method > 0) {
	    	foreach ($results_top_shipping_method as &$result_top_shipping_method) {
	    		$top_shipping_method[] = $result_top_shipping_method ['shipping_name'];

		    	$result_top_shipping_method['shipping_method_sales_display'] = $sr_currency_symbol . sr_number_format($result_top_shipping_method['shipping_amount'],$sr_decimal_places);
		    	$result_top_shipping_method['shipping_method_sales_percent'] = sr_number_format((($result_top_shipping_method ['sales_total'] / $total_monthly_sales) * 100),$sr_decimal_places) . '%';
		    }

		    if (!empty($top_shipping_method)) {
	        	$top_shipping_method_imploded = "'".implode("','", $top_shipping_method)."'";
	        	$top_shipping_method_cond = 'AND order_items.order_item_name IN ('.$top_shipping_method_imploded.')';
	        	$top_shipping_method_order_by = "ORDER BY FIND_IN_SET(order_items.order_item_name,'".implode(",", $top_shipping_method)."')";

	        } else {
		    	$top_shipping_method_cond = '';
		    	$top_shipping_method_order_by = '';
		    }

		    //Query to get the Top 5 Shipping Methods graph related data

	        $query_top_shipping_method_graph   = "SELECT COUNT( order_items.order_item_name ) AS shipping_count,
					    							SUM(order_itemmeta.meta_value) AS shipping_amount,
					    							order_items.order_item_name AS shipping_name,
				    								$select
							                        FROM `{$wpdb->prefix}posts` AS posts
						                        		JOIN {$wpdb->prefix}woocommerce_order_items as order_items ON ( posts.ID = order_items.order_id )
						                        		JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_itemmeta 
						                        			ON (order_items.order_item_id = order_itemmeta.order_item_id 
						                        				AND order_itemmeta.meta_key IN ('cost') )
														$terms_post_join
						                        WHERE posts.post_date BETWEEN '$start_date' AND '$end_date_query'
						                            AND order_items.order_item_type IN ('shipping')
						                            $terms_post_cond
						                            $top_shipping_method_cond
					                            GROUP BY shipping_name, $group_by
					                            $top_shipping_method_order_by";

	        $results_top_shipping_method_graph = $wpdb->get_results ( $query_top_shipping_method_graph, 'ARRAY_A' );
	        $rows_top_shipping_method_graph	= $wpdb->num_rows;


	        $cumm_shipping_method_temp = $date_series;
		    $cumm_shipping_method_sales = array();

			if($rows_top_shipping_method_graph > 0) {

				//call function for graph data formatting
	        	sr_graph_data_formatting($top_shipping_method_graph_data,$results_top_shipping_method_graph,$group_by,'shipping_name','shipping_method_sales_amt','shipping_method_sales_count','shipping_amount','shipping_count' );
	        }


	        $cumm_top_shipping_method_graph_data = array();

		    $index = 0;
		    $max_values = array();

		    if(!empty($top_shipping_method_graph_data)) {
		        foreach ( $top_shipping_method_graph_data as $top_shipping_method_graph_data1 ) {
		            $cumm_top_shipping_method_amt_graph_data[$index] = array();
		            $temp_shipping_method_sales_amt = array();
		            $temp_shipping_method_sales_count = array();
		            $cumm_date_amt = $date_series;
		            $cumm_date_count = $date_series;

		            $max_amt=0;
		            $max_count=0;

		            for ( $j=0;$j<sizeof($top_shipping_method_graph_data1);$j++ ) {

		                if($group_by == "display_date_time") {
		                    $cumm_date_amt[$top_shipping_method_graph_data1[$j]['comp_time']]['post_date'] = date ("Y-m-d", strtotime($start_date)) .' '. $top_shipping_method_graph_data1[$j]['display_time'];
		                    $cumm_date_count[$top_shipping_method_graph_data1[$j]['comp_time']]['post_date'] = date ("Y-m-d", strtotime($start_date)) .' '. $top_shipping_method_graph_data1[$j]['display_time'];

		                    $cumm_date_amt[$top_shipping_method_graph_data1[$j]['comp_time']]['sales'] = floatval($top_shipping_method_graph_data1[$j]['shipping_method_sales_amt']);
		                	$cumm_date_count[$top_shipping_method_graph_data1[$j]['comp_time']]['sales'] = floatval($top_shipping_method_graph_data1[$j]['shipping_method_sales_count']);
		                }
		                else {
		                	$cumm_date_amt[$top_shipping_method_graph_data1[$j][$group_by]]['sales'] = floatval($top_shipping_method_graph_data1[$j]['shipping_method_sales_amt']);
		                	$cumm_date_count[$top_shipping_method_graph_data1[$j][$group_by]]['sales'] = floatval($top_shipping_method_graph_data1[$j]['shipping_method_sales_count']);
		                }

		                //Shipping Method Sales Amt

	                    if($top_shipping_method_graph_data1[$j]['shipping_method_sales_amt'] > $max_amt) {
	                        $max_amt = floatval($top_shipping_method_graph_data1[$j]['shipping_method_sales_amt']);
	                    }

	                   //Shipping Method Sales Count

	                    if($top_shipping_method_graph_data1[$j]['shipping_method_sales_count'] > $max_count) {
	                        $max_count = floatval($top_shipping_method_graph_data1[$j]['shipping_method_sales_count']);
	                    }

		            }

		            foreach ($cumm_date_amt as $cumm_date_amt1) {
		                $temp_shipping_method_sales_amt [] = $cumm_date_amt1;
		            }

		            foreach ($cumm_date_count as $cumm_date_count1) {
		                $temp_shipping_method_sales_count [] = $cumm_date_count1;
		            }

	                $results_top_shipping_method[$index]['graph_data_sales_amt'] = $temp_shipping_method_sales_amt;    
	                $results_top_shipping_method[$index]['max_value_sales_amt'] = $max_amt;

	                $results_top_shipping_method[$index]['graph_data_sales_count'] = $temp_shipping_method_sales_count;    
	                $results_top_shipping_method[$index]['max_value_sales_count'] = $max_count;

	                $results_top_shipping_method[$index]['shipping_method'] = $results_top_shipping_method[$index]['shipping_name'];

		            $index++;
		        }    
		    }
	    }

	    //Sales Funnel

	    $cumm_sales_funnel = array();

	    //Query to get the total products added to cart

	    if ($rows_total_cart > 0) {
	    	$query_products_added_cart = "SELECT SUM(quantity) as total_prod_added_cart
						    			FROM {$wpdb->prefix}sr_woo_abandoned_items
						    			WHERE abandoned_cart_time BETWEEN '".strtotime($start_date)."' AND '". strtotime($end_date_query)."'";
			$total_products_added_cart  = $wpdb->get_col ( $query_products_added_cart );
			
			$cumm_sales_funnel['total_cart_count'] = floatval($total_cart_count[0]);
			$cumm_sales_funnel['total_products_added_cart'] = floatval($total_products_added_cart[0]);
	    } else {
	    	$cumm_sales_funnel['total_cart_count'] = 0;
	    	$cumm_sales_funnel['total_products_added_cart'] = 0;
	    }
	    

	    //Fix for woo22
	    if (!empty($sr_is_woo22) && $sr_is_woo22 == 'true') {
    		$terms_post_join = '';
    	} else {
    		$terms_post_join = ' JOIN '.$wpdb->prefix.'term_relationships AS term_relationships ON (term_relationships.object_id = posts.ID AND posts.post_status = "publish")';
    	}

	    //Query to get the placed order ids
	    $query_orders_placed = "SELECT DISTINCT id as completed_order_ids
	    							FROM {$wpdb->prefix}posts AS posts
	                                	$terms_post_join
		                            WHERE posts.post_type IN ('shop_order')
		                                AND (posts.post_date BETWEEN '$start_date' AND '$end_date_query')";
	                  
	    $results_orders_placed = $wpdb->get_col($query_orders_placed);
	    $rows_orders_placed =  $wpdb->num_rows;	    

	    if ($rows_orders_placed > 0) {
	    	
	    	$cumm_sales_funnel['orders_placed_count'] = floatval(sizeof($results_orders_placed));

	    	//Query to get the count of the products purchased
	    	
	    	$query_products_purchased = "SELECT SUM(quantity) as query_products_sold
	    							FROM {$wpdb->prefix}sr_woo_order_items
	    							WHERE order_id IN (". implode(",",$results_orders_placed) .")";
			$results_products_purchased = $wpdb->get_col($query_products_purchased);
	    	$rows_products_purchased =  $wpdb->num_rows;

	    	$cumm_sales_funnel['products_purchased_count'] = floatval($results_products_purchased[0]);

	    } else {
			$cumm_sales_funnel['orders_placed_count'] = 0;
			$cumm_sales_funnel['products_purchased_count'] = 0;
	    }

	    //Fix for woo22
	    if (!empty($sr_is_woo22) && $sr_is_woo22 == 'true') {
    		$terms_post_join = '';
    		$terms_post_cond = " AND posts.post_status IN ('wc-completed')";
    	} else {

    		$query_terms     = "SELECT term_taxonomy.term_taxonomy_id
								FROM {$wpdb->prefix}term_taxonomy AS term_taxonomy 
	                                JOIN {$wpdb->prefix}terms AS terms 
	                                    ON term_taxonomy.term_id = terms.term_id
	                    		WHERE terms.name IN ('completed')";
	          
			$terms_post      = $wpdb->get_col($query_terms);
			$rows_terms_post = $wpdb->num_rows;

			if ($rows_terms_post > 0) {
			    $terms_taxonomy_ids = implode(",",$terms_post);
			    $terms_post_join = ' JOIN '.$wpdb->prefix.'term_relationships AS term_relationships ON (term_relationships.object_id = posts.ID AND posts.post_status = "publish")';
	        	$cond_terms_post = ' term_relationships.term_taxonomy_id IN ('.$terms_taxonomy_ids.')';	
			}
    	}

	    //Query to get the completed order ids
	    $query_orders_completed = "SELECT DISTINCT id as completed_order_ids
	    							FROM {$wpdb->prefix}posts AS posts
	                                	$terms_post_join
		                            WHERE posts.post_type IN ('shop_order')
		                            	$terms_post_cond
		                                AND (posts.post_date BETWEEN '$start_date' AND '$end_date_query')";
	                  
	    $results_orders_completed = $wpdb->get_col($query_orders_completed);
	    $rows_orders_completed =  $wpdb->num_rows;	    

	    if ($rows_orders_completed > 0) {
	    	
	    	$cumm_sales_funnel['orders_completed_count'] = floatval(sizeof($results_orders_completed));

	    	//Query to get the count of the products sold
	    	
	    	$query_products_sold = "SELECT SUM(quantity) as query_products_sold
	    							FROM {$wpdb->prefix}sr_woo_order_items
	    							WHERE order_id IN (". implode(",",$results_orders_completed) .")";
			$results_products_sold = $wpdb->get_col($query_products_sold);
	    	$rows_products_sold =  $wpdb->num_rows;

	    	$cumm_sales_funnel['products_sold_count'] = floatval($results_products_sold[0]);

	    } else {
			$cumm_sales_funnel['orders_completed_count'] = 0;
			$cumm_sales_funnel['products_sold_count'] = 0;
	    }
	    

	    if (isset($post['option'])) { // Condition to get the data when the Top Products Toggle button is clicked
	        $results [0] = $cumm_top_prod_graph_data;
	    }
	    else {
	        $results [0] = $monthly_sales;
	        $results [1] = $total_monthly_sales;
	        $results [2] = $results_top_prod;
	        $results [3] = $results_cumm_top_cust;

	        if($total_monthly_sales == 0) {
	            $results [4] = floatval(0);             
	        }
	        else {
	            if ($tot_cumm_orders == 0) {
	                $results [4] = floatval($total_monthly_sales);       
	            }
	            else {
	                $results [4] = floatval($total_monthly_sales/$tot_cumm_orders);
	            }
	        }

	        if($tot_cumm_orders_qty == 0) {
	            $results [5] = floatval(0);             
	        }
	        else {

	            if ($total_cumm_cust_count == 0) {
	                $results [5] = floatval($tot_cumm_orders_qty);       
	            }
	            else {
	                $results [5] = floatval($tot_cumm_orders_qty/$total_cumm_cust_count);
	            }
	        }



	        $results [6] = floatval($max_sales+100);

	        if($total_discount_sales > 0) {
	        	$results [7] = $cumm_discount_sales;
	        	$results [8] = $total_discount_sales;
	        }
	        else{
	        	$results [7] = '';
	        	$results [8] = '';
	        }
	        
	        $results [9] = $results_cumm_coupon_count;
	        $results [10] = floatval($max_discount_total+100);

	        $results [11] = $sr_per_order_containing_coupons;

	        $results [12] = $results_top_payment_gateway;

	        $results [13] = $tax_data;

	        // $results [14] = $results_top_abandoned_products;
	        $results [14] = json_decode(sr_get_abandoned_products($start_date,$end_date_query,$group_by,$sr_currency_symbol,$sr_decimal_places,$date_series,$select_top_abandoned_prod,"LIMIT 5",$terms_taxonomy_ids,$sr_is_woo22),true);
	        
	        $results [15] = ($min_abandoned_date != '' && $min_abandoned_date <= $start_date ) ? $cumm_cart_abandoned_rate : '';

	        $results [16] = ($min_abandoned_date != '' && $min_abandoned_date <= $start_date ) ? $cumm_sales_funnel : '';

	        $results [17] = $cumm_sales_billing_country_values;
	        $results [18] = $cumm_sales_billing_country_tooltip;

	        $results [19] = $results_top_shipping_method;

	    }

	    return $results;
	}

	//Monthly widgets

	function sr_get_sales($start_date,$end_date,$diff_dates,$post) {
	    global $wpdb;

	    $cumm_sales = array();

	    $date_new = date ("Y-m-d", strtotime($start_date));
	    
	    if ($diff_dates > 0 && $diff_dates <= 30) {
	        $date_series[$date_new]['post_date'] = $date_new;
	        $date_series[$date_new]['sales'] = 0;
	        for ($i = 1;$i<=$diff_dates;$i++ ) {
	                $date_new = date ("Y-m-d", strtotime($date_new .' +1 day'));
	                $date_series[$date_new]['post_date'] = $date_new;
	                $date_series[$date_new]['sales'] = 0;
	        }

	        $end_date_query = $end_date;

	        //Dates for handling the proper Rendering of the JQplot graph
	        $min_date_sales = date('Y-m-d', strtotime($start_date .' -2 day'));
	        $max_date_sales = date('Y-m-d', strtotime($end_date_query .' +2 day'));
	        
	    }
	    else if ($diff_dates > 30 && $diff_dates <= 365) {
	        $month = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	        // $date_mn = "2012-01-01";
	        $date_mn = date('Y', strtotime($start_date)) . "-01-01";
	        $date_mn_initial = $date_mn;
	        for ($i = 0;$i<12;$i++ ) {
	            if ($i > 0) {
	                $date_series[$month[$i]]['post_date'] = date('Y-m-d', strtotime($date_mn .' +1 month'));
	                $date_mn = $date_series[$month[$i]]['post_date'];
	            }
	            else {
	                $date_series[$month[$i]]['post_date'] = $date_mn;
	            }
	            
	            $date_series[$month[$i]]['sales'] = 0;
	        }

	        $end_date_query = $end_date;

	        //Dates for handling the proper Rendering of the JQplot graph
	        $min_date_sales = date('Y-m-d', strtotime($date_mn_initial .' -2 month'));
	        $max_date_sales = date('Y-m-d', strtotime($date_mn .' +2 month'));
	    }
	    else if ($diff_dates > 365) {
	        $year_strt = substr($start_date, 0,4);
	        $year_end = substr($end_date, 0,4);

	        $year_tmp[0] = $year_strt;

	        for ($i = 1;$i<=($year_end - $year_strt);$i++ ) {
	             $year_tmp [$i] = $year_tmp [$i-1] + 1;          
	        }

	        for ($i = 0;$i<sizeof($year_tmp);$i++ ) {
	            $date_series[$year_tmp[$i]]['post_date'] = $year_tmp[$i]."-01-01";
	            $date_series[$year_tmp[$i]]['sales'] = 0;
	        }

	        $end_date_query = $end_date;

	        //Dates for handling the proper Rendering of the JQplot graph
	        $min_date_sales = $year_strt - 1 . "-01-01";
	        $max_date_sales = $year_end + 1 . "-01-01";
	    }

	    else {
	        $date_series[0]['post_date'] = date ("Y-m-d", strtotime($start_date)) .' '. "00:00:00";
	        $date_series[0]['sales'] = 0;
	        for ($i = 1;$i<24;$i++ ) {
	            $date_new = date ("Y-m-d H:i:s ", strtotime($date_new .' +1 hours'));
	            $date_series[$i]['post_date'] = $date_new;
	            $date_series[$i]['sales'] = 0; 
	        }

	        $end_date_query = $end_date;
	 
	        $min_date_sales = date ("Y-m-d H:i:s", strtotime($start_date .' -2 hours'));
	        $max_date_sales = date ("Y-m-d", strtotime($start_date .' +1 day')) .' '."01:00:00";

	    }
	    
	    $total_monthly_sales = 0;
	    $tot_cumm_orders = 0;
	    $tot_cumm_orders_qty = 0;

	    //Query to get the relevant order ids

	    // WHERE terms.name IN ('completed','processing','on-hold','pending')

	    $terms_taxonomy_ids = '';

	    if (!(!empty($post['SR_IS_WOO22']) && $post['SR_IS_WOO22'] == 'true')) {
			$query_terms = "SELECT  term_taxonomy.term_taxonomy_id
    						FROM {$wpdb->prefix}term_taxonomy AS term_taxonomy 
                                 JOIN {$wpdb->prefix}terms AS terms 
                                 	ON term_taxonomy.term_id = terms.term_id
                            WHERE terms.name IN ('completed','processing','on-hold')";
		                  
		    $terms_taxonomy_ids = $wpdb->get_col($query_terms);
		    $rows_terms_post =  $wpdb->num_rows;

		    if ($rows_terms_post > 0) {
		    	$terms_taxonomy_ids = implode(",",$terms_taxonomy_ids);
		    }	
	    }

	    if ($diff_dates > 0 && $diff_dates <= 30) {
	        $select = "DATE_FORMAT(posts.`post_date`, '%Y-%m-%d') AS display_date";

	        $select_top_prod = "GROUP_CONCAT(order_item.sales order by order_item.order_id SEPARATOR '###') AS sales_details,
	                            GROUP_CONCAT(order_item.quantity order by order_item.order_id SEPARATOR '###') AS quantity_details,
	                            GROUP_CONCAT(DATE_FORMAT(posts.`post_date`, '%Y-%m-%d') by posts.id SEPARATOR '###') AS order_dates";

	        $select_top_abandoned_prod = ", GROUP_CONCAT(FROM_UNIXTIME(abandoned_cart_time, '%Y-%m-%d') order by id SEPARATOR '###') AS abandoned_dates";

        	$results =  sr_query_sales($start_date,$end_date_query,$date_series,$select,"display_date",$select_top_prod,$select_top_abandoned_prod,$terms_taxonomy_ids,$post);

	    }
	    else if ($diff_dates > 30 && $diff_dates <= 365) {
	        $select = "DATE_FORMAT(MAX(posts.`post_date`), '%Y-%m-%d') AS display_date,
	                    DATE_FORMAT(posts.`post_date`, '%b') AS month_nm";

	        $select_top_prod = "GROUP_CONCAT(order_item.sales order by order_item.order_id SEPARATOR '###') AS sales_details,
	                            GROUP_CONCAT(order_item.quantity order by order_item.order_id SEPARATOR '###') AS quantity_details,
	                            GROUP_CONCAT(DATE_FORMAT(posts.`post_date`, '%b') by posts.id SEPARATOR '###') AS order_dates";

	        $select_top_abandoned_prod = ", GROUP_CONCAT(FROM_UNIXTIME(abandoned_cart_time, '%b') order by id SEPARATOR '###') AS abandoned_dates";

        	$results =  sr_query_sales($start_date,$end_date_query,$date_series,$select,"month_nm",$select_top_prod,$select_top_abandoned_prod,$terms_taxonomy_ids,$post);
	    }
	    else if ($diff_dates > 365) {
	        $select = "DATE_FORMAT(MAX(posts.`post_date`), '%Y-%m-%d') AS display_date,
	                    DATE_FORMAT(posts.`post_date`, '%Y') AS year_nm";

	        $select_top_prod = "GROUP_CONCAT(order_item.sales order by order_item.order_id SEPARATOR '###') AS sales_details,
	                            GROUP_CONCAT(order_item.quantity order by order_item.order_id SEPARATOR '###') AS quantity_details,
	                            GROUP_CONCAT(DATE_FORMAT(posts.`post_date`, '%Y') by posts.id SEPARATOR '###') AS order_dates";

	        $select_top_abandoned_prod = ", GROUP_CONCAT(FROM_UNIXTIME(abandoned_cart_time, '%Y') order by id SEPARATOR '###') AS abandoned_dates";
	        
        	$results =  sr_query_sales($start_date,$end_date_query,$date_series,$select,"year_nm",$select_top_prod,$select_top_abandoned_prod,$terms_taxonomy_ids,$post);  
	    }
	    else {
	        $select = "DATE_FORMAT(posts.`post_date`, '%Y/%m/%d') AS display_date_time,
	                    DATE_FORMAT(MAX(posts.`post_date`), '%H:%i:%s') AS display_time,
	                    DATE_FORMAT(posts.`post_date`, '%k') AS comp_time";
	        
	        $select_top_prod = "GROUP_CONCAT(order_item.sales order by order_item.order_id SEPARATOR '###') AS sales_details,
	                            GROUP_CONCAT(order_item.quantity order by order_item.order_id SEPARATOR '###') AS quantity_details,
	                            GROUP_CONCAT(DATE_FORMAT(posts.`post_date`, '%H:%i:%s') by posts.id SEPARATOR '###') AS display_time,
	                            GROUP_CONCAT(DATE_FORMAT(posts.`post_date`, '%k') by posts.id SEPARATOR '###') AS comp_time";

	        $select_top_abandoned_prod = ", GROUP_CONCAT(FROM_UNIXTIME(abandoned_cart_time, '%Y/%m/%d %H:%i:%s') order by id SEPARATOR '###') AS abandoned_dates,
	        							  GROUP_CONCAT(FROM_UNIXTIME(abandoned_cart_time, '%k') order by id SEPARATOR '###') AS comp_time";
	                    
	        // $end_date_query = date('Y-m-d', strtotime($end_date_query .' +1 day'));

        	$results =  sr_query_sales($start_date,$end_date_query,$date_series,$select,"display_date_time",$select_top_prod,$select_top_abandoned_prod,$terms_taxonomy_ids,$post);  
	    }

	    if (isset($post['option'])) {
	        $results[1] = $min_date_sales;
	        $results[2] = $max_date_sales;
	    }
	    else {
	        $results[20] = $min_date_sales;
	        $results[21] = $max_date_sales;
	    }

	    return $results;
	}


	// Function to convert the date in local timezone
	function date_timezone_conversion($post) {

		$_POST = $post;
		$converted_dates = array(); // array to return the converted dates
		
	    $start_date= date('Y-m-d H:i:s',(int)strtotime($_POST['start_date']));
	    $date_convert = 0;

	    if (!empty($_POST['end_date']) || $_POST['end_date'] != "") {

	        if ($_POST['end_date'] == date('Y-m-d')) {

	        	$end_date = $_POST['end_date'];
	        	$date_convert = 1;

	        } else {
	        	$end_date = $_POST['end_date'] . " 23:59:59";
	        	$end_date_org = $end_date;
	        }


	    }
	    else {
	        // $end_date = date('Y-m-d', strtotime($_POST['start_date'] .' +1 month'));
	        $end_date = date('Y-m-d H:i:s');
	        $_POST['end_date'] = $end_date;
	        $date_convert = 1;
	    }


	    if ($date_convert == 1) {
	    	$date = date('Y-m-d',(int)strtotime($end_date));
		    $curr_time_gmt = date('H:i:s',time()- date("Z"));
		    $new_date = $date ." " . $curr_time_gmt;
		    $end_date = date('Y-m-d H:i:s',((int)strtotime($new_date)) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS )) ;	
	    }

	    $converted_dates ['start_date'] = $start_date;
	    $converted_dates ['end_date'] = $end_date;

		return $converted_dates;	    

	}

	// Function for getting the formatted sales frequency
	function sr_get_frequency_formatted($days) {

		// 1 hr = 0.0416 days 

		if ($days < 0.0416)
        {
            $duration=round((($days/ 0.0416) * 60),2) . 'min';
        }
        else if ($days < 1)
        {
            /**
             * In this we convert 1 day velocity to be based upon Hours.
             * So we get say, 0.5 days we multiply it by 24 and it becomes 12hrs.
             *
             * 1min = 0.0167 hrs.
             */
            $valueAsPerDuration = $days * 24;
            $remainderValue = floor((($valueAsPerDuration % 1) / 0.0167));
            $duration =  floor($valueAsPerDuration) . 'h';
            $duration .= ($remainderValue != 0) ? ' ' . round($remainderValue,0) . 'min' : '';
        }
        else if ($days < 7)
        {
            $valueAsPerDuration = $days;
            $remainderValue = round((($valueAsPerDuration % 1) * 24),0);
            $duration = floor($valueAsPerDuration) . 'd';
            $duration .= ($remainderValue != 0) ? ' ' . $remainderValue . 'h' : '';
        }
        else if ($days < 30)
        {
            $valueAsPerDuration = $days / 7;
            $remainderValue = round(($valueAsPerDuration % 7),0);
            $duration = floor($valueAsPerDuration) . 'w';
            $duration .= ($remainderValue != 0) ? ' ' . $remainderValue . 'd' : '';
        }
        else if ($days < 365)
        {
            $valueAsPerDuration = $days / 30;
            $remainderValue =  round(($valueAsPerDuration % 30),0);
            $duration = floor($valueAsPerDuration) . 'm';
            $duration .= ($remainderValue != 0) ? ' ' . $remainderValue . 'd' : '';
        }
        else if ($days > 365)
        {
            $valueAsPerDuration = $days / 365;
            $remainderValue = round(($valueAsPerDuration % 365),0);
            $additionalText = '';

            if ($remainderValue > 30)
            {
                $remainderValue = round(($remainderValue / 30),0);
                $additionalText = 'm';
            }
            else
            {
                $additionalText = 'd';
            }
            $duration = floor($valueAsPerDuration) . 'y';
            $duration .= ($remainderValue != 0) ? ' ' . $remainderValue . $additionalText : '';
        }

        return $duration;
	}

	//Formatting the kpi data

	function sr_get_daily_kpi_data_formatted($kpi_name,$current_value,$comparison_value,$post) {

		$_POST = $post;

		if ($kpi_name == "daily_cust" || $kpi_name == "order_fulfillment") {
			$daily_widget_data['diff_'.$kpi_name] = abs($current_value - $comparison_value);	
		} else {
			if ($comparison_value == 0) {
				$daily_widget_data['diff_'.$kpi_name] = round($current_value,2);
			}
			else {
				$daily_widget_data['diff_'.$kpi_name] = abs(round(((($current_value - $comparison_value)/$comparison_value) * 100),2));
			}	
		}

		if ($daily_widget_data['diff_'.$kpi_name] != 0) {
			if ($comparison_value < $current_value) {
				if ($kpi_name == "daily_refund" || $kpi_name == "order_fulfillment") {
					$daily_widget_data['imgurl_'.$kpi_name] = $_POST ['SR_IMG_UP_RED'];
				} else {
					$daily_widget_data['imgurl_'.$kpi_name] = $_POST ['SR_IMG_UP_GREEN'];	
				}
			}
			else {
				if ($kpi_name == "daily_refund"  || $kpi_name == "order_fulfillment") {
					$daily_widget_data['imgurl_daily_refund'] = $_POST ['SR_IMG_UP_GREEN'];
				} else {
			    	$daily_widget_data['imgurl_'.$kpi_name] = $_POST ['SR_IMG_DOWN_RED'];
			    }
			}    
		}
		else {
			$daily_widget_data['diff_'.$kpi_name] = "";
			$daily_widget_data['imgurl_'.$kpi_name] = "";
		}

		if ($kpi_name == "daily_cust" || $kpi_name == "order_fulfillment") {
			$daily_widget_data[$kpi_name.'_formatted'] = sr_number_format($current_value,$_POST ['SR_DECIMAL_PLACES']);
		} else {
			$daily_widget_data[$kpi_name.'_formatted'] = $_POST ['SR_CURRENCY_SYMBOL'] . sr_number_format($current_value,$_POST ['SR_DECIMAL_PLACES']);
		}
		
		$daily_widget_data['diff_'.$kpi_name.'_formatted'] = (!empty($daily_widget_data['diff_'.$kpi_name])) ? sr_number_format($daily_widget_data['diff_'.$kpi_name], $_POST ['SR_DECIMAL_PLACES']) . '%' : "";

		return $daily_widget_data;

	}

	//Daily Widgets Data 

	function sr_get_daily_kpi_data() {

		global $wpdb;

		//Code for date localization

		$today_arr = getdate();
		$curr_time_gmt = date('H:i:s',time()- date("Z"));
		$new_date = date('Y-m-d') ." " . $curr_time_gmt;
		$today = date('Y-m-d',((int)strtotime($new_date)) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS )) ;
		$today_time = date('Y-m-d H:i:s',((int)strtotime($new_date)) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS )) ;
		$yesterday = date('Y-m-d', strtotime($today .' -1 day'));

		// $today_to_date = $today . " 00:00:00";
		$this_month_start   = date("Y-m-d H:i:s", mktime(0,0,0,date('m', strtotime($today)),1,date('Y', strtotime($today))));
		$days_in_this_month = date('t', mktime(0, 0, 0, date('m', strtotime($today)), 1, date('Y', strtotime($today))));

		$comparison_to_date = date('Y-m-d', strtotime($today . ' -1 month')) . " 00:00:00";
		$comparison_month_start   = date("Y-m-d H:i:s", mktime(0,0,0,date('m', strtotime($comparison_to_date)),1,date('Y', strtotime($comparison_to_date))));
		$comparison_days_in_month = date('t', mktime(0, 0, 0, date('m', strtotime($comparison_to_date)), 1, date('Y', strtotime($comparison_to_date))));


		$cond_terms_post = '';
	    $terms_post_join = '';

		if (!empty($_POST['SR_IS_WOO22']) && $_POST['SR_IS_WOO22'] == "true") {

			$cond_terms_post = "AND posts.post_status IN ('wc-completed','wc-processing','wc-on-hold')";
		    $terms_post_join = '';

		} else {
			$query_terms     = "SELECT term_taxonomy.term_taxonomy_id
								FROM {$wpdb->prefix}term_taxonomy AS term_taxonomy 
	                                JOIN {$wpdb->prefix}terms AS terms 
	                                    ON term_taxonomy.term_id = terms.term_id
	                    		WHERE terms.name IN ('completed','processing','on-hold')";
	          
			$terms_post      = $wpdb->get_col($query_terms);
			$rows_terms_post = $wpdb->num_rows;

			if ($rows_terms_post > 0) {
			    $terms_taxonomy_ids = implode(",",$terms_post);
			    $terms_post_join = ' JOIN '.$wpdb->prefix.'term_relationships AS term_relationships ON (term_relationships.object_id = posts.ID AND posts.post_status = "publish")';
	        	$cond_terms_post = (!empty($terms_post_join)) ? 'AND term_relationships.term_taxonomy_id IN ('.$terms_taxonomy_ids.')' : '';	    										
			}
		}

		$daily_widget_data = array();

		// ================================================
		// Todays Sales
		// ================================================

		$query_today        = "SELECT SUM( postmeta.meta_value ) AS todays_sales 
		                        FROM `{$wpdb->prefix}postmeta` AS postmeta
		                        	LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = postmeta.post_id )
		                        	$terms_post_join
		                        WHERE postmeta.meta_key IN ('_order_total')
		                            AND posts.post_date LIKE '$today%'
		                            $cond_terms_post";
		$results_today      = $wpdb->get_col ( $query_today );
		$rows_results_today = $wpdb->num_rows;

		if ($rows_results_today > 0 && (!empty($results_today[0])) ) {
			$daily_widget_data['sales_today'] = $results_today[0];
		}
		else {
			$daily_widget_data['sales_today'] = 0;
		}


		$query_yest        = "SELECT SUM( postmeta.meta_value ) AS yesterdays_sales 
		                    FROM `{$wpdb->prefix}postmeta` AS postmeta
		                    	LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = postmeta.post_id )
		                    	$terms_post_join
		                    WHERE postmeta.meta_key IN ('_order_total')
		                        AND posts.post_date LIKE '$yesterday%'
		                        $cond_terms_post";
		$results_yest       = $wpdb->get_col ( $query_yest );
		$rows_results_yest  = $wpdb->num_rows;

		if ($rows_results_yest > 0) {
			$daily_widget_data['sales_yest'] = $results_yest[0];
		}
		else {
			$daily_widget_data['sales_yest'] = 0;
		}

		$daily_sales_kpi = sr_get_daily_kpi_data_formatted('daily_sales',$daily_widget_data['sales_today'],$daily_widget_data['sales_yest'],$_POST);
		
		// Query to get the month to date and forecasted sales

		$query_month_to_date_sales = "SELECT COUNT( posts.ID ) as sales_count, 
											SUM( postmeta.meta_value ) AS month_to_date 
					                    FROM `{$wpdb->prefix}postmeta` AS postmeta
					                    	LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = postmeta.post_id )
					                    	$terms_post_join
					                    WHERE postmeta.meta_key IN ('_order_total')
				                        	AND posts.post_date between '$this_month_start' AND '$today_time'
				                        	$cond_terms_post";
		$results_month_to_date_sales = $wpdb->get_results ( $query_month_to_date_sales, 'ARRAY_A' );

		$month_to_date_sales = (!empty($results_month_to_date_sales[0]['month_to_date'])) ? $results_month_to_date_sales[0]['month_to_date'] : 0;
		$avg_sales_per_day  = round(($results_month_to_date_sales[0]['month_to_date']/$today_arr['mday']),2);
		$forcasted_sales 	= $avg_sales_per_day * $days_in_this_month;

		// Code for calculating the sales frequency
		$date_diff = round((strtotime($today_time)-strtotime($this_month_start)) / 60);
		$frequency_diff_days = $date_diff / 1440;

		$sales_frequency = (!empty($results_month_to_date_sales[0]['sales_count'])) ? ($frequency_diff_days / $results_month_to_date_sales[0]['sales_count']) : '0';

		// $diff = date_diff($today_time,$this_month_start);

		$sales_frequency_formatted = sr_get_frequency_formatted($sales_frequency);

		// Query to get the comparison month to date and forecasted sales

		$query_comparison_month_to_date_sales = "SELECT COUNT( posts.ID ) as sales_count, 
													SUM( postmeta.meta_value ) AS month_to_date
							                    FROM `{$wpdb->prefix}postmeta` AS postmeta
							                    	LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = postmeta.post_id )
							                    	$terms_post_join
							                    WHERE postmeta.meta_key IN ('_order_total')
						                        	AND posts.post_date between '$comparison_month_start' AND '$comparison_to_date'
						                        	$cond_terms_post";
		$results_comparison_month_to_date_sales = $wpdb->get_results ( $query_comparison_month_to_date_sales, 'ARRAY_A' );

		$comparison_month_to_date_sales = (!empty($results_comparison_month_to_date_sales[0]['month_to_date'])) ? $results_comparison_month_to_date_sales[0]['month_to_date'] : 0;
		$comparison_avg_sales_per_day  = round(($results_comparison_month_to_date_sales[0]['month_to_date']/$today_arr['mday']),2);
		$comparison_forcasted_sales 	= $comparison_avg_sales_per_day * $comparison_days_in_month;

		$comparison_sales_frequency = (!empty($results_comparison_month_to_date_sales[0]['sales_count'])) ? ($frequency_diff_days / $results_comparison_month_to_date_sales[0]['sales_count']) : '0';

		//Code for month to date sales KPI
		$month_to_date_sales_kpi = sr_get_daily_kpi_data_formatted('month_to_date_sales',$month_to_date_sales,$comparison_month_to_date_sales,$_POST);

		//Code for average sales per day KPI
		$avg_sales_per_day_kpi = sr_get_daily_kpi_data_formatted('avg_sales_per_day',$avg_sales_per_day,$comparison_avg_sales_per_day,$_POST);
		
		//Code for Forecasted Sales KPI
		$forcasted_sales_kpi = sr_get_daily_kpi_data_formatted('forcasted_sales',$forcasted_sales,$comparison_forcasted_sales,$_POST);

		


		//Code for Sales Frequency KPI

		// $sales_count = (!empty($results_month_to_date_sales[0]['sales_count'])) ? $results_month_to_date_sales[0]['sales_count'] : '0';
		// $comparison_sales_count = (!empty($results_comparison_month_to_date_sales[0]['sales_count'])) ? $results_comparison_month_to_date_sales[0]['sales_count'] : '0';

		if ($comparison_sales_frequency == 0) {
			$daily_widget_data['diff_sales_frequency'] = round($sales_frequency,2);
		}
		else {
			$daily_widget_data['diff_sales_frequency'] = abs(round(((($sales_frequency - $comparison_sales_frequency)/$comparison_sales_frequency) * 100),2));
		}

		if ($daily_widget_data['diff_sales_frequency'] != 0) {
			if ($comparison_sales_frequency < $sales_frequency) {
				$daily_widget_data['imgurl_sales_frequency'] = $_POST ['SR_IMG_UP_RED'];	
			}
			else {
		    	$daily_widget_data['imgurl_sales_frequency'] = $_POST ['SR_IMG_UP_GREEN'];
			}    
		}
		else {
			$daily_widget_data['diff_sales_frequency'] = "";
			$daily_widget_data['imgurl_sales_frequency'] = "";
		}

		$daily_widget_data['sales_frequency_formatted'] = $sales_frequency_formatted;
		
		$daily_widget_data['diff_sales_frequency_formatted'] = (!empty($daily_widget_data['diff_sales_frequency'])) ? sr_number_format($daily_widget_data['diff_sales_frequency'], $_POST ['SR_DECIMAL_PLACES']) . '%' : "";

		// ================================================
		// Todays Customers
		// ================================================

		$result_guest_today_email1 = array();
		$result_guest_yest_email1 = array();
		$reg_today_count = 0;
		$reg_yest_count = 0;


		//Reg Customers
		$query_reg_today    = "SELECT ID FROM `$wpdb->users` 
		                    WHERE user_registered LIKE  '$today%'";
		$reg_today_ids      = $wpdb->get_col ( $query_reg_today );
		$rows_reg_today_ids = $wpdb->num_rows;

		if ($rows_reg_today_ids > 0) {
		    $query_reg_today_count  ="SELECT DISTINCT postmeta.meta_value
		                               FROM {$wpdb->prefix}postmeta AS postmeta
		                                        JOIN {$wpdb->prefix}posts AS posts ON (posts.ID = postmeta.post_id)
		                                        $terms_post_join
		                               WHERE postmeta.meta_key IN ('_customer_user')
		                                     AND postmeta.meta_value IN (".implode(",",$reg_today_ids).")
		                                     AND posts.post_date LIKE  '$today%'
		                                     $cond_terms_post";

		    $reg_today              = $wpdb->get_col ( $query_reg_today_count ); 
		    $rows_reg_today         = $wpdb->num_rows;

		    if($rows_reg_today > 0) {
		        $reg_today_count = sizeof($reg_today);
		    }
		}

		$query_reg_yest      = "SELECT ID FROM `$wpdb->users` 
		                     WHERE user_registered LIKE  '$yesterday%'";
		$reg_yest_ids        = $wpdb->get_col ( $query_reg_yest );
		$rows_reg_yest_ids   = $wpdb->num_rows;

		if ($rows_reg_yest_ids > 0) {
		    $query_reg_today_count  ="SELECT DISTINCT postmeta.meta_value
		                               FROM {$wpdb->prefix}postmeta AS postmeta
	                                        JOIN {$wpdb->prefix}posts AS posts ON (posts.ID = postmeta.post_id)
	                                        $terms_post_join
		                               WHERE postmeta.meta_key IN ('_customer_user')
		                                     AND postmeta.meta_value IN (".implode(",",$reg_yest_ids).")
		                                     AND posts.post_date LIKE  '$yesterday%'
		                                     $cond_terms_post";

		    $reg_yest               = $wpdb->get_col ( $query_reg_today_count );  
		    $rows_reg_yest          = $wpdb->num_rows;   

		    if($rows_reg_yest > 0) {
		        $reg_yest_count = sizeof($reg_yest);
		    }

		}

		//Guest Customers

		$query_guest_today_email    = "SELECT postmeta1.meta_value
		                       FROM {$wpdb->prefix}postmeta AS postmeta1
		                                JOIN {$wpdb->prefix}posts AS posts ON (posts.ID = postmeta1.post_id)
		                                INNER JOIN {$wpdb->prefix}postmeta AS postmeta2
		                                               ON (postmeta2.post_ID = postmeta1.post_ID AND postmeta2.meta_key IN ('_customer_user'))
                                       $terms_post_join
		                       WHERE postmeta1.meta_key IN ('_billing_email')
		                             AND postmeta2.meta_value = 0
		                             AND posts.post_date LIKE  '$today%'
		                             $cond_terms_post
		                       GROUP BY postmeta1.meta_value";

		$result_guest_today_email   = $wpdb->get_col ( $query_guest_today_email );
		$rows_guest_today_email     = $wpdb->num_rows;

		if ($rows_guest_today_email > 0) {
		    $result_guest_today_email1   = array_flip($result_guest_today_email);    

		    $query_guest_today          = "SELECT DISTINCT postmeta.meta_value
		                               FROM {$wpdb->prefix}postmeta AS postmeta
		                                        JOIN {$wpdb->prefix}posts AS posts ON (posts.ID = postmeta.post_id)
		                               WHERE postmeta.meta_key IN ('_billing_email')
		                                     AND postmeta.meta_value IN ('". implode("','",$result_guest_today_email) ."')
		                                         AND posts.post_date NOT LIKE  '$today%'
		                               GROUP BY posts.ID";

		    $result_guest_today         = $wpdb->get_col ( $query_guest_today );

		    for($i=0; $i<sizeof($result_guest_today);$i++) {
		        if (isset($result_guest_today_email1[$result_guest_today[$i]])) {
		            unset($result_guest_today_email1[$result_guest_today[$i]]);
		        }        
		    }
		}

		$daily_widget_data['today_count_cust'] = 0;

		$daily_widget_data['today_count_cust'] = sizeof($result_guest_today_email1) + $reg_today_count;    

		$query_guest_yest_email    ="SELECT postmeta1.meta_value
		                           FROM {$wpdb->prefix}postmeta AS postmeta1
		                                    JOIN {$wpdb->prefix}posts AS posts ON (posts.ID = postmeta1.post_id)
		                                    INNER JOIN {$wpdb->prefix}postmeta AS postmeta2
		                                                   ON (postmeta2.post_ID = postmeta1.post_ID AND postmeta2.meta_key IN ('_customer_user'))
		                                    $terms_post_join
		                           WHERE postmeta1.meta_key IN ('_billing_email')
		                                 AND postmeta2.meta_value = 0
		                                 AND posts.post_date LIKE  '$yesterday%'
		                                 $cond_terms_post
		                           GROUP BY postmeta1.meta_value";

		$result_guest_yest_email   =  $wpdb->get_col ( $query_guest_yest_email );
		$rows_guest_yest_email     = $wpdb->num_rows;

		if ($rows_guest_yest_email > 0) {
		$result_guest_yest_email1   = array_flip($result_guest_yest_email);

		$query_guest_yest   = "SELECT DISTINCT postmeta.meta_value
		                       FROM {$wpdb->prefix}postmeta AS postmeta
		                                JOIN {$wpdb->prefix}posts AS posts ON (posts.ID = postmeta.post_id)
		                       WHERE postmeta.meta_key IN ('_billing_email')
		                             AND postmeta.meta_value IN ('". implode("','",$result_guest_yest_email) ."')
		                             AND posts.post_date NOT LIKE  '$yesterday%'
		                                 AND posts.post_date NOT LIKE  '$today%'
		                       GROUP BY posts.ID";

		$result_guest_yest   =  $wpdb->get_col ( $query_guest_yest );

		for($i=0; $i<sizeof($result_guest_yest);$i++) {
		    if (isset($result_guest_yest_email1[$result_guest_yest[$i]])) {
		        unset($result_guest_yest_email1[$result_guest_yest[$i]]);
		    }
		}    
		}

		$daily_widget_data['yest_count_cust'] = 0;

		$daily_widget_data['yest_count_cust'] = sizeof($result_guest_yest_email1) + $reg_yest_count;

		$daily_cust_kpi = sr_get_daily_kpi_data_formatted('daily_cust',$daily_widget_data['today_count_cust'],$daily_widget_data['yest_count_cust'],$_POST);
		
		// ================================================
		// Todays Returns
		// ================================================


		$cond_terms_post = '';
	    $terms_post_join = '';

		if (!empty($_POST['SR_IS_WOO22']) && $_POST['SR_IS_WOO22'] == "true") {

			$cond_terms_post = " posts.post_status IN ('wc-refunded')";
		    $terms_post_join = '';

		} else {
			$query_terms     = "SELECT term_taxonomy.term_taxonomy_id
								FROM {$wpdb->prefix}term_taxonomy AS term_taxonomy 
	                                JOIN {$wpdb->prefix}terms AS terms 
	                                    ON term_taxonomy.term_id = terms.term_id
	                    		WHERE terms.name IN ('refunded')";
	          
			$terms_post      = $wpdb->get_col($query_terms);
			$rows_terms_post = $wpdb->num_rows;

			if ($rows_terms_post > 0) {
			    $terms_taxonomy_ids = implode(",",$terms_post);
			    $terms_post_join = ' JOIN '.$wpdb->prefix.'term_relationships AS term_relationships ON (term_relationships.object_id = posts.ID AND posts.post_status = "publish")';
	        	$cond_terms_post = ' term_relationships.term_taxonomy_id IN ('.$terms_taxonomy_ids.')';	
			}
		}

		$query_terms_refund         = "SELECT id FROM {$wpdb->prefix}posts AS posts
		                            		$terms_post_join
			                            WHERE $cond_terms_post";

		$terms_refund_post          = $wpdb->get_col($query_terms_refund);
		$rows_terms_refund_post     = $wpdb->num_rows;

		if ($rows_terms_refund_post > 0) {
			$terms_refund_post = implode(",",$terms_refund_post);

			$query_today_refund     = "SELECT SUM(postmeta.meta_value) as todays_refund
			                           FROM {$wpdb->prefix}postmeta AS postmeta
			                                    JOIN {$wpdb->prefix}posts AS posts ON (posts.ID = postmeta.post_id)
			                           WHERE postmeta.meta_key IN ('_order_total')
			                                 AND posts.post_modified LIKE '$today%'
			                                 AND posts.ID IN ($terms_refund_post)";

			$result_today_refund    = $wpdb->get_col ( $query_today_refund ); 

			$query_yest_refund      = "SELECT SUM(postmeta.meta_value) as yest_refund
			                           FROM {$wpdb->prefix}postmeta AS postmeta
			                                    JOIN {$wpdb->prefix}posts AS posts ON (posts.ID = postmeta.post_id)
			                           WHERE postmeta.meta_key IN ('_order_total')
			                                 AND posts.post_modified LIKE '$yesterday%'
			                                 AND posts.ID IN ($terms_refund_post)";

			$result_yest_refund     = $wpdb->get_col ( $query_yest_refund );

		}
		else {
			$rows_today_refund = 0;
			$rows_yest_refund = 0;
		}


		if (!empty($result_today_refund[0])) {
			$daily_widget_data['today_refund']   = $result_today_refund[0];
		}
		else {
			$daily_widget_data['today_refund']   = "0";
		}

		if (!empty($result_yest_refund[0])) {
			$daily_widget_data['yest_refund']   = $result_yest_refund[0];
		}
		else {
			$daily_widget_data['yest_refund']   = "0";
		}

		$daily_refund_kpi = sr_get_daily_kpi_data_formatted('daily_refund',$daily_widget_data['today_refund'],$daily_widget_data['yest_refund'],$_POST);

		// ================================================
		// Orders Unfulfillment
		// ================================================

		$query_shipping_status  = "SELECT option_value FROM {$wpdb->prefix}options
		                        WHERE option_name LIKE 'woocommerce_calc_shipping'";
		$result_shipping_status = $wpdb->get_col ( $query_shipping_status );

		$daily_widget_data['result_shipping_status'] = $result_shipping_status[0];

		$query_physical_prod  = "SELECT post_id
		                       FROM {$wpdb->prefix}postmeta
		                       WHERE (meta_key LIKE '_downloadable' AND meta_value LIKE 'no')
		                             OR (meta_key LIKE '_virtual' AND meta_value LIKE 'no')";

		$result_physical_prod = $wpdb->get_col ( $query_physical_prod ); 
		$rows_physical_prod   = $wpdb->num_rows;

		$daily_widget_data['rows_physical_prod'] = $rows_physical_prod;

		//Woo 2.2 Fix
		if (!empty($_POST['SR_IS_WOO22']) && $_POST['SR_IS_WOO22'] == "true") {

			$cond_terms_post = " posts.post_status IN ('wc-processing')";
		    $terms_post_join = '';

		} else {
			$query_terms     = "SELECT term_taxonomy.term_taxonomy_id
								FROM {$wpdb->prefix}term_taxonomy AS term_taxonomy 
	                                JOIN {$wpdb->prefix}terms AS terms 
	                                    ON term_taxonomy.term_id = terms.term_id
	                    		WHERE terms.name IN ('processing')";
	          
			$terms_post      = $wpdb->get_col($query_terms);
			$rows_terms_post = $wpdb->num_rows;

			if ($rows_terms_post > 0) {
			    $terms_taxonomy_ids = implode(",",$terms_post);
			    $terms_post_join = ' JOIN '.$wpdb->prefix.'term_relationships AS term_relationships ON (term_relationships.object_id = posts.ID AND posts.post_status = "publish")';
	        	$cond_terms_post = ' term_relationships.term_taxonomy_id IN ('.$terms_taxonomy_ids.')';	
			}
		}

		$query_order_fulfillment_today  = "SELECT count(id) FROM {$wpdb->prefix}posts AS posts
		                                	$terms_post_join
		                                WHERE $cond_terms_post
		                                    AND (posts.post_modified LIKE '$today%'
		                                        OR posts.post_date LIKE '$today%')";
		          
		$result_order_fulfillment_today = $wpdb->get_col($query_order_fulfillment_today);
		$rows_order_fulfillment_today   = $wpdb->num_rows;

		if($rows_order_fulfillment_today > 0) {
			$daily_widget_data['count_order_fulfillment_today'] = $result_order_fulfillment_today[0];
		}
		else {
			$daily_widget_data['count_order_fulfillment_today'] = 0;
		}

		$query_order_fulfillment_yest   = "SELECT count(id) FROM {$wpdb->prefix}posts AS posts
		                                	$terms_post_join
		                                WHERE $cond_terms_post
		                                    AND (posts.post_modified LIKE '$yesterday%'
		                                        OR posts.post_date LIKE '$yesterday%')";
		          
		$result_order_fulfillment_yest   = $wpdb->get_col($query_order_fulfillment_yest);
		$rows_order_fulfillment_yest    = $wpdb->num_rows;

		if($rows_order_fulfillment_yest > 0) {
			$daily_widget_data['count_order_fulfillment_yest'] = $result_order_fulfillment_yest[0];
		}
		else {
			$daily_widget_data['count_order_fulfillment_yest'] = 0;
		}

		$daily_order_fulfillment_kpi = sr_get_daily_kpi_data_formatted('order_fulfillment',$daily_widget_data['count_order_fulfillment_today'],$daily_widget_data['count_order_fulfillment_yest'],$_POST);

		$daily_widget_data = array_merge($daily_widget_data, $daily_sales_kpi, $month_to_date_sales_kpi, $avg_sales_per_day_kpi, $forcasted_sales_kpi, $daily_cust_kpi, $daily_refund_kpi, $daily_order_fulfillment_kpi);

		return $daily_widget_data;
	}

	if (isset ( $_POST ['cmd'] ) && ($_POST ['cmd'] == 'daily')) {

		while(ob_get_contents()) {
         	   ob_clean();
		}

		echo json_encode (sr_get_daily_kpi_data());
	}

	if (isset ( $_POST ['cmd'] ) && (($_POST ['cmd'] == 'product_detailed_view_track') )) {
		$result = wp_remote_post('http://www.storeapps.org/?utm_source=SR&utm_medium=pro&utm_campaign=product_detailed_view');
	}


	if (isset ( $_POST ['cmd'] ) && (($_POST ['cmd'] == 'monthly') )) {

		$sr_currency_symbol = isset($_POST['SR_CURRENCY_SYMBOL']) ? $_POST['SR_CURRENCY_SYMBOL'] : '';
	    $sr_decimal_places = isset($_POST['SR_DECIMAL_PLACES']) ? $_POST['SR_DECIMAL_PLACES'] : '';

		//Get the converted dates    
	    $converted_dates = date_timezone_conversion($_POST);

	    $start_date = $converted_dates ['start_date'];
	    $end_date = $converted_dates ['end_date'];

	    $strtotime_start = strtotime($start_date);
	    $strtotime_end = strtotime($end_date);
	   
	    $diff_dates = (strtotime($_POST['end_date']) - strtotime($_POST['start_date']))/(60*60*24); 

	    if ($diff_dates > 0) {
	        $comparison_end_date = date('Y-m-d H:i:s', strtotime($start_date .' -1 day'));
	        $comparison_start_date = date('Y-m-d H:i:s', strtotime($comparison_end_date) - ($diff_dates*60*60*24));
	    }
	    else {
	        $comparison_start_date = date('Y-m-d', strtotime($_POST['start_date'] .' -1 day'));
	        $comparison_end_date = $comparison_start_date . " 23:59:59";  
	        $comparison_start_date .=" 00:00:00";  
	    }
	    
	    $comparison_diff_dates = (strtotime($comparison_end_date) - strtotime($comparison_start_date))/(60*60*24);

	    $actual_cumm_sales = sr_get_sales ($start_date,$end_date,$diff_dates,$_POST);

	    if (isset($_POST['option'])) { // Condition to handle the change of graph on option select
	        $encoded['graph_data'] = $actual_cumm_sales[0];
	        $encoded['cumm_sales_min_date'] = $actual_cumm_sales[1];
	        $encoded['cumm_sales_max_date'] = $actual_cumm_sales[2];
	    } 
	    else {
	        $comparison_cumm_sales = sr_get_sales ($comparison_start_date,$comparison_end_date,$comparison_diff_dates,$_POST);

	        //Code for handling the Monthly Sales Widget

	        if ($comparison_cumm_sales[1] < $actual_cumm_sales[1]) {
	            $imgurl_cumm_sales = $_POST['SR_IMG_UP_GREEN'];
	        }
	        else {
	            $imgurl_cumm_sales = $_POST['SR_IMG_DOWN_RED'];
	        }

	        if ($comparison_cumm_sales[1] == 0) {
	            $diff_cumm_sales = round($actual_cumm_sales[1],get_option( 'woocommerce_price_num_decimals' ));
	        }
	        else {
	            $diff_cumm_sales = round(((($actual_cumm_sales[1] - $comparison_cumm_sales[1])/$comparison_cumm_sales[1]) * 100),get_option( 'woocommerce_price_num_decimals' ));
	        }
	        
	        //Code for handling the Avg Order Total Widget

	        if ($comparison_cumm_sales[4] < $actual_cumm_sales[4]) {
	            $imgurl_cumm_avg_order_tot = $_POST['SR_IMG_UP_GREEN'];
	        }
	        else {
	            $imgurl_cumm_avg_order_tot = $_POST['SR_IMG_DOWN_RED'];
	        }
	        if ($comparison_cumm_sales[4] == 0) {
	            $diff_cumm_avg_order_tot = round($actual_cumm_sales[4],get_option( 'woocommerce_price_num_decimals' ));
	        }
	        else {
	            $diff_cumm_avg_order_tot = round(((($actual_cumm_sales[4] - $comparison_cumm_sales[4])/$comparison_cumm_sales[4]) * 100),get_option( 'woocommerce_price_num_decimals' ));
	        }

	        //Code for handling the Avg Order Items Per Customer Widget

	        if ($comparison_cumm_sales[5] < $actual_cumm_sales[5]) {
	            $imgurl_cumm_avg_order_items = $_POST['SR_IMG_UP_GREEN'];
	        }
	        else {
	            $imgurl_cumm_avg_order_items = $_POST['SR_IMG_DOWN_RED'];
	        }
	        if ($comparison_cumm_sales[5] == 0) {
	            $diff_cumm_avg_order_items = round($actual_cumm_sales[5],get_option( 'woocommerce_price_num_decimals' ));
	        }
	        else {
	            $diff_cumm_avg_order_items = $actual_cumm_sales[5] - $comparison_cumm_sales[5];
	        }

	        //Code for handling the Cumm Discount Sales Widget

	        if ($comparison_cumm_sales[8] < $actual_cumm_sales[8]) {
	            $imgurl_cumm_discount_sales = $_POST['SR_IMG_UP_GREEN'];
	        }
	        else {
	            $imgurl_cumm_discount_sales = $_POST['SR_IMG_DOWN_RED'];
	        }

	        if ($comparison_cumm_sales[8] == 0) {
	            $diff_discount_cumm_sales = round($actual_cumm_sales[8],get_option( 'woocommerce_price_num_decimals' ));
	        }
	        else {
	            $diff_discount_cumm_sales = round(((($actual_cumm_sales[8] - $comparison_cumm_sales[8])/$comparison_cumm_sales[8]) * 100),get_option( 'woocommerce_price_num_decimals' ));
	        }


	        //Code for handling the % Order Containing Coupons Widget

	        if ($comparison_cumm_sales[11] < $actual_cumm_sales[11]) {
	            $imgurl_cumm_per_order_coupons = $_POST['SR_IMG_UP_GREEN'];
	        }
	        else {
	            $imgurl_cumm_per_order_coupons = $_POST['SR_IMG_DOWN_RED'];
	        }
	        if ($comparison_cumm_sales[11] == 0) {
	            $diff_cumm_per_order_coupons = round($actual_cumm_sales[11],get_option( 'woocommerce_price_num_decimals' ));
	        }
	        else {
	            $diff_cumm_per_order_coupons = $actual_cumm_sales[11] - $comparison_cumm_sales[11];
	        }


	        // //Code for handling the Cumm Abandonment Rate Widget

	        if ($comparison_cumm_sales[15] < $actual_cumm_sales[15]) {
	            $imgurl_cumm_abandonment_rate = $_POST['SR_IMG_UP_GREEN'];
	        }
	        else {
	            $imgurl_cumm_abandonment_rate = $_POST['SR_IMG_DOWN_RED'];
	        }
	        if ($comparison_cumm_sales[15] == 0) {
	            $diff_cumm_abandonment_rate = round($actual_cumm_sales[15],get_option( 'woocommerce_price_num_decimals' ));
	        }
	        else {
	            $diff_cumm_abandonment_rate = $actual_cumm_sales[15] - $comparison_cumm_sales[15];
	        }

	        $encoded['result_monthly_sales'] = $actual_cumm_sales[0];
	        $encoded['total_monthly_sales'] = $sr_currency_symbol . sr_number_format($actual_cumm_sales[1],$sr_decimal_places);
	        $encoded['img_cumm_sales'] = $imgurl_cumm_sales;
	        $encoded['diff_cumm_sales'] = sr_number_format(abs($diff_cumm_sales),$sr_decimal_places);
	        $encoded['currency_symbol'] = $sr_currency_symbol;
	        $encoded['decimal_places'] = $sr_decimal_places;
	        $encoded['top_prod_data'] = $actual_cumm_sales[2];
	        $encoded['top_cust_data'] = $actual_cumm_sales[3];


	        $encoded['avg_order_total'] = $sr_currency_symbol . sr_number_format($actual_cumm_sales[4],$sr_decimal_places);
	        $encoded['img_cumm_avg_order_tot'] = $imgurl_cumm_avg_order_tot;
	        $encoded['diff_cumm_avg_order_tot'] = sr_number_format(abs($diff_cumm_avg_order_tot),$sr_decimal_places);


	        $encoded['avg_order_items'] = sr_number_format($actual_cumm_sales[5],$sr_decimal_places);
	        $encoded['img_cumm_avg_order_items'] = $imgurl_cumm_avg_order_items;
	        $encoded['diff_cumm_avg_order_items'] = sr_number_format(abs($diff_cumm_avg_order_items),$sr_decimal_places);

	        $encoded['cumm_max_sales'] = $actual_cumm_sales[6];

	        $encoded['graph_cumm_discount_sales'] = $actual_cumm_sales[7];
	        $encoded['cumm_discount_sales_total'] = $sr_currency_symbol . sr_number_format($actual_cumm_sales[8],$sr_decimal_places);
	        $encoded['img_cumm_discount_sales_total'] = $imgurl_cumm_avg_order_tot;
	        $encoded['diff_cumm_discount_sales_total'] = sr_number_format(abs($diff_cumm_avg_order_tot),$sr_decimal_places);

	        $encoded['top_coupon_data'] = $actual_cumm_sales[9];

	        $encoded['cumm_max_discount_total'] = $actual_cumm_sales[10];

	        $encoded['cumm_per_order_coupons'] = sr_number_format($actual_cumm_sales[11],$sr_decimal_places);
	        $encoded['img_cumm_per_order_coupons'] = $imgurl_cumm_per_order_coupons;
	        $encoded['diff_cumm_per_order_coupons'] = sr_number_format(abs($diff_cumm_per_order_coupons),$sr_decimal_places);

	        $encoded['top_gateway_data'] = $actual_cumm_sales[12];

	        $encoded['cumm_taxes'] = $actual_cumm_sales[13];

	        $encoded['cumm_top_abandoned_products'] = $actual_cumm_sales[14];

	        if ($actual_cumm_sales[15] != "") {
	        	$encoded['cumm_abandoned_rate'] = sr_number_format($actual_cumm_sales[15],$sr_decimal_places);
		        $encoded['img_cumm_abandoned_rate'] = $imgurl_cumm_abandonment_rate;
		        $encoded['diff_cumm_abandoned_rate'] = sr_number_format(abs($diff_cumm_abandonment_rate),$sr_decimal_places);	
	        } else {
	        	$encoded['cumm_abandoned_rate'] = '';
		        $encoded['img_cumm_abandoned_rate'] = '';
		        $encoded['diff_cumm_abandoned_rate'] = '';
	        }

	        
	        $encoded['cumm_sales_funnel'] = $actual_cumm_sales[16];

	        $encoded['cumm_sales_billing_country_values'] = $actual_cumm_sales[17];
	        $encoded['cumm_sales_billing_country_tooltip'] = $actual_cumm_sales[18];

	        $encoded['top_shipping_method_data'] = $actual_cumm_sales[19];

	        $encoded['cumm_sales_min_date'] = $actual_cumm_sales[20];
	        $encoded['cumm_sales_max_date'] = $actual_cumm_sales[21];

	        $encoded['siteurl'] = get_option('siteurl');
	        
	    }
	    
	    if ($diff_dates > 0 && $diff_dates <= 30) {
	        $encoded['tick_format'] = "%#d/%b/%Y";
	    }
	    else if ($diff_dates > 30 && $diff_dates <= 365) {
	        $encoded['tick_format'] = "%b";
	    }
	    else if ($diff_dates > 365) {
	        $encoded['tick_format'] = "%Y";
	    }
	    else {
	        $encoded['tick_format'] = "%H:%M:%S";
	    }

	    while(ob_get_contents()) {
         	   ob_clean();
		}

	    echo json_encode ( $encoded );
	    unset($encoded);
	    
	}

//=================================
//OLD SR CODE
//=================================

	$del = 3;
	$result  = array ();
	$encoded = array ();
	$months  = array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );
	$cat_rev = array ();

	global $wpdb;

	if (isset ( $_GET ['start'] ))
		$offset = $_GET ['start'];
	else
		$offset = 0;

	if (isset ( $_GET ['limit'] ))
		$limit = $_GET ['limit'];

	// For pro version check if the required file exists
	// if (file_exists ( '../pro/sr-woo.php' )){
	// 	define ( 'SRPRO', true );
	// } else {
	// 	define ( 'SRPRO', false );
	// }

	if (!function_exists('sr_arr_init')) {
		function sr_arr_init($arr_start, $arr_end, $category = '') {
			global $cat_rev, $months, $order_arr;

			for($i = $arr_start; $i <= $arr_end; $i ++) {
				$key = ($category == 'month') ? $months [$i - 1] : $i;
				$cat_rev [$key] = 0;
			}
		}	
	}

	function get_grid_data( $select, $from, $where, $where_date, $group_by, $search_condn, $order_by ) {
		global $wpdb, $cat_rev, $months, $order_arr;
			
			$woo_default_image = WP_PLUGIN_URL . '/smart-reporter-for-wp-e-commerce/resources/themes/images/woo_default_image.png';
			$query = "$select $from $where $where_date $group_by $search_condn $order_by ";
			$results 	= $wpdb->get_results ( $query, 'ARRAY_A' );

			$num_rows   = $wpdb->num_rows;
			$no_records = $num_rows;

			if ($no_records == 0) {
				$encoded ['gridItems'] 		= '';
				$encoded ['gridTotalCount'] = '';
				$encoded ['msg']			= 'No records found';
			} else {
				$count = 0 ;
				$grid_data = array();
				$grid_data [$count] ['sales']    = '';
				$grid_data [$count] ['discount'] = '';
				$grid_data [$count] ['products'] = 'All Products';
				$grid_data [$count] ['period']   = 'selected period';
				$grid_data [$count] ['category'] = 'All Categories';
				$grid_data [$count] ['id'] 	     = '';
				$grid_data [$count] ['quantity'] = 0;
				$grid_data [$count] ['image'] = $woo_default_image;		//../wp-content/plugins/wp-e-commerce/wpsc-theme/wpsc-images/noimage.png


				//Code to get the thumnail_id

				$query_thumbnail_id = "SELECT postmeta.post_id AS id,
										   postmeta.meta_value AS thumbnail
									FROM {$wpdb->prefix}postmeta AS postmeta
										JOIN {$wpdb->prefix}posts AS posts ON (postmeta.post_id = posts.id AND postmeta.meta_key = '_thumbnail_id')
									WHERE posts.post_type IN ('product', 'product_variation')";
				$results_thumnail_id = $wpdb->get_results($query_thumbnail_id, 'ARRAY_A');
				$rows_thumbnail_id = $wpdb->num_rows;

				$prod_thumnail_ids = array();

				if ( $rows_thumbnail_id > 0 ) {
					foreach ( $results_thumnail_id as $result_thumnail_id ) {
						$prod_thumnail_ids [$result_thumnail_id['id']] = $result_thumnail_id['thumbnail'];
					}
				}

				foreach ( $results as $result ) {
					$grid_data [$count] ['quantity'] = $grid_data[$count] ['quantity'] + $result ['quantity'];
					$grid_data [$count] ['sales'] = $grid_data[$count] ['sales'] + $result ['sales'];
					$grid_data [$count] ['discount'] = $grid_data[$count] ['discount'] + $result ['discount'];
				}
				$count++;
				
				foreach ( $results as $result ) {
					$grid_data [$count] ['products'] = $result ['products'];
					$grid_data [$count] ['period']   = (!empty($result ['period'])) ? $result ['period'] : '';
					$grid_data [$count] ['sales']    = $result ['sales'];
					$grid_data [$count] ['discount'] = $result ['discount'];
					$grid_data [$count] ['category'] = $result ['category'];
					$grid_data [$count] ['id'] 	 	 = $result ['id'];
					$grid_data [$count] ['quantity'] = $result ['quantity'];
					// $thumbnail = isset( $result ['thumbnail'] ) ? wp_get_attachment_image_src( $result ['thumbnail'], 'admin-product-thumbnails' ) : '';
					$thumbnail = !empty( $prod_thumnail_ids [$result ['id']] ) ? wp_get_attachment_image_src( $prod_thumnail_ids [$result ['id']], 'admin-product-thumbnails' ) : '';
					$grid_data [$count] ['image']    = ( !empty($thumbnail[0]) && $thumbnail[0] != '' ) ? $thumbnail[0] : $woo_default_image;
					$count++;
				}
					
				$encoded ['gridItems']      = $grid_data;
				$encoded ['period_div'] 	= (!empty($parts ['category'])) ? $parts ['category'] : '';
				$encoded ['gridTotalCount'] = count($grid_data);
			}

		return $encoded;
	}

	function get_graph_data( $product_id, $where_date, $parts ) {
		global $wpdb, $cat_rev, $months, $order_arr;
		
	        $cat_rev1 = array();
		
		$encoded = get_last_few_order_details( $product_id, $where_date );

	                $time = '';
	                if(isset($parts['day']) && $parts['day'] == 'today' ) {
	                    $time = ",DATE_FORMAT(max(posts.`post_date`), '%H:%i:%s') AS time";
	                    for ($i=0;$i<24;$i++) {
	                        $cat_rev1[$i] = 1;
	                    }
	                }
	        
	                
			$select  = "SELECT SUM( order_item.sales ) AS sales,
						DATE_FORMAT(posts.`post_date`, '{$parts ['abbr']}') AS period
	                                        $time    
					   ";
			
			$from = " FROM {$wpdb->prefix}sr_woo_order_items AS order_item
				  	  LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = order_item.order_id )
					";
			
			$where = ' WHERE 1 ';
			
			$group_by = " GROUP BY period";
		
			if ( isset ( $product_id ) && $product_id != 0 ) {
				$where 	   .= " AND order_item.product_id = $product_id ";
			}
			
			$query = "$select $from $where $where_date $group_by ";
			
			$results 	= $wpdb->get_results ( $query, 'ARRAY_A' );
			$num_rows   = $wpdb->num_rows;
			$no_records = ($num_rows != 0) ? count ( $cat_rev ) : 0;

			if ($no_records != 0) {
				foreach ( $results as $result ) { // put within condition
					$cat_rev [$result['period']]  = $result ['sales'];
	                                if(isset($parts['day']) && $parts['day'] == 'today' ) {
	                                    $cat_rev1 [$result['period']]  = $result ['time'];
				}
	                        }
				
	                        $i = 0;
	                        
				foreach ( $cat_rev as $mon => $rev ) {
					$record ['period'] = $mon;
					$record ['sales'] = $rev;
	                                
	                                if(isset($parts['day']) && $parts['day'] == 'today' ) {
	                                    $record ['time'] = $cat_rev1[$i];
	                                }
					$records [] = $record;
	                                $i++;
				}
			}
			
			if ($no_records == 0) {
				$encoded ['graph'] ['items'] = '';
				$encoded ['graph'] ['totalCount'] = 0;
			} else {
				$encoded ['graph'] ['items'] = $records;
				$encoded ['graph'] ['totalCount'] = count($cat_rev);
			}
		
		return $encoded;
	}

	function get_last_few_order_details( $product_id, $where_date ) {
		global $wpdb, $cat_rev, $months, $order_arr;
			
			$select = "SELECT order_item.order_id AS order_id,
							  posts.post_date AS date,
							  GROUP_CONCAT( distinct postmeta.meta_value
									ORDER BY postmeta.meta_id 
									SEPARATOR ' ' ) AS cname,
							  ( SELECT post_meta.meta_value FROM {$wpdb->prefix}postmeta AS post_meta WHERE post_meta.post_id = order_item.order_id AND post_meta.meta_key = '_order_total' ) AS totalprice
					  ";
			
			$from = " FROM {$wpdb->prefix}sr_woo_order_items AS order_item
				  	  LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = order_item.order_id )
				  	  LEFT JOIN {$wpdb->prefix}postmeta AS postmeta ON ( order_item.order_id = postmeta.post_id AND postmeta.meta_key IN ( '_billing_first_name', '_billing_last_name' ) )
					";
			
			$where = ' WHERE 1 ';
			
			$order_by = "ORDER BY date DESC";
			
			$limit = "limit 0,5";
			
			if ( isset( $product_id ) ) $group_by  = "GROUP BY order_id";
			
			if ( isset ( $product_id ) && $product_id != 0 ) {
				$where 	   .= " AND order_item.product_id = $product_id ";
			}
			
			$query = "$select $from $where $where_date $group_by $order_by $limit";
			$results 	= $wpdb->get_results ( $query, 'ARRAY_A' );
			$num_rows   = $wpdb->num_rows;
			$no_records = $num_rows;
				
			if ($no_records == 0) {
				$encoded ['orderDetails'] ['order'] 		= '';
				$encoded ['orderDetails'] ['orderTotalCount'] = 0;
			}  else {			
				$cnt = 0;
				$order_data = array();
				foreach ( $results as $result ) { // put within condition	
					$order_data [$cnt] ['purchaseid'] = $result ['order_id'];
					$order_data [$cnt] ['date']       = date( "d-M-Y",strtotime( $result ['date'] ) ); 
					$order_data [$cnt] ['totalprice'] = woocommerce_price( $result ['totalprice'] );
					$order_data [$cnt] ['cname']      = $result ['cname'];
					$orders [] = $order_data [$cnt];				
					$cnt++;
				}	
			
				$encoded ['orderDetails'] ['order'] = $orders;
				$encoded ['orderDetails'] ['orderTotalCount'] = count($orders);
			}
			
		return $encoded;
	}

	if (isset ( $_GET ['cmd'] ) && (($_GET ['cmd'] == 'getData') || ($_GET ['cmd'] == 'gridGetData'))) {
		
	        if ( defined('SRPRO') && SRPRO == true ) {
	            if ( SR_WPSC_RUNNING === true ) {
			if ( file_exists ( SR_PLUGIN_DIR_ABSPATH. '/pro/sr.php' ) ) include( SR_PLUGIN_DIR_ABSPATH. '/pro/sr.php' );
	            } else {
	                if ( file_exists ( SR_PLUGIN_DIR_ABSPATH. '/pro/sr-woo.php' ) ) include_once( SR_PLUGIN_DIR_ABSPATH. '/pro/sr-woo.php' );
	            }
	        }
	    
		if (isset ( $_GET ['fromDate'] )) {
			$from ['date'] = strtotime ( $_GET ['fromDate'] );
			$to ['date'] = strtotime ( $_GET ['toDate'] );
		 
			if ($to ['date'] == 0) {
				$to ['date'] = strtotime ( 'today' );
			}
			// move it forward till the end of day
			$to ['date'] += 86399;

			// Swap the two dates if to_date is less than from_date
			if ($to ['date'] < $from ['date']) {
				$temp = $to ['date'];
				$to ['date'] = $from ['date'];
				$from ['date'] = $temp;
			}
			// date('Y-m-d H:i:s',(int)strtotime($_POST ['fromDate']))		$from ['date']		$to['date']
			if ( defined('SRPRO') && SRPRO == true ){
				$where_date = " AND (posts.`post_date` between '" . date('Y-m-d H:i:s',$from ['date']) . "' AND '" . date('Y-m-d H:i:s',$to['date']) . "')";
			}else{
				$diff = 86400 * 7;
				if ( (( $from ['date'] - $to ['date'] ) <= $diff ) )
				$where_date = " AND (posts.`post_date` between '" . date('Y-m-d H:i:s',$from ['date']) . "' AND '" . date('Y-m-d H:i:s',$to['date']) . "')";
			}

			//BOF bar graph calc

			$frm ['yr'] = date ( "Y", $from ['date'] );
			$to ['yr'] = date ( "Y", $to ['date'] );

			$frm ['mon'] = date ( "n", $from ['date'] );
			$to ['mon'] = date ( "n", $to ['date'] );

			$frm ['week'] = date ( "W", $from ['date'] );
			$to ['week'] = date ( "W", $to ['date'] );

			$frm ['day'] = date ( "j", $from ['date'] );
			$to ['day'] = date ( "j", $to ['date'] );

			$parts ['category'] = '';
			$parts ['no'] = 0;

			if ($frm ['yr'] == $to ['yr']) {
				if ($frm ['mon'] == $to ['mon']) {

					if ($frm ['week'] == $to ['week']) {
						if ($frm ['day'] == $to ['day']) {
							$diff = $to ['date'] - $from ['date'];
							$parts ['category'] = 'hr';
							$parts ['no'] = 23;
							$parts ['abbr'] = '%k';
	                                                $parts ['day'] = 'today';

							sr_arr_init ( 0, $parts ['no'],'hr' );
						} else {
							$parts ['category'] = 'day';
							$parts ['no'] = date ( 't', $from ['date'] );
							$parts ['abbr'] = '%e';

							sr_arr_init ( 1, $parts ['no'] );
						}
					} else {
						$parts ['category'] = 'day';
						$parts ['no'] = date ( 't', $from ['date'] );
						$parts ['abbr'] = '%e';

						sr_arr_init ( 1, $parts ['no'] );
					}
				} else {
					$parts ['category'] = 'month';
					$parts ['no'] = $to ['mon'] - $frm ['mon'];
					$parts ['abbr'] = '%b';

					sr_arr_init ( $frm ['mon'], $to ['mon'], $parts ['category'] );
				}
			} else {
				$parts ['category'] = 'year';
				$parts ['no'] = $to ['yr'] - $frm ['yr'];
				$parts ['abbr'] = '%Y';

				sr_arr_init ( $frm ['yr'], $to ['yr'] );
			}
			// EOF
		}
		
		$static_select = "SELECT order_item.product_id AS id,
						 order_item.product_name AS products,
						 prod_categories.category AS category,
						 SUM( order_item.quantity ) AS quantity,
						 SUM( order_item.sales ) AS sales,
						 SUM( order_item.discount ) AS discount
						";
			
		$from = " FROM {$wpdb->prefix}sr_woo_order_items AS order_item
				  LEFT JOIN {$wpdb->prefix}posts AS products ON ( products.id = order_item.product_id )
				  LEFT JOIN ( SELECT GROUP_CONCAT(wt.name SEPARATOR ', ') AS category, wtr.object_id
						FROM  {$wpdb->prefix}term_relationships AS wtr  	 
						JOIN {$wpdb->prefix}term_taxonomy AS wtt ON (wtr.term_taxonomy_id = wtt.term_taxonomy_id and taxonomy = 'product_cat')
						JOIN {$wpdb->prefix}terms AS wt ON (wtt.term_id = wt.term_id)
						GROUP BY wtr.object_id) AS prod_categories on (products.id = prod_categories.object_id OR products.post_parent = prod_categories.object_id)
				  LEFT JOIN {$wpdb->prefix}posts as posts ON ( posts.ID = order_item.order_id )
				  ";
			
		$where = " WHERE products.post_type IN ('product', 'product_variation') ";
			
		$group_by = " GROUP BY order_item.product_id ";
			
		$order_by = " ORDER BY sales DESC ";
		
		$search_condn = '';

		if (isset ( $_GET ['searchText'] ) && $_GET ['searchText'] != '') {
			$search_on = $wpdb->_real_escape ( trim ( $_GET ['searchText'] ) );
			$search_ons = explode( ' ', $search_on );
			if ( is_array( $search_ons ) ) {	
				$search_condn = " HAVING ";
				foreach ( $search_ons as $search_on ) {
					$search_condn .= " order_item.product_name LIKE '%$search_on%' 
									   OR prod_categories.category LIKE '%$search_on%' 
									   OR order_item.product_id LIKE '%$search_on%'
									   OR";
				}
				$search_condn = substr( $search_condn, 0, -2 );
			} else {
				$search_condn = " HAVING order_item.product_name LIKE '%$search_on%' 
									   OR prod_categories.category LIKE '%$search_on%' 
									   OR order_item.product_id LIKE '%$search_on%'
							";
			}
			
		}
		
		if ($_GET ['cmd'] == 'gridGetData') {
			
			$encoded = get_grid_data( $static_select, $from, $where, $where_date, $group_by, $search_condn, $order_by );
			
		} else if ($_GET ['cmd'] == 'getData') {

			$encoded = get_graph_data( $_GET ['id'], $where_date, $parts );
			
		}
		while(ob_get_contents()) {
         	   ob_clean();
		}
		
		echo json_encode ( $encoded );
	}

// ob_end_flush();

?>
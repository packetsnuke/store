<?php
global $wpdb;
if ( empty( $wpdb ) || !is_object( $wpdb ) ) {
    if ( ! defined('ABSPATH') ) {
        include_once ('../../../../wp-load.php');
    }
    require_once ABSPATH . 'wp-includes/wp-db.php';
}


$today_arr 		    = getdate();
$today_from_date    = date("Y-m-d H:i:s", mktime(0,0,0,$today_arr['mon'],$today_arr['mday'],$today_arr['year']) );
$today_to_date      = date("Y-m-d H:i:s", mktime(23,59,59,$today_arr['mon'],$today_arr['mday'],$today_arr['year']) );
$this_month_start   = date("Y-m-d H:i:s", mktime(0,0,0,$today_arr['mon'],1,$today_arr['year']));
$days_in_this_month = date('t', mktime(0, 0, 0, $today_arr['mon'], 1, $today_arr['year']));

// ('completed','processing','on-hold','pending')

//Query to get the relevant order ids
$query_terms     = "SELECT id FROM {$wpdb->prefix}posts AS posts
                        JOIN {$wpdb->prefix}term_relationships AS term_relationships 
                                                    ON term_relationships.object_id = posts.ID 
                                    JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy 
                                                    ON term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id 
                                    JOIN {$wpdb->prefix}terms AS terms 
                                                    ON term_taxonomy.term_id = terms.term_id
                    WHERE terms.name IN ('completed','processing','on-hold')
                        AND posts.post_status IN ('publish')";
          
$terms_post      = $wpdb->get_col($query_terms);
$rows_terms_post = $wpdb->num_rows;

if ($rows_terms_post > 0) {
    $terms_post = implode(",",$terms_post);
    $cond = 'AND posts.ID IN ('.$terms_post.')';
} else {
	$cond = '';
}

// $query   	  = "SELECT SUM( order_item.sales ) AS todays_sales 
// 					FROM `{$wpdb->prefix}sr_woo_order_items` AS order_item
// 					LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = order_item.order_id )
// 					WHERE posts.post_date between '$today_from_date' AND '$today_to_date'";
$query 		  = "SELECT SUM( postmeta.meta_value ) AS todays_sales 
                    FROM `{$wpdb->prefix}postmeta` AS postmeta
                    LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = postmeta.post_id )
                    WHERE postmeta.meta_key IN ('_order_total')
                        AND posts.post_date between '$today_from_date' AND '$today_to_date'
                        $cond";
$results 	  = $wpdb->get_results ( $query, 'ARRAY_A' );
$todays_sales = ($results[0]['todays_sales']) ? ($results[0]['todays_sales']) : 0;

// $query   			 = "SELECT SUM( order_item.sales ) AS month_to_date 
// 						FROM `{$wpdb->prefix}sr_woo_order_items` AS order_item
// 						LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = order_item.order_id )
// 						WHERE posts.post_date between '$this_month_start' AND '$today_to_date'";

$query               = "SELECT SUM( postmeta.meta_value ) AS month_to_date 
	                    FROM `{$wpdb->prefix}postmeta` AS postmeta
	                    LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = postmeta.post_id )
	                    WHERE postmeta.meta_key IN ('_order_total')
                        	AND posts.post_date between '$this_month_start' AND '$today_to_date'
                        	$cond";
$results 			 = $wpdb->get_results ( $query, 'ARRAY_A' );
$month_to_date_sales = $results[0]['month_to_date'] ? $results[0]['month_to_date'] : 0;

$avg_sales_per_day  = round(($results[0]['month_to_date']/$today_arr['mday']),2);
$forcasted_sales 	= $avg_sales_per_day * $days_in_this_month;


?>

<html>
<head>
</head>
<body>

	<div id="current_summary">
		<div class="row">
		<div>
			<div class="one_half first">
				<div class="block sales">
					Today's Sales
					<p class="value">
						<?php echo woocommerce_price($todays_sales) ?>
					</p>
				</div>
			</div>
		</div>
		<div>
			<div class="one_half second">
				<div class="block sales">
					Month To Date Sales
					<p class="value">
						<?php echo woocommerce_price($month_to_date_sales) ?>
					</p>
				</div>
			</div>
		</div>
		<div>
			<div class="one_half third">
				<div class="block sales">
					Average Sales/Day
					<p class="value">
						<?php echo woocommerce_price($avg_sales_per_day) ?>
					</p>
				</div>
			</div>
		</div>
		<div>
			<div class="one_half fourth">
				<div class="block sales">
					Forecasted Sales
					<p class="value">
						<?php echo woocommerce_price($forcasted_sales) ?>
					</p>
				</div>
			</div>
		</div>
		</div>
	</div>
</body>
</html>

<?php
global $wpdb;
if ( empty( $wpdb ) || !is_object( $wpdb ) ) {
    if ( ! defined('ABSPATH') ) {
        include_once ('../../../../wp-load.php');
    }
    require_once ABSPATH . 'wp-includes/wp-db.php';
}

$today_arr 		    = getdate();
$today_from_date    = strtotime(date("F j, Y"));
$today_to_date      = $today_from_date + 86399  ;
$this_month_start   = mktime(0,0,0,$today_arr['mon'],1,$today_arr['year']);
$days_in_this_month = date('t', mktime(0, 0, 0, $today_arr['mon'], 1, $today_arr['year']));

$query   	  = "SELECT sum(totalprice) as todays_sales  FROM `{$wpdb->prefix}wpsc_purchase_logs` where processed IN (2,3,4,5) AND date between $today_from_date AND $today_to_date";
$results 	  = $wpdb->get_results ( $query, 'ARRAY_A' );
$todays_sales = ($results[0]['todays_sales']) ? ($results[0]['todays_sales']) : 0;

$query   			 = "SELECT sum(totalprice) as month_to_date FROM `{$wpdb->prefix}wpsc_purchase_logs` where processed IN (2,3,4,5) AND date between $this_month_start AND $today_to_date";
$results 			 = $wpdb->get_results ( $query, 'ARRAY_A' );
$month_to_date_sales = $results[0]['month_to_date'] ? $results[0]['month_to_date'] : 0;

$avg_sales_per_day  = round(($results[0]['month_to_date']/$today_arr['mday']),2);
$forcasted_sales 	= $avg_sales_per_day * $days_in_this_month;
?>
<div id="current_summary">
        <div class="row">
        <div>
                <div class="one_half first">
                        <div class="block sales">
                                Today's Sales
                                <p class="value">
                                        <?php echo wpsc_currency_display($todays_sales) ?>
                                </p>
                        </div>
                </div>
        </div>
        <div>
                <div class="one_half second">
                        <div class="block sales">
                                Month To Date Sales
                                <p class="value">
                                        <?php echo wpsc_currency_display($month_to_date_sales) ?>
                                </p>
                        </div>
                </div>
        </div>
        <div>
                <div class="one_half third">
                        <div class="block sales">
                                Average Sales/Day
                                <p class="value">
                                        <?php echo wpsc_currency_display($avg_sales_per_day) ?>
                                </p>
                        </div>
                </div>
        </div>
        <div>
                <div class="one_half fourth">
                        <div class="block sales">
                                Forecasted Sales
                                <p class="value">
                                        <?php echo wpsc_currency_display($forcasted_sales) ?>
                                </p>
                        </div>
                </div>
        </div>
        </div>
</div>
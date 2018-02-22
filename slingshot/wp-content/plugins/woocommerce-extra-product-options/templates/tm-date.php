<?php
/**
 * The template for displaying the date element for the builder mode
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-date.php
 *
 * NOTE that we may need to update template files and you
 * (the plugin or theme developer) will need to copy the new files
 * to your theme or plugin to maintain compatibility.
 *
 * @author 		themeComplete
 * @package 	WooCommerce Extra Product Options/Templates
 * @version 	4.0
 */
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}
if ( !isset( $fieldtype ) ) {
	$fieldtype = "tmcp-field";
}
?>
<li class="tmcp-field-wrap">
	<?php include(TM_EPO_TEMPLATE_PATH .'_quantity_start.php'); ?>
	<?php

	$date_format = 'dd/mm/yy';
	$date_placeholder = 'dd/mm/yyyy';
	$date_mask = '00/00/0000';

	switch ( $format ) {
		case "0":
			$date_format = 'dd/mm/yy';
			$date_placeholder = 'dd/mm/yyyy';
			$date_mask = '00/00/0000';
			break;
		case "1":
			$date_format = 'mm/dd/yy';
			$date_placeholder = 'mm/dd/yyyy';
			$date_mask = '00/00/0000';
			break;
		case "2":
			$date_format = 'dd.mm.yy';
			$date_placeholder = 'dd.mm.yyyy';
			$date_mask = '00.00.0000';
			break;
		case "3":
			$date_format = 'mm.dd.yy';
			$date_placeholder = 'mm.dd.yyyy';
			$date_mask = '00.00.0000';
			break;
		case "4":
			$date_format = 'dd-mm-yy';
			$date_placeholder = 'dd-mm-yyyy';
			$date_mask = '00-00-0000';
			break;
		case "5":
			$date_format = 'mm-dd-yy';
			$date_placeholder = 'mm-dd-yyyy';
			$date_mask = '00-00-0000';
			break;
	}

	if ( apply_filters( 'wc_epo_display_rtl', is_rtl() ) ) {
		$date_format = strrev( $date_format );
		$date_placeholder = strrev( $date_placeholder );
		$date_mask = strrev( $date_mask );
	}
	if ( $style != "picker" ) {
		if ( isset( $_GET[ $name ] ) && empty( $_POST ) ) {
			$value = str_replace( ".", "-", $_GET[ $name ] );
			$value = str_replace( "/", "-", $value );
			$value = explode( "-", $value );
			switch ( $format ) {
				case "0":
				case "2":
				case "4":
					$_POST[ $name . "_day" ] = $value[0];
					$_POST[ $name . "_month" ] = $value[1];
					$_POST[ $name . "_year" ] = $value[2];
					break;
				case "1":
				case "3":
				case "5":
					$_POST[ $name . "_day" ] = $value[1];
					$_POST[ $name . "_month" ] = $value[0];
					$_POST[ $name . "_year" ] = $value[2];
					break;
			}
		}

		$selectArray = array( "class" => "tmcp-date-select tmcp-date-day", "id" => $id . "_day", "name" => $name . "_day", "extra" => "data-tm-date='" . $id . "'" );
		$select_options = array();
		$tranlation_day = (!empty( $tranlation_day )) ? $tranlation_day : __( 'Day', 'woocommerce-tm-extra-product-options' );
		$select_options[] = array( "text" => $tranlation_day, "value" => "" );
		for ( $i = 1; $i != 31 + 1; $i += 1 ) {
			$select_options[] = array( "text" => $i, "value" => $i );
		}
		$day_html = TM_EPO_HTML()->tm_make_select( $selectArray, $select_options, $selectedvalue = isset( $_POST[ $name . "_day" ] ) ? $_POST[ $name . "_day" ] : "", 1, 0 );

		$selectArray = array( "class" => "tmcp-date-select tmcp-date-month", "id" => $id . "_month", "name" => $name . "_month", "extra" => "data-tm-date='" . $id . "'" );
		$select_options = array();
		$tranlation_month = (!empty( $tranlation_month )) ? $tranlation_month : __( 'Month', 'woocommerce-tm-extra-product-options' );
		$select_options[] = array( "text" => $tranlation_month, "value" => "" );
		global $wp_locale;

		for ( $i = 1; $i != 12 + 1; $i += 1 ) {
			$select_options[] = array( "text" => $wp_locale->get_month( $i ), "value" => $i );
		}
		$month_html = TM_EPO_HTML()->tm_make_select( $selectArray, $select_options, $selectedvalue = isset( $_POST[ $name . "_month" ] ) ? $_POST[ $name . "_month" ] : "", 1, 0 );

		$selectArray = array( "class" => "tmcp-date-select tmcp-date-year", "id" => $id . "_year", "name" => $name . "_year", "extra" => "data-tm-date='" . $id . "'" );
		$select_options = array();
		$tranlation_year = (!empty( $tranlation_year )) ? $tranlation_year : __( 'Year', 'woocommerce-tm-extra-product-options' );
		$select_options[] = array( "text" => $tranlation_year, "value" => "" );
		for ( $i = intval( $end_year ); $i != intval( $start_year ) - 1; $i -= 1 ) {
			$select_options[] = array( "text" => $i, "value" => $i );
		}
		$year_html = TM_EPO_HTML()->tm_make_select( $selectArray, $select_options, $selectedvalue = isset( $_POST[ $name . "_year" ] ) ? $_POST[ $name . "_year" ] : "", 1, 0 );


		switch ( $format ) {
			case "0":
			case "2":
			case "4":
				if ( apply_filters( 'wc_epo_display_rtl', is_rtl() ) ) {
					echo $year_html . $month_html . $day_html;
				} else {
					echo $day_html . $month_html . $year_html;
				}

				break;
			case "1":
			case "3":
			case "5":
				if ( apply_filters( 'wc_epo_display_rtl', is_rtl() ) ) {
					echo $year_html . $day_html . $month_html;
				} else {
					echo $month_html . $day_html . $year_html;
				}

				break;
		}
	}
	$input_type = "text";
	$showon = "both";
	$mask = 'data-mask="' . $date_mask . '" data-mask-placeholder="' . $date_placeholder . '" ';
	if ( $style == "" ) {
		$input_type = "hidden";
		$showon = "focus";
		$mask = '';
	}
	if ( isset( $textbeforeprice ) && $textbeforeprice != '' ) {
		$textbeforeprice = '<span class="before-amount' . (!empty( $hide_amount ) ? " " . $hide_amount : "") . '">' . $textbeforeprice . '</span>';
	}
	if ( isset( $textafterprice ) && $textafterprice != '' ) {
		$textafterprice = '<span class="after-amount' . (!empty( $hide_amount ) ? " " . $hide_amount : "") . '">' . $textafterprice . '</span>';
	}
	if ( !empty( $class ) ) {
		$fieldtype .= " " . $class;
	}
	$get_default_value = "";
	if ( !isset( $defaultdate ) ) {
		$defaultdate = NULL;
		$get_default_value = $defaultdate;
	} else {
		if ( $defaultdate !== '' ) {
			$get_default_value = $defaultdate;
		}
	}

	if ( TM_EPO()->tm_epo_global_reset_options_after_add == "no" && isset( $_POST[ $name ] ) ) {
		$get_default_value = esc_attr( stripslashes( $_POST[ $name ] ) );
	} elseif ( isset( $_GET[ $name ] ) ) {
		$get_default_value = esc_attr( stripslashes( $_GET[ $name ] ) );
	}
	$get_default_value = apply_filters( 'wc_epo_default_value', $get_default_value, isset( $tm_element_settings ) ? $tm_element_settings : array() );
	?>
    <label for="<?php echo $id; ?>" class="tm-epo-datepicker-label-container">
        <input type="<?php echo $input_type; ?>"
               class="<?php echo $fieldtype; ?> tm-epo-field tmcp-date tm-epo-datepicker"
               data-date-showon="<?php echo $showon; ?>"
               data-date-defaultdate="<?php echo $defaultdate; ?>"
			<?php echo $mask; ?>
               data-start-year="<?php echo $start_year; ?>"
               data-end-year="<?php echo $end_year; ?>"
               data-min-date="<?php echo $min_date; ?>"
               data-max-date="<?php echo $max_date; ?>"
               data-disabled-dates="<?php echo $disabled_dates; ?>"
               data-enabled-only-dates="<?php echo $enabled_only_dates; ?>"
               data-disabled-weekdays="<?php echo $disabled_weekdays; ?>"
               data-date-format="<?php echo $date_format; ?>"
               data-date-theme="<?php echo $date_theme; ?>"
               data-date-theme-size="<?php echo $date_theme_size; ?>"
               data-date-theme-position="<?php echo $date_theme_position; ?>"
               data-price="" data-rules="<?php echo $rules; ?>" data-original-rules="<?php echo $original_rules; ?>"
               data-rulestype="<?php echo $rules_type; ?>"
               id="<?php echo $id; ?>" tabindex="<?php echo $tabindex; ?>"
               value="<?php echo $get_default_value; ?>" 
               <?php if ( !empty( $tax_obj ) ) {
			echo 'data-tax-obj="' . $tax_obj . '" ';
		} ?>
               name="<?php echo $name; ?>"/>
    </label>
	<?php include(TM_EPO_TEMPLATE_PATH .'_price.php'); ?>
	<?php include(TM_EPO_TEMPLATE_PATH .'_quantity_end.php'); ?>
	<?php do_action( 'tm_after_element', isset( $tm_element_settings ) ? $tm_element_settings : array() ); ?>
</li>
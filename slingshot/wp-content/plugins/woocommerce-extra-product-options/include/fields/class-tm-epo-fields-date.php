<?php

class TM_EPO_FIELDS_date extends TM_EPO_FIELDS {

	public function display_field( $element = array(), $args = array() ) {
		$tm_epo_global_datepicker_theme = !empty( TM_EPO()->tm_epo_global_datepicker_theme ) ? TM_EPO()->tm_epo_global_datepicker_theme : (isset( $element['theme'] ) ? $element['theme'] : "epo");
		$tm_epo_global_datepicker_size = !empty( TM_EPO()->tm_epo_global_datepicker_size ) ? TM_EPO()->tm_epo_global_datepicker_size : (isset( $element['theme_size'] ) ? $element['theme_size'] : "medium");
		$tm_epo_global_datepicker_position = !empty( TM_EPO()->tm_epo_global_datepicker_position ) ? TM_EPO()->tm_epo_global_datepicker_position : (isset( $element['theme_position'] ) ? $element['theme_position'] : "normal");

		return array(
			'textbeforeprice'     => isset( $element['text_before_price'] ) ? $element['text_before_price'] : "",
			'textafterprice'      => isset( $element['text_after_price'] ) ? $element['text_after_price'] : "",
			'hide_amount'         => isset( $element['hide_amount'] ) ? " " . $element['hide_amount'] : "",
			'style'               => isset( $element['button_type'] ) ? $element['button_type'] : "",
			'format'              => !empty( $element['format'] ) ? $element['format'] : 0,
			'start_year'          => !empty( $element['start_year'] ) ? $element['start_year'] : "1900",
			'end_year'            => !empty( $element['end_year'] ) ? $element['end_year'] : (date( "Y" ) + 10),
			'min_date'            => isset( $element['min_date'] ) ? $element['min_date'] : "",
			'max_date'            => isset( $element['max_date'] ) ? $element['max_date'] : "",
			'disabled_dates'      => !empty( $element['disabled_dates'] ) ? $element['disabled_dates'] : "",
			'enabled_only_dates'  => !empty( $element['enabled_only_dates'] ) ? $element['enabled_only_dates'] : "",
			'disabled_weekdays'   => isset( $element['disabled_weekdays'] ) ? $element['disabled_weekdays'] : "",
			'tranlation_day'      => !empty( $element['tranlation_day'] ) ? $element['tranlation_day'] : "",
			'tranlation_month'    => !empty( $element['tranlation_month'] ) ? $element['tranlation_month'] : "",
			'tranlation_year'     => !empty( $element['tranlation_year'] ) ? $element['tranlation_year'] : "",
			'quantity'            => isset( $element['quantity'] ) ? $element['quantity'] : "",
			'defaultdate'         => isset( $element['default_value'] ) ? $element['default_value'] : "",
			'date_theme'          => $tm_epo_global_datepicker_theme,
			'date_theme_size'     => $tm_epo_global_datepicker_size,
			'date_theme_position' => $tm_epo_global_datepicker_position,
		);
	}

	public function validate() {

		$format = $this->element['format'];
		switch ( $format ) {
			case "0":
				$date_format = 'd/m/Y H:i:s';
				$sep = "/";
				break;
			case "1":
				$date_format = 'm/d/Y H:i:s';
				$sep = "/";
				break;
			case "2":
				$date_format = 'd.m.Y H:i:s';
				$sep = ".";
				break;
			case "3":
				$date_format = 'm.d.Y H:i:s';
				$sep = ".";
				break;
			case "4":
				$date_format = 'd-m-Y H:i:s';
				$sep = "-";
				break;
			case "5":
				$date_format = 'm-d-Y H:i:s';
				$sep = "-";
				break;
		}

		$passed = TRUE;
		$message = array();

		$quantity_once = FALSE;
		$min_quantity = isset( $this->element['quantity_min'] ) ? intval( $this->element['quantity_min'] ) : 0;
		if ( $min_quantity < 0 ) {
			$min_quantity = 0;
		}
		foreach ( $this->field_names as $attribute ) {

			if ( !$quantity_once && isset( $this->epo_post_fields[ $attribute ] ) && $this->epo_post_fields[ $attribute ] !== "" && isset( $this->epo_post_fields[ $attribute . '_quantity' ] ) && !(intval( $this->epo_post_fields[ $attribute . '_quantity' ] ) >= $min_quantity) ) {
				$passed = FALSE;
				$quantity_once = TRUE;
				$message[] = sprintf( __( 'The quantity for "%s" must be greater than %s', 'woocommerce-tm-extra-product-options' ), $this->element['label'], $min_quantity );
			}

			if ( $this->element['required'] ) {
				if ( !isset( $this->epo_post_fields[ $attribute ] ) || $this->epo_post_fields[ $attribute ] == "" ) {
					$passed = FALSE;
					$message[] = 'required';
					break;
				}
			}

			if ( !empty( $this->epo_post_fields[ $attribute ] ) && class_exists( 'DateTime' ) && (version_compare( phpversion(), '5.3', '>=' )) ) {
				$posted_date = $this->epo_post_fields[ $attribute ];
				if ( apply_filters( 'wc_epo_display_rtl', is_rtl() ) ) {
					$posted_date_arr = explode( $sep, $posted_date );
					if ( count( $posted_date_arr ) == 3 ) {
						$posted_date = $posted_date_arr[2] . $sep . $posted_date_arr[1] . $sep . $posted_date_arr[0];
					}
				}
				$date = DateTime::createFromFormat( $date_format, $posted_date . ' 00:00:00' );
				$date_errors = DateTime::getLastErrors();

				if ( !empty( $date_errors['error_count'] ) ) {
					$passed = FALSE;
					$message[] = __( 'Invalid date entered!', 'woocommerce-tm-extra-product-options' );
					break;
				}

				$year = $_year = $date->format( "Y" );
				$month = $_month = $date->format( "m" );
				$day = $_day = $date->format( "d" );

				$posted_date_arr = explode( $sep, $posted_date );

				if ( count( $posted_date_arr ) == 3 ) {
					switch ( $format ) {
						case "0":
						case "2":
						case "4":
							$_year = $posted_date_arr[2];
							$_month = $posted_date_arr[1];
							$_day = $posted_date_arr[0];
							break;
						case "1":
						case "3":
						case "5":
							$_year = $posted_date_arr[2];
							$_month = $posted_date_arr[0];
							$_day = $posted_date_arr[1];
							break;
					}

					if ( $year != $_year || $month != $_month || $day != $_day ) {
						$message[] = __( 'Invalid data submitted!', 'woocommerce-tm-extra-product-options' );
						$passed = FALSE;
						break;
					}
				}

				if ( checkdate( $_month, $_day, $_year ) ) {
					// valid date ...
					$start_year = intval( $this->element['start_year'] ) || 1900;
					$end_year = intval( $this->element['end_year'] ) || (date( "Y" ) + 10);
					$min_date = ($this->element['min_date'] !== '') ? ($this->element['min_date']) : FALSE;
					$max_date = ($this->element['max_date'] !== '') ? ($this->element['max_date']) : FALSE;
					$disabled_dates = $this->element['disabled_dates'];
					$enabled_only_dates = $this->element['enabled_only_dates'];
					$disabled_weekdays = $this->element['disabled_weekdays'];

					$now = new DateTime( '00:00:00' );
					$now_day = $now->format( "d" );
					$now_month = $now->format( "m" );
					$now_year = $now->format( "Y" );

					if ( $enabled_only_dates ) {
						$enabled_only_dates = explode( ",", $enabled_only_dates );
						$_pass = FALSE;
						foreach ( $enabled_only_dates as $key => $value ) {
							$value = str_replace( ".", "-", $value );
							$value = str_replace( "/", "-", $value );
							$value = explode( "-", $value );
							switch ( $format ) {
								case "0":
								case "2":
								case "4":
									$value = $value[2] . "-" . $value[1] . "-" . $value[0];
									break;
								case "1":
								case "3":
								case "5":
									$value = $value[2] . "-" . $value[0] . "-" . $value[1];
									break;
							}
							$value_to_date = date_create( $value );
							if ( !$value_to_date ) {
								continue;
							}
							$value = date_format( $value_to_date, $date_format );
							$temp = DateTime::createFromFormat( $date_format, $value );
							$interval = $temp->diff( $date );
							$sign = floatval( $interval->format( '%d%m%Y' ) );
							if ( empty( $sign ) ) {
								$_pass = TRUE;
								break;
							}
						}
						$passed = $_pass;
						if ( !$_pass ) {
							$message[] = __( 'Invalid date entered!', 'woocommerce-tm-extra-product-options' );
							break;
						}
					} else {
						// validate start,end year
						if ( $_year < $start_year || $_year > $end_year ) {
							$passed = FALSE;
							$message[] = __( 'Invalid year date entered!', 'woocommerce-tm-extra-product-options' );
							break;
						}

						// validate disabled dates
						if ( $disabled_dates ) {
							$disabled_dates = explode( ",", $disabled_dates );
							foreach ( $disabled_dates as $key => $value ) {
								$value = str_replace( ".", "-", $value );
								$value = str_replace( "/", "-", $value );
								$value = explode( "-", $value );
								switch ( $format ) {
									case "0":
									case "2":
									case "4":
										$value = $value[2] . "-" . $value[1] . "-" . $value[0];
										break;
									case "1":
									case "3":
									case "5":
										$value = $value[2] . "-" . $value[0] . "-" . $value[1];
										break;
								}
								$value_to_date = date_create( $value );
								if ( !$value_to_date ) {
									continue;
								}
								$value = date_format( $value_to_date, $date_format );
								$temp = DateTime::createFromFormat( $date_format, $value );
								$interval = $temp->diff( $date );
								$sign = floatval( $interval->format( '%d%m%Y' ) );
								if ( empty( $sign ) ) {
									$passed = FALSE;
									$message[] = __( 'You cannot select that date!', 'woocommerce-tm-extra-product-options' );
									break;
								}
							}

						}

						//validate minimum date
						if ( $min_date !== FALSE ) {

							if ( is_numeric( $min_date ) ) {
								$temp = clone $now;
								if ( $min_date > 0 ) {
									$temp->add( new DateInterval( 'P' . abs( $min_date ) . 'D' ) );
								} elseif ( $min_date < 0 ) {
									$temp->sub( new DateInterval( 'P' . abs( $min_date ) . 'D' ) );
								}
							} else {
								$temp = str_replace( ".", "-", $min_date );
								$temp = str_replace( "/", "-", $temp );
								$temp = explode( "-", $temp );
								switch ( $format ) {
									case "0":
									case "2":
									case "4":
										$temp = $temp[2] . "-" . $temp[1] . "-" . $temp[0];
										break;
									case "1":
									case "3":
									case "5":
										$temp = $temp[2] . "-" . $temp[0] . "-" . $temp[1];
										break;
								}
								$temp = date_create( $temp );
								if ( !$temp ) {
									//failsafe todo:proper handling
									$temp = clone $now;
								} else {
									$temp = date_format( $temp, $date_format );
									$temp = DateTime::createFromFormat( $date_format, $temp );
								}
							}

							$interval = $temp->diff( $date );
							$sign = $interval->format( '%r' );
							if ( !empty( $sign ) ) {
								$passed = FALSE;
								$message[] = __( 'You cannot select that date!', 'woocommerce-tm-extra-product-options' );
								break;
							}
						}

						//validate maximum date
						if ( $max_date !== FALSE ) {
							if ( is_numeric( $max_date ) ) {
								$temp = clone $now;
								if ( $max_date > 0 ) {
									$temp->add( new DateInterval( 'P' . abs( $max_date ) . 'D' ) );
								} elseif ( $max_date < 0 ) {
									$temp->sub( new DateInterval( 'P' . abs( $max_date ) . 'D' ) );
								}
							} else {
								$temp = str_replace( ".", "-", $max_date );
								$temp = str_replace( "/", "-", $temp );
								$temp = explode( "-", $temp );
								switch ( $format ) {
									case "0":
									case "2":
									case "4":
										$temp = $temp[2] . "-" . $temp[1] . "-" . $temp[0];
										break;
									case "1":
									case "3":
									case "5":
										$temp = $temp[2] . "-" . $temp[0] . "-" . $temp[1];
										break;
								}
								$temp = date_create( $temp );
								if ( !$temp ) {
									//failsafe todo:proper handling
									$temp = clone $now;
								} else {
									$temp = date_format( $temp, $date_format );
									$temp = DateTime::createFromFormat( $date_format, $temp );
								}
							}

							$interval = $date->diff( $temp );
							$sign = $interval->format( '%r' );
							if ( !empty( $sign ) ) {
								$passed = FALSE;
								$message[] = __( 'You cannot select that date!', 'woocommerce-tm-extra-product-options' );
								break;
							}
						}
					}

				} else {
					// problem with dates ...
					$passed = FALSE;
					$message[] = __( 'Invalid date entered!', 'woocommerce-tm-extra-product-options' );
					break;
				}
			}
		}

		return array( 'passed' => $passed, 'message' => $message );
	}

}
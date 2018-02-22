<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

if ( isset( $textbeforeprice ) && isset( $textafterprice ) && isset( $hide_amount ) && isset( $amount ) && isset( $original_amount ) ) {

	echo $textbeforeprice;

	echo '<span class="price tc-price';
	if ( !empty( $hide_amount ) ) {
		echo " " . $hide_amount;
	}
	echo '"><span class="amount">' . $amount . '</span></span>';
	echo $textafterprice;

	if ( isset( $tm_element_settings ) & isset( $field_counter ) ) {
		if ( !empty( $tm_element_settings['cdescription'][ $field_counter ] ) || (count( $tm_element_settings['cdescription'] ) > 1 && $tm_element_settings['type'] == 'select') ) {
			if ( TM_EPO()->tm_epo_description_inline == 'yes'){
				echo '<div class="tc-inline-description">'.esc_html( do_shortcode( $tm_element_settings['cdescription'][ $field_counter ] ) ).'</div>';
			}else{
				echo '<i data-tm-tooltip-html="' . esc_attr( do_shortcode( $tm_element_settings['cdescription'][ $field_counter ] ) ) . '" class="tm-tooltip tc-tooltip tcfa tcfa-question-circle"></i>';
			}			
		}
	}

}
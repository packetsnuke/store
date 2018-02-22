<?php

class acf_html_email_preview_field extends acf_field {


	
	function __construct() {

		$this->name     = 'html_email_preview_field';
		$this->label    = __( 'HTML Email Viewer', 'acf-order-status-selector' );
		$this->category = __( 'Content', 'acf' );
		/* $this->defaults = array(
			'return_value' => 'name',
			'field_type'   => 'textarea',
			'allowed_order_statuses'   => '',
		);
 */
		parent::__construct();

	}


	
	function render_field_settings( $field ) {

		/*  acf_render_field_setting( $field, array(
			'label'			=> __('Return Format','acf-order-status-selector'),
			'instructions'	=> __('Specify the returned value type','acf-order-status-selector'),
			'type'			=> 'radio',
			'name'			=> 'return_value',
			'layout'  =>  'horizontal',
			'choices' =>  array(
				'name'   => __( 'Status Name', 'acf-order-status-selector' ),
				//object' => __( 'Role Object', 'acf-order-status-selector' ), 
			)
		)); */

		$choices = array("guest_to_registered" =>  __('Guest to registered','acf-order-status-selector') ,
						 "customer_notification" =>  __('Customer notification','acf-order-status-selector'));
		acf_render_field_setting( $field, array(
			'label'			=> __('Select template preview','acf-order-status-selector'),
			'type'			=> 'select',
			'name'			=> 'template_preview_to_show',
			'multiple'      => false,
			//'instructions'   => __( 'To allow all statuses, select none or all of the options to the right', 'acf-order-status-selector' ),
			'choices' => $choices
		));

		/* acf_render_field_setting( $field, array(
			'label'			=> __('Field Type','acf-order-status-selector'),
			'type'			=> 'select',
			'name'			=> 'field_type',
			'choices' => array(
				__( 'Multiple Values', 'acf-order-status-selector' ) => array(
					'checkbox' => __( 'Checkbox', 'acf-order-status-selector' ),
					'multi_select' => __( 'Multi Select', 'acf-order-status-selector' )
				),
				__( 'Single Value', 'acf-order-status-selector' ) => array(
					'radio' => __( 'Radio Buttons', 'acf-order-status-selector' ),
					'select' => __( 'Select', 'acf-order-status-selector' )
				)
			)
		));  */



	}




	function render_field( $field ) 
	{
		global $wccm_configuration_model;
		if(!isset($wccm_configuration_model) || !class_exists('WC_Emails'))
			return false;
		
		$email_configuration = $wccm_configuration_model->get_email_templates_configurations();
		$template_to_used = $field['template_preview_to_show'] == 'guest_to_registered' ? $email_configuration['guest_to_registered_email_template'] : $email_configuration['customer_notification_email_template'];
		$use_footer_and_or_header = $field['template_preview_to_show'] == 'guest_to_registered' ? $email_configuration['guest_to_registered_header_footer_inlcude'] : $email_configuration['customer_notification_header_footer_inlcude'];
		 $mail = new WC_Emails();
		$email_heading = get_bloginfo('name');
		
		echo '<div id="acf_email_previw_wrapper" style="display:block; margin:50px 0 50px 0;">';
		 if($use_footer_and_or_header == 'all' || $use_footer_and_or_header == 'all')
		echo	$mail->email_header($email_heading );
		echo $template_to_used;	
		if($use_footer_and_or_header == 'all' || $use_footer_and_or_header == 'footer')
			$mail->email_footer();
		echo '</div>';
		//<!-- <iframe srcdoc="echo $template_to_used " style="height:500px; width:100%;"></iframe> -->
		 ?>
		 
		<?php
		
	}


	
	function format_value($value, $post_id, $field) {
		/* if( $field['return_value'] == 'object' )
		{
			foreach( $value as $key => $name ) {
				$value[$key] = get_role( $name );
			}
		} */
		return $value;
	}


	
	function load_value($value, $post_id, $field) {

		/* if( $field['return_value'] == 'object' )
		{
			foreach( $value as $key => $name ) {
				$value[$key] = get_role( $name );
			}
		} */

		return $value;
	}

}

new acf_html_email_preview_field();

?>

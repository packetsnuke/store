<?php

function pmui_pmxi_options_validation($errors, $post, $importObj){

	if ( !empty($post['pmui']['import_users']) ){ 
		
		if ( '' == $post['pmui']['login'] ) {
			$errors->add('form-validation', __('`Login` must be specified', 'pmxi_plugin'));
		}
		if ( '' == $post['pmui']['email'] ) {
			$errors->add('form-validation', __('`Email` must be specified', 'pmxi_plugin'));
		}				

	}

	return $errors;
}

?>
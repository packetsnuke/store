<?php
class WCCM_Wpml
{
	public function __construct()
	{
	}
	public function wpml_is_active()
	{
		return class_exists('SitePress');
	}
	public function remove_translated_id($items_array, $post_type = "product")
	{
		if(!class_exists('SitePress'))
			return false;
		
		$filtered_items_list = array();
		foreach($items_array as $item)	
		{
			/* $result = wpml_get_language_information($item->id);
			if(!is_bool (strpos($result['locale'], ICL_LANGUAGE_CODE)))
			{
				array_push($filtered_items_list, $item);
			}*/
			$item_id = is_object($item) && method_exists($item,'get_id') ? $item->get_id() : $item->id;
			$item_type = is_object($item) && method_exists($item,'get_type') ? $item->get_type() : $item->type;
			
			if(function_exists('icl_object_id'))
				$item_translated_id = icl_object_id($item_id, $item_type, false, ICL_LANGUAGE_CODE);
			else
				$item_translated_id = apply_filters( 'wpml_object_id', $item_id, $item_type, false, ICL_LANGUAGE_CODE );
			
			if($item->id == $item_translated_id)
				array_push($filtered_items_list, $item);
		}
			
		return $filtered_items_list ;
	}
	public function switch_to_default_language()
	{
		if(!$this->wpml_is_active())
			return;
		global $sitepress;
		$this->curr_lang = ICL_LANGUAGE_CODE ;
		$sitepress->switch_lang($sitepress->get_default_language());
	
	}
	public function get_original_id($item_id, $post_type = "product", $return_original = true)
	{
		if(!class_exists('SitePress'))
			return false;
		
		global $sitepress;
		if(function_exists('icl_object_id'))
			$item_translated_id = icl_object_id($item_id, $post_type, $return_original, $sitepress->get_default_language());
		else
			$item_translated_id = apply_filters( 'wpml_object_id', $item_id, $post_type, $return_original, $sitepress->get_default_language() );
		
		return $item_translated_id;
	}
	public function get_current_language()
	{
		if(!class_exists('SitePress'))
			return get_locale();
		
		return ICL_LANGUAGE_CODE."_".strtoupper(ICL_LANGUAGE_CODE);
	}
	public function get_all_product_id_translations($product_id)
	{
		global $wccm_product_model;
		
		$is_variation = $wccm_product_model->is_variation($product_id) != 0 ? true : false;
		$all_product_id = array();
		if (class_exists('SitePress') && $product_id) 
		{
			$languages = icl_get_languages('skip_missing=0&orderby=code');
			$lang_array = array();
			if(!empty($languages))
				foreach($languages as $l)
					if(!$l['active']) 
					  array_push($lang_array, $l['language_code']);
			
			foreach($lang_array as $lang)
			{
				
				 // $result = icl_object_id($product_id, !$is_variation ? 'product':'product_variation', false, $lang); 
				  $result = apply_filters( 'wpml_object_id', $product_id, !$is_variation ? 'product':'product_variation', false, $lang);
				  if($result)
					array_push($all_product_id,$result);
			}
		}
		return $all_product_id;
	}
	public function wpuef_get_option_translations_by_original_language_string_id($string_id, $index, $string_to_be_translated)
	{
		if(!class_exists('SitePress'))
			return array();
		global $sitepress;
		$default_lang = $sitepress->get_default_language();
		$translations = array();
		//Language codes
		$languages = icl_get_languages('skip_missing=0&orderby=code');
		$lang_array = array(); 
		if(!empty($languages))
			foreach($languages as $l)
				//if(!$l['active']) //diversa da quella corrente
					array_push($lang_array, $l['language_code']);
						
		//WPUEF
		$string_default_lang =  apply_filters( 'wpml_translate_single_string', $string_to_be_translated, 'wp-user-extra-fields', 'wpuef_'.$string_id."_sublabel_".$index, $default_lang  );
		//wccm_var_dump("default: ".$string_default_lang);
		foreach($lang_array as $current_lang)
		{
			$result = apply_filters( 'wpml_translate_single_string', $string_default_lang, 'wp-user-extra-fields', 'wpuef_'.$string_id."_sublabel_".$index, $current_lang  );
			if($result != $string_default_lang)
				$translations[] = $result;
		}
		/* $options = get_option( 'wpuef_options');
		$options = isset($options) ? $options: null;
		if(isset($options['json_fields_string']))
		{
			$options = $options['json_fields_string'];
			$options = json_decode(stripcslashes($options));
			
			foreach($options->fields as $extra_field)
				if($extra_field->cid == $string_id)
				{
					if(isset($extra_field->field_options->options))
						foreach($extra_field->field_options->options as $index => $extra_option)
						{
							if(isset($extra_option->label))
							{
								$translations[] = $extra_option->label;
								foreach($lang_array as $current_lang)
								{
									$result = apply_filters( 'wpml_translate_single_string', $extra_option->label, 'wp-user-extra-fields', 'wpuef_'.$extra_field->cid."_sublabel_".$index, $current_lang  );
									if($result != $extra_option->label)
										$translations[] = $result;
								}
							}
								
						}
				}
		}  */
		return $translations;
		
	}
}
?>
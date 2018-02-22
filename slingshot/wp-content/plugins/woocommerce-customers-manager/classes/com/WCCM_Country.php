<?php 
class WCCM_Country
{
	function __construct()
	{
	}
	
	function get_country_code_by_name($name)
	{
		foreach(WC()->countries->countries as $code => $country_name)
			if(strtolower($name) == strtolower($country_name))
				return $code;
			
		return $name;
	}
	function get_state_code_by_name($country_code, $state_name)
	{
		$countries_obj   = new WC_Countries();
		$states = $countries_obj->get_states( $country_code );
		if ( is_array( $states ) && !empty( $states ) )
		{
			foreach($states as $state_code => $state_name_tmp)
				if(strtolower($state_name) == strtolower($state_name_tmp))
				{
					//wccm_var_dump("ok: ".$state_code);
					return $state_code;
				}
		}
		
		return $state_name;
	}
}
?>
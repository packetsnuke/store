<?php
function woo_ce_product_fields_gm_rss( $fields = array() ) {

	foreach( $fields as $key => $field ) {
		switch( $field['name'] ) {

			case 'product_id':
				$fields[$key]['label'] = 'g:id';
				break;

			case 'name':
				$fields[$key]['label'] = 'g:title';
				break;

			case 'description':
				$fields[$key]['label'] = 'g:description';
				break;

			case 'product_url':
				$fields[$key]['label'] = 'g:link';
				break;

			case 'image':
				$fields[$key]['label'] = 'g:image_link';
				break;

/*
			case 'condition':
				break;
*/

			case 'stock_status':
				$fields[$key]['label'] = 'g:availability';
				break;

			case 'price':
				$fields[$key]['label'] = 'g:price';
				break;

		}
	}
	return $fields;

}
?>
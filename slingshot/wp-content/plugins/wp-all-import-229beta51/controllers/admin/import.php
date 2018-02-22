<?php 
/** 
 * 
 * @author Pavel Kulbakin <p.kulbakin@gmail.com>
 */

class PMWI_Admin_Import extends PMWI_Controller_Admin 
{				
	public function index($post) {			
				
		$this->data['post'] =& $post;

		switch ($post['custom_type']) 
		{
			case 'product':
				$this->render('admin/import/product/index');
				break;

			case 'shop_order':
				$this->render('admin/import/shop_order/index');			
				break;

			default:
				# code...
				break;
		}			
	}			

	public function options( $isWizard = false, $post = array() )
	{
		$this->data['isWizard'] = $isWizard;	

		$this->data['post'] =& $post;				

		$this->render();
	}
}

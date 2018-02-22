var pager;
jQuery(document).ready(function()
{
	if(document.getElementById('orders-list').children.length > 10)
	{
		pager  = new Pager('orders-list', 10); 
		pager.init(); 
		pager.showPageNav('pager', 'order-list-paging'); 
		pager.showPage(1);
	}
});
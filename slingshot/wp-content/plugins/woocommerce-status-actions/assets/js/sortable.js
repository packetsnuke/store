jQuery(document).ready(function($) {
	function stopSortStatuses(event, ui) {

    var postid     = ui.item.find( '.check-column input' ).val(); // this post id
    var nextpostid = ui.item.next().find( '.check-column input' ).val();
    var data = {
      action : 'wc_sa_sort',
      id     : postid,
      nextid : nextpostid
    }

    ui.item.find('.column-sort').append('<span class="spinner" style="display: block; visibility: visible;"></span>');
    ui.item.find('.column-sort').addClass('saving');
    $.ajax({
      url: wc_sa_sortable_opt.ajax_url,
      data: data,
      type: 'POST',
      success: function( response ) {
        ui.item.find('.column-sort .spinner').remove();
        ui.item.find('.column-sort').removeClass('saving');
      }
    });
  }
  
  if($('table.widefat').length > 0 ){
    $( "table.widefat tbody" ).sortable({
          placeholder: "ui-state-highlight",
          axis: "y",
          handle: ".column-sort",
          stop: stopSortStatuses
        });
  }

});
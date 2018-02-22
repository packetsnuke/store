jQuery(document).ready(function($) {
  var count_scaning = 0;
  var bulk_update   = false;
  $(document).anysearch({
      searchSlider: false,
      isBarcode: function (barcode) {
          searchOrder(barcode);
      },
      searchFunc: function (search) {
          searchOrder(search);
      },
  });

  $('#search-order-form').on('submit', function(event) {
    var order_id = $('#order-number').val();
    if( order_id != ''){
      searchOrder(order_id.trim());
    }
    return false;
  });
  $('#clear-table').on('click', function(event) {
    clear_table();    
    return false;
  });

  $('#change_status').on('click', function(event) {
    bulk_change_status();
    return false;
  });

  function remove_errors() {
    setTimeout( function(){ 
      $('.error-scan-number').remove();
    }, 5000);
  }

  function clear_table() {
    $('.woocommerce-BlankState').show();
    $('#orders-list table.wp-list-table').hide();
    $('#orders-list table #the-list').html('');
  }
  function bulk_change_status() {
    if( $('#order-status').val() != '' ) {
        var orders = [];
        $('.row_order_id').each(function(index, el) {
          orders.push( $(el).val() );
        });
        bulk_update = true;
        var data = {action: 'wc_sa_search_order_bulk', orders : orders, status: $('#order-status').val()};
        $('#posts-filter').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });

        $.ajax({
          url: wc_sa_opt.ajax_url,
          type: 'POST',
          dataType: 'json',
          data: data,
        })
        .done(function(result) {
          if( result.result != 'success'){
            $('.wp-header-end').after('<div class="error error-scan-number"><p>'+wc_sa_opt.error_i18n+'</p></div>');  
            remove_errors();
          }else{            
            $('#orders-list table #the-list').html(result.order_rows);          
            $('.woocommerce-BlankState').hide();
            $('#orders-list table.wp-list-table').show();
          }
        })
        .fail(function() {
          $('.wp-header-end').after('<div class="error error-scan-number"><p>'+wc_sa_opt.error_i18n+'</p></div>');
          remove_errors();      
        })
        .always(function(result) {
          $('#posts-filter').unblock();        
          $('#order-number').val('');
          bulk_update = false;
        });
    }
  }
  function searchOrder(code) {
    if( bulk_update ) return;
    count_scaning++;
    $('#orders-list').block({
        message: null,
        overlayCSS: {
            background: '#fff',
            opacity: 0.6
        }
    });

    var data = {action: 'wc_sa_search_order', order_id : code }
    if( $('#apply-automatically').is(':checked') && $('#order-status').val() != '' ) {
      data.status = $('#order-status').val();
    }    
    $.ajax({
      url: wc_sa_opt.ajax_url,
      type: 'POST',
      dataType: 'json',
      data: data,
    })
    .done(function(result) {
      if( result.result != 'success'){
        $('.wp-header-end').after('<div class="error error-scan-number"><p>'+result.result+'</p></div>');  
        remove_errors();
      }else{
        if( $('#order-row-'+code).length ){
          $('#order-row-'+code).remove();
        }
        $('#orders-list table #the-list').prepend(result.order_row);          
        $('.woocommerce-BlankState').hide();
        $('#orders-list table.wp-list-table').show();
      }
    })
    .fail(function() {
      $('.wp-header-end').after('<div class="error error-scan-number"><p>'+wc_sa_opt.error_i18n+'</p></div>');
      remove_errors();      
    })
    .always(function(result) {
      count_scaning--;
      if( count_scaning == 0){
        $('#orders-list').unblock();        
        $('#order-number').val('');
      }
    });

  }


  $( document.body ).on( 'click', '.show_order_items', function() {
    $( this ).closest( 'td' ).find( 'table' ).toggle();
    return false;
  });

});
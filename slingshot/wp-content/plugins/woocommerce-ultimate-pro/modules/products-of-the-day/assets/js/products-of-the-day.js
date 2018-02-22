(function($){
    $(document).ready(function(){

        if ( jQuery( '#sortable-lists').length ) {
            jQuery( '#sortable-lists .sortable' ).sortable({
                cursor: 'move',
                opacity: 0.5,
                scroll: true,
                tolerance: 'pointer',
                update: function(event, ui) {
                    var newOrder = jQuery(this).sortable('toArray').toString();
                    var box = jQuery(this).closest('.day-box');
                    var currentDay = jQuery(this).data('day');
                    jQuery.ajax({
                        url: 'admin-ajax.php',
                        type: 'POST',
                        data: {
                            action: 'products_of_the_day_sort',
                            order: newOrder,
                            day: currentDay,
                            _ajax_nonce: podt.potdne },
                        error: function(xhr, status, error) {
                            box.find('h3 span').removeClass('fail success').addClass('fail').text(status).fadeIn().fadeOut();
                        },
                        success: function(data, status) {
                            box.find('h3 span').removeClass('fail success').addClass('success').text(data).fadeIn().fadeOut();
                        }
                    });
                }
            });
        }
    });
})(jQuery);

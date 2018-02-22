/**
 * AJAX requests used on Product of the day configuration page
 */

(function($){
    $(document).ready(function(){

        // click on the remove button
        $('.day-box .potd-product-remove').live('click', function(){

            // display confirmation window
            if ( confirm( ajax_data.confirm_removal ) ) {

                var anchor  = $(this);
                var post_id = anchor.parents('li.potd-day').data('post-id');
                var prods   = [];

                anchor.parents('div.day-box').find('ul > li').each(function(){
                    if ($(this).data('post-id') !== post_id) {
                        prods.push($(this).data('post-id'));
                    }
                });

                $.ajax({
                    type: "POST",
                    url: ajax_data.ajaxurl,
                    data: {
                        action          : 'products_of_the_day_remove_item',
                        day             : anchor.parents('.day-box').data('day'),
                        post_id         : post_id,
                        excl_products   : prods
                    },
                    success: function(response) {
                        anchor.parents('li.potd-day').hide(300, function(){
                            $(this).remove();
                        });
                    }
                });
            }
            return false;
        });

        // autocomplete functionality
        $('.ajax-product-list').autocomplete({
            source: function( request, response ) {
                
                // text input fiels to which autocomplete is assigned
                var input = $( this.element );

                $.ajax({
                    url: ajax_data.ajaxurl,
                    type: 'POST',
                    data: {
                        action  : 'products_of_the_day_list',
                        day     : input.parents( '.day-box' ).data( 'day' ),
                        term    : request.term
                    },
                    success: function( data ) {
                        if ( $( data ).size() ) {
                            input.removeClass( 'potd-autocomplete-no-results' );               
                        } else {
                            input.addClass( 'potd-autocomplete-no-results' );
                        }
                        
                        response( $.map( data, function( item ) {
                            return {
                                label: item.post_title,
                                value: item.ID,
                            };
                        }));                         
                    }
                });
            },
            minLength: 1,
            open: function( event, ui ) {
                $( this ).autocomplete( 'widget' ).css({
                    width: 287
                }).find( 'li.ui-menu-item' ).css({
                    whiteSpace: 'normal'
                });
            },
            select: function( event, ui ) {
                
                // set the correct (empty) label in input element
                var input = $( this );
                input.val( '' );
                
                $.ajax({
                    type: "POST",
                    url: ajax_data.ajaxurl,
                    data: {
                        action  : 'products_of_the_day_add_item',
                        day     : input.parents( '.day-box' ).data( 'day' ),
                        post_id : ui.item.value,
                        title   : ui.item.label
                    },
                    success: function( response ) {
                        input.parents( '.day-box' ).find( 'ul' ).append( response.new_element );
                    }
                });                
                
                return false;
            }
        });
    });
})(jQuery);
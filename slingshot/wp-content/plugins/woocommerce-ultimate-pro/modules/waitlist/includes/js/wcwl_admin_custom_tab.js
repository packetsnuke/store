// JS required for the waitlist custom tab on the product edit screen
jQuery( document ).ready( function( $ ){

    // Display message for variations if no waitlists are shown. This will occur when all variations are in stock and
    // none of them have any users registered on them
    if( $( '.wcwl_product_tab_wrap' ).length <= 0 ) {
        $( '#wcwl_in_stock_notice' ).show();
    }

    // Toggle display of variations
    $( ".wcwl_header_wrap" ).click( function() {
        $( this ).closest( '.wcwl_product_tab_wrap' ).find( ".wcwl_body_wrap" ).slideToggle();
    });

    // Toggle email input field when user clicks 'add new user' link
    $( ".wcwl_hidden_tab" ).hide();
    $( ".wcwl_reveal_tab a" ).click( function() {
        $( this ).closest( '.wcwl_reveal_tab' ).find( ".wcwl_hidden_tab" ).slideToggle();
    });

    // Upon clicking the 'add' button, insert table elements to show which email has been added to the list and insert a link to remove it
    // Update hidden email input field to include the newly added email address
    $( ".wcwl_email_button_tab" ).click( function() {
        var email = $( this ).closest( '.wcwl_product_tab_wrap' ).find( '.wcwl_email_text_tab' ).val();
        if ( validateEmail( email ) ) {
            $( '<tr class="' + email + '"><td><input style="font-size: 13px;" type="text" readonly class="wcwl_email_to_add_tab" value=' + email + '></td><td><a href="#" class="wcwl_remove_email_tab">Remove</a></td></tr>').appendTo( $( this ).closest( '.wcwl_product_tab_wrap' ).find( ".wcwl_new_users_tab tbody" ) );
            var email_list = $( this ).closest( '.wcwl_product_tab_wrap' ).find( ".wcwl_email_list_tab" ).val();
            email_list = email_list + email + " ,";
            console.log( email_list );
            $( this ).closest( '.wcwl_product_tab_wrap' ).find( ".wcwl_email_list_tab" ).val( email_list );
            $( this ).prev().val( "" );
            $( '.wcwl_add_new_emails_tab p:not([class])').hide();
        } else {
            alert( wcwl_tab.invalid_email );
        }
    });

    // Remove email from displayed list
    // Remove email from the hidden input of all emails
    $( '.wcwl_new_users_tab' ).on( 'click', 'a.wcwl_remove_email_tab', function( e ) {
        e.preventDefault();
        var tr = $( this ).closest( 'tr' );
        var emailToRemove = tr.attr( 'class' );
        value = $( this ).closest( '.wcwl_product_tab_wrap' ).find( ".wcwl_email_list_tab" ).val();
        value = value.replace( emailToRemove, "" );
        $( this ).closest( '.wcwl_product_tab_wrap' ).find( ".wcwl_email_list_tab" ).val( value );
        console.log( value );
        tr.remove();
    });

    // Validate given email address
    var validateEmail = function (email) {
        var valid = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return valid.test(email);
    }
});
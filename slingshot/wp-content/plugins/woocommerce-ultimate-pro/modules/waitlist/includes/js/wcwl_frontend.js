// JS required for the front end
jQuery( document ).ready( function( $ ){

    // Hack to add the email input for simple subscriptions to the page.
    // WCS has the filter we require wrapped with wp_kses which removes out input field
    if ( $( 'div.product-type-subscription' ).length && ! $( '#wcwl_email' ).length ) {
      $( 'div.wcwl_email_field' ).append( '<input type="email" name="wcwl_email" id="wcwl_email" />' );
    }

    //Grab href of join waitlist button
    var href         = 'undefined' != typeof($( "a.woocommerce_waitlist" ).attr( "href" ) ) ?$( "a.woocommerce_waitlist" ).attr( "href" ): '' ;
    var a_href       = href.split('&wcwl_email');
    var email        = '';
    var product_id   = $( '.wcwl_control a' ).data( 'id' );

    // When email input is changed update the buttons href attribute to include the email
    $( "#wcwl_email" ).on( "input", function( e ) {
        email = $( this ).val();
        $( "a.woocommerce_waitlist" ).prop( "href", a_href+"&wcwl_email="+email );

    });

    // Create two arrays, showing products user wishes to join/leave the waitlist for
    var join_array =  $( "input:checkbox:checked.wcwl_checkbox" ).map( function() {
        return $( this ).attr( "id" ).replace( 'wcwl_checked_', '' );
    }).get();
    var leave_array = $( "input:checkbox:not(:checked).wcwl_checkbox" ).map( function() {
        return $( this ).attr( "id" ).replace( 'wcwl_checked_', '' );
    }).get();

    // When a checkbox is clicked, add/remove the product ID to/from the appropriate arrays
    $( ".wcwl_checkbox" ).change( function() {
        var changed = $( this ).attr( "id" ).replace( 'wcwl_checked_', '' );
        if( this.checked ) {
            leave_array.splice( $.inArray( changed, leave_array ), 1 );
            join_array.push( changed );
        }
        if( !this.checked ) {
            join_array.splice( $.inArray( changed, join_array ), 1 );
            leave_array.push( changed );
        }
    });

    // Modify the buttons href attribute to include the updated array of checkboxes and user email
    $( '#wcwl-product-'+product_id ).on( 'click', function(e) {
        $( "a.woocommerce_waitlist" ).prop( "href", a_href+"&wcwl_email="+email+"&wcwl_leave="+leave_array+"&wcwl_join="+join_array );
    });

    // Hide the add to cart button if the "Join Waitlist" button is visible
    // This needs to fire on page load and each time a variation attribute is changed
    var hide_cart_button = function() {
        if ( $( 'p.out-of-stock' ).length > 0 ) {
            $( '.woocommerce-variation-add-to-cart' ).hide();
        } else {
            $( '.woocommerce-variation-add-to-cart' ).show();
        }
    };
    hide_cart_button();
    $( '.variations' ).on( 'change', 'select', function() {
        window.setTimeout( hide_cart_button, 0 );
    });

});

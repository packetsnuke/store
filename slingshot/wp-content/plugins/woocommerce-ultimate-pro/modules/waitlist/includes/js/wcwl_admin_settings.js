/**
 * JS required for the waitlist settings screen
 *
 * Adding a custom update count button and hooking up the functionality for it
 */
jQuery( document ).ready( function( $ ) {
    // Store the button HTML matching the other settings
    var html = '<tr valign="top" class=""><th scope="row" class="titledesc">' + wcwl_settings.update_desc + '</th><td class="forminp forminp-checkbox"><fieldset><legend class="screen-reader-text"><span>' + wcwl_settings.update_desc + '</span></legend><button name="woocommerce_waitlist_update_counts" id="woocommerce_waitlist_update_counts" type="button" class="button">' + wcwl_settings.update_button_text + '</button><span class="wcwl_settings_update_text">' + wcwl_settings.update_warning + '</span></fieldset></td></tr>';
    // Grab the table we need to store into
    var table = $( '#woocommerce_waitlist_registration_needed' ).closest( 'tbody' );
    // Insert our new setting into the top of the table
    $( html ).prependTo( table );
    // Show all settings (this avoids the delay for the button appearing while JS is loading)
    $( '.form-table' ).show();
    // Hook up ajax function to the button
    $( '#woocommerce_waitlist_update_counts' ).click( updateWaitlistCounts );
    // Function to fire the call to update waitlist counts
    function updateWaitlistCounts() {

        var data = {
            action: 'wcwl_get_products',
            wcwl_get_products: wcwl_settings.get_products_nonce
        };
        $.post( ajaxurl, data, function( products ) {
            products = $.parseJSON( products );
            if( products.length > 0 ) {
                outputUpdating( 1, products.length );
                doUpdates( products );
            } else {
                alert( 'No products found.' );
            }
        } );
    }
    // Carry out updates
    function doUpdates( products ) {
        var products_total = products.length;
        var current_total = 0;

        // Run ajax request
        function runRequest() {
            // Check to make sure there are more products to update
            if( products.length > 0 ) {

                var current = products.splice( 0, 10 );

                // Make the AJAX request with the given products
                var data = {
                    action: 'wcwl_update_counts',
                    products: current,
                    wcwl_update_counts: wcwl_settings.update_counts_nonce,
                    success: function() {
                        runRequest();
                    }
                };
                $.post( ajaxurl, data, function( response ) {
                    console.log( response );
                    current_total = parseInt( current.length ) + parseInt( current_total );
                    if( current_total == products_total ) {
                        outputSuccess( products_total );
                    } else {
                        updateMessageCounts( current_total );
                    }
                } );
            }
        }
        runRequest();
    }
    // Add notice and loader to let user know we're running updates and disable buttons
    function outputUpdating( current, total ) {
        $( '#woocommerce_waitlist_update_counts, .woocommerce-save-button' ).prop( 'disabled', true );
        var html = '<div id="message" class="updated inline"><p><strong>' + wcwl_settings.update_message + '</strong></p></div>';
        $( html ).insertBefore( '.form-table' );
        $( '.wcwl_current_update' ).text( current );
        $( '.wcwl_total_updates' ).text( total );
    }
    // Update notice to reflect current product updates
    function updateMessageCounts( current ) {
        $( '.wcwl_current_update' ).text( current );
    }
    // Update notice, remove loader and re-enable buttons on complete
    function outputSuccess( total ) {
        $( '#message p strong' ).html( wcwl_settings.update_message_complete );
        $( '.wcwl_total_updates' ).text( total );
        $( '#woocommerce_waitlist_update_counts, .woocommerce-save-button' ).prop( 'disabled', false );
    }
} );

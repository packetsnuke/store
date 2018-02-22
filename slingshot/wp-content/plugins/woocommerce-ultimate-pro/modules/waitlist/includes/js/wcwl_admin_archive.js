// JS required for the waitlist archive admin screen
jQuery( document ).ready( function( $ ){

    // Toggle display of variations
    $( ".wcwl_variation_title" ).click( function() {
        $( this ).closest( '.wcwl_variation_tab' ).find( "ul.wcwl_archive_list" ).slideToggle();
    });

    // Manually change title as submenu has no parent so title is null
    document.title = 'Waitlist Archive ' + document.title;

    // Adjust menu css classes so that products is the "current" menu and reapply dashboard hover effect
    $( '.wp-has-current-submenu' ).each( function() {
        $( this ).addClass( 'wp-not-current-submenu' );
        $( this ).removeClass( 'wp-has-current-submenu' );
    });
    $( '.menu-icon-product' ).each( function() {
        $( this ).removeClass( 'wp-not-current-submenu' );
        $( this ).addClass( 'wp-has-current-submenu' );
    });
    $( '.wp-first-item' ).hover( function() {
        $( this ).find( '.wp-submenu' ).toggle();
    });
});
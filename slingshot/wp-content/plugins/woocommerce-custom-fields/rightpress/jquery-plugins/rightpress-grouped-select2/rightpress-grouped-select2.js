/**
 * RightPress - Grouped Select2 Scripts
 */

(function () {

    /**
     * Register plugin
     */
    jQuery.fn.rightpress_grouped_select2 = function(params) {

        /**
         * Select2 configuration
         */
        var config = jQuery.extend({
            minimumResultsForSearch: Infinity,
            dropdownCssClass: '',
            width: '100%',
        }, params);

        // Add main class
        config.dropdownCssClass += ' rightpress_grouped_select2';

        /**
         * Initialize Select2
         */
        if (typeof RP_Select2 !== 'undefined') {
            RP_Select2.call(this, config);
        }
        else if (typeof this.selectWoo !== 'undefined') {
            this.selectWoo(config);
        }

        /**
         * Auto open group with selected option
         */
        this.on('select2:open', function() {

            setTimeout(function() {

                jQuery('.rightpress_grouped_select2 .select2-results .select2-results__option--highlighted[aria-selected]').each(function() {

                    var group_container = jQuery(this).closest('.select2-results__options');
                    var group_header = group_container.prev('.select2-results__group');

                    // Show options
                    group_container.show();

                    // Highlight header
                    group_header.addClass('rightpress_grouped_select2_header_expanded');

                    // Scroll to header
                    scroll_to_group(group_header);
                });
            }, 250);
        });

        /**
         * Check if body click event handler was already added (we only need one handler for any number of fields)
         */
        var body_click_handler_added = false;
        var body_events = jQuery('body').data('events');

        if (typeof body_events !== 'undefined' && typeof body_events.click === 'object') {
            jQuery.each(body_events.click, function(index, body_event) {
                if (typeof body_event.selector !== 'undefined' && body_event.selector === '.rightpress_grouped_select2 .select2-results > ul > li > .select2-results__group') {
                    body_click_handler_added = true;
                    return false;
                }
            });
        }

        /**
         * Add body click event handler to enable collapsible option groups
         */
        if (!body_click_handler_added) {

            jQuery('body').on({
                click: function () {

                    // Reference group container and parent container
                    var container = jQuery(this).closest('.select2-results__option[role="group"]');
                    var parent = container.parent();

                    // Reference options
                    var options = container.find('.select2-results__options');

                    // Options are hidden
                    if (options.css('display') === 'none') {

                        // Add expanded class
                        jQuery(this).addClass('rightpress_grouped_select2_header_expanded');

                        // Show options
                        options.show();

                        // Hide options for other groups
                        parent.find('.select2-results__option[role="group"] .select2-results__options').not(options).each(function() {
                            jQuery(this).prev('.select2-results__group').removeClass('rightpress_grouped_select2_header_expanded');
                            jQuery(this).hide();
                        });

                        // Scroll to current group
                        scroll_to_group(jQuery(this));
                    }
                    // Options are displayed
                    else {
                        jQuery(this).removeClass('rightpress_grouped_select2_header_expanded');
                        options.hide();
                    }
                },
            }, '.rightpress_grouped_select2 .select2-results > ul > li > .select2-results__group');
        }

        /**
         * Scroll to group header
         */
        function scroll_to_group(group_header)
        {
            var scroll_to = 0;

            group_header.closest('li').prevAll().each(function() {
                scroll_to += jQuery(this).outerHeight();
            });

            document.getElementById(jQuery('.rightpress_grouped_select2 .select2-results__options').attr('id')).scrollTop = scroll_to;
        }

    };

}());

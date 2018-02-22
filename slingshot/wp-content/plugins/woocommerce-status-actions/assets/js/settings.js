jQuery(document).ready(function () {
    if(jQuery('#trigger_options_days_off').length){
        var days_off = JSON.parse(jQuery('#trigger_options_days_off').val());
        var calendar = jQuery('#days_off').multiDatesPicker({
            dateFormat: 'dd/mm/yy',
            onSelect: function () {
                jQuery('#trigger_options_days_off').val(jQuery(this).multiDatesPicker('getDates'));
            },
        });
        if (days_off[0]) {
            calendar.multiDatesPicker('addDates', days_off);
        }
        jQuery('#trigger_options_days_off').val(jQuery('#days_off').multiDatesPicker('getDates'));
    }
});

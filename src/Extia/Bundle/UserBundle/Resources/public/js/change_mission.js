jQuery(document).ready(function($) {

    // mission select toggle
    $('#ic-toggler input').on('change', function() {
        if ($(this).is(':checked')) {
            $('#select-mission').hide();
            $('#select-mission').find('label').removeClass('required');
            $('#select-mission').find('select, input').attr('required', null);
        }
        else {
            $('#select-mission').show();
            $('#select-mission').find('label').addClass('required');
            $('#select-mission').find('select, input').attr('required', 'required');
        }
    });
});

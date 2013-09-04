jQuery(document).ready(function($) {

    // mission select toggle
    var onProfile = function() {
        if ($('#profile-toggler input').is(':checked')) {
            $('#select-manager').show();
            $('#select-manager').find('label').addClass('required');
            $('#select-manager').find('select').attr('required', 'required');

            $('#select-mission').hide();
            $('#select-mission').find('label').removeClass('required');
            $('#select-mission').find('select').attr('required', null);
        }
        else {
            $('#select-mission').show();
            $('#select-mission').find('label').addClass('required');
            $('#select-mission').find('select').attr('required', 'required');

            $('#select-manager').hide();
            $('#select-manager').find('label').removeClass('required');
            $('#select-manager').find('select').attr('required', null);
        }
    };

    $('#profile-toggler input').on('change', onProfile);
    onProfile();
});

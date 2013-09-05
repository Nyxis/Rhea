jQuery(document).ready(function($) {
    $('#change_mission').each(function() {

        var $modal = $(this);

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

        // modal switching with mission creation
        $modal.find('#select-mission .btn').on('click', function() {
            $modal.find('a[data-dismiss="modal"]').trigger('click'); // close mission switching to open new mission

            // reopen on close
            $('#new_mission').on('hide', function() {
               $modal.modal('show');
            });
        });
    });
});

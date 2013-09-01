jQuery(document).ready(function($) {

    // new mission submit
    $('#new_mission form').on('submit', function(e){
        e.preventDefault();
        var form = $(this);
        $.post(form.attr('action'), form.serialize(), function(data) {

            var $select = $('[data-mission-select] select');
            if (data.missions) {
                $select.empty().append('<option value=""></option>');
                $.each(data.missions, function(index, mission) {
                    $select.append('<option value="'+ index +'">'+ mission +'</option>')
                });
            }
            if (data.current) {
                $select.val(data.current);
            }

            // close modal
            form.find('[data-dismiss]').trigger('click');

            // adds message
            if (data.notifications) {
                $.each(data.notifications, function(i, notif) {
                    Notifications.push({
                        "imagePath": 'placeholder',
                        "autoDismiss": 10,
                        "text": notif
                    });
                });
            }
        });

        return false;
    });

    // new client toggle display
    $('#modal_new_client_display input').on('change', function() {
        if ($(this).is(':checked')) {
            $('#new_client_form').show();
            $('#new_client_form label[for*=title]').addClass('required');
            $('#new_client_form input[name*=title]').attr('required', 'required');
        }
        else {
            $('#new_client_form').hide();
            $('#new_client_form label[for*=title]').removeClass('required');
            $('#new_client_form input[name*=title]').attr('required', null);
        }
    });


});
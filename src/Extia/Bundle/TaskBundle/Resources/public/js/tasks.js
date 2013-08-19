jQuery(document).ready(function($) {

    // differ links
    $('a[data-differ-id]').on('click', function(e) {
        e.preventDefault();

        $modal = $('#differ-task');
        $modal.find('input[type="text"][name*="task_id"]')
            .val($(this).data('differ-id'))
            .parents('.control-group').hide();

        $modal.modal();
    });

});
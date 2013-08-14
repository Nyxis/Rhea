jQuery(document).ready(function($) {

    // all documents buttons are decorated
    $('input[type=file][data-document]').bootstrapFileInput();

    // ajax upload on document list
    document.submittable_documents_forms = {};
    $('.document-list .document a[data-document-upload] + form.document-upload').each(function(index, elt) {
        var $form  = $(elt);
        var $link  = $form.prev();
        var $filer = $form.find('input[type=file]');

        if (!$form.length || !$link.length || !$filer.length) {
            return;
        }

        document.submittable_documents_forms[$form.attr('id')] = false;

        $link.on('click', function(e) {
            e.preventDefault();
            $filer.trigger('click');
            document.submittable_documents_forms[$form.attr('id')] = true;
        });

        $filer.on('change', function(e) {
            if (!document.submittable_documents_forms[$form.attr('id')]) {
                return;
            }
            document.submittable_documents_forms[$form.attr('id')] = false;
            $form.submit();
        });

        $form.on('submit', function(e) {

            $(this).ajaxSubmit({
                dataType: 'json',
                error: function(data, code) {
                    // window.location.reload();
                },
                success: function(data) {
                    Notifications.push({
                        "imagePath": 'placeholder',
                        "autoDismiss": 5,
                        "text": data.message
                    });

                    if (data.ext) {
                        $form.parents('.dtable').find('.doc-icon50').attr('class', 'dtable-cell doc-icon50 ' + data.ext);
                    }
                    if (data.filename) {
                        $form.parents('.dtable').find('.title').html(data.filename);
                    }
                }
            });

            return false;
        });
    });
});

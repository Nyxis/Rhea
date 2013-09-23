jQuery(document).ready(function($) {

    // display form on click on comment button
    $('.add_comment a[data-form-comment-open]').on('click', function(e){
        e.preventDefault();
        var parent = $(this).parents('.add_comment');
        parent.hide();
        parent.next('.comment_form').show();
    });

    // hide form on cancel
    $('.comment_form button[data-form-comment-close]').on('click', function(e){
        e.preventDefault();

        var parent = $(this).parents('.comment_form');
        if (parent.siblings('.comment_list').find('blockquote').length < 1) {
            $(this).parents('.comments').slideUp();
        }
        else {
            parent.hide();
            parent.prev('.add_comment').show();
        }
    });

    // comment submit
    $('.comment_form form').on('submit', function(e){
        e.preventDefault();
        var form = $(this);
        $.post(form.attr('action'), form.serialize(), function(data) {
            form.parents('.comment_form').siblings('.comment_list').append(data);
            form.find('textarea').val('');
        });

        return false;
    });

});

jQuery(document).ready(function($) {
    $('.main-nav li.parent > a').on('click', function(e) {
        e.preventDefault();
        var parentLi = $(this).parents('li.parent');
        parentLi.toggleClass('open');
    });
    $('.main-nav li.active > a').off('click').on('click', function(e) {
        e.preventDefault();
    });
});

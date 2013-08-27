jQuery(document).ready(function($) {

    // js pagination
    $('[data-pager]').each(function(index, elt) {

        var options = {
            'per_page': 2 // @change with more data
        };

        var $pageSelector = $(elt).find('.page-selector');
        var $dataLanes    = $(elt).find('tbody > tr');

        var total = $dataLanes.length;

        if (total <= options.per_page) {
            return;
        }

        $dataLanes.hide();

        var nbPages = Math.ceil(total/options.per_page);
        for (var i = 1; i <= nbPages; i++) {
            $pageSelector.append('<a href="#" class="button">'+i+'</a>');
        };

        $pageSelector.find('a').on('click', function(e) {
            e.preventDefault();
            var link = $(this);
            var page = parseInt(link.html());

            link.addClass('inactive').siblings().removeClass('inactive');

            $dataLanes.hide();
            for (var i = (page-1)*options.per_page; i < (page)*options.per_page; i++ ) {
                $dataLanes.eq(i).show();
            };
        });

        $pageSelector.find('a:first-child').trigger('click');
    });


    // form submit
    $('a[data-form]').on('click', function(e) {
        e.preventDefault();
        $($(this).data('form')).trigger('submit');
    });

});

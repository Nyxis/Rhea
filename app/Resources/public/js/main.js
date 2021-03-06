jQuery(document).ready(function($) {

    // js pagination
    $('[data-pager]').each(function(index, elt) {

        var options = {
            'per_page': 5 // @change with more data
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

    // radio as buttons

    function activateLabel(input) {
        var id = input.attr('id');
        input.parent().siblings('label').removeClass('active');
        input.parent().addClass('active');
    };

    $('form .asButtons').each(function(index, elt) {

        // build tb btn-group
        var buttons = $(elt).find('label');
        var radios  = buttons.find('input');

        radios.hide().on('change', function(){
            activateLabel($(this));
        });

        buttons.addClass('btn')
            .parent().addClass('btn-group');

        $.each(radios.filter(':checked'),function(){
            activateLabel($(this));
        });
    });

    // // autocomplete example - v1.1
    // $("#mission_client").tokenInput("{{ path('extia_mission_company_ajax_list') }}", {
    //     minChars: 2,
    //     tokenLimit: 1,
    //     zindex: 9999,
    //     theme: 'rhea'
    // });

});

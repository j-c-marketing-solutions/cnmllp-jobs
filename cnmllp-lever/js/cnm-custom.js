jQuery(document).ready( function($){


function jobSearch(){

    $(document).on('keyup','#job_search',function (e) {

        //remove active job categories..
        $(".jobs-teams").find("a").removeClass("active");

        if(e.which === 13) {

            var term = $(this).val();

            //perform search..

            $.blockUI({
                overlayCSS: { backgroundColor: '#ddd' },
                css : {
                    border : 'none',
                    padding: '20px',
                    'border-radius' : '5px'
                },
                message : 'Searching jobs, please wait....'
            });


            var team = $('#team-filter').val();
            var location = $('#location-filter').val();

            $.ajax({
                type: "post",
                dataType: "json",
                url: my_ajax_object.ajax_url,
                data: {action : 'job_search', term : term, team : team, location : location},
                success: function(data){
                    $('#jlist').html(data.html);
                    $('#pagination_wrap').html(data.pages);
                    $.unblockUI();
                }
            });


        }

    });


    $(document).on('click','#job_search_btn',function (e) {

        //remove active job categories..
        $(".jobs-teams").find("a").removeClass("active");


        var term = $('#job_search').val();

        //perform search..

        $.blockUI({
            overlayCSS: { backgroundColor: '#ddd' },
            css : {
                border : 'none',
                padding: '20px',
                'border-radius' : '5px'
            },
            message : 'Searching jobs, please wait....'
        });


        var team = $('#team-filter').val();
        var location = $('#location-filter').val();

        $.ajax({
            type: "post",
            dataType: "json",
            url: my_ajax_object.ajax_url,
            data: {action : 'job_search', term : term, team : team, location : location},
            success: function(data){
                $('#jlist').html(data.html);
                $('#pagination_wrap').html(data.pages);
                $.unblockUI();
            }
        });


    });

    $('#team-filter, #location-filter').change(function (e) {

        e.preventDefault();
        e.stopImmediatePropagation();

        $.blockUI({
            overlayCSS: { backgroundColor: '#ddd' },
            css : {
                border : 'none',
                padding: '20px',
                'border-radius' : '5px'
            },
            message : 'Searching jobs, please wait....'
        });

        var team = $('#team-filter').val();
        var location = $('#location-filter').val();
        var term = $('#job_search').val();

        $.ajax({
            type: "post",
            dataType: "json",
            url: my_ajax_object.ajax_url,
            data: {action : 'job_search', term : term, team : team, location : location},
            success: function(data){
                $('#jlist').html(data.html);
                $('#pagination_wrap').html(data.pages);
                $.unblockUI();
            }
        });


    });


    $('#resetjs').click(function (e) {

        e.preventDefault();
        e.stopImmediatePropagation();

        $('#team-filter').val('');
        $('#location-filter').val('');
        var term = $('#job_search').val('');


        $.blockUI({
            overlayCSS: { backgroundColor: '#ddd' },
            css : {
                border : 'none',
                padding: '20px',
                'border-radius' : '5px'
            },
            message : 'Resetting job search, please wait....'
        });


        $.ajax({
            type: "post",
            dataType: "json",
            url: my_ajax_object.ajax_url,
            data: {action : 'load_jobs'},
            success: function(data){
                $('#jlist').html(data.html);
                $('#pagination_wrap').html(data.pages);
                $.unblockUI();
            }
        });

    });

}

jobSearch();


$(document).on('click','a.lever-page',function (e) {

    e.preventDefault();
    e.stopImmediatePropagation();

    $('a.lever-page').removeClass('lever-page-active');
    $(this).addClass('lever-page-active');

    $.blockUI({
        overlayCSS: { backgroundColor: '#ddd' },
        css : {
            border : 'none',
            padding: '20px',
            'border-radius' : '5px'
        },
        message : 'Searching jobs, please wait....'
    });

    var team = $('#team-filter').val();
    var location = $('#location-filter').val();
    var term = $('#job_search').val();
    var page = $(this).data('page');

    $.ajax({
        type: "post",
        dataType: "json",
        url: my_ajax_object.ajax_url,
        data: {action : 'job_search', term : term, team : team, location : location, page : page},
        success: function(data){
            $('#jlist').html(data.html);
            $.unblockUI();

            $('html, body').animate({
                scrollTop: $("#jobs-container").offset().top - 100
            }, 800);

        }
    });

});

});
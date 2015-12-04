jQuery(document).ready( function($) {
    
    // Add Main menu of the page Write and read
    $('#write_here_ajax_wrap').before('<div class="ajax-top-btn" id="write_btn_ajax"><span class="dashicons dashicons-edit"></span> Write</div>');
    $('#wh-form-on-ajax-page').before('<div class="ajax-top-btn" id="list_btn_ajax"><span class="dashicons dashicons-arrow-left-alt"></span> Go Back</div>');
    $('#wh-form-on-ajax-page').after('<div class="ajax-top-btn" id="write_btn_ajax_bottom"><span class="dashicons dashicons-edit"></span></div>');
    
    /*
    **  Get posts on page loads
    */
    function write_here_list_page(){
        $.ajax({
            type: "GET",
            url: ajax_object.ajax_url,
            dataType: 'html',
            data: {
                action: 'write_here_get_posts',
                security: ajax_object.ajax_nonce
            },
            error: function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
                return false;
            },
            success: function(data) {
                //console.log(data);
                $('.ajax-top-btn').hide();
                $('#write_btn_ajax').show();
                $('#write_here_ajax_wrap').html('');
                $('#write_here_ajax_wrap').append(data);
                $('#write_btn_ajax_bottom').removeClass('dis-no');
                // Remove href on a links in pagination to prevent open it on new tab or window
                $('.wh-pagenavi a').removeAttr('href');
            }

        });
    }
    // Initiate function on page load
    write_here_list_page();
    
    // Get pagenumber on pagination
    function find_page_number( element ) {
        element.find('span').remove();
        return parseInt( element.html() );
    }

    // AJAX pagination
    $(document).on( 'click', '.wh-pagenavi a', function( event ) {
        event.preventDefault();
        page = find_page_number( $(this).clone() );

        $.ajax({
            type: "GET",
            url: ajax_object.ajax_url,
            dataType: 'html',
            data: {
                action: 'write_here_get_posts',
                security: ajax_object.ajax_nonce,
                page: page
            },
            error: function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
                return false;
            },
            success: function(data) {
                $('#write_here_ajax_wrap').html('');
                $(document).scrollTop(0);
                $('#write_here_ajax_wrap').append(data);
                $('.wh-pagenavi a').removeAttr('href');
            }
        });

    });
    
    // Display write form
    $(document).on('click', '#write_btn_ajax, #write_btn_ajax_bottom', function() {
        $('#write_here_ajax_wrap').hide();
        $('.ajax-top-btn').hide();
        $('#write_btn_ajax_bottom').addClass('dis-no');
        $(document).scrollTop(0);
        $('#wh-form-on-ajax-page').show();
        $('#list_btn_ajax').show();
    });
    
    // Extra write button
    $(window).scroll(function() {
        if($(window).scrollTop() > $(document).height()*0.1){
            $('#write_btn_ajax_bottom').css('display', 'block');
        }else{
            $('#write_btn_ajax_bottom').css('display', 'none');
        }
    });
    
    // Load post list
    $(document).on('click', '#list_btn_ajax', function() {
        $('#wh-form-on-ajax-page').hide();
        $('#write_here_ajax_wrap').show();
        write_here_list_page();
    });

});
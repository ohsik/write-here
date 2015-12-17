jQuery(document).ready( function($) {
    
    // Set post_parent to 0(Unattached) for all images uploaded via Add Media button.
    // Rather than parent them to the page contains [write-here] or [write-here-edit]
    jQuery('.write-here #insert-media-button').on('click', function( event ){
        event.preventDefault();
        wp.media.model.settings.post.id = 0;
    });
    
    /*
    **  Get value from TinyMCE editor 
    */
    function tmce_getContent(editor_id, textarea_id) {
        if ( typeof editor_id == 'wh_content' ) editor_id = wpActiveEditor;
        if ( typeof textarea_id == 'wh_content' ) textarea_id = editor_id;

        if ( $('#wp-'+editor_id+'-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id) ) {
            return tinyMCE.get(editor_id).getContent();
        }else{
            return $('#'+textarea_id).val();
        }
    }
    
    /*
    **  Upload featured image AJAX
    */ 
    $(document).on('change', '#wh_image_upload', function(){
        var userFile    =   new FormData();  
        var fileInput   =   $( '#wh_image_upload' )[0].files[0];
        //console.log(fileInput);
        
        // Allow image files only to upload 
        var imagefile = fileInput.type;
        var match= ["image/jpeg","image/png","image/jpg","image/gif"];
        if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2]) || (imagefile==match[3])))
        {
            alert('Image file only');
            $('#wh_image_upload').val('');
            $('#attachment_id').val('');
            return false;
        }
        // Set max file upload size
        if(fileInput.size > 10485760){
            alert('Max upload file size 10 MB');
            $('#wh_image_upload').val('');
            $('#attachment_id').val('');
            return false;
        }
        
        userFile.append('file', fileInput);
        userFile.append('action', 'write_here_img_upload');
        userFile.append('security', ajax_object.ajax_nonce);
        
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: userFile,
            processData: false,
            contentType: false,
            error: function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
                return false;
            },
            success: function(data) {
                
                //console.log(data);
                var json = $.parseJSON(data);
                console.log(json.att_id + ' Uploaded!');
                
                $('#attachment_id').val(json.att_id);
                $('#wh_img_preview').css('background-image', 'url('+json.att_url+')');
                $('#wh_img_preview').fadeIn();
                $('#wh_image_upload').fadeOut();
            }
        });
    });

    /*
    **  Remove featured image AJAX
    */
    $(document).on('click', '.prv_del', function() {
        
        var attID = $('#attachment_id').val();

        $.ajax({
            type: 'post',
            url: ajax_object.ajax_url,
            data: {
                action: 'delete_attachment',
                att_ID: attID,
                post_type: 'attachment',
                security: ajax_object.ajax_nonce
            },
            error: function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
                return false;
            },
            success: function() {
                console.log(attID + ' Removed!');
                
                $('#wh_image_upload').val('');
                $('#attachment_id').val('');
                $('#wh_img_preview').fadeOut(); 
                $('#wh_image_upload').fadeIn(); 
            }
        });
    });
    
    /*
    **  Validation new_post form and post AJAX
    */
    $('.write-here #new_post').validate({
        rules: {
            title: {
                required: true,
                maxlength: 70
            }
        },
        submitHandler: function(form) {
            // Get content from TinyMCE editor and assign it in hidden field
            $('#wh_content_js').val(tmce_getContent('wh_content', 'wh_content'));
            // Disable submit button
            $('#new_post #submit').attr('disabled', true).val('Submitting!');
            
            // Serialize form data
            dataString = $('#new_post').serialize();
            
            // Post data AJAX
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                action: 'write_here_new_post',
                data: dataString,
                error: function(jqXHR, textStatus, errorThrown){                                        
                    console.error('The following error occured: ' + textStatus, errorThrown);
                    return false;
                },
                success: function(data) {
                    console.log('Post Added! ' + data);
                    // Redirect to new post
                    window.location.href = data;
                }  
            });
        }
    });

    /*
    **  Validation edit_post form and post AJAX
    */
    $('.write-here #edit_post').validate({
        rules: {
            title: {
                required: true,
                maxlength: 70
            }
        },
        submitHandler: function(form) {
            // Get content from TinyMCE editor and assign it in hidden field
            $('#wh_content_js').val(tmce_getContent('wh_content', 'wh_content'));
            // Disable edit button
            $('#edit_post #submit').attr('disabled', true).val('Updating!');
            
            // Serialize form data
            dataString = $('#edit_post').serialize();     
            
            // Post data AJAX
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                action: 'write_here_edit_post',
                data: dataString,
                error: function(jqXHR, textStatus, errorThrown){                                        
                    console.error('The following error occured: ' + textStatus, errorThrown);
                    return false;
                },
                success: function(data) {                                       
                    console.log('Post Updated! ' + data);
                    // Redirect to new post
                    window.location.href = data;
                }  
            });
        }
    });
    
    /*
    **  Show loading gif on AJAX requests
    */
    var loading = $('<div id="loading_ajax"></div>');
    $(document).ajaxStart(function() {
        $('body').append( loading );
        $('#loading_ajax').show();
    });
    $(document).ajaxStop(function() {
        $('#loading_ajax').hide();
    });
    
});
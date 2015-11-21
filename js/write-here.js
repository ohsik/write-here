jQuery(document).ready( function($) {
    // Remove featured image on edit page
    $('.remImage').live('click', function() {
        var attID = jQuery(this).attr('name');
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'delete_attachment',
                att_ID: jQuery(this).attr('name'),
                _ajax_nonce: jQuery('#nonce').val(),
                post_type: 'attachment'
            },
            success: function() {
                //console.log( '#file-' + attID );
                $ ('#file-' + attID ).fadeOut(); 
                $('#wh_image_upload').fadeIn(); 
            }
        });
    });
        
    // Image preview
    $('#wh_image_upload').change( function(){
        var files = !!this.files ? this.files : [];
        if (!files.length || !window.FileReader) return; // no file selected, or no FileReader support

        if (/^image/.test( files[0].type)){ // only image file
            var reader = new FileReader(); // instance of the FileReader
            reader.readAsDataURL(files[0]); // read the local file

            reader.onloadend = function(){ // set image data as background of div
                $("#wh_img_preview").css("background-image", "url("+this.result+")");
                $("#wh_img_preview").css('display', 'block');
            }
        }else{
            alert('Image files only!');
        }                                                    
    });
});
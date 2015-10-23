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
                console.log('#file-'+attID)
                $('#file-'+attID).fadeOut(); 
                $('#wh_image_upload').fadeIn(); 
            }
        });
    });
        
});
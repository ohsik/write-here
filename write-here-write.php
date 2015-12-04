<?php
/*
**  Write Here
    
    Front end From
    ------------------------------------------------------------------
*/
function write_here_form(){
    ob_start();
?>
<div class="write-here write">
    <?php write_here_show_error_messages(); ?>
    <form id="new_post" name="new_post" method="post" action="" enctype="multipart/form-data">
        <label for="wh_image_upload">Featured Image</label>
        <div id="wh_img_preview">
            <p class="prv_del">Delete</p>
        </div>
        <input type="file" name="wh_image_upload" id="wh_image_upload" multiple="false" />
        
        <label for="title">Title</label>
        <input type="text" id="title" name="title" />

        <label for="wh_content">Content</label>
        <?php
            $content = '';
            $editor_id = 'wh_content';
            wp_editor( $content, $editor_id );
        ?>

        <label for="cat">Category</label>
        <?php wp_dropdown_categories( 'taxonomy=category&hide_empty=0' ); ?>

        <label for="post_tags">Tags</label>
        <input type="text" id="post_tags" name="post_tags" />

        <label for="date">Date</label>
        <div id="timestampdiv" class="hide-if-js"><?php write_here_time(0, 0, 5); ?></div>
        
        <input type="submit" value="Publish" id="submit" name="submit" />
        <input type="hidden" name="action" value="write_here_new_post" />
        <input type="hidden" name="wh_content_js" id="wh_content_js" value="" />
        <input type="hidden" name="attachment_id" id="attachment_id" value="" />
        <?php wp_nonce_field( 'new-post', 'new-post-nonce' ); ?>
    </form>
</div>
<?php  
    return ob_get_clean();
}

/*
**  Process data from front end form
    http://codex.wordpress.org/Function_Reference/wp_insert_post
*/
function write_here_add_new_post() {
    //var_dump($_POST);
    if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == "write_here_new_post" && wp_verify_nonce( $_POST['new-post-nonce'], 'new-post' )  ) {
        // Set default date on the post
        $postdate = date('Y-m-d H:i:s');
        // Get values from front end form
        $wh_month   = $_POST['mm'];
        $wh_day     = $_POST['jj'];
        $wh_year    = $_POST['aa'];
        $wh_hour    = $_POST['hh'];
        $wh_min     = $_POST['mn'];
        $wh_sec     = $_POST['ss'];
        $title      = wp_strip_all_tags($_POST['title']);
        $content    = $_POST['wh_content_js'];
        $tags       = $_POST['post_tags'];
        $cat        = $_POST['cat'];
        $att_id     = $_POST['attachment_id'];
        
        // Server side validation
        if ($title == '') {
            write_here_errors()->add('title_not_vaild', __('Title not valid'));
        }
        if ($content == '') {
            write_here_errors()->add('content_not_vaild', __('Content not valid'));
        }
        if ( strlen($wh_month) != 2 || strlen($wh_day) != 2 || strlen($wh_year) != 4 || strlen($wh_hour) != 2 || strlen($wh_min) != 2 || !ctype_digit($wh_month) || !ctype_digit($wh_day) || !ctype_digit($wh_year) || !ctype_digit($wh_hour) || !ctype_digit($wh_min) ) {
            write_here_errors()->add('date_not_vaild', __('Date not valid'));
        } else {
            $postdate = $wh_year.'-'.$wh_month.'-'.$wh_day.' '.$wh_hour.':'.$wh_min.':'.$wh_sec;
        }
        
        // Add the content of the form to $post as an array
        $new_post = array(
            'post_title'    => $title,
            'post_content'  => $content,
            'post_category'	=> array($cat),     // Default empty.
            'tags_input'    => array($tags),    // Default empty.
            'post_status'   => 'publish',       // Choose: publish, preview, future, draft, etc. Default 'draft'.
            'post_date'     => $postdate,       // The time post was made.
            'post_date_gmt' => $postdate        // The time post was made, in GMT.
        );

        $errors = write_here_errors()->get_error_messages();
        
        // only create post if there are no errors
		if(empty($errors)) {
            // save the new post and return its ID
            $post_id = wp_insert_post($new_post);
            //echo "Added post id: ".$post_id;
            
            // if post added successfully.
            if($post_id) {
                // Set thumbnail
                // $newupload returns the attachment id of the file that
                $newupload = set_post_thumbnail( $post_id, $att_id );
                
                // Set post_parent for attachemnt
                wp_update_post(
                    array(
                        'ID' => $att_id, 
                        'post_parent' => $post_id
                    )
                );

                // This will redirect you to the newly created post (Using GUID)
                $post = get_post($post_id);
                echo site_url('/?p=').$post->ID;
                
                exit();
            }
        }
    }
    die();
}
add_action( 'wp_ajax_write_here_new_post', 'write_here_add_new_post' );
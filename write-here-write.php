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
    <form id="new_post" name="new_post" method="post" action="">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" />

        <label for="wh_content">Content</label>
        <?php
            $content = '';
            $editor_id = 'wh_content';
            wp_editor( $content, $editor_id );
        ?>

        <label for="cat">Category</label>
        <?php wp_dropdown_categories( 'show_option_none=Category&taxonomy=category&hide_empty=0' ); ?>

        <label for="post_tags">Tags</label>
        <input type="text" id="post_tags" name="post_tags" />

        <label for="date">Date</label>
        <div id="timestampdiv" class="hide-if-js"><?php write_here_time(0, 0, 5); ?></div>

        <input type="submit" value="Publish" id="submit" name="submit" />
        <input type="hidden" name="action" value="write_here_new_post" />
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
    if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "write_here_new_post" && wp_verify_nonce( $_POST['new-post-nonce'], 'new-post' )  ) {
        // Set default date on the post
        $postdate = date('Y-m-d H:i:s');
        // Get values from front end form
        $wh_month   = $_POST['mm'];
        $wh_day     = $_POST['jj'];
        $wh_year    = $_POST['aa'];
        $wh_hour    = $_POST['hh'];
        $wh_min     = $_POST['mn'];
        $wh_sec     = $_POST['ss'];
        $title      =  wp_strip_all_tags($_POST['title']);
        $content    = $_POST['wh_content'];
        $tags       = $_POST['post_tags'];
        $cat        = $_POST['cat'];
        
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
            //save the new post and return its ID
            $post_id = wp_insert_post($new_post);
            
            if($post_id) {
                // This will redirect you to the newly created post (Using GUID)
                $post = get_post($post_id);
                wp_redirect($post->guid);
                exit();
            }
        }
        
    }
}
add_action('init', 'write_here_add_new_post');

// used for tracking error messages
function write_here_errors(){
    static $wp_error; // Will hold global variable safely
    return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
}

// displays error messages from form submissions
function write_here_show_error_messages() {
	if($codes = write_here_errors()->get_error_codes()) {
        echo '<div class="form-error">';
        // Loop error codes and display errors
        foreach($codes as $code){
            $message = write_here_errors()->get_error_message($code);
            echo '<span class="error"><strong>' . __('Error') . '</strong>: ' . $message . '</span><br/>';
        }
        echo '</div>';
	}	
}
?>
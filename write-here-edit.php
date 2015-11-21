<?php
/*
**  Write Here

    Edit posts
    ------------------------------------------------------------------
*/
function write_here_edit_form(){
    // Check if nonce exist
    if(isset($_REQUEST['_wpnonce'])){
        $nonce = $_REQUEST['_wpnonce'];
        
        if ( !wp_verify_nonce( $nonce ) ) {
            // This nonce is not valid.
            die( 'Security check' ); 
        } else {
            // nonce is valid, show edit form
            $post_id = $_REQUEST['post'];
            if ($post_id){
                $post_to_edit = get_post($post_id);

        ?>
                <div class="write-here edit">
                    <?php 
                        write_here_show_error_messages();
                        //write_here_show_success_messages( 'Post Updated!' );    // <------------------------------------------------ Update Message need to be fixed
                    ?>
                    <form id="edit_post" name="edit_post" method="post" action="" enctype="multipart/form-data">

                        <label for="wh_image_upload">Featured Image</label>
                        <div id="wh_img_preview"></div>
                        <?php 
                            //http://wordpress.stackexchange.com/questions/36361/delete-attachment-from-front-end
                            if(get_the_post_thumbnail($post_id)){
                                $attachment_id = get_post_thumbnail_id( $post_id );
                                ?>
                                <div class="wh-f-img">
                                    <?php
                                        echo get_the_post_thumbnail($post_id, 'thumbnail', array('class' => 'file-'.$attachment_id.''));
                                    ?>
                                    <p id="rm_fi"><a class="remImage" name="<?php echo $attachment_id; ?>" href=""><?php _e('Delete'); ?></a></p> <?php //<---------------- AJAX delete does not work smoothly ?>
                                    <input type="hidden" id="att_remove" name="att_remove[]" value="<?php echo $attachment_id; ?>" />
                                    <input type="hidden" name="nonce" id="nonce" value="<?php echo wp_create_nonce( 'delete_attachment' ); ?>" />
                                    <script type="text/javascript"> var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>'; </script>
                                </div>
                                <input type="file" name="wh_image_upload" id="wh_image_upload" multiple="false" style="display: none;" />
                        <?php }else{ ?>
                            <input type="file" name="wh_image_upload" id="wh_image_upload" multiple="false" />
                        <?php } ?>
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?php echo $post_to_edit->post_title; ?>" />

                        <label for="wh_content">Content</label>
                        <?php
                            $content = $post_to_edit->post_content;
                            $editor_id = 'wh_content';
                            wp_editor( $content, $editor_id );
                        ?>

                        <label for="cat">Category</label>
                        <?php 
                            $cat = wp_get_post_terms( $post_to_edit->ID, 'category');
                            if($cat){
                                wp_dropdown_categories( 'show_option_none=Category&taxonomy=category&selected='.$cat[0]->term_id); 
                            }else{
                                wp_dropdown_categories( 'show_option_none=Category&taxonomy=category&hide_empty=0' );
                            } 
                        ?>

                        <label for="post_tags">Tags</label>
                        <input type="text" id="post_tags" name="post_tags" value="<?php get_existing_tags($post_id); ?>" />

                        <label for="date">Date</label>
                        <div id="timestampdiv" class="hide-if-js"><?php write_here_time_edit(0, 0, 5); ?></div>

                        <input type="submit" value="Update" id="submit" name="submit" />
                        <input type="hidden" name="action" value="write_here_edit_post" />
                        <input type="hidden" name="pid" value="<?php echo $post_to_edit->ID; ?>" />
                        <?php wp_nonce_field( 'edit-post', 'edit-post-nonce' ); ?>
                    </form>
                </div>
        <?php  
            }else{ 
                echo "It needs post to edit."; 
            } // post_id close
        } //nonce close
    }else{
        echo "Please click edit link on dashbord page."; 
    }
}

// Get existing tags for the post
function get_existing_tags($post_id){
    $tags = wp_get_post_tags($post_id); 
    $iflast = $tags;
    foreach ($tags as $tag){ 
        echo $tag->name; 
        if(next($iflast)){
            echo ', ';
        }
    } 
}

/*
**  Process data from edit form
    http://codex.wordpress.org/Function_Reference/wp_update_post
*/
function write_here_edit_post() {
    if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "write_here_edit_post" && wp_verify_nonce( $_POST['edit-post-nonce'], 'edit-post' ) ) {
        // Set default date on the post
        $postdate = date('Y-m-d H:i:s');
        // Get values from front end form
        $pid        = $_POST['pid'];
        $wh_month   = $_POST['mm'];
        $wh_day     = $_POST['jj'];
        $wh_year    = $_POST['aa'];
        $wh_hour    = $_POST['hh'];
        $wh_min     = $_POST['mn'];
        $wh_sec     = $_POST['ss'];
        $title      = wp_strip_all_tags($_POST['title']);
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
        $edit_post = array(
            'ID'            => $pid,
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
            $post_id = wp_update_post($edit_post);
            
            // if post updated successfully. // <------------------------------------------------ Update Message need to be fixed
            if ( $post_id != 0 ) { 
                write_here_show_success_messages( 'Update Post' );
            } 
            
            // These files need to be included as dependencies when on the front end.
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            // Let WordPress handle the upload.
            $attachment_id = media_handle_upload( 'wh_image_upload', $post_id );
            if ( !is_wp_error( $attachment_id ) ) {
                // If the image was uploaded successfully, set it as featured image
                set_post_thumbnail( $post_id, $attachment_id );
            }
        }
        
    }
}
add_action('init', 'write_here_edit_post');
<?php
/*
http://wordpress.stackexchange.com/questions/15283/i-am-trying-to-create-a-simple-frontend-form-for-posting
https://pippinsplugins.com/creating-custom-front-end-registration-and-login-forms/

Plugin Name: Write Here
Plugin URI: http://www.URL.com
Description: Simple front end form
Author: O
Version: 1.0
Author URI: http://www.URL.com
License: GPL2
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Define plug-in path
define('WH_PATH', plugins_url() . '/write-here');


/*
** Register CSS & JS assets for plug in
------------------------------------------------------------------
*/
// register our form css
function write_here_register_assets() {
	wp_register_style('write-here', WH_PATH . '/css/write-here.css');
    wp_register_script( 'write-here', WH_PATH . '/js/write-here.js', array( 'jquery' ), '1.0', true );
}
add_action('init', 'write_here_register_assets');

// load our form css
function write_here_print_assets() {
	global $write_here_load_assets;
 
	if ( !$write_here_load_assets )
		return;
 
	wp_print_styles('write-here');
    wp_print_scripts('write-here');
}
add_action('wp_footer', 'write_here_print_assets');


/*
**  Add a shortcode for front end form
    https://codex.wordpress.org/Function_Reference/add_shortcode
*/
function form_write_here(){
    // Load CSS & JS files
    global $write_here_load_assets;
    $write_here_load_assets = true;
    
    // Show only to logged in users
    if ( is_user_logged_in() ) {
        $output = write_here_form();
        return $output;
    }else{
        echo 'Please Sign in to continue...';
    }
}
add_shortcode('write-here', 'form_write_here');


/*
**  Front end From
    ------------------------------------------------------------------
*/
function write_here_form(){
    ob_start();
    $postdate = date('Y-m-d H:i:s');
?>
<div class="write-here">
    <?php write_here_show_error_messages(); ?>
    <form id="new_post" name="new_post" method="post" action="">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" />

        <label for="content">Content</label>
        <textarea id="content" name="content" rows="6"></textarea>

        <label for="cat">Category</label>
        <?php wp_dropdown_categories( 'show_option_none=Category&taxonomy=category&hide_empty=0' ); ?>

        <label for="post_tags">Tags</label>
        <input type="text" id="post_tags" name="post_tags" />

        <label for="date">Date</label>
        <input type="text" value="<?php echo $postdate; ?>" id="date" name="date" />

        <input type="submit" value="Publish" id="submit" name="submit" />
        <input type="hidden" name="action" value="write_here_new_post" />
        <?php wp_nonce_field( 'new-post' ); ?>
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
    if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "write_here_new_post") {
        
        $title =  wp_strip_all_tags($_POST['title']);
        $content = $_POST['content'];
        $postdate = $_POST['date'];
        $gmtpostdate = $_POST['date'];
        $tags = $_POST['post_tags'];
        $cat  = $_POST['cat'];
        
        // Server side validation
        if ($title == '') {
            write_here_errors()->add('title_not_vaild', __('Title not valid'));
        }
        if ($content == '') {
            write_here_errors()->add('content_not_vaild', __('Content not valid'));
        }
        if (!$postdate) {
            $postdate = date('Y-m-d H:i:s');
            $gmtpostdate = gmdate('Y-m-d H:i:s');
        }
           
        // Add the content of the form to $post as an array
        $new_post = array(
            'post_title'    => $title,
            'post_content'  => $content,
            'post_category'	=> array($cat),     // Default empty.
            'tags_input'    => array($tags),    // Default empty.
            'post_status'   => 'publish',       // Choose: publish, preview, future, draft, etc. Default 'draft'.
            'post_date'     => $postdate,       // The time post was made.
            'post_date_gmt' => $gmtpostdate     // The time post was made, in GMT.
        );

        $errors = write_here_errors()->get_error_messages();
        
        // Test validation messages
        //echo '<pre>';
        //var_dump($errors);
        //echo '</pre>';
        
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

<?php
/*
http://wordpress.stackexchange.com/questions/15283/i-am-trying-to-create-a-simple-frontend-form-for-posting

Plugin Name: Write Here
Plugin URI: http://www.ohsikpark.com
Description: Simple WordPrss front end form
Author: O
Version: 1.0
Author URI: http://www.ohsikpark.com
License: GPL2
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Define plug-in path
define('WH_PATH', plugins_url() . '/write-here');

// Load css & js files
function my_enqueued_assets() {
	wp_enqueue_style( 'write-here', WH_PATH . '/css/write-here.css' );
    wp_enqueue_script( 'write-here', WH_PATH . '/js/write-here.js', array( 'jquery' ), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'my_enqueued_assets' );

/*
**  Add front end from in a Shortcode
    https://codex.wordpress.org/Function_Reference/add_shortcode
*/
function form_write_here(){
    $postdate = date('Y-m-d H:i:s');
    
    // Show only to logged in users
    if ( is_user_logged_in() ) {
?>
    <div class="write-here">
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
    }else{
        echo 'Please Sign in to continue...';
    }
}
add_shortcode('write-here', 'form_write_here');


/*
**  Process data from front end form
    http://codex.wordpress.org/Function_Reference/wp_insert_post
*/
if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "write_here_new_post") {

    // Do some minor form validation to make sure there is content
    if ($_POST['title']) {
        $title =  wp_strip_all_tags($_POST['title']);
    } else {
        echo 'Please enter a title';
    }

    if ($_POST['content']) {
        $content = $_POST['content'];
    } else {
        echo 'Please enter the content';
    }

    if ($_POST['date']) {
        $postdate = $_POST['date'];
    } else {
        $postdate = date('Y-m-d H:i:s');
    }

    $tags = $_POST['post_tags'];
    $cat  = $_POST['cat'];

    // Add the content of the form to $post as an array
    $new_post = array(
        'post_title'    => $title,
        'post_content'  => $content,
        'post_category'	=> array($cat),   // Default empty.
        'tags_input'    => array($tags),    // Default empty.
        'post_status'   => 'publish',       // Choose: publish, preview, future, draft, etc. Default 'draft'.
        'post_date'     => $postdate,      // The time post was made.
        'post_date_gmt' => $postdate       // The time post was made, in GMT.
    );

    //save the new post and return its ID
    $post_id = wp_insert_post($new_post, $wp_error ); // this not working

    // redirect after submition
    wp_redirect( site_url()."?post=".$post_id);
    exit();
}

/*
 ADMIN PAGE
*/


?>
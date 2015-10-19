<?php
/*
http://wordpress.stackexchange.com/questions/15283/i-am-trying-to-create-a-simple-frontend-form-for-posting

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
**  Register CSS & JS assets for plug in
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
**  Front end From
    ------------------------------------------------------------------
*/
function write_here_form(){
    ob_start();
?>
<div class="write-here">
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

// Load Date & Time fields function
if( !function_exists( 'write_here_time' ) ){
    require_once( dirname( __FILE__ ) . '/write-here-time.php' );
}


/*  
**  Dashboard, save it to Shortcode
    ------------------------------------------------------------------
*/
function write_here_dashboard(){
    global $current_user;
    get_currentuserinfo();
    
    $page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
    $author_total_posts = count_user_posts($current_user->ID);

    $post_pp = 10; // Set number of posts per page
    
    $pages = ceil($author_total_posts/$post_pp);
    $offset = ($page * $post_pp) - $post_pp;
    
    $author_query = array(
        'posts_per_page' => $post_pp,
        'offset'=>  $offset,
        'author' => $current_user->ID
     );
    $author_posts = new WP_Query($author_query);
    
    // Show all posts by current user
    if($author_posts->have_posts()){
        echo '<div class="write-here-dashboard"><ul>';
        while($author_posts->have_posts()) : $author_posts->the_post();
        ?>
            <li>
                <p><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></p>
                <p class="wh-post-meta"><i><?php echo get_the_date(); ?></i> <span class="wh-edit"><a href="http://localhost:8888/WordPress/edit-post/?post_id=<?php echo get_the_ID(); ?>">Edit</a> / <a href="">Delete</a></span></p>
            </li>
        <?php           
        endwhile;
        echo '</ul></div>';
        
        // Show pagination for the posts
        echo '<div class="wh-pagenavi">';
            $big = 999999999999; // need an unlikely integer
            $prev_arrow = is_rtl() ? '&rarr;' : '&larr;';
		    $next_arrow = is_rtl() ? '&larr;' : '&rarr;';
            
            $args = array(
                'base'         => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                'format'       => '?page=%#%',
                'total'        => $pages,
                'current'      => $page,
                'show_all'     => False,
                'end_size'     => 1,
                'mid_size'     => 3,
                'prev_next'    => True,
                'prev_text'		=> $prev_arrow,
				'next_text'		=> $next_arrow,
                'type'         => 'list');
                // ECHO THE PAGENATION 
            echo paginate_links( $args );
        echo '</div>';
        
        wp_reset_query();
    }else{
        echo '<p>Write your first post!</p>';
    }
}

/*  
**  Edit posts
    http://wordpress.stackexchange.com/questions/17400/how-can-i-edit-a-post-from-the-frontend
    ------------------------------------------------------------------
*/
function write_here_edit_form(){
    
    $post_id = $_GET['post_id'];

    if ($post_id){
    
        $post_to_edit = get_post($post_id);
    
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

?>
<div class="write-here">
    <?php write_here_show_error_messages(); ?>
    <form id="new_post" name="new_post" method="post" action="">
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
        <div id="timestampdiv" class="hide-if-js"><?php write_here_time(0, 0, 5); ?></div>

        <input type="submit" value="Publish" id="submit" name="submit" />
        <input type="hidden" name="action" value="write_here_edit_post" />
        <input type="hidden" name="pid" value="<?php echo $post_to_edit->ID; ?>" />
        <?php wp_nonce_field( 'new-post' ); ?>
    </form>
</div>
<?php  
    }else{ echo"Nonono"; }
}

/*
**  Process data from edit form
    http://codex.wordpress.org/Function_Reference/wp_insert_post
*/
function write_here_edit_post() {
    if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "write_here_edit_post") {
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
            $post_id = wp_update_post($new_post);
            
            if($post_id) {
                // This will redirect you to the newly created post (Using GUID)
                $post = get_post($post_id);
                wp_redirect($post->guid);
                exit();
            }
        }
        
    }
}
add_action('init', 'write_here_edit_post');




/*
**  Add a shortcode for front end form
    [write-here]
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
**  Add a shortcode for dashboard
    [write-here-dashboard]
    https://codex.wordpress.org/Function_Reference/add_shortcode
*/
function dashboard_write_here(){
    // Load CSS & JS files
    global $write_here_load_assets;
    $write_here_load_assets = true;
    
    // Show only to logged in users
    if ( is_user_logged_in() ) {
        $output = write_here_dashboard();
        return $output;
    }else{
        echo 'Please Sign in to continue...';
    }
}
add_shortcode('write-here-dashboard', 'dashboard_write_here');

/*
**  Add a shortcode for edit formß
    [write-here-edit]
    https://codex.wordpress.org/Function_Reference/add_shortcode
*/
function edit_write_here(){
    // Load CSS & JS files
    global $write_here_load_assets;
    $write_here_load_assets = true;
    
    // Show only to logged in users
    if ( is_user_logged_in() ) {
        $output = write_here_edit_form();
        return $output;
    }else{
        echo 'Please Sign in to continue...';
    }
}
add_shortcode('write-here-edit', 'edit_write_here');

<?php
/*
Plugin Name: Write Here
Plugin URI: http://wp.ohsikpark.com/write-here/
Description: Simple front end form for WordPress. Write Here will allow you to have registered users to write & manage articles from front end.
Author: writegnj
Version: 1.4
Author URI: http://www.ohsikpark.com
Text Domain: write-here
License: GPL2
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Define plug-in path
define('WH_PATH', plugins_url() . '/write-here');

// Load plug in files
require_once( dirname( __FILE__ ) . '/write-here-write.php' );
require_once( dirname( __FILE__ ) . '/write-here-dashboard.php' );
require_once( dirname( __FILE__ ) . '/write-here-edit.php' );
require_once( dirname( __FILE__ ) . '/write-here-ajax.php' );
require_once( dirname( __FILE__ ) . '/admin/write-here-admin.php' );

/**
 * Setting default settings on plugin activation only 
 */
function write_here_activation_actions(){
    do_action( 'wp_writehere_extension_activation' );
}
register_activation_hook( __FILE__, 'write_here_activation_actions' );
// Set default values here
function write_here_default_options(){
    $default = array(
        'pid_num'     => '0',
        'num_of_posts'   => '10'
    );
    update_option( 'write_here_options', $default );
}
add_action( 'wp_writehere_extension_activation', 'write_here_default_options' );

/*
**  Register CSS & JS assets for plug in
    ------------------------------------------------------------------
*/
// register our form css
function write_here_register_assets() {
	wp_register_style( 'write-here', WH_PATH . '/css/write-here.css' );
	wp_register_style( 'themename-style', get_stylesheet_uri(), array( 'dashicons' ), '1.0', true );
    wp_register_script( 'validate', WH_PATH . '/js/jquery.validate.min.js', array('jquery'), '1.0.0', true );
    wp_register_script( 'ajax-script', WH_PATH . '/js/write-here.js', array('jquery'), '1.2', true );
}
add_action('init', 'write_here_register_assets');

// load our form css
function write_here_print_assets() {
	global $write_here_load_assets;
 
	if ( !$write_here_load_assets )
		return;
 
	wp_print_styles('write-here');
	wp_print_styles('themename-style');
	wp_print_scripts('validate');
	wp_print_scripts('ajax-script');
}
add_action('wp_footer', 'write_here_print_assets');

// Load Date & Time fields function
if( !function_exists( 'write_here_time' ) || !function_exists( 'write_here_time_edit' ) ){
    require_once( dirname( __FILE__ ) . '/write-here-time.php' );
}

// Load and localize JS for AJAX
function wh_enqueue() {
    wp_register_script( 'wh-ajax-app', WH_PATH . '/js/write-here-ajax.js', array('jquery'), '1.2', true );
    wp_localize_script( 'ajax-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'ajax_nonce' => wp_create_nonce('wh_obj_ajax')) );
}
add_action( 'wp_enqueue_scripts', 'wh_enqueue' );


/*
**  Image upload with AJAX
    Used on Write and Edit forms
*/
function write_here_featured_image_upload() {
    check_ajax_referer( 'wh_obj_ajax', 'security' );
    //var_dump($_FILES);
    
    // Temporary post id
    $post_id = 0;
    
    // These files need to be included as dependencies when on the front end.
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );

    // Let WordPress handle the upload.
    $attachment_id = media_handle_upload('file' , $post_id );
    $image_attributes = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
    
    // Send value back to jQuery
    echo json_encode(array("att_id" => $attachment_id, "att_url" => $image_attributes[0]));
    
    die();
}
add_action( 'wp_ajax_write_here_img_upload', 'write_here_featured_image_upload' );

/*
**  Delete featured image on edit page
    Used on Write and Edit forms
*/
function delete_attachment( $post ) {
    check_ajax_referer( 'wh_obj_ajax', 'security' );
    
    $msg = 'Attachment ID [' . $_POST['att_ID'] . '] has been deleted!';
    if( wp_delete_attachment( $_POST['att_ID'], true )) {
        echo $msg;
    }
    die();
}
add_action( 'wp_ajax_delete_attachment', 'delete_attachment' );

// Tracking error messages
function write_here_errors(){
    static $wp_error; // Will hold global variable safely
    return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
}

// Show error messages from form submissions
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
        echo '<p class="login-write-here">Please <a href="'.wp_login_url().'" title="Login">Login</a> to continue...</p>';
    }
}
add_shortcode('write-here', 'form_write_here');

/*
**  Add a shortcode for dashboard
    [write-here-dashboard]
*/
function dashboard_write_here(){

    global $write_here_load_assets;
    $write_here_load_assets = true;
    
    if ( is_user_logged_in() ) {
        $output = write_here_dashboard();
        return $output;
    }else{
        echo '<p class="login-write-here">Please <a href="'.wp_login_url().'" title="Login">Login</a> to continue...</p>';
    }
}
add_shortcode('write-here-dashboard', 'dashboard_write_here');

/*
**  Add a shortcode for edit form
    [write-here-edit]
*/
function edit_write_here(){
    
    global $write_here_load_assets;
    $write_here_load_assets = true;
    
    if ( is_user_logged_in() ) {
        $output = write_here_edit_form();
        return $output;
    }else{
        echo '<p class="login-write-here">Please <a href="'.wp_login_url().'" title="Login">Login</a> to continue...</p>';
    }
}
add_shortcode('write-here-edit', 'edit_write_here');

/*
**  Add a shortcode for edit form
    [write-here-ajax]
*/
function ajax_write_here(){

    global $write_here_load_assets;
    $write_here_load_assets = true;
    
    if ( is_user_logged_in() ) {
        wp_enqueue_script( 'wh-ajax-app' );
        $output = '<div id="wh-form-on-ajax-page" style="display: none;">';
        $output .= write_here_form();
        $output .= '</div>';
        $output .= '<div id="write_here_ajax_wrap"></div>';
        return $output;
    }else{
        echo '<p class="login-write-here">Please <a href="'.wp_login_url().'" title="Login">Login</a> to continue...</p>';
    }
}
add_shortcode('write-here-ajax', 'ajax_write_here');

// Add plug in link to setting page
function write_here_action_links( $links ) {
   $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=write-here-setting') ) .'">Settings</a>';
   $links[] = '<a href="http://wp.ohsikpark.com/write-here/" target="_blank">Documentation</a>';
   return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'write_here_action_links' );
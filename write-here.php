<?php
/*
Plugin Name: Write Here
Plugin URI: http://wp.ohsikpark.com/write-here/
Description: Simple front end form for WordPress. Write Here will allow you to have registered users to write & manage articles from front end.
Author: Ohsik Park
Version: 1.0
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
require_once( dirname( __FILE__ ) . '/admin/write-here-admin.php' );

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

// Load Date & Time fields function
if( !function_exists( 'write_here_time' ) || !function_exists( 'write_here_time_edit' ) ){
    require_once( dirname( __FILE__ ) . '/write-here-time.php' );
}

// used for tracking error messages
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

// Delete featured image on edit page
add_action( 'wp_ajax_delete_attachment', 'delete_attachment' );
function delete_attachment( $post ) {
    //echo $_POST['att_ID'];
    $msg = 'Attachment ID [' . $_POST['att_ID'] . '] has been deleted!';
    if( wp_delete_attachment( $_POST['att_ID'], true )) {
        echo $msg;
    }
    die();
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
        echo 'Please <a href="'.wp_login_url().'" title="Login">Login</a> to continue...';
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
        echo 'Please <a href="'.wp_login_url().'" title="Login">Login</a> to continue...';
    }
}
add_shortcode('write-here-dashboard', 'dashboard_write_here');

/*
**  Add a shortcode for edit form
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
        echo 'Please <a href="'.wp_login_url().'" title="Login">Login</a> to continue...';
    }
}
add_shortcode('write-here-edit', 'edit_write_here');

// Add plug in link to setting page
function write_here_action_links( $links ) {
   $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=write-here-setting') ) .'">Settings</a>';
   $links[] = '<a href="http://wp.ohsikpark.com/write-here/" target="_blank">Documentation</a>';
   return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'write_here_action_links' );
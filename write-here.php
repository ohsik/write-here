<?php
/*
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

// Load plug in files
require_once( dirname( __FILE__ ) . '/write-here-write.php' );
require_once( dirname( __FILE__ ) . '/write-here-dashboard.php' );
require_once( dirname( __FILE__ ) . '/write-here-edit.php' );

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
        echo 'Please Sign in to continue...';
    }
}
add_shortcode('write-here-edit', 'edit_write_here');
?>
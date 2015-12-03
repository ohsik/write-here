<?php 
/*
**  Add admin menu in backend
    https://codex.wordpress.org/Creating_Options_Pages
*/

class WriteHereSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Writing Here Setting', 
            'Write Here', 
            'manage_options', 
            'write-here-setting', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'write_here_options' );
        ?>
        <div class="wrap">
            <h1>Write Here</h1>
            <p>A simple front end form for WordPress. Write Here will allow you to have registered users to write & manage articles from front end.</p>
            <h3>How to Use?</h3>
                <ol>
                    <li>Create page for writing on front end. Add <b>[write-here]</b></li>
                    <li>Create page for dashboard. Add <b>[write-here-dashboard]</b></li>
                    <li>Create page for editing. Add <b>[write-here-edit]</b></li>
                    <li>Set your edit page below.</li>
                </ol>    
            
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );   
                do_settings_sections( 'write-here-setting' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'my_option_group', // Option group
            'write_here_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Set Edit Page', // Title
            array( $this, 'print_section_info' ), // Callback
            'write-here-setting' // Page
        );  

        add_settings_field(
            "pid_num", 
            "Select Page >>", 
            array( $this, 'wh_select_list' ),  
            "write-here-setting", 
            "setting_section_id"
        );
        
        add_settings_field(
            'num_of_posts', // ID
            'Number of Posts to show', // Title 
            array( $this, 'num_of_posts_callback' ), // Callback
            'write-here-setting', // Page
            'setting_section_id' // Section           
        ); 
   
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['pid_num'] ) )
            $new_input['pid_num'] = absint( $input['pid_num'] );
        
        if( isset( $input['num_of_posts'] ) )
            $new_input['num_of_posts'] = absint( $input['num_of_posts'] );
        
        return $new_input;
    }
    
    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Select the page where you have inserted <b>[write-here-edit]</b>';
    }
    
    public function wh_select_list() 
    {
        $options = get_option("write_here_options");
        wp_dropdown_pages(
            array(
                 'name' => 'write_here_options[pid_num]',
                 'show_option_none' => __( 'Select' ),
                 'selected' => $options['pid_num']
            )
        );
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function num_of_posts_callback()
    {
        printf(
            '<input type="number" placeholder="Number Only" min="1" id="num_of_posts" name="write_here_options[num_of_posts]" value="%s" required />',
            isset( $this->options['num_of_posts'] ) ? esc_attr( $this->options['num_of_posts']) : ''
        );
    }

}

if( is_admin() )
    $my_settings_page = new WriteHereSettingsPage();
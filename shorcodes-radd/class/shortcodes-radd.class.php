<?php

class customShortcodeSettings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    public $options;

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
            'Settings Admin', 
            'Short Codes RADD', 
            'manage_options', 
            'shortcodes-radd', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'scradd_option_name' );
        ?>
        <div class="wrap">
            <h2>Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'scradd_option_group' );   
                do_settings_sections( 'scradd-setting-admin' );
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
            'scradd_option_group', // Option group
            'scradd_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Twitter API', // Title
            array( $this, 'print_section_info' ), // Callback
            'scradd-setting-admin' // Page
        );  
        add_settings_field(
            'consumer_key', // ID
            'CONSUMER_KEY', // Title 
            array( $this, 'consumer_key_callback' ), // Callback
            'scradd-setting-admin', // Page
            'setting_section_id' // Section           
        );
        add_settings_field(
            'consumer_secret', // ID
            'CONSUMER_SECRET', // Title 
            array( $this, 'consumer_secret_callback' ), // Callback
            'scradd-setting-admin', // Page
            'setting_section_id' // Section           
        );
        add_settings_field(
            'access_token', // ID
            'ACCESS_TOKEN', // Title 
            array( $this, 'access_token_callback' ), // Callback
            'scradd-setting-admin', // Page
            'setting_section_id' // Section           
        );
        add_settings_field(
            'access_token_secret', // ID
            'ACCESS_TOKEN_SECRET', // Title 
            array( $this, 'access_token_secret_callback' ), // Callback
            'scradd-setting-admin', // Page
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
        if( isset( $input['consumer_key'] ) )
            $new_input['consumer_key'] = ( $input['consumer_key'] );

        if( isset( $input['consumer_secret'] ) )
            $new_input['consumer_secret'] = ( $input['consumer_secret'] );

        if( isset( $input['access_token'] ) )
            $new_input['access_token'] = ( $input['access_token'] );

        if( isset( $input['access_token_secret'] ) )
            $new_input['access_token_secret'] = ( $input['access_token_secret'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function consumer_key_callback()
    {
        printf(
            '<input type="text" id="consumer_key" name="scradd_option_name[consumer_key]" value="%s" />',
            isset( $this->options['consumer_key'] ) ? esc_attr( $this->options['consumer_key']) : ''
        );
    }
    /** 
     * Get the settings option array and print one of its values
     */
    public function consumer_secret_callback()
    {
        printf(
            '<input type="text" id="consumer_secret" name="scradd_option_name[consumer_secret]" value="%s" />',
            isset( $this->options['consumer_secret'] ) ? esc_attr( $this->options['consumer_secret']) : ''
        );
    }
    /** 
     * Get the settings option array and print one of its values
     */
    public function access_token_callback()
    {
        printf(
            '<input type="text" id="access_token" name="scradd_option_name[access_token]" value="%s" />',
            isset( $this->options['access_token'] ) ? esc_attr( $this->options['access_token']) : ''
        );
    }
    /** 
     * Get the settings option array and print one of its values
     */
    public function access_token_secret_callback()
    {
        printf(
            '<input type="text" id="access_token_secret" name="scradd_option_name[access_token_secret]" value="%s" />',
            isset( $this->options['access_token_secret'] ) ? esc_attr( $this->options['access_token_secret']) : ''
        );
    }

}

if( is_admin() )
    $scradd_settings_page = new customShortcodeSettings();
<?php

  /***********************************************************
     _ _ _     _____           _ 
    | | | |___| __  |___ ___  |_|
    | | | | . | __ -| . | . | | |
    |_____|  _|_____|___|___|_| |
          |_|               |___|

    WpBooj

  */
    
class WpBoojAdmin
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
            'WpBooj', 
            'WpBooj', 
            'manage_options', 
            'my-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'wp-booj' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>My Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );   
                do_settings_sections( 'my-setting-admin' );
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
            'wp-booj', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'My Custom Settings', // related_posts
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );  

        add_settings_field(
            'proxy_admin_urls', // ID
            'Proxy Admin Urls', // related_posts 
            array( $this, 'proxy_admin_urls_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'related_posts', 
            'Use Related Posts', 
            array( $this, 'related_posts_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
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
        if( isset( $input['proxy_admin_urls'] ) )
            $new_input['proxy_admin_urls'] = $input['proxy_admin_urls'] ;

        if( isset( $input['related_posts'] ) )
            $new_input['related_posts'] = sanitize_text_field( $input['related_posts'] );

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
    public function proxy_admin_urls_callback(){
        ?>
        <input type="checkbox" name="wp-booj[proxy_admin_urls]" <? if( $this->options['proxy_admin_urls'] == 'on' ){ echo 'checked="checked"'; } ?> />        
        <?
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function related_posts_callback()
    {
        ?>
        <input type="checkbox" name="wp-booj[related_posts]" <? if( $this->options['related_posts'] == 'on' ){ echo 'checked="checked"'; } ?> />        
        <?
    }
}
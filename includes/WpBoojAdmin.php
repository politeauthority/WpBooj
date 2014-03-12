<?php

  /*********************************************************************                                                                  
                                                             
   _ _ _     _____           _    _ _    _____   _       _     
  | | | |___| __  |___ ___  |_|  |_|_|  |  _  |_| |_____|_|___ 
  | | | | . | __ -| . | . | | |   _ _   |     | . |     | |   |
  |_____|  _|_____|___|___|_| |  |_|_|  |__|__|___|_|_|_|_|_|_|
        |_|               |___|                                

  WpBooj :: Admin

  */
    
class WpBoojAdmin
{
  /**
   * Holds the values to be used in the fields callbacks
   */
  private $options;

  public function __construct(){
    add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
    add_action( 'admin_init', array( $this, 'page_init' ) );


    add_action( 'admin_head', array( $this, 'booj_branding' )          );
    add_action( 'admin_head', array( $this, 'proxy_admin_urls' )       );

    add_action( 'show_user_profile',        array( $this, 'booj_profile_fields_admin_display' ) );
    add_action( 'edit_user_profile',        array( $this, 'booj_profile_fields_admin_display' ) );

    add_action( 'admin_init', array( $this, 'remove_nag' )             );
    add_action( 'admin_head', array( $this, 'remove_nag_css' )         );

  }

  public function booj_branding(){
    if( substr( get_bloginfo('version'), 0, 1 ) == '3' ){
      ?>
      <style type="text/css">
        #footer-upgrade{
          display: none;
        }
        #wpfooter{
          background-image: url('<? print get_site_url(); ?>/wp-content/plugins/WpBooj/logo-enterprise-network.gif');
          background-repeat: no-repeat;
          background-position: right;
        }
      </style>
      <?
    }
  }

  /***********************************************************                                                          
     _____   _       _        _____     _   _   _             
    |  _  |_| |_____|_|___   |   __|___| |_| |_|_|___ ___ ___ 
    |     | . |     | |   |  |__   | -_|  _|  _| |   | . |_ -|
    |__|__|___|_|_|_|_|_|_|  |_____|___|_| |_| |_|_|_|_  |___|
                                                     |___|    
    Admin Settings
  */

  /**
   * Add options page
   */
  public function add_plugin_page(){
      // This page will be under "Settings"
      add_options_page(
          'WpBooj', 
          'WpBooj', 
          'manage_options', 
          'wp-booj-admin', 
          array( $this, 'create_admin_page' )
      );
  }

  /**
   * Options page callback
   */
  public function create_admin_page(){
    // Set class property
    $this->options = get_option( 'wp-booj' );
    ?>
    <div class="wrap">
      <?php screen_icon(); ?>
      <h2>My Settings</h2>           
      <form method="post" action="options.php">
        <?php
          // This prints out all hidden setting fields
          settings_fields( 'wp-booj' );   
          do_settings_sections( 'wp-booj-admin' );
          submit_button(); 
        ?>
      </form>
    </div>
    <?php
  }

  /**
   * Register and add settings
   */
  public function page_init(){        
    register_setting(
      'wp-booj', // Option group
      'wp-booj', // Option name
      array( $this, 'sanitize' ) // Sanitize
    );

    add_settings_section(
      'setting_section_id', // ID
      'My Custom Settings', // related_posts
      array( $this, 'print_section_info' ), // Callback
      'wp-booj-admin' // Page
    );  

    add_settings_field(
      'proxy_admin_urls', // ID
      'Proxy Admin Urls', // related_posts 
      array( $this, 'proxy_admin_urls_callback' ), // Callback
      'wp-booj-admin', // Page
      'setting_section_id' // Section           
    );      

    add_settings_field(
      'related_posts', 
      'Use Related Posts', 
      array( $this, 'related_posts_callback' ), 
      'wp-booj-admin', 
      'setting_section_id'
    );      
  }

  /**
   * Sanitize each setting field as needed
   *
   * @param array $input Contains all settings fields as array keys
   */
  public function sanitize( $input ){
    $new_input = array();
    if( isset( $input['proxy_admin_urls'] ) )
      $new_input['proxy_admin_urls'] = $input['proxy_admin_urls'] ;

    if( isset( $input['related_posts'] ) )
      $new_input['related_posts'] = $input['related_posts'];

    return $new_input;
  }

  /** 
   * Print the Section text
   */
  public function print_section_info(){
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
  public function related_posts_callback(){
    ?>
    <input type="checkbox" name="wp-booj[related_posts]" <? if( $this->options['related_posts'] == 'on' ){ echo 'checked="checked"'; } ?> />        
    <?
  }



  /***********************************************************
     _____     _   _              _____     _       
    |  _  |_ _| |_| |_ ___ ___   |     |___| |_ ___ 
    |     | | |  _|   | . |  _|  | | | | -_|  _| .'|
    |__|__|___|_| |_|_|___|_|    |_|_|_|___|_| |__,|
  

    Author Meta 

    Add and display extra meta data for authors

  */

  public function booj_profile_fields_admin_display( $user ) { 
    ?>
    <h3>Enterprise Network Information</h3>
    <table class="form-table">
      <tr>
        <th><label for="twitter">Agent Bio Url</label></th>
        <td>
          <input type="text" name="agent_bio_url" id="agent_bio_url" value="<?php echo esc_attr( get_the_author_meta( 'agent_bio_url', $user->ID ) ); ?>" class="regular-text" /><br />
          <span class="description">Please enter your agent bio page web address. (ex: http://www.clarkhawaii.com/our_agents/info/leslie-m-agorastos )</span>
        </td>
      </tr>
    </table>
    <? 
  }

  public function booj_profile_fields_save( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) )
      return false;
    update_usermeta( $user_id, 'agent_bio_url', $_POST['agent_bio_url'] );
  }



  /***********************************************************
     _____  
    |  |  |___| |___ 
    |  |  |  _| |_ -|
    |_____|_| |_|___|
  
    Urls

    This updates bad urls for the admin because of our Apache Proxy

  */

  public function proxy_admin_urls(){
    // Update the HTTP_HOST because wordpress bases urls off of this
    // We're removing 7 characters, 'http://' so the site_url MUST BE ex: http://www.clarkhawaii.com/
    // @todo: Make this more robust for the above reason!
    $options = get_option( 'wp-booj' );
    if( $options['proxy_admin_urls'] == 'on' ){
      if( substr( get_site_url(), 0, 7 ) == 'http://'){
        $_SERVER['HTTP_HOST'] = substr( get_site_url(), 7 );
      } else {
        $_SERVER['HTTP_HOST'] = get_site_url() ;        
      }
      // This updates the remote_addr because of the proxy this would normally get lost
      if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $_SERVER['REMOTE_ADDR'] = trim($ips[0]);
      } elseif ( isset($_SERVER['HTTP_X_REAL_IP']) && !empty($_SERVER['HTTP_X_REAL_IP']) ) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];
      } elseif ( isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) ) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CLIENT_IP'];
      }

      // Update the links we may have missed with this
      // we catch all urls that start with "/" and put /blog/ ahead
      ?>
      <script type="text/javascript">
        uri_segment = '<? echo WpBoojFindURISegment(); ?>';
        uri_len     = uri_segment.length + 1;
        jQuery(document).ready( function() {
          jQuery('a').each( function(){
            link = jQuery(this).attr('href');
            if( link && link.length && link.substring( 0, 1 ) == '/' && link.substring( 0, uri_len ) != '/' +  uri_segment ){
              jQuery(this).attr('href', '/' + uri_segment + link );
            }
          });
          jQuery('form').each( function(){
            action = jQuery(this).attr('action');
            if( action && action.length && action.substring( 0, 1 ) == '/' && action.substring( 0, uri_len ) != '/' + uri_segment ){
              jQuery(this).attr('action', '/' + uri_segment + action );
            }
          });
        });
      </script>
      <?
    }
  }



  /***********************************************************                      
     _____            _____                     
    |   | |___ ___   |   __|___ ___ ___ ___ ___ 
    | | | | .'| . |  |__   |  _|  _| -_| -_|   |
    |_|___|__,|_  |  |_____|___|_| |___|___|_|_|
              |___|                             

    Nag Screens

    This removes the "Update Wordpress" nag screen from the Admin 

  */

  public function remove_nag() {
    remove_action('admin_notices', 'update_nag', 3);
  }

  public function remove_nag_css(){
    ?>
    <style type="text/css">
      /* heres the css */
      #wp-admin-bar-updates{ display: none; }
      #menu-dashboard ul li a .update-count{ display: none; }
      .plugin-count{ display: none !important; }
    </style>
    <?
  }



}
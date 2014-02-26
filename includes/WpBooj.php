<?php

  /***********************************************************
     _ _ _     _____           _ 
    | | | |___| __  |___ ___  |_|
    | | | | . | __ -| . | . | | |
    |_____|  _|_____|___|___|_| |
          |_|               |___|

    WpBooj

  */

class WpBooj {

  protected $option_group = 'wp-booj';  
  protected $option_name  = 'wp-booj';

  // Default values
  protected $data = array(
      'proxy_admin_urls' => '0',
      'related_posts'    => '0',
  );
  
  function __construct(){

    add_action( 'admin_init', array( $this, 'admin_init')              );
    add_action( 'admin_init', array( $this, 'remove_nag' )             );
    add_action( 'admin_init', array( $this, 'register_the_settings' )  );
    add_action( 'admin_head', array( $this, 'remove_nag_css' )         );

    add_action( 'admin_head', array( $this, 'booj_branding' )          );
    add_action( 'admin_head', array( $this, 'proxy_admin_urls' )       );
    add_action( 'admin_menu', array( $this, 'setting_menu' )           );

    add_action( 'wp_head',    array( $this, 'redirect_activeclients' ) );


    //Hooks for Author Meta
    add_action( 'personal_options_update',  array( $this, 'booj_profile_fields_save' ) );
    add_action( 'edit_user_profile_update', array( $this, 'booj_profile_fields_save' ) );
    
    add_action( 'show_user_profile',        array( $this, 'booj_profile_fields_admin_display' ) );
    add_action( 'edit_user_profile',        array( $this, 'booj_profile_fields_admin_display' ) );

    // Listen for the plugin activate/deactivate event
    register_activation_hook(   WP_BOOJ_FILE,   array( $this, 'activate' ) );
    register_deactivation_hook( WP_BOOJ_FILE,   array( $this, 'deactivate' ) );
  }

  public function register_the_settings(){
    register_setting( $this->option_group, $this->option_name, '0' );
  }

  public function activate() {
    update_option( $this->option_name, $this->data );
  }

  public function deactivate() {
    delete_option( $this->option_name );
  }

  public function validate($input) {
    $valid = array();
    $valid['proxy_admin_urls'] = $input['proxy_admin_urls'];
    $valid['related_posts']    = $input['related_posts'];
    return $valid;
  }


  /***********************************************************                        
     _____           _ 
    | __  |___ ___  |_|
    | __ -| . | . | | |
    |_____|___|___|_| |
                  |___|

    Booj

    Basic Setup for the entire plugin

  */

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

  public function setting_menu() {
    // add_options_page( 'My Plugin Options', 'My Plugin', 'manage_options', 'my-unique-identifier', 'my_plugin_options' );
    add_options_page( 'Booj Options', 'WpBooj', 'manage_options', 'wpbooj_options', array( $this, 'booj_options_page' ) );
  }

    //Booj Options Page
  public function booj_options_page() {
    if ( !current_user_can( 'manage_options' ) )  {
      wp_die( __( 'You do not have sufficient permissions to access this pag!e.' ) );
    }
    $options = get_option($this->option_group);

    //  alix data dump  {debug}  
    // echo "<pre>"; print_r( $options ); die();

    // echo "<pre>"; print_r( $this->option_name  );
    // echo "<pre>"; print_r( $this->option_group  );    
    // echo "<pre>asd"; print_r( $whitelist_options  ); die();
    ?>
    <div class="wrap">
      <?php screen_icon(); ?>
      <h2>WpBooj</h2>
      <p>If you don't understand these options, you will want to leave them as they are!</p>
        <form method="post" action="options.php">
          <?php settings_fields('todo_list_options'); ?>
          <table class="form-table">
            <tr valign="top"><th scope="row">Use Proxy Urls:</th>
              <td><input type="checkbox" name="<?php echo $this->option_name; ?>['proxy_admin_urls']" <? if( $options['proxy_admin_urls'] == 'on' ){ echo 'checked="checked"'; } ?> /></td>
            </tr>
            <tr valign="top"><th scope="row">Use Related Posts:</th>
              <td><input type="checkbox" name="<?php echo $this->option_name; ?>['related_posts']" <? if( $options['related_posts'] == 'on' ){ echo 'checked="checked"'; } ?> /></td>
            </tr>            
          </table>
          <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
          </p>
        </form>
    </div>
    <?
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
      #wp-admin-bar-updates{ display: none; }
    </style>
    <?
  }



  /***********************************************************
     _____  
    |  |  |___| |___ 
    |  |  |  _| |_ -|
    |_____|_| |_|___|
  
    Urls

    This updates bad urls for the admin because of our Apache Proxy and stops users from access the active-clients.com location

  */

  public function redirect_activeclients(){
    $options = get_option( $this->option_name );
    if( $options['proxy_admin_urls'] == 'on' && ! isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ){
      header( 'Location: ' . get_site_url() . '/' );   
    }
  }

  public function proxy_admin_urls(){
    // Update the HTTP_HOST because wordpress bases urls off of this
    // We're removing 7 characters, 'http://' so the site_url MUST BE ex: http://www.clarkhawaii.com/
    // @todo: Make this more robust for the above reason!
    $options = get_option( $this->option_name );
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
     _____     _   _              _____     _       
    |  _  |_ _| |_| |_ ___ ___   |     |___| |_ ___ 
    |     | | |  _|   | . |  _|  | | | | -_|  _| .'|
    |__|__|___|_| |_|_|___|_|    |_|_|_|___|_| |__,|
  

    Author Meta 

    Add and display extra meta data for authors

  */


  function booj_profile_fields_admin_display( $user ) { 
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


  function booj_profile_fields_save( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) )
      return false;
    update_usermeta( $user_id, 'agent_bio_url', $_POST['agent_bio_url'] );
  }



  /***********************************************************                                                              
     _____             _            _____         _           _   
    |  _  |___ ___ _ _| |___ ___   |     |___ ___| |_ ___ ___| |_ 
    |   __| . | . | | | | .'|  _|  |   --| . |   |  _| -_|   |  _|
    |__|  |___|  _|___|_|__,|_|    |_____|___|_|_|_| |___|_|_|_|  
              |_|                                                 

    Popular Content

    Select and generate the popular posts, authors, and top content authors.
    Many of these methods require the Wp_PostViews plugin that Booj has modified.

  */

    public static function get_top_content_creators( $num_creators = 3, $blacklist_ids = array() ){
      global $wpdb;
      if( ! empty( $blacklist_ids ) ){
        $where = ' WHERE ';
        foreach ($blacklist_ids as $blacklist_id ) {
          $where .= '`post_author` != ' . $blacklist_id . ' AND ';
        }
        $where = substr( $where, 0, -4 );
      } else {
        $where = "";
      }

      $sql = "SELECT DISTINCT(post_author), COUNT(*) FROM {$wpdb->prefix}posts ".$where." GROUP BY 1 ORDER BY 2 DESC LIMIT " . $num_creators;

      $authors_db = $wpdb->get_results( $sql  );

      $authors = array();
      foreach( $authors_db  as $author ){
        $authors[] = get_userdata( $author->post_author );
      }
      return $authors;
    }


  /***
    GET TOP POSTS
    Description - Collects the posts with the most views for the current blog. 
      This is done through wordpress meta_key meta_value store for posts
      
    Requires - Wp_PostViews

    Usage -
      foreach( WpBooj::get_top_posts( 5 ) as $post ){ echo $post['post_title']; }

    Args -
      $count ( defaults 5 )

    Return - 
      array
   */
    public static function get_top_posts( $count = 5 ){
      //@todo check to make sure WP-PostViews is installed!
      include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
      if( is_plugin_active( 'wp-postviews/wp-postviews.php' ) ){
        global $wpdb;
        $sql = "SELECT posts.ID, meta.meta_value, posts.post_title, posts.post_name, posts.post_date FROM `{$wpdb->prefix}postmeta` as meta
               INNER JOIN `{$wpdb->prefix}posts` as posts
               ON meta.post_id  = posts.ID
               WHERE `meta_key` = 'views' ORDER BY CAST( `meta_value` AS DECIMAL ) DESC LIMIT " . $count;
        $posts = $wpdb->get_results( $sql  );

        $popular = array();
        foreach( $posts as $key => $post ){
          $popular[$key]['post_id']    = $post->ID;
          $popular[$key]['post_views'] = $post->meta_value;
          $popular[$key]['post_title'] = $post->post_title;
          $popular[$key]['post_slug']  = $post->post_name;
          $popular[$key]['post_date']  = $post->post_date;
          $popular[$key]['url']      = '/' . date( 'Y/m/', strtotime( $popular[$key]['post_date'] ) ) . $popular[$key]['post_slug'];
        }
        return $popular;

      } else {
        return 'Please install and enable the plugin "Wp Post Views"';
      }
    }

    public static function get_top_posts_for_loop( $count = 10 ){
      include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
      if( is_plugin_active( 'wp-postviews/wp-postviews.php' ) ){
        global $wpdb;
        $sql = "SELECT posts.`ID`, meta.`meta_value`
              FROM `{$wpdb->prefix}postmeta` as meta
               INNER JOIN `{$wpdb->prefix}posts` as posts
               ON meta.`post_id`  = posts.`ID`
               WHERE 
                 meta.`meta_key` = 'views' 
                 AND posts.`post_type` = 'post'
                 AND posts.`post_status` = 'publish'
              ORDER BY CAST( `meta_value` AS DECIMAL ) DESC LIMIT " . $count;
        $posts = $wpdb->get_results( $sql  );
        $popular = array();
        foreach( $posts as $key => $post ){
          $popular[] = get_post( $post->ID );
        }
        return $popular;
      } else {
        exit( 'Please install and enable the plugin "Wp Post Views"' );
      }
    }


  /***********************************************************
     _____         _     _ 
    |   __|___ ___|_|___| |
    |__   | . |  _| | .'| |
    |_____|___|___|_|__,|_|

    Social

    A grab bag of social plugins.

  */

    /***
      Get the most recent facebook status.
      @params
        id        : ex( 'aclient')
        appId     : ex( '121207014572698' )
        appSecret : ex( '20b366570115d4444ff61274d7bf4338')
    */
  public static function get_latest_fb_status( $id, $appId, $appSecret ){
    $facebook_url = "https://graph.facebook.com/$id/feed?fields=message,name,link&limit=1&access_token=$appId|$appSecret";
    $curl = curl_init( $facebook_url );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $results = json_decode(curl_exec($curl), true);
    curl_close($curl);
    if ( isset($results['data']) ){
      $results = isset($results['data'][0]) ? $results['data'][0] : false;
    } else {
      throw new Exception('Problem with Facebook');
    }
    $results['created_time'] = strtotime( $results['created_time'] );
    return $results;
  }


  /***********************************************************                     
     _____ _         
    |     |_|___ ___ 
    | | | | |_ -|  _|
    |_|_|_|_|___|___|

    Misc

    A collection of random functions that are put here for hopes of some sort of collection

  */ 

  public static function truncate( $string, $length ){
    if( strlen( $string ) > $length ){
      $new_string = substr( $string, 0, $length ) . '...';
    } else {
      $new_string = $string;
    }
    return $new_string;
  }
  
  public static function truncate_by_word( $string, $length ){
    $words        = explode( ' ', $string );
    $truncated    = '';
    $letter_count = 0;

    foreach ($words as $key => $word) {
      if( $letter_count < $length ){
        $truncated    = $truncated . $word . ' ';
        $letter_count = $letter_count + strlen ( $word );
      } else {
        $truncated = substr( $truncated, 0, -1 );
        break;
      }
    }
    if( strlen( $string ) > strlen( $truncated ) ){
      $truncated = $truncated . '...';
    }
    return $truncated;
  }

  /***
    Remove HTML, PHP, and Wordpress captions, also, truncate if desired.
    @params:
      $string = string of content with potential html, php or Wordpress caption code
      $length = int, length of the return string after code stripping
   */
  public static function removeCode( $string ){
    $butterfly = strip_shortcodes( $string );
    $butterfly = strip_tags( $butterfly );
    return $butterfly;
  }

}

/* ENDFILE: includes/WpBooj.php */

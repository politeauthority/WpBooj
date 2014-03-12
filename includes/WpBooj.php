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
  
  function __construct(){
    add_action( 'wp_head',    array( $this, 'redirect_activeclients' ) );

    // Listen for the plugin activate/deactivate event
    register_activation_hook(   WP_BOOJ_FILE,   array( $this, 'activate' ) );
    register_deactivation_hook( WP_BOOJ_FILE,   array( $this, 'deactivate' ) );
  }

  public function activate() {
    update_option( $this->option_name, $this->data );
  }

  public function deactivate() {
    delete_option( $this->option_name );
  }



  /***********************************************************
     _____  
    |  |  |___| |___ 
    |  |  |  _| |_ -|
    |_____|_| |_|___|
  
    Urls

    stops users from access the active-clients.com location

  */

  public function redirect_activeclients(){
    $options = get_option( $this->option_name );
    if( $options['proxy_admin_urls'] == 'on' && ! isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ){
      header( 'Location: ' . get_site_url() . '/' );   
    }
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

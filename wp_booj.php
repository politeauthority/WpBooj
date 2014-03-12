<?php
/*
Plugin Name: WP Booj
Plugin URI: https://github.com/politeauthority/WpBooj/
Description: Booj general plugin. Fixes Admin URLs and many other simple tweaks
Version: .090
Author: Alix Fullerton
Author URI: http://www.booj.com/
Release Date: 2014-02-16 15:27

This version currently supports; 
- Enterprise Branding Footer
- Removes "update nag screen" from all views. 
- Fixes ALL admin url breaks that can come about in Apache proxies
- Adds author meta options in admin, see README.md for front-end ussage.
- Facebook status integration.
- Variable table prefixes

@Todo
  - Add option to redirect off of Active-Clients.com URL ALWAYS
  - Add option to force "featured images" on a post
*/

define( 'WP_BOOJ_PATH', plugin_dir_path( __FILE__ ) );
require WP_BOOJ_PATH . 'includes/WpBooj.php';

// new WpBooj();

if( is_admin() ){
  require WP_BOOJ_PATH . 'includes/WpBoojAdmin.php';
  new WpBoojAdmin();
}

// if( $options['related_posts'] == 'on' ){
//   require WP_BOOJ_PATH . 'includes/WpBoojRelated.php';
//   // $WpBoojRelated = new WpBoojRelated();
// }

$options = get_option( 'wp-booj' );

if (! function_exists( 'wp_redirect' ) && $options['proxy_admin_urls'] == 'on' ) {
  function wp_redirect($location, $status = 302) {
    global $is_IIS;

    $location = apply_filters('wp_redirect', $location, $status);
    $status   = apply_filters('wp_redirect_status', $status, $location);

    if ( !$location ) // allows the wp_redirect filter to cancel a redirect
    return false;

    $location = wp_sanitize_redirect($location);

    if ( !$is_IIS && php_sapi_name() != 'cgi-fcgi' )
      status_header($status); // This causes problems on IIS and some FastCGI setups

    $uri_ext = '/' . WpBoojFindURISegment();
    $uri_len = strlen( $uri_ext ) + 1;
    if( substr( $location, 0, 1 ) == '/' && substr( $location, 0, $uri_len ) != $uri_ext ){
      $location = '/blog' . $location;
    }
    header("Location: $location", true, $status);
  } 
}

function WpBoojFindURISegment(){
  $site_url =  get_site_url();
  //Currently we're only going to support .com TLD
  if ( strpos( $site_url, '.com' ) !== false ){
    $exploded = explode( '.com', $site_url );
    if( ! empty( $exploded[1] ) ){
      $uri_segment = explode( '/', $exploded[1] );
      $uri = $uri_segment[1];
    } else{
      $uri = '';
    }
  }
  return $uri;
}

?>

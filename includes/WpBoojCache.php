<?php
/***********************************************************                                                          
	 _ _ _     _____           _    _ _    _____     _     _         _ 
	| | | |___| __  |___ ___  |_|  |_|_|  | __  |___| |___| |_ ___ _| |
	| | | | . | __ -| . | . | | |   _ _   |    -| -_| | .'|  _| -_| . |
	|_____|  _|_____|___|___|_| |  |_|_|  |__|__|___|_|__,|_| |___|___|
	      |_|               |___|                                      
	  
  WpBooj :: Cache

*/

class WpBoojCache {
	
	private function check( $post_id ){
		global $wpdb;
		//@todo: get the correct timeago;
		$seconds_cache_can_live = 1000;
		$time_ago = 1000;
		$sql = """SELECT * FROM {$wpdb->prefix}WpBoojCache WHERE 
			`type` = 'WpBoojRelated' AND
			`post_id` = {$post_id} AND
			`last_update_ts` > "{$time_ago}";""";
		//@todo: if we get a result, unserialize and send it back
		return False;
	}

	private function store( $post_id, $posts ){
		//@todo: serialize $posts
		global $wpdb;
		$sql = """INSERT INTO {$wpdb->prefix}WpBoojCache 
			`type`,`post_id` VALUES( "WpBoojRelated", {$post_id} );""";
	}

	//@todo: this needs to get called whenever a post is updated, probably a button in the admin too
	public static function clear_cache( $post_id = None ){
		//@todo: serialize $posts
		global $wpdb;
		$sql = """INSERT INTO {$wpdb->prefix}WpBoojCache 
			`type`,`post_id` VALUES( "WpBoojRelated", {$post_id} );""";
	}

	public function install(){
    global $wpdb;
    $WpBoojRelated_cache_table_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}WpBoojCache (
      `cache_id` int(11) NOT NULL AUTO_INCREMENT,
      `post_id` int(11) DEFAULT NULL,
      `type` varchar(255) DEFAULT NULL,
      `related_posts` longtext(40) DEFAULT NULL,
      `last_update_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`cache_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";		
	}

}
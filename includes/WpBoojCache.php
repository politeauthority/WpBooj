<?php
/***********************************************************                                             
	 _ _ _     _____           _    _ _    _____         _       
	| | | |___| __  |___ ___  |_|  |_|_|  |     |___ ___| |_ ___ 
	| | | | . | __ -| . | . | | |   _ _   |   --| .'|  _|   | -_|
	|_____|  _|_____|___|___|_| |  |_|_|  |_____|__,|___|_|_|___|
	      |_|               |___|                                

	  
  WpBooj :: Cache

*/

class WpBoojCache {
	
	public static function check( $post_id ){
		global $wpdb;
		$seconds_cache_can_live = 1000;
		$time_ago = 86400;
		$time_back = time() - $time_ago;
		$dt = new DateTime("@$time_back");
		$dt = $dt->format('Y-m-d H:i:s');
		$sql = "SELECT * FROM {$wpdb->prefix}WpBoojCache WHERE 
			`type` = 'WpBoojRelated' AND
			`post_id` = {$post_id} AND
			`last_update_ts` > '{$dt}';";
		$response = $wpdb->get_results( $sql );
		if( count( $response ) == 0 ){
			return False;
		} else {
			$loaded_cache = unserialize( $response[0]->data );
			return $loaded_cache;
		}
	}

	public static function store( $post_id, $post_type, $data ){
		global $wpdb;
		// check if an old cache exists to overwrite
		$data = mysql_real_escape_string( serialize( $data ) ); 
		$sql  = "INSERT INTO {$wpdb->prefix}WpBoojCache ( `type`,`post_id`,`data` ) VALUES( '$post_type', '$post_id', '$data' );";
		$wpdb->query( $sql );
	}

	//@todo: this needs to get called whenever a post is updated, probably a button in the admin too
	public static function clear_cache( $post_id = None, $type_id = None ){
		global $wpdb;
		$sql = "DELETE FROM {$wpdb->prefix}WpBoojCache WHERE `post_id` = '{$post_id}' AND `type` = {$type};";
		die( $sql );
	}

}
<?php

	/*********************************************************************                                                                  
	 _ _ _     _____           _    _ _    _____     _     _         _ 
	| | | |___| __  |___ ___  |_|  |_|_|  | __  |___| |___| |_ ___ _| |
	| | | | . | __ -| . | . | | |   _ _   |    -| -_| | .'|  _| -_| . |
	|_____|  _|_____|___|___|_| |  |_|_|  |__|__|___|_|__,|_| |___|___|
	      |_|               |___|                                      
	*/

class WpBoojRelated {
	
	public static function get( $post, $count = 4 ){
		global $wpdb;

		// GET THE CATEGORIES AND TAG IDS THAT WE WANT TO FIND
		$tags  = wp_get_post_tags( $post->ID );
		$tag_ids = '';
		foreach ( $tags as $key => $tag) {
			$tag_ids .= $tag->term_id . ',';
		}
		$tag_ids = substr( $tag_ids, 0, -1);


		$cats  = get_the_category( $post->ID );
		$cat_ids = '';
		foreach ( $cats as $key => $cat) {
			$cat_ids .= $cat->term_id . ',';
		}
		$cat_ids = substr( $cat_ids, 0, -1);



		// NOW WELL LOOK FOR ALL THE OTHER POSTS / PAGES
		// WHICH USE ANY OF THE SAME CATS / TAGS ORDERD AMMOUNT OF SIMILARITIES
		// $potential_posts is created here
		$query = "SELECT DISTINCT( object_id ), COUNT(*) AS count 
			FROM blog_kentwood.wp_term_relationships 
			WHERE term_taxonomy_id IN( " . $tag_ids . "," . $cat_ids ." ) 
				AND object_id != " . $post->ID . "
			GROUP BY 1
			ORDER BY 2 DESC
			LIMIT 50";

		$rows = $wpdb->get_results( $query );

		$potential_posts = array();

		$post_ids = '';
		foreach( $rows as $key => $row ) {
			$potential_posts[$row->object_id] = array( 
				'post_id'    => $row->object_id,
				'occurance'  => $row->count,
				'points'     => ( $row->count * 1000 ), 
			);
			$post_ids .= $row->object_id . ',';
		}



		// NOW WE QUERY AGAINST THE POST IDS FROM ABOVE
		// WE ARE FILTERING OUT ANY POSTS WHICH ARE NOT PUBLISHED
		// $potential_posts is important here
		$post_ids = substr( $post_ids, 0, -1);
		$query2 = "SELECT * FROM `blog_kentwood`.`wp_posts` 
			WHERE `ID` IN( ". $post_ids ." ) 
				AND `post_status` = 'publish'
				AND `post_type`   = 'post'
			ORDER BY `post_date` DESC";

		$rows = $wpdb->get_results( $query2 );


		foreach( $rows as $key => $row ) {
			if( is_array( $potential_posts[ $row->ID ]) ){
				$date_point_penelty = round( ( time() - strtotime( $row->post_date ) ) / 86400, 0 );
				$potential_posts[ $row->ID ]['post']   = $row;
				$potential_posts[ $row->ID ]['points'] = $potential_posts[ $row->ID ]['points'] - $date_point_penelty;
			}
		}



		// HERE WE COMPUTE ARE POINTS
		$weighted_posts = array();
		foreach( $potential_posts as $key => $value ) {
			if( is_object( $value['post'] ) ){
				$weighted_posts[$key] = $value['points'];			
			} else {
				unset( $potential_posts[$key] );
			}
		}

		array_multisort( $weighted_posts, SORT_DESC, $potential_posts );




		// NOW LETS GROOM THE LIST, MAKE SURE WE HAVE WHAT WE NEED
		// MAKE SURE WE HAVE ENOUGH CONTENT, OTHER WISE FIND SOME
		if( count( $potential_posts ) < $count ){
			$ids_to_omit = $post->ID . ',';
			$posts_more_needed = $count - count( $potential_posts );
			if( ! empty( $potential_posts ) ){
				foreach ($potential_posts as $key => $value) {
					$ids_to_omit .= $value['post']->ID . ',';
				}
			}
			$ids_to_omit = substr( $ids_to_omit, 0, -1);			

			$query3 = "SELECT * FROM `wp_posts` 
				WHERE `ID` NOT IN( ". $ids_to_omit ." )
					AND `post_status` = 'publish'  
					AND `post_type` = 'post' 
				ORDER BY `post_date` DESC
				LIMIT " . $posts_more_needed;


			$rows = $wpdb->get_results( $query3 );

			foreach( $rows as $key => $row ) {
				$potential_posts[$row->ID]['ID']   = $row->ID;
				$potential_posts[$row->ID]['post'] = $row;
			}			
		} 

		// add the link to the posts so we can link ot them, and then we're done!
		foreach( $potential_posts as $key => $post ) {
			$potential_posts[$key]['guid'] = date( '/Y/m/', strtotime( $post['post']->post_date ) ) . $post['post']->post_name;
			$potential_posts[$key]['post']->post_content  = trim( strip_tags( $post['post']->post_content ) );
		}

		if( count( $potential_posts ) > $count ){
			$potential_posts = array_slice( $potential_posts, 0, $count, True );
		}

		return $potential_posts;
	}

}
WpBooj
=======

This is a plugin for Wordpress. Primarily Wordpress 3+

- Resolves Apache Proxy url issues which arise in the Wordpress Admin links, when enabled our blogs can exist at client.com/blog/
- Removes Wordpress update nag screen for all users, though this needs updating with newer versions of wordpress
- Allows for the author meta option of "agent_bio_url" available to templates by "get_the_author_meta( 'agent_bio_url', $user->ID )"

Front End Developer Tools
There are a handful of commonly needed functions available to front end developers.

Popular Content
- NOTE: Many of these functions require the WP Post Views plugin install to work properly.
- WpBooj::get_top_content_creators( $num_creators =  int(), $blacklisted_ids = array() )
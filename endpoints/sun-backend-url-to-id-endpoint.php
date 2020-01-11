<?php
/**
 * Handles the /urltoid endpoint, which returns the integer ID associated with
 * the post URL passed in the `url` parameter.
 */

class SunAppExtension_UrlToIdEndpoint {

	/**
	 * Initialize and register everything necessary for the /urltoid endpoint to
	 * run properly.
	 */
	public static function init() {
		$cur_file_path = plugin_dir_path( __FILE__ );
		include_once( $cur_file_path . "../includes/sun-backend-constants.php" );

		register_rest_route( PLUGIN_ENDPOINT . '/' . PRODUCTION_VERSION, '/urltoid', array(
			'methods'  => 'GET',
			'callback' => 'SunAppExtension_UrlToIdEndpoint::get_url_to_id',
		) );
	}

	/**
	 * Return the ID associated with the post at the given url. 0
	 * if no ID is found.
	 */
	public static function get_url_to_id( $request ) {
		$url = $request->get_param( 'url' );

		return url_to_postid( $url );
	}
}

?>
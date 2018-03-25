<?php
/**
 * Handles the /comments endpoint, which handles retrieving the associated comments
 * for any given article ID.
 */
class SunAppExtension_CommentsEndpoint {

    /**
     * Set up and register the /comments endpoint with the rest_api_init function.
     */
    public static function init() {
        $cur_file_path = plugin_dir_path( __FILE__ );
        include_once( $cur_file_path . "../includes/sun-backend-constants.php" );

        register_rest_rout( PLUGIN_ENDPOINT . '/' . PRODUCTION_VERSION, '/comments', array(
            'methods' => 'GET',
            'callback' => 'SunAppExtension_CommentsEndpoint::get_default_comments'
        ));

        register_rest_route( PLUGIN_ENDPOINT . '/' . PRODUCTION_VERSION, '/comments/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => 'SunAppExtension_CommentsEndpoint::get_post_comments'
        ));
    }

    public static function get_default_comments() {
        return 1;
    }

    /**
     * Return a list of comment JSON objects associated with the post denoted
     * by the ID passed into the request.
     */
    public static function get_post_comments( $request ) {
        return $request;
    }
}

?>
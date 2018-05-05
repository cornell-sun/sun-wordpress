<?php
/**
 * Handles the /featured endpoint, which returns the featured post prominently posted
 * on the first home page using the largo plugin.
 */

class SunAppExtension_FeaturedEndpoint {

    /**
     * Initialize and register everything necessary for the /featured endpoint to
     * run properly.
     */
    public static function init() {
        $cur_file_path = plugin_dir_path( __FILE__ );
        include_once( $cur_file_path . "../includes/sun-backend-constants.php" );

        register_rest_route( PLUGIN_ENDPOINT . '/' . PRODUCTION_VERSION, '/featured', array(
            'methods' => 'GET',
            'callback' => 'SunAppExtension_FeaturedEndpoint::get_featured_home_post',
        ));
    }

    /**
     * Return the entire post object corresponding to the featured
     * post on the home page of cornellsun.com.
     */
    public static function get_featured_home_post() {
        $featured_post = get_object_vars( largo_home_single_top() );
        $featured_post["post_info_dict"] = SunAppExtension_PostsFunctions::generate_post_entry( $featured_post["ID"] );
        return $featured_post;
    }
}

?>

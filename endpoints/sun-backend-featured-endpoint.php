<?php
/**
 * Handles the /featured endpoint, which returns the featured post prominently posted
 * on the first home page using the largo plugin.
 */

class SunAppExtension_FeaturedEndpoint {

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
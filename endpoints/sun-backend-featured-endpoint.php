<?php
/**
 * Takes care of all featured post endpoints.
 * The default /featured endpoint returns the featured post prominently posted
 * on the first home page using the largo plugin.
 *
 * The /featured/{category} endpoint returns the most popular post within a category using the largo plugin
 */
class SunAppExtension_FeaturedEndpoint {
    /**
     * Initialize and register everything necessary for the /featured endpoint to
     * run properly.
     */
    public static function init() {
        $cur_file_path = plugin_dir_path(__FILE__);
        include_once $cur_file_path . "../includes/sun-backend-constants.php";

        register_rest_route(
            PLUGIN_ENDPOINT . '/' . PRODUCTION_VERSION, '/featured/(?P<category>[A-Za-z]+)', array(
                'methods' => 'GET',
                'callback' => 'SunAppExtension_FeaturedEndpoint::get_featured_in_category',
            )
        );

        register_rest_route(
            PLUGIN_ENDPOINT . '/' . PRODUCTION_VERSION, '/featured', array(
                'methods' => 'GET',
                'callback' => 'SunAppExtension_FeaturedEndpoint::get_featured_home_post',
            )
        );
    }
    /**
     * Return the entire post object corresponding to the featured
     * post on the home page of cornellsun.com.
     */
    public static function get_featured_home_post() {
        $featured_post = get_object_vars(largo_home_single_top());
        $featured_post["post_info_dict"] = SunAppExtension_PostsFunctions::generate_post_entry($featured_post["ID"]);
        return $featured_post;
    }

    /**
     * Return the featured post object of a specific category
     *
     * @param ArrayAccess $request is an array of parameters which contains the category parameter whose featured post will be fetched
     *
     * @return array $featured_post the currently featured post in the category
     */
    public static function get_featured_in_category($request) {
        $featured_post = largo_get_featured_posts_in_category($request->get_param("category"), 1);
        $featured_post = get_object_vars($featured_post[0]);
        $featured_post["post_info_dict"] = SunAppExtension_PostsFunctions::generate_post_entry($featured_post["ID"]);
        return $featured_post;
    }
}

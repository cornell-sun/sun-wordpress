<?php

/**
 * Handles the /author endpoint, which handles retrieving the author information
 * and author posts for a given author name in string format
 */
class SunAppExtension_AuthorEndpoint
{

    /**
     * Set up and register the /author endpoint with the rest_api_init function.
     */
    public static function init()
    {
        $cur_file_path = plugin_dir_path(__FILE__);
        include_once $cur_file_path . "../includes/sun-backend-constants.php";

        register_rest_route(PLUGIN_ENDPOINT . '/' . PRODUCTION_VERSION, '/author/(?P<author_name>[A-Za-z0-9\+\%]+)(\?page=(/(?P<page>\d+)))?', array(
            'methods' => 'GET',
            'callback' => 'SunAppExtension_AuthorEndpoint::get_author_info',
        ));
    }

    /**
     * Handle a request to the /author endpoint
     */
    public static function get_author_info($request)
    {
        $author_name = $request->get_param("author_name");
        $author_name = urldecode($author_name);
        $page = $request->get_param("page");
        $author_info_dict = SunAppExtension_PostsFunctions::get_author_metadata($author_name);
        $author_info_dict["posts"] = self::get_author_posts($author_name, $page);
        $author_info_dict["target_name"] = $author_name;
        return $author_info_dict;
    }

    /**
     * Search the Wordpress Database for all posts associated with author_name.
     * 
     * Return an array of post_info_dict containing posts associated with
     * the target author_name
     */
    public static function get_author_posts($author_name, $page)
    {
        $author_posts_query = array(
            'posts_per_page'   => 10,
            'orderby'          => 'post_date',
            'order'            => 'DESC',
            'post_status'      => 'publish',
            'paged'             => $page ? $page : 1,
            'meta_query'       => array(
                array(
                    'key'     => 'largo_byline_text',
                    'value'   => $author_name,
                    'compare' => 'LIKE',
                ),
            ),
        );
        $author_posts = get_posts($author_posts_query);

        return array_map(function ($post) {
            return SunAppExtension_PostsFunctions::generate_post_entry($post->ID);
        },  $author_posts);
    }
}

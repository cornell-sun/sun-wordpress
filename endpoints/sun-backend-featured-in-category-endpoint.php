<?php
  class SunAppExtension_FeaturedInCategoryEndpoint {

    /**
    * Initialize and register everything necessary for the
    * /featured_in_category endpoint to run properly
    */
    public static function init() {
        $cur_file_path = plugin_dir_path( __FILE__ );
        include_once( $cur_file_path . "../includes/sun-backend-constant.php" );

        register_rest_route( PLUGIN_ENDPOINT . '/' . PRODUCTION_VERSION, '/featured_in_category/(?P<category>[A-Za-z]+)',
        array(
            'methods' => GET,
            'callback' => 'SunAppExtension_FeaturedInCategoryEndpoint::get_featured_in_category'
        ));
    }

    /**
     * Return the featured post object of a specific category
     * @param String $category_name the category whose featured post will be fetched
     * @return array $featured_post the currently featured post in the category
     */
     public static function get_featured_in_category( $request ) {
        $featured_post = largo_get_featured_posts_in_category( $request->get_param( "category" ), 1 );
        $featured_post = get_object_vars( $featured_post[0] );
        $featured_post[ "post_info_dict" ] = SunAppExtension_PostsFunctions::generate_post_entry( $featured_post["ID"] );
        return $featured_post;
     }
  }
 ?>

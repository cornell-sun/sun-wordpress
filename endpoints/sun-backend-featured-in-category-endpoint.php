<?php
  class SunAppExtension_CategoryFeaturedEndpoint {

    /**
    * Initialize and register everything necessary for the
    * /featured-category-endpoint.php to run properly
    */
    public static function init() {
        $cur_file_path = plugin_dir_path( __FILE__ );
        include_once( $cur_file_path . "../includes/sun-backend-constant.php" );

        register_rest_route( PLUGIN_ENDPOINT . '/' . PRODUCTION_VERSION, '/featured_in_category', array (
            'methods' => GET,
            'callback' => 'SunAppExtension_FeaturedEndpoint::get_category_featured'
        ));
    }

    /**
     * Return the featured post object corresponding to
     * a category
     */
     public static function get_category_featured( $category_name ) {
        $featured_post = largo_get_featured_posts_in_category( $category_name, 1 );
        $featured_post = get_object_vars( $featured_post[0] );
        $featured_post["post_info_dict"] = SunAppExtension_PostsFunctions::generate_post_entry( $featured_post["ID"] );
        return $featured_post;
     }
  }
 ?>

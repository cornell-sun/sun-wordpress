<?php
  class SunAppExtension_CategoryFeaturedEndpoint {

    /**
    * Initialize and register everything necessary for the
    * /featured-category-endpoint.php to run properly
    */
    public static function init() {
        $cur_file_path = plugin_dir_path( __FILE__ );
        include_once( $cur_file_path . "../includes/sun-backend-constant.php" );

        register_rest_route( PLUGIN_ENDPOINT . '/' . PRODUCTION_VERSION, '/featured', array (
            'methods' => GET,
            'callback' => 'SunAppExtension_FeaturedEndpoint::get_featured_home_post'
        ));
    }

    /**
     *
     */
     public static function 
  }
 ?>

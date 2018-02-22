<?php
/**
 * @package Sun-Wordpress
 * @version 1.0
 */
/*
Plugin Name: Sun Backend Extension
Plugin URI: https://github.com/cornell-sun/sun-wordpress
Description: The Sun Backend Extension adds extra functionality to the traditional Wordpress backend so it is easier and faster to use with the new Cornell Daily Sun iOS app.
Author: Cornell Sun App Team (Managed by Chris Sciavolino)
Version: 1.0
Author URI: http://cdsciavolino.github.io
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'SunAppExtension_Plugin' ) ) {

    class SunAppExtension_Plugin {

        /**
         * Add all necessary actions to add desired information in Wordpress endpoints.
         */
        public static function init() {

            include_once( 'endpoints/sun-backend-featured-endpoint.php' );
            include_once( 'endpoints/sun-backend-trending-endpoint.php' );
            include_once( 'includes/sun-backend-constants.php' );

            // add post_info_dict to each of the posts requested
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::get_post_info_dict' );

            // set up endpoints
            add_action( 'rest_api_init', function () {
                register_rest_route( PLUGIN_ENDPOINT . '/' . PRODUCTION_VERSION, '/trending', array(
                    'methods' => 'GET',
                    'callback' => 'SunAppExtension_TrendingEndpoint::get_trending_tags',
                ));

                register_rest_route( PLUGIN_ENDPOINT . '/' . PRODUCTION_VERSION, '/featured', array(
                    'methods' => 'GET',
                    'callback' => 'SunAppExtension_FeaturedEndpoint::get_featured_home_post',
                ));
            } );
        }

        /**
         * Return a dictionary of additional information about information and
         * register it as a field to be returned for each of the posts requested.
         */
        public static function get_post_info_dict( $data ) {
            include_once( 'functions/sun-backend-posts-functions.php' );
            register_rest_field( 'post', 'post_info_dict', array(
                'get_callback' => function ( $post_arr ) {
                    return SunAppExtension_PostsFunctions::generate_post_entry( $post_arr["id"] );
                }
            ));
        }

        public static function posts_add_content_no_srcset( $data ) {
            register_rest_field( 'post', 'post_content_no_srcset', array(
                'get_callback' => function ( $post_arr ) {
                    $post_content = get_the_content( $post_id );
                    $rendered_content = stripslashes( apply_filters( 'the_content', $post_content ) );
                    $content_srcset_removed = preg_replace( "/srcset=\".*\"/", '', $rendered_content );
                    return $content_srcset_removed;
                }
            ));
        }
    }
        
    SunAppExtension_Plugin::init();
}

?>
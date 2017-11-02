<?php
/**
 * @package Sun-Wordpress
 * @version 1.0
 */
/*
Plugin Name: Sun Backend Extension
Plugin URI: n/a
Description: The Sun Backend Extension adds extra functionality to the traditional Wordpress backend so it is easier and faster to use with the new Cornell Daily Sun iOS app.
Author: Chris Sciavolino
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
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::posts_add_author_name' );
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::posts_add_media_url' );
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::posts_add_category_name' );
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::posts_add_tags_name' );
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::posts_add_comments' );
        }

        /**
        * Return the string name of the post's author given a post.
        */
        public static function posts_add_author_name( $data ) {
            register_rest_field( 'post', 'author_string', array(
                'get_callback' => function ( $post_arr ) {
                    $user_obj = get_user_by( 'id', $post_arr['author'] );
                    if ( empty( $user_obj ) ) {
                        return new WP_Error(
                            'rest_author_name_failed',
                            __( "Failed to retrieve author's name from given post." ),
                            array( 'status' => 500 )
                        );
                    }
                    return $user_obj->data->user_nicename;
                }
            ));
        }

        /**
        * Return the string URL for the media associated with a post.
        * Note:    Returns false if the post has no featured media.
        */
        public static function posts_add_media_url( $data ) {
            register_rest_field( 'post', 'featured_media_url_string', array(
                'get_callback' => function ( $post_arr ) {
                    $media_obj = wp_get_attachment_url( $post_arr["featured_media"] );
                    return $media_obj;
                }
            ));
        }

        /**
        * Return the string name of the category this post is in.
        */
        public static function posts_add_category_name( $data ) {
            register_rest_field( 'post', 'category_strings', array(
                'get_callback' => function ( $post_arr ) {
                    $categories = $post_arr["categories"];
                    return array_map( 
                        function ( $cat_id ) { return get_cat_name( $cat_id ); }, 
                        $categories
                    );
                }
            ));
        }

        /**
        * Return the corresponding array of tag strings.
        */
        public static function posts_add_tags_name( $data ) {
            register_rest_field( 'post', 'tag_strings', array(
                'get_callback' => function ( $post_arr ) {
                    $tags = $post_arr["tags"];
                    return array_map( 
                        function ( $tag_id ) { return get_tag( $tag_id )->name; }, 
                        $tags
                    );
                }
            ));
        }

        /**
        * Return the corresponding comments for a post.
        */
        public static function posts_add_comments( $data ) {
            register_rest_field( 'post', 'post_comments', array(
                'get_callback' => function ( $post_arr ) {
                    $post_id = $post_arr["id"];
                    return get_comments( array(
                        "post_id" => $post_id
                    ));
                }
            ));
        }
    }

    SunAppExtension_Plugin::init();
}

?>
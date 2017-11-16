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
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::posts_add_author_dict' );
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::posts_add_media_url' );
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::posts_add_category_name' );
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::posts_add_primary_category');
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::posts_add_tags_name' );
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::posts_add_comments' );
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::posts_add_type_enum' );
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::posts_add_attachments' );
        }

        /**
        * Return the string name of the post's author given a post.
        */
        public static function posts_add_author_dict( $data ) {
            register_rest_field( 'post', 'author_dict', array(
                'get_callback' => function ( $post_arr ) {
                    $post_meta = get_post_meta( $post_arr["id"] );
                    $author_names = $post_meta["largo_byline_text"];
                    $users = array();
                    if ( empty( $author_names ) ) {
                        $author_id = $post_arr["author"];
                        $user_obj = get_user_by( "id", $author_id );
                        $user_meta = get_user_meta( $author_id );

                        $user = array(
                            "id"            => $author_id,
                            "name"          => $user_obj->display_name,
                            "avatar_url"    => get_avatar_url( $author_id ),
                            "bio"           => $user_meta["description"][0],
                            "link"          => get_author_posts_url( $author_id )
                        );

                        array_push($users, $user);
                        return $users;
                    }
                    foreach ($author_names as $name) {
                        $user_dict = array();
                        $user_dict["name"] = $name;

                        $user_query = new WP_User_Query( array (
                            'search' => $name,
                            'search_columns' => array( 'display_name' )
                        ));

                        if ( !empty( $user_query->results ) ) {
                            $first_res = $user_query->results[0];
                            $user_id = $first_res->data->ID;
                            $user_meta = get_user_meta( $user_id );

                            $user_dict["id"] = $user_id;
                            $user_dict["avatar_url"] = get_avatar_url( $user_id );
                            $user_dict["bio"] = $user_meta["description"][0];
                            $user_dict["link"] = get_author_posts_url( $user_id );
                        }
                        array_push( $users, $user_dict );
                    }
                    return $users;
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
                    $media_med_lg = wp_get_attachment_image_src( 
                        $post_arr["featured_media"],
                        "medium_large"
                    );

                    $media_thumb = wp_get_attachment_image_src( 
                        $post_arr["featured_media"],
                        "thumbnail"
                    );

                    $media_full = wp_get_attachment_image_src( 
                        $post_arr["featured_media"],
                        "full"
                    );

                    $featured_media = array();
                    $featured_media["medium_large"] = array(
                        "url" => $media_med_lg[0],
                        "width" => $media_med_lg[1],
                        "height" => $media_med_lg[2]
                    );

                    $featured_media["thumbnail"] = array(
                        "url" => $media_thumb[0],
                        "width" => $media_thumb[1],
                        "height" => $media_thumb[2]
                    );

                    $featured_media["full"] = array(
                        "url" => $media_full[0],
                        "width" => $media_full[1],
                        "height" => $media_full[2]
                    );

                    return $featured_media;
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
         * Return the primary category this post is associated with. 
         * This means we return the category with the smallest ID and 
         * return News if no categories are attributed.
         */
        public static function posts_add_primary_category( $data ) {
            register_rest_field( 'post', 'primary_category', array(
                'get_callback' => function ( $post_arr ) {
                    $categories = $post_arr["categories"];
                    if ( empty( $categories ) ) {
                        // no categories, return News = 1
                        return "News";
                    }
                    $min_cat_id = $categories[0];
                    foreach ($categories as $cat) {
                        if ( $cat < $min_id ) {
                            $min_id = $cat;
                        }
                    }
                    return get_cat_name( $min_cat_id );
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

        /**
         * Returns whether a given post is a photoGallery (this week in photos) or simply
         * another article to those requesting a post.
         */
        public static function posts_add_type_enum( $data ) {
            register_rest_field( 'post', 'post_type_enum', array(
                'get_callback' => function ( $post_arr ) {
                    $post_title = $post_arr["title"]["rendered"];
                    if ( strpos( strtolower( $post_title ), "this week in photos" ) !== false) {
                        // photo gallery
                        return "photoGallery";
                    } else {
                        // normal article for now
                        return "article";
                    }
                }
            ));
        }

        /**
         * Return the URLs, captions, and other necessary metadata for all the media 
         * attachments associated with a single post.
         */
        public static function posts_add_attachments( $data ) {
            register_rest_field( 'post', 'post_attachments_meta', array(
                'get_callback' => function ( $post_arr ) {
                    $post_attachments = get_attached_media( "image", $post_arr["id"] );
                    $media_results = array();
                    foreach ( $post_attachments as $attachment ) {
                        $media_id = $attachment->ID;
                        $media_meta = wp_get_attachment_metadata( $media_id );
                        $author_id = $attachment->post_author;
                        $media_obj = array(
                            "id"            => $media_id,
                            "name"          => end( explode( "/", $media_meta["file"] ) ),
                            "caption"       => $attachment->post_excerpt,
                            "media_type"    => $attachment->post_mime_type,
                            "author_name"   => get_the_author_meta( 'display_name', $author_id ),
                            "full"          => wp_get_attachment_url( $media_id, 'full' )
                        );
                        
                        array_push( $media_results, $media_obj);
                    }
                    $rendered_content = stripslashes( $post_arr["content"]["rendered"] );
                    $used_images = array();
                    preg_match_all( "/<img .* src=(.*)\?re.* alt=.*>/", $rendered_content, $used_images );
                    $used_images = array_map( function ($ele) {
                        return end( explode( "/", $ele ) );
                    }, $used_images[1] );

                    foreach ($media_results as $media ) {
                        if (!in_array( $media["name"], $used_images ) ) {
                            unset( $media_results[$id] );
                        }
                    }
                    return $media_results;
                }
            ));
        }
    }

    SunAppExtension_Plugin::init();
}

?>
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
            add_action( 'rest_api_init', 'SunAppExtension_Plugin::posts_add_content_no_srcset' );

            add_action( 'rest_api_init', function () {
                register_rest_route( 'sun-backend-extension/v1', '/trending', array(
                    'methods' => 'GET',
                    'callback' => 'SunAppExtension_Plugin::get_trending_tags',
                ));
                register_rest_route( 'sun-backend-extension/v1', '/featured', array(
                    'methods' => 'GET',
                    'callback' => 'SunAppExtension_Plugin::get_featured_home_post',
                ));
            } );
        }

        /**
         * Return the top NUM_TRENDING_TAGS tags being used on the most popular
         * 10 articles being read in the last few days.
         */
        public static function get_trending_tags() {
            include_once( 'includes/sun-backend-config.php' );
            $request_url = add_query_arg( array(
                'api_key'   => JETPACK_API_KEY,
                'blog_uri'  => get_home_url(),
                'format'    => 'json',
                'days'      => 2,
                'table'     => 'postviews',
                'limit'     => 10
            ), esc_url( 'https://stats.wordpress.com/csv.php' ) );
            $response = wp_remote_get( $request_url );
            $data = json_decode( wp_remote_retrieve_body( $response ) );
            // unsuccessful request, no trending results
            if ( ! is_array( $data ) ) return array( "no trending tags" );
            $results_arr = (array)$data[1]; // TODO: Understand why this is randomly 1
            $postviews_arr = $results_arr["postviews"];
            
            $tag_counts = array();
            foreach($postviews_arr as $post) {
                $post_arr = (array)$post;
                $post_tags = wp_get_post_tags( $post_arr["post_id"], array( 'fields' => 'names') );
                foreach($post_tags as $tag) {
                    if ( array_key_exists( $tag, $tag_counts ) ) {
                        $tag_counts[$tag] += 1;
                    } else {
                        $tag_counts[$tag] = 1;
                    }
                }
            }

            // TODO: Put this into constants file
            define( 'NUM_TRENDING_TAGS', 7);
            $num_trending_tags = min( count( $tag_counts ), NUM_TRENDING_TAGS );

            // TODO: optimize this to not be as suspect
            $sorted_top_tags = array();
            while ( count($sorted_top_tags) < $num_trending_tags ) {
                $cur_most_popular = null;
                $cur_max = 0;
                foreach($tag_counts as $tag => $count) {
                    if ( $count > $cur_max ) {
                        $cur_most_popular = $tag;
                        $cur_max = $count;
                    }
                }
                array_push( $sorted_top_tags, $cur_most_popular );
                unset( $tag_counts[$cur_most_popular] );
            }
            return $sorted_top_tags;
        }

        /**
         * Return the entire post object corresponding to the featured
         * post on the home page of cornellsun.com.
         */
        public static function get_featured_home_post() {
            return largo_home_single_top();
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
                    $categories = get_the_category( $post_id );
                    if ( empty( $categories ) || !$categories ) {
                        // no categories, return News = 1
                        return "News";
                    }
                    
                    // linear search for minimum category id
                    $min_category = $categories[0];
                    foreach ($categories as $category) {
                        if ( $category->term_id < $min_category->term_id ) {
                            $min_category = $category;
                        }
                    }
                    return $min_category->name;
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
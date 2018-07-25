<?php
/**
 * Handles the /trending endpoint, which returns the most popular tags being used
 * in the last few days.
 */

class SunAppExtension_TrendingEndpoint {

    /**
     * Initialize and register everything necessary for the /trending endpoint to
     * run properly.
     */
    public static function init() {
        $cur_file_path = plugin_dir_path( __FILE__ );
        include_once( $cur_file_path . "../includes/sun-backend-constants.php" );

        register_rest_route( PLUGIN_ENDPOINT . '/' . PRODUCTION_VERSION, '/trending', array(
            'methods' => 'GET',
            'callback' => 'SunAppExtension_TrendingEndpoint::get_trending_tags',
        ));
    }

    /**
     * Makes a GET request and returns the JETPACK data associated with the website
     * that keeps track of all data on post readership and popularity.
     */
    private static function _get_jetpack_post_data() {
        $cur_file_path = plugin_dir_path( __FILE__ );
        include_once( $cur_file_path . "../includes/sun-backend-config.php" );

        // request 15 most recent popular posts for 2 days
        $request_url = add_query_arg( array(
            'api_key'   => JETPACK_API_KEY,
            'blog_uri'  => 'cornellsun.com',
            'format'    => 'json',
            'days'      => 2,
            'table'     => 'postviews',
            'limit'     => 15
        ), esc_url( 'https://stats.wordpress.com/csv.php' ) );
        $response = wp_remote_get( $request_url );
        return json_decode( wp_remote_retrieve_body( $response ) );
    }

    /**
     * Takes in a 2D array of arrays and flattens it into a single array,
     * retaining all elements in the original arrays.
     */
    private static function _array_flatten ( $arrs ) {
        $flat = [];
        foreach( $arrs as $arr ) {
            foreach( $arr as $ele ) {
                array_push( $flat, $ele );
            }
        }
        return $flat;
    }

    /**
     * Return an associative array where each key is a distinct
     * value from $keys and each value is the number of occurrences
     * of the key in $keys.
     */
    private static function _bucket_count( $keys ) {
        $buckets = [];
        foreach( $keys as $key ) {
            $trimmed_key = trim( $key );
            if ( array_key_exists( $trimmed_key, $buckets ) ) {
                $buckets[$trimmed_key] += 1;
            } else {
                $buckets[$trimmed_key] = 1;
            }
        }
        return $buckets;
    }

    /**
     * Convert a list of post objects with post_id fields to
     * a list of tags associated with the posts, then flattened.
     */
    private static function _posts_to_tags( $posts ) {
        $popular_post_ids = array_map( function ($post) {
            return $post->post_id;
        }, $posts);

        $popular_post_tags = array_map( function ($post_id) {
            return wp_get_post_tags( $post_id, array( 'fields' => 'names' ) );
        }, $popular_post_ids);

        return self::_array_flatten( $popular_post_tags );
    }

    /**
     * Return the top $n keys in $tags based on their values
     */
    private static function _get_top_n_tags( $n, $tags ) {
        if ( count( $tags ) <= $n ) {
            return array_keys( $tags );
        }

        $top = [];
        while ( count( $top ) < $n ) {
            $cur_most_popular = null;
            $cur_max = 0;
            foreach( $tags as $tag => $count ) {
                if ( $count > $cur_max ) {
                    $cur_most_popular = $tag;
                    $cur_max = $count;
                }
            }

            array_push( $top, $cur_most_popular );
            unset( $tags[ $cur_most_popular ] );
        }

        return $top;
    }

    /**
     * Return the top NUM_TRENDING_TAGS tags being used on the most popular
     * 10 articles being read in the last few days.
     */
    public static function get_trending_tags() {
        $cur_file_path = plugin_dir_path( __FILE__ );
        include_once( $cur_file_path . "../includes/sun-backend-constants.php" );

        $data = self::_get_jetpack_post_data();

        // unsuccessful request, no trending results
        if ( !is_array( $data ) ) return [ "no trending tags" ];

        // convert array of objects into 2d of day popular posts array
        $posts_by_day = array_map( function ($ele) {
            return $ele->postviews;
        }, $data );

        $popular_posts = self::_array_flatten( $posts_by_day );
        $popular_tags = self::_posts_to_tags( $popular_posts );
        $tag_counts = self::_bucket_count( $popular_tags );
        $top_tags = self::_get_top_n_tags( NUM_TRENDING_TAGS, $tag_counts );

        return $top_tags;
    }
}

?>
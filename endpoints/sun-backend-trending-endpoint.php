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
     * Return the top NUM_TRENDING_TAGS tags being used on the most popular
     * 10 articles being read in the last few days.
     */
    public static function get_trending_tags() {
        $cur_file_path = plugin_dir_path( __FILE__ );
        include_once( $cur_file_path . "../includes/sun-backend-config.php" );
        include_once( $cur_file_path . "../includes/sun-backend-constants.php" );

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
        $data = json_decode( wp_remote_retrieve_body( $response ) );

        // unsuccessful request, no trending results
        if ( ! is_array( $data ) ) return array( "no trending tags" );

        // convert array of objects into 2d of day popular posts array
        $data = array_map( function ($ele) {
            return $ele->postviews;
        }, $data );

        // flatten array and count popular tags
        $popular_tags = [];
        foreach( $data as $day_data ) {
            foreach( $day_data as $pop_post ) {
                $post_tags = wp_get_post_tags( $pop_post->post_id, array( 'fields' => 'names' ) );
                foreach( $post_tags as $tag ) {
                    if ( array_key_exists( $tag, $popular_tags ) ) {
                        $popular_tags[$tag] += 1;
                    } else {
                        $popular_tags[$tag] = 1;
                    }
                }
            }
        }

        $num_trending_tags = min( count( $popular_tags ), NUM_TRENDING_TAGS );

        // TODO: optimize this to not be as suspect
        $sorted_top_tags = array();
        while ( count( $sorted_top_tags ) < $num_trending_tags ) {
            $cur_most_popular = null;
            $cur_max = 0;
            foreach( $popular_tags as $tag => $count ) {
                if ( $count > $cur_max ) {
                    $cur_most_popular = $tag;
                    $cur_max = $count;
                }
            }

            array_push( $sorted_top_tags, $cur_most_popular );
            unset( $popular_tags[$cur_most_popular] );
        }

        return $sorted_top_tags;
    }
}

?>
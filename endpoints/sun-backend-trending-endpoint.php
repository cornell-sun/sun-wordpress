<?php
/**
 * Handles the /trending endpoint, which returns the most popular tags being used
 * in the last few days.
 */

include_once( '../includes/sun-backend-constants.php' );
include_once( '../includes/sun-backend-config.php' );

class SunAppExtension_TrendingEndpoint{

    /**
     * Return the top NUM_TRENDING_TAGS tags being used on the most popular
     * 10 articles being read in the last few days.
     */
    public static function get_trending_tags() {
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
}

?>
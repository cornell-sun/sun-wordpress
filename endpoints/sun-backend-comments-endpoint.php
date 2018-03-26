<?php
/**
 * Handles the /comments endpoint, which handles retrieving the associated comments
 * for any given article ID.
 */
class SunAppExtension_CommentsEndpoint {

    /**
     * Set up and register the /comments endpoint with the rest_api_init function.
     */
    public static function init() {
        $cur_file_path = plugin_dir_path( __FILE__ );
        include_once( $cur_file_path . "../includes/sun-backend-constants.php" );

        register_rest_route( PLUGIN_ENDPOINT . '/' . PRODUCTION_VERSION, '/comments/(?P<post_id>\d+)', array(
            'methods' => 'GET',
            'callback' => 'SunAppExtension_CommentsEndpoint::get_post_comments',
        ));
    }

    /**
     * Return the URL endpoint request string for requesting the comments associated
     * with a url (denoted $post_url).
     */
    private static function _create_url_comments_request_url( $post_url ) {
        return '/?fields=og_object{comments.limit(100)}&id=' . $post_url;
    }

    /**
     * Return the URL endpoint request string for requesting the user profile
     * associated with the $user_id.
     */
    private static function _create_user_picture_request_url( $user_id ) {
        return "/" . $user_id . "/picture?redirect=false";
    }

    /**
     * Return the URL endpoint request string for requesting the replies for
     * a comment with id $comment_id.
     */
    private static function _create_reply_comment_request_url( $comment_id ) {
        return "/" . $comment_id . "/comments";
    }

    /**
     * Return a batched response for an array of $requests using the instantiated
     * Facebook API object $fb.
     * Returns: 2-element array. If an error is thrown, then return the error code and
     * corresponding message. If the request is successful, return the response with
     * an error code of 0.
     *      - [ ( FacebookErrorCode | 0 ), ( FacebookErrorMessage | Response ) ]
     *      - Ex. good call: [ 0, $responses ] | bad call: [ 190, "Invalid OAuth 2.0 access token" ]
     */
    private static function _get_batched_response( $fb, $requests ) {
        try {
            $responses = $fb->sendBatchRequest( $requests );
        } catch ( Facebook\Exceptions\FacebookResponseException $e ) {
            return [ $e->getCode(), "Graph returned an error " . $e->getMessage() ];
        } catch ( Facebook\Exceptions\FacebookSDKException $e ) {
            return [ $e->getCode(), "Facebook SDK returned an error " . $e->getMessage() ];
        }
        return [ 0, $responses ];
    }

    /**
     * Return a list of comment JSON objects associated with the post denoted
     * by the ID passed into the request.
     */
    public static function get_post_comments( $request ) {
        include_once( __DIR__ . "/../includes/Facebook/autoload.php" );
        include_once( __DIR__ . "/../includes/sun-backend-config.php" );

        // temporarily wrap everything in a try-catch just to prevent complete crashes
        try {
            // set up facebook API object to make requests with
            $fb = new \Facebook\Facebook([
                'app_id' => FACEBOOK_APP_ID,
                'app_secret' => FACEBOOK_SECRET_ID,
                'default_graph_version' => 'v2.6',
                'default_access_token' => FACEBOOK_APP_ID . "|" . FACEBOOK_SECRET_ID, // optional
            ]);

            $post_id = $request->get_param( "post_id" );
            $post_full_link = get_permalink( $post_id );
            $post_perma = str_replace( home_url(), "", $post_full_link );
            $post_url = "http://cornellsun.com" . $post_perma;

            $og_object_endpoint = self::_create_url_comments_request_url( $post_url );

            try {
                $urlCommentsResponse = $fb->get( $og_object_endpoint );
            } catch ( Facebook\Exceptions\FacebookResponseException $e ) {
                return "Graph returned an error " . $e->getMessage();
            } catch ( Facebook\Exceptions\FacebookSDKException $e ) {
                return "Facebook SDK returned an error " . $e->getMessage();
            }

            // TODO: Account for the pagination case
            // TODO: Try and find a way to do this efficiently
            $graphNodeArr = $urlCommentsResponse->getGraphNode()->asArray();
            $ogObject = $graphNodeArr["og_object"];
            $ogObjectComments = $ogObject["comments"];

            // if no comments, no need to batch request anything
            if ( !is_array( $ogObjectComments ) || count( $ogObjectComments ) < 1 ) {
                return [];
            }

            // batch requests for all the users commenting and all the comment objects
            $profile_requests = [];
            $replies_requests = [];
            foreach ( $ogObjectComments as $comment_obj ) {
                $user_id = $comment_obj["from"]["id"];
                $comment_id = $comment_obj["id"];
                $user_request = $fb->request( "GET", self::_create_user_picture_request_url( $user_id ) );
                $reply_request = $fb->request( "GET", self::_create_reply_comment_request_url( $comment_id ) );

                $profile_requests[ $user_id ] = $user_request;
                $replies_requests[ $comment_id ] = $reply_request;
            }

            // returns [ error code, response / error msg ]
            $profile_response = self::_get_batched_response( $fb, $profile_requests );
            $replies_response = self::_get_batched_response( $fb, $replies_requests );

            // if any error code, return the error message
            if ( $profile_response[0] !== 0 ) {
                return $profile_response[1];
            }
            if ( $replies_response[0] !== 0 ) {
                return $replies_response[1];
            }

            // no error code -> ditch the error code and flatten array
            $profile_response = $profile_response[1];
            $replies_response = $replies_response[1];

            // convert to dictionaries of arrays rather than response objects
            $profile_response = array_map( function ( $prof ) {
                $prof_node = $prof->getGraphNode()->asArray();
                if ( $prof_node === null ) return false;
                return $prof_node;
            }, $profile_response->getResponses() );

            $replies_response = array_map( function ( $reply ) {
                $reply_node = $reply->getGraphEdge()->asArray();
                if ( $reply_node === null ) return false;
                return $reply_node;
            }, $replies_response->getResponses() );

            // create final comments response using batched information
            $full_comments_response = [];
            foreach ( $ogObjectComments as $comment ) {
                $comment_id = $comment["id"];
                $time_dict = $comment["created_time"];
                $user_dict = $comment["from"];
                $comment_msg = $comment["message"];

                $user_dict["profile_picture"] = $profile_response[ $user_dict["id"] ];

                array_push( $full_comments_response, [
                    "id" => $comment_id,
                    "created_time" => $time_dict,
                    "from" => $user_dict,
                    "message" => $comment_msg,
                    "replies" => $replies_response[ $comment_id ]
                ] );
            }

            return $full_comments_response;
        } catch ( Exception $e ) {
            return $e->getMessage();
        }
    }
}

?>
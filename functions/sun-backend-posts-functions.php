<?php
/**
 * Contains all static functions pertaining to adding information about posts
 * to the response.
 */

class SunAppExtension_PostsFunctions {

    /**
     * Return a dictionary aggregating all of the extra information about a post
     * used in the app. Currently includes:
     *      ~ author_dict - dictionary of information about the author of the post
     *      ~ featured_media_url_string - dictionary of information about the post featured image
     *      ~ featured_media_caption - string caption for the featured image caption
     *      ~ featured_media_credit - string name of the photographer who took the featured image
     *      ~ category_strings - list of category names this post is a part of
     *      ~ primary_category - string of the main category this post is a part of
     *      ~ tag_strings - list of tag names this post is tagged with
     *      ~ post_type_enum - string denoting which type of post this is { article, photoGallery, etc. }
     *      ~ post_attachments_meta - list of image dictionaries for all the images in a post.
     *                                guaranteed only for photoGallery post_type_enum
     *      ~ post_content_no_srcset - rendered content string with all srcset attributes stripped
     */
    public static function generate_post_entry( $post_id ) {
        $post = get_post( $post_id );
        $date_val = str_replace(" ", "T", $post->post_date );
        $title_val = array( "rendered" => $post->post_title );
        $rendered_content = stripslashes( apply_filters( 'the_content', $post->post_content ) );
        $content_val = array( "rendered" => $rendered_content );
        $excerpt_val = array( "rendered" => get_the_excerpt( $post_id ) );
        $link_val = $post->guid;
        $author_val = (int) $post->post_author;

        return array(
            'id'                        => $post_id,
            'date'                      => $date_val,
            'title'                     => $title_val,
            'content'                   => $content_val,
            'excerpt'                   => $excerpt_val,
            'link'                      => $link_val,
            'author'                    => $author_val,
            'author_dict'               => self::get_author_dict( $post_id ),
            'featured_media_url_string' => self::get_featured_media_urls( $post_id ),
            'featured_media_caption'    => self::get_featured_media_caption( $post_id ),
            'featured_media_credit'     => self::get_featured_media_credits( $post_id ),
            'category_strings'          => self::get_category_names( $post_id ),
            'primary_category'          => self::get_primary_category( $post_id ),
            'tag_strings'               => self::get_tag_names( $post_id ),
            'post_type_enum'            => self::get_post_type_enum( $post_id ),
            'post_attachments_meta'     => self::get_post_image_attachments( $post_id ),
            'post_content_no_srcset'    => self::get_content_no_srcset( $post_id )
        );

    }

    /**
     * Return the information about the author for a given post with
     * id = $post_id. This includes id, name, url for the avatar, bio snippet
     * and a link to their author page.
     */
    public static function get_author_dict( $post_id ) {
        $post = get_post( $post_id );
        $post_meta = get_post_meta( $post_id );
        $author_names = $post_meta["largo_byline_text"];

        if ( empty( $author_names ) ) {
            // no byline author text, default to normal author
            $author_id = (int)$post->post_author;
            $user_obj = get_user_by( "id", $author_id );
//            $user_meta = get_user_meta( $author_id );
            $user = $user_obj->display_name;

//            $user = array(
//                "id"            => $author_id,
//                "name"          => $user_obj->display_name,
//                "avatar_url"    => get_avatar_url($author_id),
//                "bio"           => $user_meta["description"][0],
//                "link"          => get_author_posts_url($author_id),
//            );

            return [ [ "name" => $user ] ];
        }

        $users = array();
        foreach ( $author_names as $name ) {
            // reverse search for user by display name
            $user_query = new WP_User_Query( array(
                'search' => $name,
                'search_columns' => array( 'display_name' ),
            ));

            if ( !empty( $user_query->results ) ) {
                // query found user entry, add it to the array
//                $first_res = $user_query->results[0];
//                $user_id = $first_res->data->ID;
//                $user_meta = get_user_meta( $user_id );
                $name_dict = [ "name" => $name ];
//                $user_dict = array(
//                    "id"            => $user_id,
//                    "name"          => $name,
//                    "avatar_url"    => get_avatar_url( $user_id ),
//                    "bio"           => $user_meta["description"][0],
//                    "link"          => get_author_posts_url( $user_id ),
//                );

                array_push( $users, $name_dict );
            } else {
                // empty results, just return the name for now
                $name_dict = [ "name" => $name ];
                array_push( $users, $name_dict );
            }
        }
        return $users;
    }

    /**
    * Return the featured image for a given post with id = $post_id
    * in 3 different sizes: thumbnail, medium_large, and full. Gives back
    * the url, width, and height for each image size.
    */
    public static function get_featured_media_urls( $post_id ) {
        $featured_media_id = (int)get_post_thumbnail_id( $post_id );
        $media_med_lg = wp_get_attachment_image_src(
            $featured_media_id,
            "medium_large"
        );

        $media_thumb = wp_get_attachment_image_src(
            $featured_media_id,
            "thumbnail"
        );

        $media_full = wp_get_attachment_image_src(
            $featured_media_id,
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

    /**
     * Return the string caption for the featured media for a given post with
     * id = $post_id.
     */
    public static function get_featured_media_caption( $post_id ) {
        $featured_media_id = (int)get_post_thumbnail_id( $post_id );
        $image = get_post( $featured_media_id );
        return $image->post_excerpt;
    }

    /**
     * Return a list of string names of the categories a given post with
     * id $post_id is tagged with.
     */
    public static function get_category_names( $post_id ) {
        $categories = get_the_category( $post_id );

        // no categories for given post
        if ( !$categories ) { return array(); }

        return array_map(
            function ( $category ) { return $category->name; },
            $categories
        );
    }

    /**
     * Return the primary category this post with id $post_id is
     * associated with. We return the category with the smallest ID
     * and return News if no categories are attributed.
     */
    public static function get_primary_category( $post_id ) {
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

    /**
     * Return the corresponding array of tag strings for a given
     * post with id $post_id.
     */
    public static function get_tag_names( $post_id ) {
        $tags = get_the_tags( $post_id );

        // no tags for associated post
        if ( !$tags ) { return array(); }

        return array_map(
            function ( $tag ) { return $tag->name; },
            $tags
        );
    }

    /**
     * Returns whether a given post with id $post_id is a photoGallery
     * (this week in photos) or simply another article to those requesting
     * a post.
     */
    public static function get_post_type_enum( $post_id ) {
        $post = get_post( $post_id );
        $title = $post->post_title;

        if ( strpos( strtolower( $title ), "this week in photos" ) !== false) {
            // photo gallery
            return "photoGallery";
        } else {
            // normal article for now
            return "article";
        }
    }

    /**
     * Return the URLs, captions, and other necessary metadata for all the image
     * attachments associated with a single post with id $post_id. Guaranteed to
     * be populated only on photoGallery posts (THIS WEEK IN PHOTOS).
     */
    public static function get_post_image_attachments( $post_id ) {
        $post_attachments = get_attached_media( "image", $post_id );

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

        $post_content = get_the_content( $post_id );
        $rendered_content = stripslashes( apply_filters( 'the_content', $post_content ) );
        $used_images = array();
        preg_match_all( "/<img .* src=(.*)\?re.* alt=.*>/", $rendered_content, $used_images );
        $used_images = array_map(
            function ($ele) { return end( explode( "/", $ele ) ); },
            $used_images[1]
        );

        $result = array();
        foreach ($media_results as $media ) {
            if (in_array( $media["name"], $used_images ) ) {
                array_push( $result, $media );
            }
        }

        return $result;
    }

    /**
     * Remove all occurences of the attribute srcset from all images in the
     * given post with id $post_id.
     */
    public static function get_content_no_srcset( $post_id ) {
        $post_content = get_post_field( 'post_content', $post_id );
        $rendered_content = stripslashes( apply_filters( 'the_content', $post_content ) );
        $content_srcset_removed = preg_replace( "/srcset=\".*\"/", '', $rendered_content );

        return $content_srcset_removed;
    }

    /**
     * Return the string name of the photographer who took the featured image.
     * Null if credits are stored in the caption or no associated image.
     */
    public static function get_featured_media_credits( $post_id ) {
        $featured_media_id = (int)get_post_thumbnail_id( $post_id );
        $image_meta = get_post_meta( $featured_media_id );
        return $image_meta["_media_credit"][0];
    }

    /*
     * func createArticleContentType(content: String) -> [ArticleContentType] {
        var sections: [ArticleContentType] = []
        guard let doc: Document = try? SwiftSoup.parse(content) else { return sections }
        guard let elements = try? doc.getAllElements() else { return sections }

        for element in elements {
            if element.tag().toString() == "p" {
                guard let pItem = parsePTag(element: element) else { continue }
                sections.append(pItem)
            } else if element.tag().toString() == "img" {
                guard let imgItem = parseImg(element: element) else { continue }
                sections.append(imgItem)
            } else if element.tag().toString() == "aside" {
                guard let blockquote = parseAside(element: element) else { continue }
                sections.append(blockquote)
            }
        }
        return sections
    }

    func parsePTag(element: Element) -> ArticleContentType? {
        guard let text = try? element.text() else { return nil }
        guard let html = try? element.outerHtml() else { return nil }
        if element.hasClass("wp-media-credit") {
            return .imageCredit(text)
        } else if element.hasClass("wp-caption-text") {
            return .caption(text)
        } else {
            return .text(html.convertHtml())
        }
    }

    func parseImg(element: Element) -> ArticleContentType? {
        guard let src = try? element.select("img[src]") else { return nil }
        guard let srcUrl = try? src.attr("src").description else { return nil }
        cacheImage(imageLink: srcUrl) //cache the image
        return .image(srcUrl)
    }

    func parseAside(element: Element) -> ArticleContentType? {
        guard let text = try? element.text() else { return nil}
        if element.hasClass("module") && text != "" {
            return .blockquote(text.htmlToString)
        }
        return nil
    }*/

    /**
     * @param $post_id
     */
    public static function get_parsed_content_array($post_id ) {
        // Remove if unused later
        include_once( '../includes/simple_html_dom.php' );

        $post_content = get_post_field( 'post_content', $post_id );
        $html_content = stripslashes( apply_filters( 'the_content', $post_content ) );
        $dom = new DOMDocument();
        $dom->loadHTML($html_content);
        $parsed = [];

        foreach( $dom->childNodes as $node ) {
            if ( $node->nodeType == XML_ELEMENT_NODE ) {
                if ( $node->nodeName == 'p' ) {
                    // treating as text
                    array_push( $parsed, self::parse_p_tag( $node ) );
                } elseif ( $node->nodeName == 'img' ) {
                    // treating as inline image
                    array_push( $parsed, self::parse_img_tag( $node ) );
                } elseif ( $node->nodeName == 'aside' ) {
                    // treating as blockquote
                    array_push( $parsed, self::parse_aside_tag( $node ) );
                }
            }
        }
    }

    private static function parse_p_tag( $node ) {
        $node_content = $node->textContent;
        $classes = $node->attributes->getNamedItem('class');
        if ( str_pos( $classes, 'wp-media-credit' ) !== -1 ) {
            // credit
            return [
                'node_type' => 'credit',
                'node_content' => $node_content
            ];
        } elseif ( str_pos( $classes, 'wp-caption-text' ) !== -1 ) {
            // caption
            return [
                'node_type' => 'caption',
                'node_content' => $node_content
            ];
        }
        return null;
    }

    private static function parse_img_tag( $node ) {
        return null;
    }

    private static function parse_aside_tag( $node ) {
        return null;
    }
}

?>
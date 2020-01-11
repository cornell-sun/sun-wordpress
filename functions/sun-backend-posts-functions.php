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
		$post        = get_post( $post_id );
		$date_val    = str_replace( " ", "T", $post->post_date );
		$title_val   = $post->post_title;
		$excerpt_val = get_the_excerpt( $post_id );
		$link_val    = $post->guid;
		$author_val  = (int) $post->post_author;

		return array(
			'id'                        => $post_id,
			'date'                      => $date_val,
			'title'                     => $title_val,
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
			'post_attachments_meta'     => self::get_post_enum_metadata( $post_id ),
			'post_content_no_srcset'    => self::get_content_no_srcset( $post_id ),
			'suggested_article_ids'     => self::get_suggested_article_ids( $post_id ),
		);
	}

	/**
	 *  If any of the values in $array are empty strings, set them to null instead
	 */
	public static function set_empty_null( $arr ) {
		foreach ( $arr as $key => $val ) {
			if ( $val === "" ) {
				$arr[ $key ] = null;
			}
		}

		return $arr;
	}

	/**
	 * Return the information about the author for a given post with
	 * id = $post_id. This includes id, name, url for the avatar, bio snippet
	 * and a link to their author page.
	 */
	public static function get_author_dict( $post_id ) {
		$post         = get_post( $post_id );
		$post_meta    = get_post_meta( $post_id );
		$author_names = explode( ", ", $post_meta["largo_byline_text"][0] );

		// no byline author text, default to normal author
		if ( $author_names[0] === "" ) {
			$author_id = (int) $post->post_author;
			$user_obj  = get_user_by( "id", $author_id );
			$user_meta = get_user_meta($author_id);

			$user      = array(
				"id"         => $author_id,
				"name"       => $user_obj->display_name,
				"avatar_url" => get_avatar_url( $author_id ),
				"bio"        => $user_meta["description"][0],
				"link"       => get_author_posts_url( $author_id ),
				"twitter"    => $user_meta["twitter"][0],
				"linkedin"   => $user_meta["linkedin"][0],
				"email"      => $user_obj->data->user_email,
			);
			$user      = self::set_empty_null( $user );

			return [ $user ];
		}

		$users = array();
		foreach ( $author_names as $name ) {

			$query_args = array(
				'search'         => '*' . $name . '*',
				'search_columns' => array( 'display_name' ),
			);

			// reverse search for user by display name
			$user_query = new WP_User_Query( $query_args );
			$res        = $user_query->get_results();

			if ( ! empty( $res ) ) {
				// query found user entry, add it to the array
				$user      = $res[0];
				$user_id   = $user->data->ID;
				$user_meta = get_user_meta( $user_id );

				$user = array(
					"id"         => $user_id,
					"name"       => $user->display_name,
					"avatar_url" => get_avatar_url( $user_id ),
					"bio"        => $user_meta["description"][0],
					"link"       => get_author_posts_url( $user_id ),
					"twitter"    => $user_meta["twitter"][0],
					"linkedin"   => $user_meta["linkedin"][0],
					"email"      => $user->data->user_email,
				);
				//If any of the values are empty strings, set them to null instead
				$user = self::set_empty_null( $user );
				array_push( $users, $user );
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
		$featured_media_id              = (int) get_post_thumbnail_id( $post_id );
		$media_med_lg                   = wp_get_attachment_image_src(
			$featured_media_id,
			"medium_large"
		);
		$media_thumb                    = wp_get_attachment_image_src(
			$featured_media_id,
			"thumbnail"
		);
		$media_full                     = wp_get_attachment_image_src(
			$featured_media_id,
			"full"
		);
		$featured_media                 = array();
		$featured_media["medium_large"] = array(
			"url"    => $media_med_lg[0],
			"width"  => $media_med_lg[1],
			"height" => $media_med_lg[2],
		);
		$featured_media["thumbnail"]    = array(
			"url"    => $media_thumb[0],
			"width"  => $media_thumb[1],
			"height" => $media_thumb[2],
		);
		$featured_media["full"]         = array(
			"url"    => $media_full[0],
			"width"  => $media_full[1],
			"height" => $media_full[2],
		);

		return $featured_media;
	}

	/**
	 * Return the string caption for the featured media for a given post with
	 * id = $post_id.
	 */
	public static function get_featured_media_caption( $post_id ) {
		$featured_media_id = (int) get_post_thumbnail_id( $post_id );
		$image             = get_post( $featured_media_id );

		return $image->post_excerpt;
	}

	/**
	 * Return a list of string names of the categories a given post with
	 * id $post_id is tagged with.
	 */
	public static function get_category_names( $post_id ) {
		$categories = get_the_category( $post_id );
		// no categories for given post
		if ( ! $categories ) {
			return array();
		}

		return array_map(
			function ( $category ) {
				return $category->name;
			},
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
		if ( empty( $categories ) || ! $categories ) {
			// no categories, return News = 1
			return "News";
		}
		// linear search for minimum category id
		$min_category = $categories[0];
		foreach ( $categories as $category ) {
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
		if ( ! $tags ) {
			return array();
		}

		return array_map(
			function ( $tag ) {
				return $tag->name;
			},
			$tags
		);
	}

	/**
	 * returns whether a given post with id $post_id is a photoGallery or video
	 * or simply another article based on the categories associated with the post
	 *
	 */
	public static function get_post_type_enum( $post_id ) {
		$category_names = self::get_category_names( $post_id );
		if ( in_array( "Video", $category_names ) ) {
			return "video";
		} else if ( in_array( "Photo Gallery", $category_names ) ) {
			return "photoGallery";
		} else {
			return "article";
		}
	}

	/**
	 * Returns the URLs, captions, and other necessary metadata for image attachments or
	 * video, or slideshows, or articles based on the $type_enum of a $post_id
	 */
	public static function get_post_enum_metadata( $post_id ) {
		$type_enum = self::get_post_type_enum( $post_id );
		if ( strcmp( $type_enum, "photoGallery" ) == 0 ) {
			return self::get_post_image_attachments( $post_id );
		} elseif ( strcmp( $type_enum, "video" ) == 0 ) {
			return self::get_post_video_attachments( $post_id );
		} else {
			return [];
		}
	}

	/**
	 * Return the URLs, captions, and other necessary metadata for all the video
	 * attachments associated with a single post with id $post_id. Use regex to match
	 * for iframe or video tags in the content and look for source of the video
	 */
	public static function get_post_video_attachments( $post_id ) {
		//Gets raw html content
		$post_content = self::get_content_no_srcset( $post_id );
		//Filter out any extraneous characters
		$post_content = preg_replace( "/( )|(')|(\\\")/", "", $post_content );
		$used_video   = array();
		//Regex for getting the video URL from $post_content
		preg_match_all(
			"/<iframe.*src=.*((http|https):\/\/www.youtube.com\/embed\/[A-Za-z\d\-\_]{11}).*<\/iframe>/",
			$post_content,
			$used_video
		);
		$video = $used_video[1];
		// builds a media object with the url and all other fields null
		$media_obj = array(
			"id"          => null,
			"name"        => null,
			"caption"     => null,
			"media_type"  => null,
			"author_name" => null,
			"url"         => $video[0],
		);

		return [ $media_obj ];
	}

	/**
	 * Return the URLs, captions, and other necessary metadata for all the image
	 * attachments associated with a single post with id $post_id.
	 */
	public static function get_post_image_attachments( $post_id ) {
		$post_attachments = get_attached_media( "image", $post_id );
		$media_results    = array();
		/* Create an array of $media_obj which contain a bunch of metadata
		 * associated with a post
		 */
		foreach ( $post_attachments as $attachment ) {
			$media_id   = $attachment->ID;
			$media_meta = wp_get_attachment_metadata( $media_id );
			$author_id  = $attachment->post_author;
			$media_obj  = array(
				"id"          => $media_id,
				"name"        => end( explode( "/", $media_meta["file"] ) ),
				"caption"     => $attachment->post_excerpt,
				"media_type"  => $attachment->post_mime_type,
				"author_name" => get_the_author_meta( 'display_name', $author_id ),
				"url"         => wp_get_attachment_url( $media_id, 'full' ),
			);
			array_push( $media_results, $media_obj );
		}
		$post_content     = get_the_content( $post_id );
		$rendered_content = stripslashes( apply_filters( 'the_content', $post_content ) );
		$used_images      = array();
		/*
		 * Looks for images used in the specific post by looking at the content
		 * and matching for HTML <img> tags
		 */
		preg_match_all( "/<img .* src=(.*)\?re.* alt=.*>/", $rendered_content, $used_images );
		$used_images = array_map(
			function ( $ele ) {
				return end( explode( "/", $ele ) );
			},
			$used_images[1]
		);
		/*
		 * Builds a new array which consists of the metadata for actual images
		 * used in the post by checking
		 */
		$result = array();
		foreach ( $media_results as $media ) {
			if ( in_array( $media["name"], $used_images ) ) {
				array_push( $result, $media );
			}
		}

		return $result;
	}

	/**
	 * Remove all occurrences of the attribute srcset from all images in the
	 * given post with id $post_id.
	 */
	public static function get_content_no_srcset( $post_id ) {
		$post_content     = get_post_field( 'post_content', $post_id );
		$rendered_content = stripslashes( apply_filters( 'the_content', $post_content ) );
		$dom              = new DOMDocument();
		$rendered_content = mb_convert_encoding( $rendered_content, 'HTML-ENTITIES', 'UTF-8' );
		$dom->loadHTML( $rendered_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		$images = $dom->getElementsByTagName( 'img' );
		foreach ( $images as $image ) {
			$image->removeAttribute( 'srcset' );
		}

		return mb_convert_encoding( $dom->saveHTML(), 'UTF-8', 'HTML-ENTITIES' );
	}

	/**
	 * Return the string name of the photographer who took the featured image.
	 * Null if credits are stored in the caption or no associated image.
	 */
	public static function get_featured_media_credits( $post_id ) {
		$featured_media_id = (int) get_post_thumbnail_id( $post_id );
		$image_meta        = get_post_meta( $featured_media_id );

		return $image_meta["_media_credit"][0];
	}

	/**
	 * Returns two mini post dictionaries that are suggested reads given a post with id $post_id.
	 * The returned dictionaries contain the posts's unique ID, its title, its author dictionary,
	 * and its featured media dictionary.
	 */
	public static function get_suggested_article_ids( $post_id ) {
		// Docs: http://largo.readthedocs.io/api/inc/related-content.html?highlight=largo_related
		$related_arts = new Largo_Related( NUM_RELATED_ARTICLES, $post_id );
		// turn related post ids into desired dictionaries
		$articles = array_map( function ( $post_id ) {
			$post = get_post( $post_id );

			return [
				"post_id"        => $post_id,
				"title"          => $post->post_title,
				"author_dict"    => self::get_author_dict( $post_id ),
				"featured_media" => self::get_featured_media_urls( $post_id ),
			];
		}, $related_arts->ids() );

		return array_values(
			array_filter( $articles, function ( $article ) {
				return is_numeric( $article["post_id"] );
			} )
		);
	}
}

# Sun-Wordpress Overview
Wordpress plugin that extends the current core Wordpress functionality for the Cornell Sun iOS app

# Added Post Information 
Add additional information to the post object to prevent additional Wordpress API call. Please note that all extra fields are located within the `post_info_dict` dictionary in the response.

## author_dict

List of author dictionaries
* id: int -- the unique identifier for an author in the Wordpress DB
* name: String -- the display name of the author on the website
* avatar_url: String -- the URL to the author's avatar image
* bio: String -- the author's bio snippet
* link: String -- the URL to the articles posted by a given author.

Note: If there is not a corresponding author object for the given name, only the name will be returned.

Example output:
``` javascript
"author_dict": [
    {
        "id": 746,
        "name": "BreAnne Fleer",
        "avatar_url": "http://1.gravatar.com/avatar/1e4d0b9587d009b746fd30cf32729214?s=96&d=mm&r=g",
        "bio": "BreAnne Fleer is a member of the Class of 2020 in the College of Arts and Sciences. She is a staff writer for the News department and can be reached at bfleer@cornellsun.com.",
        "link": "http://cornellsun.com/author/breannefleer/"
    }
]
```

## featured_media_url_string

Dictionary of media dictionaries for three of the featured media sizes: medium_large, thumbnail, and full

Each media dictionary has the following information:
* url: String -- the URL to the image at this size
* width: int -- the width of the image at this size
* height: int -- the height of the image at this size

Example output:
``` javascript
"featured_media_url_string": {
    "medium_large": {
        "url": "http://cornellsun.com/wp-content/uploads/2017/11/BINGHAMTON-14-768x511.jpg",
        "width": 768,
        "height": 511
     },
     "thumbnail": {
         "url": "http://i1.wp.com/cornellsun.com/wp-content/uploads/2017/11/BINGHAMTON-14.jpg?resize=140%2C140",
         "width": 140,
         "height": 140
     },
     "full": {
         "url": "http://i1.wp.com/cornellsun.com/wp-content/uploads/2017/11/BINGHAMTON-14.jpg?fit=1170%2C779",
         "width": 1170,
         "height": 779
     }
}
```


## category_strings

List of strings denoting the categories an article pertains to.

Example output:
``` javascript
"category_strings": [
    "Full Court Press",
    "Sports"
]
```

## primary_category

String representing the lowest ID (most general) category string the post is a part of. 

Example output:
``` javascript
"primary_category": "Sports"
```

## tag_strings

List of strings denoting the tags this article has.

Example output:
``` javascript
"tag_strings": [
    "Men's Basketball"
]
```

## post_type_enum

A string denoting which controller the iOS app should use to display this article. As of right now, it could only be:

* "article" -- a normal article that presents a hero image and text in a detail view controller
* "photoGallery" -- a post consisting of a series of images to be presented with captions

Example output:
``` javascript
"post_type_enum": "article"
```

## post_attachments_meta

A list of attachment dictionaries containing all the information necessary to display any associated attachments, images, or media for a post.

Each attachment dictionary has the following attributes:
* id: int -- the unique identifier for this media object in the Wordpress database
* caption: String -- the caption associated with this image on the post
* media_type: String -- the type of this media object. Ex. "image/png", "image/jpg", etc.
* author_name: String -- the name of the user who uploaded this image or media to the post
* full: String -- the URL pointing to the full-sized image or media to be displayed

Example output:
``` javascript
"post_attachments_meta": [
    {
        "id": 2347113,
        "caption": "Facing the threat of losing a 20-point lead, Joel Davis (#23) was able to come up big in the second half for his team and stop the bleeding.",
        "media_type": "image/jpeg",
        "author_name": "Zachary Silver",
        "full": "http://cornellsun.com/wp-content/uploads/2017/11/BINGHAMTON-14.jpg"
    }
]
```

## post_content_no_srcset

Exactly equivalent to the rendered content in the normal API response, but with all instances of `srcset='...'` removed.

Example output:
``` javascript
"post_content_no_srcset": "<p>Content here with image however no extra src set attribute <img src='google.com' /></p>"
```

# `/trending` Endpoint

## Default response

Returns top NUM_TRENDING_TAGS (currently 7) most popular tags as strings to be displayed in the search trending topics from the last 2 days.

Example output:
``` javascript
[
    "Top trending",
    "Second most trending",
    "Cornell University",
    "Martha Pollack",
    "Okenshields Playlist",
    "161 Faces",
    "Sex on Thursdays"
]
```

# `/featured` Endpoint

## Default response

Returns the extended post object for the first post displayed prominently on the home page of cornellsun.com.

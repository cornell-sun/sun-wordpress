# Sun-Wordpress Overview
Wordpress plugin that extends the current core Wordpress functionality for the Cornell Sun iOS app

# Added Post Information 
Add additional information to the post object to prevent additional Wordpress API call. Please note that all extra fields are located within the `post_info_dict` dictionary in the response.

## author_dict

List of author dictionaries
* name: String -- the display name of the author on the website

Example output:
``` javascript
"author_dict": [
    {
        "name": "BreAnne Fleer"
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

## featured_media_caption

String caption for a given post's featured image. Empty string if no associated caption or no featured image.
``` javascript
"featured_media_caption": "Granting the popular bar and pizza joint landmark status would prevent significant exterior modifications."
```

## featured_media_credit

String name of the photographer credited with taking a post's featured image. 
**Note:** Null if credit is stored in the caption field or no image associated with the given post.

``` javascript
"featured_media_credit": "Cameron Pollack / Sun Photography Editor"
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

# `/comments` Endpoint

## `/comments/{post_id}`

Returns a list of comment objects for the post with ID `post_id`. `post_id` must be a number corresponding to a post's unique Wordpress identifier. If there are no comments for a given post, then an empty list `[]` is returned. If there is an error fetching the comments from Facebook, then the error will be returned. If there is an error going through the responses from the Facebook Graph API, then the exception is returned to the requester.

### Response comment object

* **id**: *String* -- the unique Facebook Graph API identifier of the comment node.
* **created_time**: *Object* 
    * **date**: *String* -- the date and time the comment was posted on the article
    * **timezone_type**: *int* -- (tbh not entirely sure what this is and documentation isn't very helpful)
    * **timezone**: *String* -- how far off of GMT the posted timezone is
* **from**: *Object*
    * **name**: *String* -- the name of the user that posted the comment
    * **id**: *String* -- the Facebook Graph API's unique identifier for the user
    * **profile_picture**: *Object*
        * **height**: *int* -- height of the image
        * **is_silhouette**: *boolean* -- true if the icon is the default Facebook user image
        * **url**: *String* -- url to the user's profile picture image
        * **width**: *int* -- width of the image
* **message**: *String* -- the string comment the user wrote for the given article
* **replies**: *Comment List* -- a list of additional comment objects that directly reply to this comment 

### Example response

``` javascript
[
    {
        "id": "1558607794255395_1559620154154159",
        "created_time": {
            "date": "2018-02-09 01:45:36.000000",
            "timezone_type": 1,
            "timezone": "+00:00"
        },
        "from": {
            "name": "Vivian Li",
            "id": "10213617649489868",
            "profile_picture": {
                "height": 50,
                "is_silhouette": false,
                "url": "https://lookaside.facebook.com/platform/profilepic/?asid=10213617649489868&height=50&width=50&ext=1523574773&hash=AeTWdUvznEbXzdgO",
                "width": 50
            }
        },
        "message": "Hi Kelly,\n\nWhile I'm not in a sorority myself, some of my best friends are part of Greek life, namely Asian Greek, and they're definitely not like the \"typical\" sorority girl that you described in your article. While I (and they) do believe that there are problems with the system, it's unfair to stereotype all of the members of the Greek community into just one image - just because a couple of sisters are in the same major or participate in the same activity, it does not mean that they are the same person. \n\nYou definitely presented an interesting idea in your article in rushing as an undercover journalist, but I had two issues with the process itself. First, it's unfair for you to base your judgments of the entire Greek community off of one rush event, especially one in which you didn't have a long period of time to get to know anyone in particular. If you had five minutes to talk to someone, what would you be able to say that would distinguish yourself? Second, while you claimed that you didn't go into the event with a bias, I disagree, based on the way you already had these preconceptions of the sisters - you mention this \"sorority girl style\" and the \"perky\" attitude. How can you expect to see anything different when you are just there to reinforce this sorority girl stereotype that you see? \n\nI don't love Greek life but I love some people in the Greek community and don't think that this article gives them an accurate representation.",
        "replies": []
    },
    {
        "id": "1558607794255395_1559546730828168",
        "created_time": {
            "date": "2018-02-08 23:54:45.000000",
            "timezone_type": 1,
            "timezone": "+00:00"
        },
        "from": {
            "name": "Kayla Fitzgerald",
            "id": "10212326984629967",
            "profile_picture": {
                "height": 50,
                "is_silhouette": false,
                "url": "https://lookaside.facebook.com/platform/profilepic/?asid=10212326984629967&height=50&width=50&ext=1523574773&hash=AeS4VSUrcLeNpjq5",
                "width": 50
            }
        },
        "message": "I appreciate this different approach to sororities and rush! I certainly did not and do not consider myself the “typical” sorority girl.  At Cornell, I found situations where it was far to easy to be surrounded by the same people in your classes or major.  Instead of meeting others, I would stick to who I knew in the classroom buildings I knew. Sorority life offered a different option to meet people in other majors and programs who were just as outgoing or gregarious as myself. \n\nThrough joining a sorority I simply found a like minded group of athletes, bookworms, partiers and couch potatoes — all colors of Cornellian. Whether Friday night was spent in Olin or at a party— I never felt pressure to be anything but what I wanted to be. The sorority brought my friends together and facilitated the friendship and I am grateful for that.\n\nMy experience is certainly different from others. I heard of tales of hazing and pledging horrors.  Luckily, I was never in that situation. The system does need improvements and I hope this discussion continues so all can share a similar experience to mine.",
        "replies": []
    }
]
```

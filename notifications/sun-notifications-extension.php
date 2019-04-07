<?php
/*
 * Plugin Name: iOS Notifications
 * Description: Allows the ability to send notifications to the Cornell Sun iOS app.
 * Author: Austin Astorga and Mike Fang
 * Version: 1.0
 */


function add_notification_style () {
    wp_register_style( 'sun-notification-style', plugins_url( 'style.css', __FILE__ ) );
    wp_enqueue_style( 'sun-notification-style' );
}
add_action( 'admin_enqueue_scripts', 'add_notification_style');

/**
 * Outputs the content of the meta box
 */
function notifications_meta_callback($post) {
    wp_nonce_field(basename(__FILE__), 'notifications_nonce');
    $notifications_stored_meta = get_post_meta($post->ID);
    include 'notifications-ui.php';
}

function notifications_custom_meta() {
    add_meta_box(
        'notification_meta', __('iOS Notifications', 'notification-textdomain'),
        'notifications_meta_callback', 'post', 'side', 'high'
    );
}

add_action('add_meta_boxes', 'notifications_custom_meta');

/**
 * Saves the notification meta input
 */
function notifications_meta_post($new_status, $old_status, $post) {
    $post_id = ($post->ID);
    if ('publish' === $new_status && 'publish' !== $old_status && $post->post_type === 'post') {
        // Checks save status
        $is_autosave = wp_is_post_autosave($post_id);
        $is_revision = wp_is_post_revision($post_id);
        $is_valid_nonce = (!isset($_POST['notifications_nonce']) &&
            wp_verify_nonce($_POST['notifications_nonce'], basename(__FILE__))) ? 'true' : 'false';

        // Exits script depending on save status
        if ($is_autosave || $is_revision || !$is_valid_nonce) {
            return;
        }

        update_post_meta($post_id, 'custom-title', $_POST['custom-title']);
        update_post_meta($post_id, 'custom-blurb', $_POST['custom-blurb']);
        update_post_meta($post_id, 'send-time', $_POST['send-time']);

        //Breaking news
        if (isset($_POST['checkbox-breaking-news'])) {
            update_post_meta($post_id, 'checkbox-breaking-news', 'yes');
        } else {
            update_post_meta($post_id, 'checkbox-breaking-news', 'no');
        }

        //Local news
        if (isset($_POST['checkbox-local-news'])) {
            update_post_meta($post_id, 'checkbox-local-news', 'yes');
        } else {
            update_post_meta($post_id, 'checkbox-local-news', 'no');
        }

        //Opinion
        if (isset($_POST['checkbox-opinion'])) {
            update_post_meta($post_id, 'checkbox-opinion', 'yes');
        } else {
            update_post_meta($post_id, 'checkbox-opinion', 'no');
        }

        //Sports
        if (isset($_POST['checkbox-sports'])) {
            update_post_meta($post_id, 'checkbox-sports', 'yes');
        } else {
            update_post_meta($post_id, 'checkbox-sports', 'no');
        }

        //Sunspots
        if (isset($_POST['checkbox-sunspots'])) {
            update_post_meta($post_id, 'checkbox-sunspots', 'yes');
        } else {
            update_post_meta($post_id, 'checkbox-sunspots', 'no');
        }

        //Multimedia
        if (isset($_POST['checkbox-multimedia'])) {
            update_post_meta($post_id, 'checkbox-multimedia', 'yes');
        } else {
            update_post_meta($post_id, 'checkbox-multimedia', 'no');
        }

        //Arts and Entertainment
        if (isset($_POST['checkbox-arts-entertainment'])) {
            update_post_meta($post_id, 'checkbox-arts-entertainment', 'yes');
        } else {
            update_post_meta($post_id, 'checkbox-arts-entertainment', 'no');
        }

        //Science
        if (isset($_POST['checkbox-science'])) {
            update_post_meta($post_id, 'checkbox-science', 'yes');
        } else {
            update_post_meta($post_id, 'checkbox-science', 'no');
        }

        //Dining
        if (isset($_POST['checkbox-dining'])) {
            update_post_meta($post_id, 'checkbox-dining', 'yes');
        } else {
            update_post_meta($post_id, 'checkbox-dining', 'no');
        }

        //Send option
        if (isset($_POST['checkbox-send-option'])) {
            update_post_meta($post_id, 'checkbox-send-option', 'yes');
        } else {
            update_post_meta($post_id, 'checkbox-send-option', 'no');
        }
    }
}

add_action('transition_post_status', 'notifications_meta_post', 10, 3);

/**
 * Takes the stored meta data in $meta and extracts the segments
 */
function get_included_segments($post) {
    $meta = get_post_meta($post->ID);
    $segments = [];
    foreach ($meta as $category => $value) {
        if ($value[0] === 'yes') {
            $stripped_category = str_replace('-', ' ', str_replace('checkbox-', '', $category));
            $stripped_category = ucwords($stripped_category);
            array_push($segments, $stripped_category);
        }
    }
    return $segments;
}

function get_blurb($post) {
    setup_postdata($post);
    $meta = get_post_meta($post->ID);
    if ($meta['custom-blurb'][0] === '') {
        $excerpt = get_the_excerpt();
        $excerpt = trim(
            preg_replace('/ +/', ' ',
                urldecode(
                    html_entity_decode(
                        strip_tags($excerpt)
                    )
                )
            )
        );
        return $excerpt;
    }
    return $meta['custom-blurb'][0];
}

function get_title($post) {
    $meta = get_post_meta($post->ID);
    if ($meta['custom-title'][0] === '') {
        return get_the_title($post);
    }
    return $meta['custom-title'][0];
}

function get_delivery_time_of_day($post) {
    $meta = get_post_meta($post->ID);
    if ($meta['send-time'][0] === '') {
        return '';
    }
    $time = explode(":", $meta['send-time'][0]);
    $hours = (int) $time[0];
    $suffix = "AM";
    if ($hours > 12) {
        $hours = $hours - 12;
        $suffix = "PM";
    }
    $new_time = strval($hours) . ":" . $time[1] . $suffix;
    return $new_time;
}

function get_send_option($post) {
    $meta = get_post_meta($post->ID);
    if ($meta['checkbox-send-option'][0] === 'no') {
        if ($meta['send-time'][0] !== '') {
            return 'timezone';
        }
        return 'last-active';
    }
    return '';
}

function onesignal_notification_send($new_status, $old_status, $post) {
    if ('publish' === $new_status && 'publish' !== $old_status && $post->post_type === 'post') {
        $body = new stdClass();
        $body->app_id = 'c7e28bf2-698c-4a07-b56c-f2077e43c1b4';
        $body->headings = array('en' => get_title($post));
        $body->contents = array('en' => get_blurb($post));
        $body->included_segments = get_included_segments($post);
        $body->data = array('id' => strval($post->ID));
        $body->delayed_option = get_send_option($post);
        $body->delivery_time_of_day = get_delivery_time_of_day($post);
        $bodyAsJson = json_encode($body);

        $response = wp_remote_post(
            "https://onesignal.com/api/v1/notifications", array(
                'method' => 'POST',
                'timeout' => '45',
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(
                    "Content-type" => "application/json; charset=utf-8",
                    "Authorization" => "Basic ZWFkMGYzYjMtNTY1ZS00YzQ2LThlNjktMzg1YzcyODA3ZGFh",
                ),
                'body' => $bodyAsJson,
            )
        );
    }
}
add_action('transition_post_status', 'onesignal_notification_send', 10, 3);
?>
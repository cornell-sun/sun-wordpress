<?php

/**
 * @package Onesignal-Extension
 * @version 1.0
 */
/*
Plugin Name: iOS Notifications
Description: Allows the ability to send notifications to the Cornell Sun iOS app.
Author: Austin Astorga
Version: 1.0
 */

add_action('load-post-new.php', 'notifications_setup');

/**
 * Sets up the notifications extension by adding the necessary actions
 * for the extension
 */
function notifications_setup()
{
    add_action('add_meta_boxes', 'notifications_custom_meta');
    add_action('save_post', 'notifications_meta_save');
}

function notifications_custom_meta()
{
    add_meta_box('notification_meta', __('iOS Notifications', 'notification-textdomain'), 'notifications_meta_callback', 'post', 'side', 'high');
}

/**
 * Outputs the content of the meta box
 */
function notifications_meta_callback( $post )
{
    write_log('CREATING NOTIFICATION MENU');
    wp_nonce_field(basename(__FILE__), 'notifications_nonce');
    $notifications_stored_meta = get_post_meta($post->ID);
    ?>

    <p>
    <label for="onesignal-post-class"><?php _e("Send notifications to the Cornell Sun iOS app.", 'example'); ?></label>
    <br />
    </p>

    <p>
    <label for="checkbox-breaking-news">
    <input type="checkbox" name="checkbox-breaking-news" id="checkbox-breaking-news" value="no"
        <?php if (isset($notifications_stored_meta['checkbox-breaking-news'])) checked($notifications_stored_meta['checkbox-breaking-news'][0], 'yes'); ?> />
        <?php _e('Breaking News', 'notification-textdomain') ?>
    </label>
    </p>

    <p>
        <label for="checkbox-local-news">
            <input type="checkbox" name="checkbox-local-news" id="checkbox-local-news" value="no"
                <?php 
                if (isset($notifications_stored_meta['checkbox-local-news'])) {
                    checked($notifications_stored_meta['checkbox-local-news'][0], 'yes'); 
                }?> 
                />
            <?php _e('Local News', 'notification-textdomain') ?>
        </label>
    </p>

    <p>
        <label for="checkbox-opinion">
            <input type="checkbox" name="checkbox-opinion" id="checkbox-opinion" value="no"
                <?php 
                if (isset($notifications_stored_meta['checkbox-opinion'])) {
                    checked($notifications_stored_meta['checkbox-opinion'][0], 'yes');
                }?> 
            />
            <?php _e('Opinion', 'notification-textdomain') ?>
        </label>
    </p>

    <p>
    <label for="checkbox-sports">
    <input type="checkbox" name="checkbox-sports" id="checkbox-sports" value="no"
        <?php if (isset($notifications_stored_meta['checkbox-sports'])) checked($notifications_stored_meta['checkbox-sports'][0], 'yes'); ?> />
        <?php _e('Sports', 'notification-textdomain') ?>
    </label>
    </p>

    <p>
    <label for="checkbox-sunspots">
    <input type="checkbox" name="checkbox-sunspots" id="checkbox-sunspots" value="no"
        <?php if (isset($notifications_stored_meta['checkbox-sunspots'])) checked($notifications_stored_meta['checkbox-sunspots'][0], 'yes'); ?> />
        <?php _e('Sunspots', 'notification-textdomain') ?>
    </label>
    </p>

    <p>
    <label for="checkbox-multimedia">
    <input type="checkbox" name="checkbox-multimedia" id="checkbox-multimedia" value="no"
        <?php if (isset($notifications_stored_meta['checkbox-multimedia'])) checked($notifications_stored_meta['checkbox-multimedia'][0], 'yes'); ?> />
        <?php _e('Multimedia', 'notification-textdomain') ?>
    </label>
    </p>

    <p>
    <label for="checkbox-arts-entertainment">
    <input type="checkbox" name="checkbox-arts-entertainment" id="checkbox-arts-entertainment" value="no"
        <?php if (isset($notifications_stored_meta['checkbox-arts-entertainment'])) checked($notifications_stored_meta['checkbox-arts-entertainment'][0], 'yes'); ?> />
        <?php _e('Arts and Entertainment', 'notification-textdomain') ?>
    </label>
    </p>

    <p>
    <label for="checkbox-science">
    <input type="checkbox" name="checkbox-science" id="checkbox-science" value="no"
        <?php if (isset($notifications_stored_meta['checkbox-science'])) checked($notifications_stored_meta['checkbox-science'][0], 'yes'); ?> />
        <?php _e('Science', 'notification-textdomain') ?>
    </label>
    </p>

    <p>
        <label for="checkbox-dining">
            <input type="checkbox" name="checkbox-dining" id="checkbox-dining" value="no"
                <?php 
                if (isset($notifications_stored_meta['checkbox-dining'])) { 
                    checked($notifications_stored_meta['checkbox-dining'][0], 'yes'); 
                }?> 
                />
            <?php _e('Dining', 'notification-textdomain') ?>
        </label>
    </p>

  <?php

}

/**
 * Saves the notification meta input
 */
function notifications_meta_save( $post_id )
{
    write_log('SAVING THE DATA');

    //Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = (isset($_POST['notifications_nonce']) &&
        wp_verify_nonce($_POST['notifications_nonce'], basename(__FILE__))) ? 'true' : 'false';

    // Exits script depending on save status
    if ($is_autosave || $is_revision || !$is_valid_nonce) {
        return;
    }

    write_log('SCRIPT DID NOT EXIT, NOW PROPERLY SAVING');

    //Breaking news
    if (isset($_POST['checkbox-breaking-news'])) {
        update_post_meta($post_id, 'checkbox-breaking-news', 'yes');
    } else {
        update_post_meta($post_id, 'checkbox-breaking-news', '');
    }

    //Local news
    if (isset($_POST['checkbox-local-news'])) {
        update_post_meta($post_id, 'checkbox-local-news', 'yes');
    } else {
        update_post_meta($post_id, 'checkbox-local-news', '');
    }

    //Opinion
    if (isset($_POST['checkbox-opinion'])) {
        update_post_meta($post_id, 'checkbox-opinion', 'yes');
    } else {
        update_post_meta($post_id, 'checkbox-opinion', '');
    }

    //Sports
    if (isset($_POST['checkbox-sports'])) {
        update_post_meta($post_id, 'checkbox-sports', 'yes');
    } else {
        update_post_meta($post_id, 'checkbox-sports', '');
    }

    //Sunspots
    if (isset($_POST['checkbox-sunspots'])) {
        update_post_meta($post_id, 'checkbox-sunspots', 'yes');
    } else {
        update_post_meta($post_id, 'checkbox-sunspots', '');
    }

    //Multimedia
    if (isset($_POST['checkbox-multimedia'])) {
        update_post_meta($post_id, 'checkbox-multimedia', 'yes');
    } else {
        update_post_meta($post_id, 'checkbox-multimedia', '');
    }

    //Arts and Entertainment
    if (isset($_POST['checkbox-arts-entertainment'])) {
        update_post_meta($post_id, 'checkbox-arts-entertainment', 'yes');
    } else {
        update_post_meta($post_id, 'checkbox-arts-entertainment', '');
    }

    //Science
    if (isset($_POST['checkbox-science'])) {
        update_post_meta($post_id, 'checkbox-science', 'yes');
    } else {
        update_post_meta($post_id, 'checkbox-science', '');
    }


    //Dining
    if (isset($_POST['checkbox-dining'])) {
        update_post_meta($post_id, 'checkbox-dining', 'yes');
    } else {
        update_post_meta($post_id, 'checkbox-dining', '');
    }
}


function onesignal_notification_send($new_status, $old_status, $post)
{
    write_log('GOING TO SEND NOTIFICATION!');
    if ('publish' === $new_status && 'publish' !== $old_status && $post->post_type === 'post') {

        $body = new stdClass();
        $body->app_id = "c7e28bf2-698c-4a07-b56c-f2077e43c1b4";
        $body->contents = array('en' => 'English Message');
        $body->included_segments = array('Sports');
        $bodyAsJson = json_encode($body);

        $response = wp_remote_post("https://onesignal.com/api/v1/notifications", array(
            'method' => 'POST',
            'timeout' => '45',
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(
                "Content-type" => "application/json; charset=utf-8",
                "Authorization" => "Basic ZWFkMGYzYjMtNTY1ZS00YzQ2LThlNjktMzg1YzcyODA3ZGFh"
            ),
            'body' => $bodyAsJson
        ));
        write_log($repsonse['body']);
    }
}

add_action('transition_post_status', 'onesignal_notification_send', 10, 3);

if (!function_exists('write_log')) {
    function write_log($log)
    {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }
}

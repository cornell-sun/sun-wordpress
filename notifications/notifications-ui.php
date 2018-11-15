<p>Send intelligent notifications to the Cornell Sun iOS app.</p>
<div class="custom_info_wrapper">
    <div class="custom_info_item">
        <label for="custom-title">
            <?php _e('Custom Notification Title', 'notification-textdomain')?>
            <input type='text' name='custom-title' class="custom_input_item"
            value="<?php echo esc_attr(get_post_meta($post->ID, 'custom-title', true)) ?>"/>
        </label>
    </div>
    <div class="custom_info_item">
        <label for="custom-blurb">
            <?php _e('Custom Notification Blurb', 'notification-textdomain')?>
            <input type='text' name='custom-blurb' class="custom_input_item"
                value="<?php echo esc_attr(get_post_meta($post->ID, 'custom-blurb', true)) ?>"/>
        </label>
    </div>
</div>
<div class="section_header">Segments</div>

    <div class="checkbox_wrapper">
    
        <label for="checkbox-breaking-news">
        <input type="checkbox" name="checkbox-breaking-news"
            value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-breaking-news', true)); ?>" >
            <?php _e('Breaking News', 'notification-textdomain')?>
        </label>

        <label for="checkbox-local-news">
            <input type="checkbox" name="checkbox-local-news"
                value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-local-news', true)); ?>" >
            <?php _e('Local News', 'notification-textdomain')?>
        </label>

        <label for="checkbox-opinion">
            <input type="checkbox" name="checkbox-opinion"
                value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-opinion', true)); ?>" >
            <?php _e('Opinion', 'notification-textdomain')?>
        </label>

        <label for="checkbox-sports">
            <input type="checkbox" name="checkbox-sports"
                value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-sports', true)); ?>" >
            <?php _e('Sports', 'notification-textdomain')?>
        </label>

        <label for="checkbox-sunspots">
            <input type="checkbox" name="checkbox-sunspots"
            value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-sunspots', true)); ?>" >
            <?php _e('Sunspots', 'notification-textdomain')?>
        </label>

        <label for="checkbox-multimedia">
            <input type="checkbox" name="checkbox-multimedia"
            value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-multimedia', true)); ?>" >
            <?php _e('Multimedia', 'notification-textdomain')?>
        </label>

        <label for="checkbox-arts-entertainment">
            <input type="checkbox" name="checkbox-arts-entertainment"
            value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-arts-entertainment', true)); ?>" >
            <?php _e('Arts and Entertainment', 'notification-textdomain')?>
        </label>

        <label for="checkbox-science">
            <input type="checkbox" name="checkbox-science"
            value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-science', true)); ?>" >
            <?php _e('Science', 'notification-textdomain')?>
        </label>

        <label for="checkbox-dining">
            <input type="checkbox" name="checkbox-dining"
            value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-dining', true)); ?>" >
            <?php _e('Dining', 'notification-textdomain')?>
        </label>
    </div>
    <div class="section_header">Schedule Notifications</div>
    <div class="schedule_option_wrapper">
        <label for="checkbox-send-option">
            <input type="checkbox" name="checkbox-send-option"
            value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-send-option', true)); ?>" >
            <?php _e('Send immediately', 'notification-textdomain') ?>
        </label>
        <div class="send_time_wrapper">
            <label for="send-time">
                <?php _e('Time to send (optional)', 'notification-textdomain') ?>
                <input type="time" name="send-time" min="00:00" max="24:00"
                value="<?php echo esc_attr(get_post_meta($post->ID, 'send-time', true)) ?>">
            </label>
        </div>
    </div>

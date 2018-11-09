<label for="onesignal-post-class">
  <?php _e("Send notifications to the Cornell Sun iOS app.", 'example'); ?>
</label>

<label for="custom-title">
    <?php _e('Custom Notification Title', 'notification-textdomain') ?>
    <input type='text' name='custom-title' id='custom-title'
    value="<?php echo esc_attr(get_post_meta($post->ID, 'custom-title', true)) ?>"/>
</label>

<label for="custom-blurb">
    <?php _e('Custom Notification Blurb', 'notification-textdomain') ?>
    <input type='text' name='custom-blurb' id='custom-blurb'
        value="<?php echo esc_attr(get_post_meta($post->ID, 'custom-blurb', true)) ?>"/>
</label>

<label for="checkbox-breaking-news">
<input type="checkbox" name="checkbox-breaking-news" id="checkbox-breaking-news"
    value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-breaking-news', true)); ?>" />
    <?php _e('Breaking News', 'notification-textdomain') ?>
</label>

<label for="checkbox-local-news">
    <input type="checkbox" name="checkbox-local-news" id="checkbox-local-news"
        value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-local-news', true)); ?>" />
    <?php _e('Local News', 'notification-textdomain') ?>
</label>

<label for="checkbox-opinion">
    <input type="checkbox" name="checkbox-opinion" id="checkbox-opinion"
        value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-opinion', true)); ?>" />
    <?php _e('Opinion', 'notification-textdomain') ?>
</label>

<label for="checkbox-sports">
    <input type="checkbox" name="checkbox-sports" id="checkbox-sports"
        value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-sports', true)); ?>" />
    <?php _e('Sports', 'notification-textdomain') ?>
</label>

<label for="checkbox-sunspots">
    <input type="checkbox" name="checkbox-sunspots" id="checkbox-sunspots"
    value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-sunspots', true)); ?>" />
    <?php _e('Sunspots', 'notification-textdomain') ?>
</label>

<label for="checkbox-multimedia">
    <input type="checkbox" name="checkbox-multimedia" id="checkbox-multimedia"
    value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-multimedia', true)); ?>"  />
    <?php _e('Multimedia', 'notification-textdomain') ?>
</label>

<label for="checkbox-arts-entertainment">
    <input type="checkbox" name="checkbox-arts-entertainment" id="checkbox-arts-entertainment"
    value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-arts-entertainment', true)); ?>" />
    <?php _e('Arts and Entertainment', 'notification-textdomain') ?>
</label>

<label for="checkbox-science">
    <input type="checkbox" name="checkbox-science" id="checkbox-science"
    value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-science', true)); ?>" />
    <?php _e('Science', 'notification-textdomain') ?>
</label>

<label for="checkbox-dining">
    <input type="checkbox" name="checkbox-dining" id="checkbox-dining"
    value="<?php echo esc_attr(get_post_meta($post->ID, 'checkbox-dining', true)); ?>" />
    <?php _e('Dining', 'notification-textdomain') ?>
</label>

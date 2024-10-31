<?php
/**
 * Plugin Name: PushPrime
 * Plugin URI: http://pushprime.com
 * Description: PushPrime makes it super quick & easy to send mass notifications to your users on mobile and desktop, you will be up and running in less than 5 minutes.
 * Version: 1.5
 * Author: PushPrime
 * Author URI: http://pushprime.com
 * License: GPL2
 */
$pushprime_script = '
<script type=\'text/javascript\'>
window.pup=window.pup||[];(function(w,d,s){var f=d.getElementsByTagName(s)[0];var j=d.createElement(s);j.async=true;j.src=\'https://pushprime-cdn.com/clients/embed/PUSHPRIME_URL_KEY.js\';f.parentNode.insertBefore(j,f);})(window,document,\'script\');
</script>';

$pushprime_manifest = '<link rel="manifest" href="/manifest.json">';

//add_action( 'init', 'service_worker_rewrite' );
add_action( 'wp_head', 'pushprime_headercode',1 );
add_action( 'admin_menu', 'pushprime_plugin_menu' );
add_action( 'admin_init', 'pushprime_register_mysettings' );
add_action( 'admin_notices','pushprime_warn_nosettings');
add_action( 'add_meta_boxes', "pushprime_add_custom_meta_box");
add_action( 'save_post', 'pushprime_save_meta_box' );
add_action( 'publish_post', 'pushprime_process_post');

function pushprime_add_custom_meta_box(){
    add_meta_box("pushprime-meta-box", "PushPrime", "pushprime_meta_box_markup", "post", "side", "high", null);
}

function pushprime_meta_box_markup($object){
    wp_nonce_field(basename(__FILE__), "pushprime-nonce");
    ?>
    <p>Please enter the title and body to send a push notification announcing this post when the post is published</p>
    <table width="100%">
        <tr><td>Title</td></tr>
        <tr><td><input name="notification_title" type="text" value="<?php echo getNotificationTitle($object); ?>"></td></tr>
        <tr><td>Body</td></tr>
        <tr><td><textarea rows="5" placeholder="Leave empty if don't want the notification to be sent" name="notification_body"><?php echo get_post_meta($object->ID, "notification_body", true); ?></textarea></td></tr>
    </table>

    <?php
}

function getNotificationTitle($object){
    $title = get_post_meta($object->ID, "notification_title", true);
    if($title == ""){
        $title = get_bloginfo("name");
    }

    return $title;
}

function pushprime_save_meta_box($post_id){
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'pushprime-nonce' ] ) && wp_verify_nonce( $_POST[ 'pushprime-nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }

    if( isset( $_POST[ 'notification_title' ] ) ) {
        update_post_meta( $post_id, 'notification_title', sanitize_text_field( $_POST[ 'notification_title' ] ) );
    }

    if( isset( $_POST[ 'notification_body' ] ) ) {
        update_post_meta( $post_id, 'notification_body', sanitize_text_field( $_POST[ 'notification_body' ] ) );
    }
}

function pushprime_process_post($post_ID){

    $notificationSent = get_post_meta($post_ID, 'notification_sent', true);
    if($notificationSent != 'yes') {
        $title = sanitize_text_field($_POST['notification_title']);
        $body = sanitize_text_field($_POST['notification_body']);
        $url = get_permalink($post_ID);
        $thumbnail = "";
        if (has_post_thumbnail()) {
            if (function_exists("get_the_post_thumbnail_url")) {
                $thumbnail = get_the_post_thumbnail_url($post_ID, 'thumbnail');
            } else {
                $thumb_id = get_post_thumbnail_id($post_ID);
                $thumb_url = wp_get_attachment_image_src($thumb_id, 'thumbnail-size', true);
                if (count($thumb_url) > 0) {
                    $thumbnail = $thumb_url[0];
                }
            }
        }

        $apiKey = get_option("pushprime_api_key");
        if ($title <> "" && $body <> "" && $apiKey <> "") {
            $apiUrl = 'https://pushprime.com/api/send';
            $fields = array(
                'notification_title' => urlencode($title),
                'notification_description' => urlencode($body),
                'notification_url' => urlencode($url),
                'notification_icon' => urlencode($thumbnail),
            );

            $fields_string = "";
            foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }
            rtrim($fields_string, '&');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('token: ' . $apiKey));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            add_post_meta($post_ID, 'notification_sent', 'yes');
        }
    }
}

function pushprime_plugin_menu() {
    add_options_page('PushPrime', 'PushPrime', 'create_users', 'pushprime_options', 'pushprime_options_page');
}

function pushprime_register_mysettings(){
    register_setting('pushprime_options','pushprime_url_key');
    register_setting('pushprime_options','pushprime_api_key');
    register_setting('pushprime_options','pushprime_native_enabled');
}

function pushprime_headercode(){
    // runs in the header
    global $pushprime_script;
    global $pushprime_manifest;
    $pushprime_url_key = get_option('pushprime_url_key');
    $isNativeEnabled = get_option('pushprime_native_enabled');

    if($pushprime_url_key){
        echo str_replace('PUSHPRIME_URL_KEY', $pushprime_url_key, $pushprime_script); // only output if options were saved
    }

    if($isNativeEnabled == "1"){
        echo $pushprime_manifest;
    }
}

function pushprime_options_page() {
    echo '<div class="wrap">';?>
    <h2>PushPrime</h2>
    <p>You need to have a <a target="_blank" href="https://pushprime.com">PushPrime</a> account in order to use this plugin. This plugin inserts the neccessary code into your Wordpress site automatically without you having to touch anything. In order to use the plugin, you need to enter your PushPrime Website ID (Your Website ID (a string of characters) can be found in your Dashboard area after you <a target="_blank" href="https://pushprime.com/login">login</a> into your PushPrime account.)</p>

    <p>If you want to setup notification sending from your wordpress website you will have to enter an api key in the field labeled "PushPrime API Key" below, you can find/generate an api key by going to your dashboard, select the corresponding website in the dashboard and than select API Keys from the left menu</p>
    <form method="post" action="options.php">
    <?php settings_fields( 'pushprime_options' ); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Your PushPrime Website ID</th>
            <td><input type="text" name="pushprime_url_key" value="<?php echo get_option('pushprime_url_key'); ?>" /></td>
        </tr>
        <tr valign="top">
            <th scope="row">PushPrime API Key</th>
            <td><input type="text" name="pushprime_api_key" value="<?php echo get_option('pushprime_api_key'); ?>" /></td>
        </tr>
        <tr valign="top">
            <th scope="row">Add Native Tags</th>
            <td>
                <select name="pushprime_native_enabled">
                    <option value="0" <?php if(get_option('pushprime_native_enabled') == 0){?>selected<?php } ?>>No</option>
                    <option value="1" <?php if(get_option('pushprime_native_enabled') == 1){?>selected<?php } ?>>Yes</option>
                </select>
            </td>
        </tr>
    </table>

    <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
    <p>PushPrime lets you send / schedule notifications to your users, push notifications work great in driving users back to your website. It is expected that in a couple of years Push Notifications will replace email as the primary source of communication with your users.</p>
    <br /><br />
    <?php
    echo '</div>';
}

function pushprime_warn_nosettings(){
    if (!is_admin())
        return;

    $option = get_option("pushprime_url_key");
    if (!$option){
        echo "<div class='updated fade'><p><strong>PushPrime is almost ready.</strong> You must <a target=\"_blank\" href=\"https://app.pushprime.com/websites\">enter your Website ID</a> for it to work.</p></div>";
    }

    $option = get_option("pushprime_api_key");
    if (!$option){
        echo "<div class='updated fade'><p><strong>In order to send notifications from your website, you need to enter an api key, you can generate one from your website dashboard > API Keys</p></div>";
    }
}

/**
 * Functions for native opt-in files
 */


function service_worker_rewrite() {
    $url = str_replace( trailingslashit( site_url() ), '', plugins_url( '/sw.js', __FILE__ ) );
    add_rewrite_rule( '^sw\\.js$', $url, 'top' );
    flush_rewrite_rules();
}
?>
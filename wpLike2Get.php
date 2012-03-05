<?php
    /**
     * Plugin Name: wpLike2Get
     * Version: 1.1.0
     * Plugin URI: http://markusdrubba.de/wordpress/wplike2get/#utm_source=wpadmin&utm_medium=plugin&utm_campaign=wplike2getplugin
     * Description: The first true social media download-protection solution for WordPress. Hide downloads until user like, tweet or +1 your content.
     * Author: Markus Drubba
     * Author URI: http://markusdrubba.de
     * Text Domain: wpl2g
     * Domain Path: /languages
     *
     * @package WpLike2Get
     * @version 1.1.0
     * @author Markus Drubba <markus@markusdrubba.de>
     * @copyright Copyright (c) 2008 - 2012, Markus Drubba
     * @link http://markusdrubba.de/wordpress/wplike2get
     */

    /* Set up the plugin on the 'plugins_loaded' hook. */
    add_action('plugins_loaded', 'wplike2get_setup');

    /* Upgrade plugin. */
    add_action('plugins_loaded', 'wplike2get_upgrade');

    /**
     * Plugin setup function.  Loads the textdomain and plugin files where appropriate.  It also defines a few
     * constants for use throughout the plugin.
     *
     * @since 1.1.0
     */
    function wplike2get_setup()
    {

        /* Load the translation files. */
        load_plugin_textdomain('wplike2get', false, '/wplike2get/languages');

        /* Define the directory path constant. */
        define('WPLIKE2GET_DIR', trailingslashit(plugin_dir_path(__FILE__)));

        /* Define the URI path constant. */
        define('WPLIKE2GET_URI', trailingslashit(plugin_dir_url(__FILE__)));

        /* Define the plugin version. */
        define('WPLIKE2GET_VERSION', '1.1.0');

        /* if both logged in and not logged in users can send this AJAX request, add both of these actions, otherwise add only the appropriate one */
        add_action('wp_ajax_nopriv_l2g-get-download-link', 'wplike2get_get_download_link');
        add_action('wp_ajax_l2g-get-download-link', 'wplike2get_get_download_link');

        /* Load admin files if we're in the admin. */
        if (is_admin()) {
            require_once(WPLIKE2GET_DIR . 'settings.php');

            /* add l2g checkbox to wp uploader */
            add_filter('attachment_fields_to_edit', 'wplike2get_attachment_fields_to_edit', 10, 2);
            add_filter('media_send_to_editor', 'wplike2get_media_send_to_editor', 20, 3);
        } else {
            /* activate shortcode [l2g] */
            add_shortcode('l2g', 'wplike2get_shortcode');

            /* Load any scripts needed. */
            add_action('template_redirect', 'wplike2get_enqueue_script');

            /* Load any styles needed. */
            add_action('template_redirect', 'wplike2get_enqueue_style');
        }
    }

    /**
     * Get a setting for the wpLike2Get plugin.  Set $wplike2get_settings as a global variable, so we're
     * not having to use get_option() every time we need to find a setting.
     *
     * @since 1.1.0
     * @global $wplike2get wpLike2Get plugin object.
     * @param $option string|int|array Specific wpLike2Get setting we want to get.
     * @return $wplike2get->settings[$option] mixed Value of the setting input.
     */
    function wplike2get_get_setting($option = '')
    {
        global $wplike2get;

        if (!$option)
            return false;

        if (!isset($wplike2get->settings) || !is_array($wplike2get->settings))
            $wplike2get->settings = get_option('wplike2get_settings');

        return $wplike2get->settings[$option];
    }

    /**
     * Upgrade different thinks after updating the plugin files.
     *
     * @since 1.1.0
     */
    function wplike2get_upgrade()
    {
        $upgrade = false;
        $userversion = wplike2get_get_setting('version');
        if (empty($userversion)) $userversion = '1.0.0';
        if (-1 == version_compare(wplike2get_get_setting('version'), '1.1.0')) {
            $user_options = get_option('l2g_options');
            $user_options['version'] = WPLIKE2GET_VERSION;
            delete_option('l2g_options');
            $upgrade = true;
        }
        if ($upgrade)
            add_option('wplike2get_settings', $user_options);
    }

    /**
     * integrate l2g checkbox into wp uploader
     *
     * @since 1.0.0
     * @param $fields
     * @param $post
     * @return array
     */
    function wplike2get_attachment_fields_to_edit($fields, $post)
    {
        $fields['l2g_button_script'] = array(
            'label' => 'wpLike2Get',
            'input' => 'html',
            'html' => '<input type="checkbox" id="l2g-protected-' . $post->ID . '" name="l2g-protected-' . $post->ID . '" value="1" /> <label for="l2g-protected-' . $post->ID . '">' . __('Protect Download with wpLike2Get', 'wplike2get') . '</label>'
        );
        return $fields;
    }

    /**
     * insert l2g shortcode into editor when l2g checkbox set
     *
     * @since 1.0.0
     * @param $html
     * @param $send_id
     * @param $post
     * @return string
     */
    function wplike2get_media_send_to_editor($html, $send_id, $post)
    {
        if (isset($_POST['l2g-protected-' . $send_id])) {
            $html = '[l2g name="' . $post['post_title'] . '" id="' . $send_id . '"]';
        }
        return $html;
    }

    /**
     * ajax call for getting attachment-link from frontend
     *
     * @since 1.0.0
     */
    function wplike2get_get_download_link()
    {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $id = str_replace('attachment-', '', $_POST['id']);

            /* generate the response */
            $response = json_encode(array('link' => wp_get_attachment_url($id)));

            /* response output */
            header("Content-Type: application/json");
            echo $response;
            exit;
        } else {
            exit;
        }
    }

    /**
     * Load the wplike2get stylesheet if needed.
     *
     * @since 1.1.0
     */
    function wplike2get_enqueue_style() {
        wp_enqueue_style( 'wplike2get', WPLIKE2GET_URI . 'css/wplike2get.css', false, WPLIKE2GET_VERSION, 'all' );
    }

    /**
     * * Load the scripts that are used on frontend and set variables that used in custom js.
     *
     * @since 1.1.0
     */
    function wplike2get_enqueue_script()
    {
        wp_register_script('wplike2get-cookie', WPLIKE2GET_URI . 'js/jquery.cookie.js', array('jquery'), WPLIKE2GET_VERSION, true);

        if (wplike2get_get_setting('fb_activated'))
            wp_enqueue_script('wplike2get-fbjlike', WPLIKE2GET_URI . 'js/jquery.fbjlike.1.4.js', array('jquery', 'wplike2get-cookie'), WPLIKE2GET_VERSION, true);

        if (wplike2get_get_setting('tw_activated'))
            wp_enqueue_script('wplike2get-twitterbutton', WPLIKE2GET_URI . 'js/jquery.twitterbutton.1.1.js', array('jquery', 'wplike2get-cookie'), WPLIKE2GET_VERSION, true);

        if (wplike2get_get_setting('gp_activated'))
            wp_enqueue_script('wplike2get-gplusone', WPLIKE2GET_URI . 'js/jquery.gplusone.1.1.js', array('jquery', 'wplike2get-cookie'), WPLIKE2GET_VERSION, true);

        if (wplike2get_get_setting('fb_activated') || wplike2get_get_setting('tw_activated') || wplike2get_get_setting('gp_activated'))
            wp_enqueue_script('wplike2get-script', WPLIKE2GET_URI . 'js/l2g.custom.js', array('jquery', 'wplike2get-cookie'), WPLIKE2GET_VERSION, true);

        $options = array_merge(array('ajaxurl' => admin_url('admin-ajax.php'), 'cookie_suffix' => $_SERVER['REQUEST_URI']), get_option('wplike2get_settings'));
        wp_localize_script('wplike2get-script', 'l2g_options', $options);
    }

    /**
     * Generates output for shortcode.
     *
     * @since 1.0.0
     * @param $atts
     * @param null $content
     * @return string
     */
    function wplike2get_shortcode($atts, $content = null)
    {
        global $wplike2get;

        $wplike2get->shortcodeActive = true;
        extract(shortcode_atts(array(
            "id" => '',
            "name" => wplike2get_get_setting('l2g_link_identifier'),
            "twitter" => wplike2get_get_setting('tw_activated'),
            "facebook" => wplike2get_get_setting('fb_activated'),
            "gplusone" => wplike2get_get_setting('gp_activated')
        ), $atts));

        $return = '';
        if (wplike2get_is_true($twitter) || wplike2get_is_true($facebook) || wplike2get_is_true($gplusone)) {
            $return .= '<div id="l2g-download-link" style="display:none;"><a>' . $name . '</a>';

            if (wplike2get_get_setting('l2g_show_plugin_link')) $return .= '<span class="l2g-plugin-link"><a href="http://markusdrubba.de/wordpress/wplike2get/#utm_source=wpfrontend&utm_medium=pluginlink&utm_term=link&utm_campaign=wplike2getplugin">wpLike2Get</a></span>';

            $return .= '</div><div id="l2g" class="attachment-' . $id . '">';
            if (wplike2get_is_true($facebook)) {
                $return .= '<div class="facebook"></div>';
            }
            if (wplike2get_is_true($twitter)) {
                $return .= '<div class="twitter"></div>';
            }
            if (wplike2get_is_true($gplusone)) {
                $return .= '<div class="gplusone"></div>';
            }
            $return .= '<br/>';
        }
        if (wplike2get_is_true($twitter) || wplike2get_is_true($facebook) || wplike2get_is_true($gplusone)) {
            $return .= '</div>';
        }

        return $return;
    }

    /**
     * convert strings to boolean this function is needed
     * because casting to boolean is not working for:
     * (bool) "false"
     *
     * @since 1.0.0
     * @param $data the value to check for true
     * @return bool
     */
    function wplike2get_is_true($data)
    {
        if ($data === true) return $data;
        switch ($data) {
            case 1:
            case "1":
            case "true":
                return true;
                break;

            case 0:
            case "0":
            case "false":
                return false;
                break;

            default:
                return false;
                break;
        }
    }
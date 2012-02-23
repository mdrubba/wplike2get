<?php
    /*
    Plugin Name: wpLike2Get
    Version: 1.0
    Plugin URI: http://markusdrubba.de/wordpress/wplike2get/#utm_source=wpadmin&utm_medium=plugin&utm_campaign=wplike2getplugin
    Description: The first true social media download-protection solution for WordPress. Hide downloads until user like, tweet or +1 your content.
    Author: Markus Drubba
    Author URI: http://markusdrubba.de
    Text Domain: wpl2g
    Domain Path: /languages
    */
?><?php


    if (!function_exists('is_admin')) {
        header('Status: 403 Forbidden');
        header('HTTP/1.1 403 Forbidden');
        exit();
    }

    if (!class_exists('wpLike2Get')) {

        define('L2G_ADMIN_PAGE_NAME', 'wpl2g');
        define('L2G_PLUGIN_TEXTDOMAIN', 'wpl2g');
        define('L2G_VERSION', '1.0');

        load_plugin_textdomain('wpl2g', false, dirname(plugin_basename(__FILE__)) . '/languages');

        class wpLike2Get
        {
            /**
             * variables used in class
             * @var string
             */
            protected $plugin_path, $shortcodeActive, $cookie_suffix, $l2g_options, $pagehook;

            function wpLike2Get()
            {
                if (!class_exists('WPlize')) {
                    require_once('inc/WPlize.class.php');
                }

                // set path for plugin
                $this->plugin_path = WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__));
                // create option in db
                $this->l2g_options = new WPlize('l2g_options');
                // set cookie suffix
                $this->cookie_suffix = str_replace('/', '_', $_SERVER['REQUEST_URI']);

                //add filter for WordPress 2.8 changed backend box system !
                add_filter('screen_layout_columns', array(&$this, 'l2g_layout_columns'), 10, 2);

                //register callback for admin menu setup
                add_action('admin_menu', array(&$this, 'l2g_add_to_admin_menu'));

                //register the callback been used if options of page been submitted and needs to be processed
                add_action('admin_post_save_l2g_options', array(&$this, 'l2g_save'));

                // set hooks for activation and deactivation
                register_activation_hook(__FILE__, array(&$this, 'l2g_activate'));
                register_deactivation_hook(__FILE__, array(&$this, 'l2g_deactivate'));

                // activate shortcode [l2g]
                add_shortcode('l2g', array(&$this, 'l2g_shortcode'));

                // register scripts, used for frontend
                add_action('init', array(&$this, 'l2g_register_frontend_scripts'));

                // enqueue scripts for output
                add_action('wp_footer', array(&$this, 'l2g_print_frontend_scripts'));

                // add plugin action link (settings)
                add_filter('plugin_action_links', array(&$this, 'l2g_plugin_action_links'), 10, 2);

                // add l2g checkbox to wp uploader
                add_filter('attachment_fields_to_edit', array(&$this, 'l2g_attachment_fields_to_edit'), 10, 2);
                add_filter('media_send_to_editor', array(&$this, 'l2g_media_send_to_editor'), 20, 3);

                // add contextual help to admin option page
                add_filter('contextual_help', array(&$this, 'l2g_contextual_help'), 10, 3);

                // if both logged in and not logged in users can send this AJAX request,
                // add both of these actions, otherwise add only the appropriate one
                add_action('wp_ajax_nopriv_l2g-get-download-link', array(&$this, 'l2g_get_download_link'));
                add_action('wp_ajax_l2g-get-download-link', array(&$this, 'l2g_get_download_link'));

            }

            /**
             * add settings link to plugin
             *
             * @param $links
             * @param $file
             * @return array
             */
            function l2g_plugin_action_links($links, $file)
            {
                static $this_plugin;
                if (!$this_plugin) {
                    $this_plugin = plugin_basename(__FILE__);
                }
                if ($file == $this_plugin) {
                    $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . L2G_ADMIN_PAGE_NAME . '">' . __('Settings') . '</a>';
                    array_unshift($links, $settings_link);
                }
                return $links;
            }

            /**
             * for WordPress 2.8 we have to tell, that we support 2 columns
             *
             * @param $columns
             * @param $screen
             * @return array
             */
            function l2g_layout_columns($columns, $screen)
            {
                if ($screen == $this->pagehook) {
                    $columns[$this->pagehook] = 2;
                }
                return $columns;
            }

            /**
             * add the admin menu
             */
            function l2g_add_to_admin_menu()
            {
                //add our own option page, you can also add it to different sections or use your own one
                $this->pagehook = add_options_page('wpLike2Get Plugin', "wpLike2Get", 'manage_options', L2G_ADMIN_PAGE_NAME, array(&$this, 'l2g_admin_page'));
                //register  callback gets call prior your own page gets rendered
                add_action('load-' . $this->pagehook, array(&$this, 'l2g_on_page_load'));
            }

            /**
             * will be executed if wordpress core detects this page has to be rendered
             */
            function l2g_on_page_load()
            {
                //ensure, that the needed javascripts been loaded to allow drag/drop, expand/collapse and hide/show of boxes
                wp_enqueue_script('common');
                wp_enqueue_script('wp-lists');
                wp_enqueue_script('postbox');

                // load admin css
                wp_enqueue_style('wpl2g-admin-css', $this->plugin_path . 'inc/css/wpl2g_admin.css', L2G_VERSION);

                // create metaboxes for normal
                add_meta_box('wpl2g-metabox-general-options', __('General Options', L2G_PLUGIN_TEXTDOMAIN), array(&$this, 'l2g_general_options_content'), $this->pagehook, 'normal', 'core');
                add_meta_box('wpl2g-metabox-facebook-options', __('Facebook Options', L2G_PLUGIN_TEXTDOMAIN), array(&$this, 'l2g_facebook_options_content'), $this->pagehook, 'normal', 'core');
                add_meta_box('wpl2g-metabox-twitter-options', __('Twitter Options', L2G_PLUGIN_TEXTDOMAIN), array(&$this, 'l2g_twitter_options_content'), $this->pagehook, 'normal', 'core');
                add_meta_box('wpl2g-metabox-google-options', __('Google+ Options', L2G_PLUGIN_TEXTDOMAIN), array(&$this, 'l2g_google_options_content'), $this->pagehook, 'normal', 'core');

                // create metaboxes for side
                add_meta_box('wpl2g-metabox-love', __('Give a little love', L2G_PLUGIN_TEXTDOMAIN), array(&$this, 'l2g_love_box_content'), $this->pagehook, 'side', 'core');
            }

            /**
             * executed to show the plugins complete admin page
             */
            function l2g_admin_page()
            {
                //we need the global screen column value to be able to have a sidebar in WordPress 2.8
                global $screen_layout_columns;
                ?>
            <div id="l2g-options" class="wrap">
                <?php screen_icon('options-general'); ?>
                <h2><?php _e('wpLike2Get Settings', L2G_PLUGIN_TEXTDOMAIN); ?></h2>

                <div id="poststuff"
                     class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
                    <div id="side-info-column" class="inner-sidebar">
                        <?php do_meta_boxes($this->pagehook, 'side', array()); ?>
                    </div>
                    <form action="admin-post.php" method="post">
                        <?php wp_nonce_field('l2g_options'); ?>
                        <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
                        <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
                        <input type="hidden" name="action" value="save_l2g_options"/>

                        <div id="post-body" class="has-sidebar">
                            <div id="post-body-content" class="has-sidebar-content">
                                <?php do_meta_boxes($this->pagehook, 'normal', array()); ?>
                                <?php echo $this->l2g_get_submit_button(); ?>
                            </div>
                        </div>
                        <br class="clear"/>
                    </form>
                </div>
            </div>
            <script type="text/javascript">
                //<![CDATA[
                jQuery(document).ready(function ($) {
                    // close postboxes that should be closed
                    $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
                    // postboxes setup
                    postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
                });
                //]]>
            </script>

            <?php
            }

            /**
             * executed if the post arrives initiated by pressing the submit button of form
             */
            function l2g_save()
            {
                //user permission check
                if (!current_user_can('manage_options'))
                    wp_die(__('Cheatin&#8217; uh?'));
                //cross check the given referer
                check_admin_referer('l2g_options');

                //process here on $_POST validation and / or option saving
                $options = array();
                // options-general
                $options['fb_activated'] = (isset($_POST['fb_activated']) && !empty($_POST['fb_activated'])) ? true : false;
                $options['tw_activated'] = (isset($_POST['tw_activated']) && !empty($_POST['tw_activated'])) ? true : false;
                $options['gp_activated'] = (isset($_POST['gp_activated']) && !empty($_POST['gp_activated'])) ? true : false;
                $options['keep_after_reload'] = (isset($_POST['keep_after_reload']) && !empty($_POST['keep_after_reload'])) ? true : false;
                $options['l2g_link_identifier'] = (isset($_POST['l2g_link_identifier']) && !empty($_POST['l2g_link_identifier'])) ? $_POST['l2g_link_identifier'] : '';
                $options['l2g_show_plugin_link'] = (isset($_POST['l2g_show_plugin_link']) && !empty($_POST['l2g_show_plugin_link'])) ? true : false;
                $options['l2g_delete_options'] = (isset($_POST['l2g_delete_options']) && !empty($_POST['l2g_delete_options'])) ? true : false;

                // facebook-options
                $options['fb_appID'] = (isset($_POST['fb_appID']) && !empty($_POST['fb_appID'])) ? $_POST['fb_appID'] : '';
                $options['fb_userID'] = (isset($_POST['fb_userID']) && !empty($_POST['fb_userID'])) ? $_POST['fb_userID'] : '';
                $options['fb_siteName'] = (isset($_POST['fb_siteName']) && !empty($_POST['fb_siteName'])) ? $_POST['fb_siteName'] : '';
                $options['fb_buttonWidth'] = (isset($_POST['fb_buttonWidth']) && !empty($_POST['fb_buttonWidth'])) ? $_POST['fb_buttonWidth'] : '';
                $options['fb_showfaces'] = (isset($_POST['fb_showfaces']) && !empty($_POST['fb_showfaces'])) ? true : false;
                $options['fb_send'] = (isset($_POST['fb_send']) && !empty($_POST['fb_send'])) ? true : false;
                $options['fb_comments'] = (isset($_POST['fb_comments']) && !empty($_POST['fb_comments'])) ? true : false;
                $options['fb_font'] = (isset($_POST['fb_font']) && !empty($_POST['fb_font'])) ? $_POST['fb_font'] : '';
                $options['fb_layout'] = (isset($_POST['fb_layout']) && !empty($_POST['fb_layout'])) ? $_POST['fb_layout'] : '';
                $options['fb_action'] = (isset($_POST['fb_action']) && !empty($_POST['fb_action'])) ? $_POST['fb_action'] : '';
                $options['fb_colorscheme'] = (isset($_POST['fb_colorscheme']) && !empty($_POST['fb_colorscheme'])) ? $_POST['fb_colorscheme'] : '';
                $options['fb_hide'] = (isset($_POST['fb_hide']) && !empty($_POST['fb_hide'])) ? true : false;
                $options['fb_ga'] = (isset($_POST['fb_ga']) && !empty($_POST['fb_ga'])) ? true : false;
                $options['fb_ga_version'] = (isset($_POST['fb_ga_version']) && !empty($_POST['fb_ga_version'])) ? $_POST['fb_ga_version'] : '';

                // twitter-options
                $options['tw_user'] = (isset($_POST['tw_user']) && !empty($_POST['tw_user'])) ? $_POST['tw_user'] : '';
                $options['tw_user_description'] = (isset($_POST['tw_user_description']) && !empty($_POST['tw_user_description'])) ? $_POST['tw_user_description'] : '';
                $options['tw_url'] = (isset($_POST['tw_url']) && !empty($_POST['tw_url'])) ? $_POST['tw_url'] : '';
                $options['tw_count_url'] = (isset($_POST['tw_count_url']) && !empty($_POST['tw_count_url'])) ? $_POST['tw_count_url'] : '';
                $options['tw_title'] = (isset($_POST['tw_title']) && !empty($_POST['tw_title'])) ? $_POST['tw_title'] : '';
                $options['tw_layout'] = (isset($_POST['tw_layout']) && !empty($_POST['tw_layout'])) ? $_POST['tw_layout'] : '';
                $options['tw_action'] = (isset($_POST['tw_action']) && !empty($_POST['tw_action'])) ? $_POST['tw_action'] : '';
                $options['tw_lang'] = (isset($_POST['tw_lang']) && !empty($_POST['tw_lang'])) ? $_POST['tw_lang'] : '';
                $options['tw_hide'] = (isset($_POST['tw_hide']) && !empty($_POST['tw_hide'])) ? true : false;
                $options['tw_ga'] = (isset($_POST['tw_ga']) && !empty($_POST['tw_ga'])) ? true : false;
                $options['tw_ga_version'] = (isset($_POST['tw_ga_version']) && !empty($_POST['tw_ga_version'])) ? $_POST['tw_ga_version'] : '';

                // gplus-options
                $options['gp_size'] = (isset($_POST['gp_size']) && !empty($_POST['gp_size'])) ? $_POST['gp_size'] : '';
                $options['gp_count'] = (isset($_POST['gp_count']) && !empty($_POST['gp_count'])) ? true : false;
                $options['gp_url'] = (isset($_POST['gp_url']) && !empty($_POST['gp_url'])) ? $_POST['gp_url'] : '';
                $options['gp_lang'] = (isset($_POST['gp_lang']) && !empty($_POST['gp_lang'])) ? $_POST['gp_lang'] : '';
                $options['gp_hide'] = (isset($_POST['gp_hide']) && !empty($_POST['gp_hide'])) ? true : false;
                $options['gp_ga'] = (isset($_POST['gp_ga']) && !empty($_POST['gp_ga'])) ? true : false;
                $options['gp_ga_version'] = (isset($_POST['gp_ga_version']) && !empty($_POST['gp_ga_version'])) ? $_POST['gp_ga_version'] : '';

                $this->l2g_options->update_option($options);

                // redirect the post request into get request
                wp_redirect($_POST['_wp_http_referer'] . '&updated=true');
                exit;
            }

            /**
             * register scripts that are used on frontend
             * and set variables that used in custom js
             */
            function l2g_register_frontend_scripts()
            {
                wp_register_script('jquery.cookie', $this->plugin_path . 'inc/js/jquery.cookie.js', array('jquery'));

                wp_register_script('jquery.fbjlike', $this->plugin_path . 'inc/js/jquery.fbjlike.1.4.js', array('jquery', 'jquery.cookie'));

                wp_register_script('jquery.twitterbutton', $this->plugin_path . 'inc/js/jquery.twitterbutton.1.1.js', array('jquery', 'jquery.cookie'));

                wp_register_script('jquery.gplusone', $this->plugin_path . 'inc/js/jquery.gplusone.1.1.js', array('jquery', 'jquery.cookie'));

                wp_register_script('l2g.script', $this->plugin_path . 'inc/js/l2g.custom.js');

                $options = array_merge(array('ajaxurl' => admin_url('admin-ajax.php'), 'cookie_suffix' => $_SERVER['REQUEST_URI']), get_option('l2g_options'));
                wp_localize_script('l2g.script', 'l2g_options', $options);
            }

            /**
             * print scripts
             */
            function l2g_print_frontend_scripts()
            {
                // if shortcode is not used on page, do not print scripts
                if (!$this->shortcodeActive) return;

                // shortcode used on page
                if ($this->l2g_get_option('fb_activated'))
                    wp_print_scripts('jquery.fbjlike');

                if ($this->l2g_get_option('tw_activated'))
                    wp_print_scripts('jquery.twitterbutton');

                if ($this->l2g_get_option('gp_activated'))
                    wp_print_scripts('jquery.gplusone');

                if ($this->l2g_get_option('fb_activated') || $this->l2g_get_option('tw_activated') || $this->l2g_get_option('gp_activated'))
                    wp_print_scripts('l2g.script');
            }

            /**
             * integrate l2g checkbox into wp uploader
             *
             * @param $fields
             * @param $post
             * @return array
             */
            function l2g_attachment_fields_to_edit($fields, $post)
            {
                $fields['l2g_button_script'] = array(
                    'label' => 'wpLike2Get',
                    'input' => 'html',
                    'html' => '<input type="checkbox" id="l2g-protected-' . $post->ID . '" name="l2g-protected-' . $post->ID . '" value="1" /> <label for="l2g-protected-' . $post->ID . '">' . __('Protect Download with wpLike2Get', L2G_PLUGIN_TEXTDOMAIN) . '</label>'
                );
                return $fields;
            }

            /**
             * insert l2g shortcode into editor when l2g checkbox set
             *
             * @param $html
             * @param $send_id
             * @param $post
             * @return string
             */
            function l2g_media_send_to_editor($html, $send_id, $post)
            {
                if (isset($_POST['l2g-protected-' . $send_id])) {
                    $html = '[l2g name="' . $post['post_title'] . '" id="' . $send_id . '"]';
                }
                return $html;
            }

            /**
             * generate output for shortcode
             *
             * @param $atts
             * @param null $content
             * @return string
             */
            function l2g_shortcode($atts, $content = null)
            {
                $this->shortcodeActive = true;
                extract(shortcode_atts(array(
                    "id" => '',
                    "name" => $this->l2g_get_option('l2g_link_identifier'),
                    "twitter" => $this->l2g_get_option('tw_activated'),
                    "facebook" => $this->l2g_get_option('fb_activated'),
                    "gplusone" => $this->l2g_get_option('gp_activated')
                ), $atts));

                $return = '';
                if ($this->l2g_is_true($twitter) || $this->l2g_is_true($facebook) || $this->l2g_is_true($gplusone)) {
                    $return .= '<div id="l2g-download-link" style="display:none;"><a>' . $name . '</a>';

                    if ($this->l2g_get_option('l2g_show_plugin_link')) $return .= '<span class="l2g-plugin-link" style="position: absolute;bottom:0;right: 4px;font-size:8px;"><a href="http://markusdrubba.de/wordpress/wplike2get/#utm_source=wpfrontend&utm_medium=pluginlink&utm_term=link&utm_campaign=wplike2getplugin">wpLike2Get</a></span>';

                    $return .= '</div><div id="l2g" class="attachment-' . $id . '">';
                    if ($this->l2g_is_true($facebook)) {
                        $return .= '<div class="facebook"></div>';
                    }
                    if ($this->l2g_is_true($twitter)) {
                        $return .= '<div class="twitter"></div>';
                    }
                    if ($this->l2g_is_true($gplusone)) {
                        $return .= '<div class="gplusone"></div>';
                    }
                }
                if ($this->l2g_is_true($twitter) || $this->l2g_is_true($facebook) || $this->l2g_is_true($gplusone)) {
                    $return .= '</div>';
                }

                return $return;
            }

            /**
             * executed when activating the plugin
             * initilize the std options
             *
             * @return void
             */
            function l2g_activate()
            {
                $std_options = array(
                    'plugin_path' => $this->plugin_path,
                    'keep_after_reload' => false,
                    'l2g_link_identifier' => 'Download',
                    'l2g_show_plugin_link' => false,

                    'fb_activated' => true,
                    'fb_appID' => '',
                    'fb_userID' => '',
                    'fb_siteName' => '',
                    'fb_buttonWidth' => '',
                    'fb_showfaces' => false,
                    'fb_send' => false,
                    'fb_comments' => false,
                    'fb_font' => 'lucida grande',
                    'fb_layout' => 'box_count',
                    'fb_action' => 'like',
                    'fb_colorscheme' => 'light',
                    'fb_hide' => false,
                    'fb_ga' => false,
                    'fb_ga_version' => 'pageTracker',

                    'tw_activated' => true,
                    'tw_user' => false,
                    'tw_user_description' => false,
                    'tw_url' => false,
                    'tw_count_url' => false,
                    'tw_title' => false,
                    'tw_layout' => 'vertical',
                    'tw_action' => 'tweet',
                    'tw_lang' => 'en',
                    'tw_hide' => false,
                    'tw_ga' => false,
                    'tw_ga_version' => 'pageTracker',

                    'gp_activated' => true,
                    'gp_size' => 'tall',
                    'gp_count' => true,
                    'gp_url' => false,
                    'gp_lang' => 'en-US',
                    'gp_hide' => false,
                    'gp_ga' => false,
                    'gp_ga_version' => 'pageTracker'
                );
                $this->l2g_options->init_option($std_options);

            }


            /**
             * executed when deactivating the plugin
             *
             * @return void
             */
            function l2g_deactivate()
            {
                if ($this->l2g_get_option('l2g_delete_options'))
                    $this->l2g_options->delete_option('l2g_options');
            }

            /**
             * get option from db via WPlize class
             *
             * @param $option option name
             * @return mixed
             */
            function l2g_get_option($option)
            {
                return $this->l2g_options->get_option($option);
            }

            /**
             * create general metabox
             *
             * @param $data
             */
            function l2g_general_options_content($data)
            {
                ?>

            <table class="form-table">
                <tbody>
                <tr>
                    <th>
                        <label for="fb_activated"><?php _e('Facebook', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="fb_activated" id="fb_activated"
                               value="1" <?php checked($this->l2g_get_option('fb_activated'), 1) ?>>
                        <label class="description"
                               for="fb_activated"><?php _e('activate Facebook support', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="tw_activated"><?php _e('Twitter', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="tw_activated" id="tw_activated"
                               value="1" <?php checked($this->l2g_get_option('tw_activated'), 1) ?>>
                        <label class="description"
                               for="tw_activated"><?php _e('activate Twitter support', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="gp_activated"><?php _e('Google+', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="gp_activated" id="gp_activated"
                               value="1" <?php checked($this->l2g_get_option('gp_activated'), 1) ?>>
                        <label class="description"
                               for="gp_activated"><?php _e('activate Google+ support', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="keep_after_reload"><?php _e('Keep Download Link', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="keep_after_reload"
                               id="keep_after_reload" <?php checked($this->l2g_get_option('keep_after_reload'), 1) ?>>
                        <label class="description"
                               for="keep_after_reload"><?php _e('Keep Download Link after reload', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="l2g_link_identifier"><?php _e('Link name', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="text" class="normal-text" name="l2g_link_identifier" id="l2g_link_identifier"
                               value="<?php echo $this->l2g_get_option('l2g_link_identifier') ?>"/>
                        <label class="description"
                               for="l2g_link_identifier"><?php _e('Define the fallback name for the Download-Link', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="l2g_show_plugin_link"><?php _e('Give love to the Developer', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="l2g_show_plugin_link"
                               id="l2g_show_plugin_link" <?php checked($this->l2g_get_option('l2g_show_plugin_link'), 1) ?>/>
                        <label class="description"
                               for="l2g_show_plugin_link"><?php printf(__('show a small link to the <a href="%s">wpLike2Get Plugin page</a>', L2G_PLUGIN_TEXTDOMAIN), 'http://markusdrubba.de/wordpress/wplike2get/#utm_source=wpadmin&utm_medium=settingslink&utm_term=link&utm_campaign=wplike2getplugin') ?></label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="l2g_delete_options"><?php _e('Uninstall', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="l2g_delete_options"
                               id="l2g_delete_options" <?php checked($this->l2g_get_option('l2g_delete_options'), 1) ?>/>
                        <label class="description"
                               for="l2g_delete_options"><?php _e('Delete plugin options when plugin is disabled', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </td>
                </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" class="button-primary" name="wpl2g_submit" value="<?php _e('Save Changes') ?>"/>
            </p>
            <?php
            }


            /**
             * create facebook metabox
             *
             * @param $data
             */
            function l2g_facebook_options_content($data)
            {
                ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th>
                        <label for="fb_hide"><?php _e('Hide after like', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="fb_hide"
                               id="fb_hide" <?php checked($this->l2g_get_option('fb_hide'), 1) ?>>
                        <label class="description"
                               for="fb_hide"><?php _e('Hide the button after user click the like button', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Facebook like Button', L2G_PLUGIN_TEXTDOMAIN) ?>
                    </th>
                    <td class="child-table">
                        <table class="form-child-table">
                            <tr>
                                <th>
                                    <select name="fb_layout" id="fb_layout">
                                        <option value="standard" <?php selected($this->l2g_get_option('fb_layout'), 'standard') ?>>
                                            standard
                                        </option>
                                        <option value="button_count" <?php selected($this->l2g_get_option('fb_layout'), 'button_count') ?>>
                                            button_count
                                        </option>
                                        <option value="box_count" <?php selected($this->l2g_get_option('fb_layout'), 'box_count') ?>>
                                            box_count
                                        </option>
                                    </select>
                                </th>
                                <td><label class="description"
                                           for="fb_layout"><?php _e('Determines the size and amount of social context next to the button', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <select name="fb_action" id="fb_action">
                                        <option value="like" <?php selected($this->l2g_get_option('fb_action'), 'like') ?>><?php _e('like', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                        <option value="recommend" <?php selected($this->l2g_get_option('fb_action'), 'recommend') ?>><?php _e('recommend', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                    </select>
                                </th>
                                <td><label class="description"
                                           for="fb_action"><?php _e('The verb to display in the button. Currently only "like" and "recommend" are supported', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <select name="fb_font" id="fb_font">
                                        <option value="arial" <?php selected($this->l2g_get_option('fb_font'), 'arial') ?>>
                                            arial
                                        </option>
                                        <option value="lucida grande" <?php selected($this->l2g_get_option('fb_font'), 'lucida grande') ?>>
                                            lucida grande
                                        </option>
                                        <option value="segoe ui" <?php selected($this->l2g_get_option('fb_font'), 'segoe ui') ?>>
                                            segoe ui
                                        </option>
                                        <option value="tahoma" <?php selected($this->l2g_get_option('fb_font'), 'tahoma') ?>>
                                            tahoma
                                        </option>
                                        <option value="trebuchet ms" <?php selected($this->l2g_get_option('fb_font'), 'trebuchet ms') ?>>
                                            trebuchet ms
                                        </option>
                                        <option value="verdana" <?php selected($this->l2g_get_option('fb_font'), 'verdana') ?>>
                                            verdana
                                        </option>
                                    </select>
                                </th>
                                <td><label class="description"
                                           for="fb_font"><?php _e('The font of the button', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <select name="fb_colorscheme" id="fb_colorscheme">
                                        <option value="light" <?php selected($this->l2g_get_option('fb_colorscheme'), 'light') ?>><?php _e('light', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                        <option value="dark" <?php selected($this->l2g_get_option('fb_colorscheme'), 'dark') ?>><?php _e('dark', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                    </select>
                                </th>
                                <td><label class="description"
                                           for="fb_colorscheme"><?php _e('The color scheme of the button', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th><input type="text" class="small-text" name="fb_buttonWidth" id="fb_buttonWidth"
                                           value="<?php echo $this->l2g_get_option('fb_buttonWidth') ?>"/></th>
                                <td><label class="description"
                                           for="fb_buttonWidth"><?php _e('The width of the button in pixels', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th><input type="checkbox" name="fb_send"
                                           id="fb_send" <?php checked($this->l2g_get_option('fb_send'), 1) ?>></th>
                                <td><label class="description"
                                           for="fb_send"><?php _e('Include a send button', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th><input type="checkbox" name="fb_showfaces"
                                           id="fb_showfaces" <?php checked($this->l2g_get_option('fb_showfaces'), 1) ?>>
                                </th>
                                <td><label class="description"
                                           for="fb_showfaces"><?php _e('Show profile pictures below the button', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th><input type="checkbox" name="fb_comments"
                                           id="fb_comments" <?php checked($this->l2g_get_option('fb_comments'), 1) ?>>
                                </th>
                                <td><label class="description"
                                           for="fb_comments"><?php _e('Include comments below the button', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
                <tr>
                    <th><?php _e('Open Graph Settings', L2G_PLUGIN_TEXTDOMAIN) ?></th>
                    <td class="child-table">
                        <table class="form-child-table">
                            <tr>
                                <th><input type="text" class="normal-text" name="fb_appID" id="fb_appID"
                                           value="<?php echo $this->l2g_get_option('fb_appID') ?>"/></th>
                                <td><label class="description"
                                           for="fb_appID"><?php _e('Facebook App-ID', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th><input type="text" class="normal-text" name="fb_userID" id="fb_userID"
                                           value="<?php echo $this->l2g_get_option('fb_userID') ?>"/></th>
                                <td><label class="description"
                                           for="fb_userID"><?php _e('Facebook User-ID', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th><input type="text" class="normal-text" name="fb_siteName" id="fb_siteName"
                                           value="<?php echo $this->l2g_get_option('fb_siteName') ?>"/></th>
                                <td><label class="description"
                                           for="fb_siteName"><?php _e('The name of your site/your company or brand', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="fb_ga"><?php _e('Google Analytics Tracking', L2G_PLUGIN_TEXTDOMAIN) ?></label>

                        <p class="description"><?php _e('Be sure to have a working Google Analytics-Code on your page', L2G_PLUGIN_TEXTDOMAIN) ?></p>
                    </th>
                    <td class="child-table">
                        <table class="form-child-table">
                            <tr>
                                <th><input type="checkbox" name="fb_ga"
                                           id="fb_ga" <?php checked($this->l2g_get_option('fb_ga'), 1) ?>></th>
                                <td><label class="description"
                                           for="fb_ga"><?php _e('Track likes with Google Analytics', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <select name="fb_ga_version" id="fb_ga_version">
                                        <option value="_gaq" <?php selected($this->l2g_get_option('fb_ga_version'), '_gaq') ?>><?php _e('new (_gaq)', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                        <option value="pageTracker" <?php selected($this->l2g_get_option('fb_ga_version'), 'pageTracker') ?>><?php _e('old (pageTracker)', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                    </select>
                                </th>
                                <td><label class="description"
                                           for="fb_ga_version"><?php _e('Decide between old and new Tracking-Version', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php
                echo $this->l2g_get_submit_button();
            }


            /**
             * create twitter metabox
             *
             * @param $data
             */
            function l2g_twitter_options_content($data)
            {
                ?>

            <table class="form-table">
                <tbody>
                <tr>
                    <th>
                        <label for="tw_hide"><?php _e('Hide after tweet/follow', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="tw_hide"
                               id="tw_hide"  <?php checked($this->l2g_get_option('tw_hide'), 1) ?>>
                        <label class="description"
                               for="tw_hide"><?php _e('Hide the button after user sent tweet/followed', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="tw_user"><?php _e('User', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="text" class="normal-text" name="tw_user" id="tw_user"
                               value="<?php echo $this->l2g_get_option('tw_user') ?>"/>
                        <label class="description"
                               for="tw_user"><?php _e('Twitter username who is followed/mentioned', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="tw_user_description"><?php _e('User Description', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="text" class="normal-text" name="tw_user_description" id="tw_user_description"
                               value="<?php echo $this->l2g_get_option('tw_user_description') ?>"/>
                        <label class="description"
                               for="tw_user_description"><?php _e('Is shown after the user sent the tweet', L2G_PLUGIN_TEXTDOMAIN); ?></label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="tw_url"><?php _e('URL', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="text" class="normal-text" name="tw_url" id="tw_url"
                               value="<?php echo $this->l2g_get_option('tw_url') ?>"/>
                        <label class="description"
                               for="tw_url"><?php _e('The URL that is sent in tweet. Leave blank to get dynamic URL.', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="tw_title"><?php _e('Title', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="text" class="normal-text" name="tw_title" id="tw_title"
                               value="<?php echo $this->l2g_get_option('tw_title') ?>"/>
                        <label class="description"
                               for="tw_title"><?php _e('Set the title for the tweet. Leave blank to get dynamic title.', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Twitter Button', L2G_PLUGIN_TEXTDOMAIN) ?>
                    </th>
                    <td class="child-table">
                        <table class="form-child-table">
                            <tr>
                                <th>
                                    <select name="tw_layout" id="tw_layout">
                                        <option value="vertical" <?php selected($this->l2g_get_option('tw_layout'), 'vertical') ?>><?php _e('vertical', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                        <option value="horizontal" <?php selected($this->l2g_get_option('tw_layout'), 'horizontal') ?>><?php _e('horizontal', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                        <option value="none" <?php selected($this->l2g_get_option('tw_layout'), 'none') ?>><?php _e('none', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                    </select>
                                </th>
                                <td><label class="description"
                                           for="tw_layout"><?php _e('Layout is affecting only on Tweet button.', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th><input type="text" class="normal-text" name="tw_count_url" id="tw_count_url"
                                           value="<?php echo $this->l2g_get_option('tw_count_url') ?>"/></th>
                                <td><label class="description"
                                           for="tw_count_url"><?php _e('The displayed counter is based on this URL. Leave blank to get dynamic URL.', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <select name="tw_action" id="tw_action">
                                        <option value="tweet" <?php selected($this->l2g_get_option('tw_action'), 'tweet') ?>><?php _e('tweet', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                        <option value="follow" <?php selected($this->l2g_get_option('tw_action'), 'follow') ?>><?php _e('follow', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                    </select>
                                </th>
                                <td><label class="description"
                                           for="tw_action"><?php _e('Decide to use tweet or follow button', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <select name="tw_lang" id="tw_lang">
                                        <option value="en" <?php selected($this->l2g_get_option('tw_lang'), 'en') ?>>
                                            en
                                        </option>
                                        <option value="de" <?php selected($this->l2g_get_option('tw_lang'), 'de') ?>>
                                            de
                                        </option>
                                        <option value="ja" <?php selected($this->l2g_get_option('tw_lang'), 'ja') ?>>
                                            ja
                                        </option>
                                        <option value="fr" <?php selected($this->l2g_get_option('tw_lang'), 'fr') ?>>
                                            fr
                                        </option>
                                        <option value="es" <?php selected($this->l2g_get_option('tw_lang'), 'es') ?>>
                                            es
                                        </option>
                                    </select>
                                </th>
                                <td><label class="description"
                                           for="tw_lang"><?php _e('Select language for button', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="fb_ga"><?php _e('Google Analytics Tracking', L2G_PLUGIN_TEXTDOMAIN) ?></label>

                        <p class="description"><?php _e('Be sure to have a working Google Analytics-Code on your page', L2G_PLUGIN_TEXTDOMAIN) ?></p>
                    </th>
                    <td class="child-table">
                        <table class="form-child-table">
                            <tr>
                                <th><input type="checkbox" name="tw_ga"
                                           id="tw_ga" <?php checked($this->l2g_get_option('tw_ga'), 1) ?>></th>
                                <td><label class="description"
                                           for="tw_ga"><?php _e('Track likes with Google Analytics', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <select name="tw_ga_version" id="tw_ga_version">
                                        <option value="_gaq" <?php selected($this->l2g_get_option('tw_ga_version'), '_gaq') ?>><?php _e('new (_gaq)', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                        <option value="pageTracker" <?php selected($this->l2g_get_option('tw_ga_version'), 'pageTracker') ?>><?php _e('old (pageTracker)', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                    </select>
                                </th>
                                <td><label class="description"
                                           for="tw_ga_version"><?php _e('Decide between old and new Tracking-Version', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php
                echo $this->l2g_get_submit_button();
            }

            /**
             * create google+ metabox
             *
             * @param $data
             */
            function l2g_google_options_content($data)
            {
                ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th>
                        <label for="gp_hide"><?php _e('Hide after gplusedone', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="gp_hide"
                               id="gp_hide" <?php checked($this->l2g_get_option('gp_hide'), 1) ?>>
                        <label class="description"
                               for="gp_hide"><?php _e('Hide the button after user gplusedoned', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Google+ Button', L2G_PLUGIN_TEXTDOMAIN) ?></th>
                    <td class="child-table">
                        <table class="form-child-table">
                            <tr>
                                <th>
                                    <select name="gp_size" id="gp_size">
                                        <option value="small" <?php selected($this->l2g_get_option('gp_size'), 'small') ?>><?php _e('small', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                        <option value="medium" <?php selected($this->l2g_get_option('gp_size'), 'medium') ?>><?php _e('medium', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                        <option value="standard" <?php selected($this->l2g_get_option('gp_size'), 'standard') ?>><?php _e('standard', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                        <option value="tall" <?php selected($this->l2g_get_option('gp_size'), 'tall') ?>><?php _e('tall', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                    </select>
                                </th>
                                <td><label class="description"
                                           for="gp_size"><?php _e('Decide for a button size', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th><input type="checkbox" name="gp_count"
                                           id="gp_count"  <?php checked($this->l2g_get_option('gp_count'), 1) ?>></th>
                                <td><label class="description"
                                           for="gp_count"><?php _e('Show counter (not working for tall button)', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th><input type="text" class="large-text" name="gp_url" id="gp_url"
                                           value="<?php echo $this->l2g_get_option('gp_url') ?>"></th>
                                <td><label class="description"
                                           for="gp_url"><?php _e('The URL that is sent. Leave blank to get dynamic URL.', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th><input type="text" class="small-text" name="gp_lang" id="gp_lang"
                                           value="<?php echo $this->l2g_get_option('gp_lang') ?>"></th>
                                <td><label class="description"
                                           for="gp_lang"><?php _e('Set language (en-US, en-GB, de-DE, es, fr ...)', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="fb_ga"><?php _e('Google Analytics Tracking', L2G_PLUGIN_TEXTDOMAIN) ?></label>

                        <p class="description"><?php _e('Be sure to have a working Google Analytics-Code on your page', L2G_PLUGIN_TEXTDOMAIN) ?></p>
                    </th>
                    <td class="child-table">
                        <table class="form-child-table">
                            <tr>
                                <th><input type="checkbox" name="gp_ga"
                                           id="gp_ga" <?php checked($this->l2g_get_option('gp_ga'), 1) ?>></th>
                                <td><label class="description"
                                           for="gp_ga"><?php _e('Track likes with Google Analytics', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <select name="gp_ga_version" id="gp_ga_version">
                                        <option value="_gaq" <?php selected($this->l2g_get_option('gp_ga_version'), '_gaq') ?>><?php _e('new (_gaq)', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                        <option value="pageTracker" <?php selected($this->l2g_get_option('gp_ga_version'), 'pageTracker') ?>><?php _e('old (pageTracker)', L2G_PLUGIN_TEXTDOMAIN) ?></option>
                                    </select>
                                </th>
                                <td><label class="description"
                                           for="gp_ga_version"><?php _e('Decide between old and new Tracking-Version', L2G_PLUGIN_TEXTDOMAIN) ?></label>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php
                echo $this->l2g_get_submit_button();
            }

            /**
             * create lovebox metabox
             *
             * @param $data
             */
            function l2g_love_box_content($data)
            {
                ?>
            <div class="love-box">
                <p><?php _e('The development of this Plugin cost me hours of work. To give a little love to me, send $5, $10, $20, $50, $100 or what ever you want.', L2G_PLUGIN_TEXTDOMAIN); ?></p>

                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
                    <input type="hidden" name="cmd" value="_s-xclick">
                    <input type="hidden" name="hosted_button_id" value="BCVM7FZ6ZCM2A">
                    <input type="image" src="https://www.paypalobjects.com/en_US/DE/i/btn/btn_donateCC_LG.gif"
                           border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                    <img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1"
                         height="1">
                </form>
                <p><strong><?php _e('And I\'m pleased to', L2G_PLUGIN_TEXTDOMAIN); ?></strong></p>
                <ul>
                    <li>
                        <a href="http://wordpress.org/extend/plugins/wplike2get/"><?php _e('get a 5 ★ rating on WordPress.org', L2G_PLUGIN_TEXTDOMAIN); ?></a>
                    </li>
                    <li>
                        <a href="http://wordpress.org/tags/wplike2get"><?php _e('get feedback and improvement proposal', L2G_PLUGIN_TEXTDOMAIN); ?></a>
                    </li>
                    <li>
                        <a href="http://markusdrubba.de/wordpress/wplike2get/#utm_source=wpadmin&utm_medium=sidebanner&utm_term=link&utm_campaign=wplike2getplugin"><?php _e('read a review and get a link to the plugin page', L2G_PLUGIN_TEXTDOMAIN); ?></a>
                    </li>
                </ul>
            </div>
            <?php
            }

            /**
             * adding contextual help to admin menu
             *
             * @param $contextual_help
             * @param $screen_id
             * @param $screen
             * @return mixed
             */
            function l2g_contextual_help($contextual_help, $screen_id, $screen)
            {
                if ($screen_id == $this->pagehook) {

                    //$contextual_help = '<p>This is where I would provide help to the user on how everything in my admin panel works. Formatted HTML works fine in here too.</p>';
                    get_current_screen()->add_help_tab(array(
                        'id' => 'overview',
                        'title' => __('Overview'),
                        'content' => '<p>coming soon</p>'
                    ));
                    get_current_screen()->add_help_tab(array(
                        'id' => 'troubleshooting',
                        'title' => __('Troubleshooting'),
                        'content' => '<p>coming soon</p>'
                    ));

                    get_current_screen()->set_help_sidebar('<p>coming soon</p>');
                }
                return $contextual_help;
            }

            /**
             * creating submit button
             *
             * @return string
             */
            function l2g_get_submit_button()
            {
                if (function_exists('get_submit_button'))
                    return get_submit_button();

                return '<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="' . __('Save Changes') . '"></p>';
            }

            /**
             * ajax call for getting attachment-link from frontend
             */
            function l2g_get_download_link()
            {
                if (isset($_POST['id']) && !empty($_POST['id'])) {
                    $id = str_replace('attachment-', '', $_POST['id']);

                    // generate the response
                    $response = json_encode(array('link' => wp_get_attachment_url($id)));

                    // response output
                    header("Content-Type: application/json");
                    echo $response;
                    exit;
                } else {
                    exit;
                }
            }

            /**
             * convert strings to boolean
             * this function is needed because casting to boolean
             * is not working for:
             * (bool) "false"
             *
             * @param $data
             * @return bool
             */
            function l2g_is_true($data)
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
        }
    }

    $GLOBALS['wpLike2Get'] = new wpLike2Get();

?>
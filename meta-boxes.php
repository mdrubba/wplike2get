<?php
    /**
     * This file holds all the meta boxes and the function to create the meta boxes for the wpLike2Get settings
     * page in the admin.
     *
     * @package WpLike2Get
     */

    /* Add the meta boxes for the settings page on the 'add_meta_boxes' hook. */
    add_action('add_meta_boxes', 'wplike2get_create_meta_boxes');

    /**
     * Adds all the meta boxes to the wpLike2Get settings page in the WP admin.
     *
     * @since 1.1.0
     */
    function wplike2get_create_meta_boxes()
    {
        global $wplike2get;

        /* Add the 'About' meta box. */
        add_meta_box('wplike2get-about', __('About', 'wplike2get'), 'wplike2get_meta_box_display_about', $wplike2get->settings_page, 'side', 'default');

        /* Add the 'Donate' meta box. */
        add_meta_box('wplike2get-donate', __('Like this plugin?', 'wplike2get'), 'wplike2get_meta_box_display_donate', $wplike2get->settings_page, 'side', 'high');

        /* Add the 'Support' meta box. */
        add_meta_box('wplike2get-support', __('Support', 'wplike2get'), 'wplike2get_meta_box_display_support', $wplike2get->settings_page, 'side', 'low');

	    /**
	     * @since 1.2.2
	     */
	    /* Add the 'Wishlist' meta box. */
        add_meta_box('wplike2get-wishlist', __('Wishlist', 'wplike2get'), 'wplike2get_meta_box_display_wishlist', $wplike2get->settings_page, 'side', 'low');

        /* Add the 'Support' meta box. */
        add_meta_box('wplike2get-general', __('General', 'wplike2get'), 'wplike2get_meta_box_display_general', $wplike2get->settings_page, 'normal', 'high');

        /* Add the 'Support' meta box. */
        add_meta_box('wplike2get-facebook', __('Facebook', 'wplike2get'), 'wplike2get_meta_box_display_facebook', $wplike2get->settings_page, 'normal', 'high');

        /* Add the 'Support' meta box. */
        add_meta_box('wplike2get-twitter', __('Twitter', 'wplike2get'), 'wplike2get_meta_box_display_twitter', $wplike2get->settings_page, 'normal', 'high');

        /* Add the 'Support' meta box. */
        add_meta_box('wplike2get-google', __('Google', 'wplike2get'), 'wplike2get_meta_box_display_google', $wplike2get->settings_page, 'normal', 'high');

    }

    /**
     * Display the love meta box
     *
     * @since 1.0.0
     */
    function wplike2get_meta_box_display_donate()
    {
        ?>
    <div class="love-box">
        <p><strong><?php _e('Here\'s how you can give back:', 'wplike2get'); ?></strong></p>
        <ul>
            <li>
                <a href="http://wordpress.org/extend/plugins/wplike2get/"><?php _e('Give the plugin a 5 ★ rating on WordPress.org', 'wplike2get'); ?></a>
            </li>
            <li>
                <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=BCVM7FZ6ZCM2A"><?php _e('Donate a few dollars.', 'wplike2get'); ?></a>
            </li>
            <li>
                <a href="http://wordpress.org/tags/wplike2get"><?php _e('Give feedback and improvement proposal', 'wplike2get'); ?></a>
            </li>
            <li>
                <a href="http://markusdrubba.de/wordpress/wplike2get/#utm_source=wpadmin&utm_medium=sidebanner&utm_term=link&utm_campaign=wplike2getplugin"><?php _e('Write a review and give a link to the plugin page', 'wplike2get'); ?></a>
            </li>
        </ul>
    </div>
    <?php
    }

    /**
     * Displays the about plugin meta box.
     *
     * @since 1.1.0
     */
    function wplike2get_meta_box_display_about($object, $box)
    {

        $plugin_data = get_plugin_data(WPLIKE2GET_DIR . 'wpLike2Get.php'); ?>

    <p>
        <strong><?php _e('Version:', 'wplike2get'); ?></strong> <?php echo $plugin_data['Version']; ?>
    </p>
    <p>
        <strong><?php _e('Description:', 'wplike2get'); ?></strong>
    </p>
    <p>
        <?php echo $plugin_data['Description']; ?>
    </p>
    <?php
    }

    /**
     * Displays the support meta box.
     *
     * @since 1.1.0
     */
    function wplike2get_meta_box_display_support($object, $box)
    {
        ?>
    <p>
        <?php printf(__('Support for this plugin is provided via the support forums at %1$s. If you need any help using it, please ask your support questions there.', 'wplike2get'), '<a href="http://wordpress.org/tags/wplike2get" title="' . __('wpLike2Get Support Forums', 'wplike2get') . '">' . __('wpLike2Get', 'wplike2get') . '</a>'); ?>
    </p>
	<?php
    }

	/**
	 * Displays the support meta box.
	 *
	 * @since 1.2.2
	 */
	function wplike2get_meta_box_display_wishlist($object, $box)
	{
	    ?>
		<SCRIPT charset="utf-8" type="text/javascript" src="http://ws.amazon.de/widgets/q?ServiceVersion=20070822&MarketPlace=DE&ID=V20070822/DE/drumbadebrauc-21/8004/c3b192dc-0fab-41f9-837e-91fb908a1151"> </SCRIPT> <NOSCRIPT><A HREF="http://ws.amazon.de/widgets/q?ServiceVersion=20070822&MarketPlace=DE&ID=V20070822%2FDE%2Fdrumbadebrauc-21%2F8004%2Fc3b192dc-0fab-41f9-837e-91fb908a1151&Operation=NoScript">Amazon.de Widgets</A></NOSCRIPT>
		<?php
	}

    /**
     * Display the general meta box
     *
     * @since 1.1.0
     */
    function wplike2get_meta_box_display_general()
    {
        ?>

    <table class="form-table">
        <tbody>
        <tr>
            <th>
                <label for="fb_activated"><?php _e('Facebook', 'wplike2get') ?></label>
            </th>
            <td>
                <input type="checkbox" name="wplike2get_settings[fb_activated]" id="fb_activated"
                       value="1"<?php checked(wplike2get_get_setting('fb_activated'), 1) ?>>
                <label class="description"
                       for="fb_activated"><?php _e('activate Facebook support', 'wplike2get') ?></label>
            </td>
        </tr>
        <tr>
            <th>
                <label for="tw_activated"><?php _e('Twitter', 'wplike2get') ?></label>
            </th>
            <td>
                <input type="checkbox" name="wplike2get_settings[tw_activated]" id="tw_activated"
                       value="1"<?php checked(wplike2get_get_setting('tw_activated'), 1) ?>>
                <label class="description"
                       for="tw_activated"><?php _e('activate Twitter support', 'wplike2get') ?></label>
            </td>
        </tr>
        <tr>
            <th>
                <label for="gp_activated"><?php _e('Google+', 'wplike2get') ?></label>
            </th>
            <td>
                <input type="checkbox" name="wplike2get_settings[gp_activated]" id="gp_activated"
                       value="1"<?php checked(wplike2get_get_setting('gp_activated'), 1) ?>>
                <label class="description"
                       for="gp_activated"><?php _e('activate Google+ support', 'wplike2get') ?></label>
            </td>
        </tr>
        <tr>
            <th>
                <label for="keep_after_reload"><?php _e('Keep Download Link', 'wplike2get') ?></label>
            </th>
            <td>
                <input type="checkbox" name="wplike2get_settings[keep_after_reload]" id="keep_after_reload"
                       value="1"<?php checked(wplike2get_get_setting('keep_after_reload'), 1) ?>>
                <label class="description"
                       for="keep_after_reload"><?php _e('Keep Download Link after reload', 'wplike2get') ?></label>
            </td>
        </tr>
        <tr>
            <th>
                <label for="l2g_link_identifier"><?php _e('Link name', 'wplike2get') ?></label>
            </th>
            <td>
                <input type="text" class="normal-text" name="wplike2get_settings[l2g_link_identifier]"
                       id="l2g_link_identifier"
                       value="<?php echo wplike2get_get_setting('l2g_link_identifier') ?>"/>
                <label class="description"
                       for="l2g_link_identifier"><?php _e('Define the fallback name for the Download-Link', 'wplike2get') ?></label>
            </td>
        </tr>
        <tr>
            <th>
                <label for="l2g_single_activation"><?php _e('Show on single only', 'wplike2get') ?></label>
            </th>
            <td>
	            <input type="checkbox" name="wplike2get_settings[l2g_single_activation]" id="l2g_single_activation"
	                                   value="1"<?php checked(wplike2get_get_setting('l2g_single_activation'), 1) ?>/>
                <label class="description"
                       for="l2g_single_activation"><?php _e('Show the Button(s) only on single views, not on home, or archive pages', 'wplike2get') ?></label>
            </td>
        </tr>
        <tr>
            <th>
                <label for="l2g_show_plugin_link"><?php _e('Give love to the Developer', 'wplike2get') ?></label>
            </th>
            <td>
                <input type="checkbox" name="wplike2get_settings[l2g_show_plugin_link]" id="l2g_show_plugin_link"
                       value="1"<?php checked(wplike2get_get_setting('l2g_show_plugin_link'), 1) ?>/>
                <label class="description"
                       for="l2g_show_plugin_link"><?php printf(__('show a small link to the <a href="%s">wpLike2Get Plugin page</a>', 'wplike2get'), 'http://markusdrubba.de/wordpress/wplike2get/#utm_source=wpadmin&utm_medium=settingslink&utm_term=link&utm_campaign=wplike2getplugin') ?></label>
            </td>
        </tr>
        </tbody>
    </table>
    <?php
    }

    /**
     * Display the facebook meta box
     *
     * @since 1.1.0
     */
    function wplike2get_meta_box_display_facebook()
    {
        ?>
    <table class="form-table">
        <tbody>
        <tr>
            <th>
                <label for="fb_hide"><?php _e('Hide after like', 'wplike2get') ?></label>
            </th>
            <td>
                <input type="checkbox" name="wplike2get_settings[fb_hide]" id="fb_hide"
                       value="1"<?php checked(wplike2get_get_setting('fb_hide'), 1) ?>>
                <label class="description"
                       for="fb_hide"><?php _e('Hide the button after user click the like button', 'wplike2get') ?></label>
            </td>
        </tr>
        <tr>
            <th>
                <?php _e('Facebook like Button', 'wplike2get') ?>
            </th>
            <td class="child-table">
                <table class="form-child-table">
                    <tr>
                        <th>
                            <select name="wplike2get_settings[fb_layout]" id="fb_layout">
                                <option value="standard" <?php selected(wplike2get_get_setting('fb_layout'), 'standard') ?>>
                                    standard
                                </option>
                                <option value="button_count" <?php selected(wplike2get_get_setting('fb_layout'), 'button_count') ?>>
                                    button_count
                                </option>
                                <option value="box_count" <?php selected(wplike2get_get_setting('fb_layout'), 'box_count') ?>>
                                    box_count
                                </option>
                            </select>
                        </th>
                        <td><label class="description"
                                   for="fb_layout"><?php _e('Determines the size and amount of social context next to the button', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <select name="wplike2get_settings[fb_action]" id="fb_action">
                                <option value="like" <?php selected(wplike2get_get_setting('fb_action'), 'like') ?>><?php _e('like', 'wplike2get') ?></option>
                                <option value="recommend" <?php selected(wplike2get_get_setting('fb_action'), 'recommend') ?>><?php _e('recommend', 'wplike2get') ?></option>
                            </select>
                        </th>
                        <td><label class="description"
                                   for="fb_action"><?php _e('The verb to display in the button. Currently only "like" and "recommend" are supported', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <select name="wplike2get_settings[fb_font]" id="fb_font">
                                <option value="arial" <?php selected(wplike2get_get_setting('fb_font'), 'arial') ?>>
                                    arial
                                </option>
                                <option value="lucida grande" <?php selected(wplike2get_get_setting('fb_font'), 'lucida grande') ?>>
                                    lucida grande
                                </option>
                                <option value="segoe ui" <?php selected(wplike2get_get_setting('fb_font'), 'segoe ui') ?>>
                                    segoe ui
                                </option>
                                <option value="tahoma" <?php selected(wplike2get_get_setting('fb_font'), 'tahoma') ?>>
                                    tahoma
                                </option>
                                <option value="trebuchet ms" <?php selected(wplike2get_get_setting('fb_font'), 'trebuchet ms') ?>>
                                    trebuchet ms
                                </option>
                                <option value="verdana" <?php selected(wplike2get_get_setting('fb_font'), 'verdana') ?>>
                                    verdana
                                </option>
                            </select>
                        </th>
                        <td><label class="description"
                                   for="fb_font"><?php _e('The font of the button', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <select name="wplike2get_settings[fb_colorscheme]" id="fb_colorscheme">
                                <option value="light" <?php selected(wplike2get_get_setting('fb_colorscheme'), 'light') ?>><?php _e('light', 'wplike2get') ?></option>
                                <option value="dark" <?php selected(wplike2get_get_setting('fb_colorscheme'), 'dark') ?>><?php _e('dark', 'wplike2get') ?></option>
                            </select>
                        </th>
                        <td><label class="description"
                                   for="fb_colorscheme"><?php _e('The color scheme of the button', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><input type="text" class="small-text" name="wplike2get_settings[fb_buttonWidth]"
                                   id="fb_buttonWidth"
                                   value="<?php echo wplike2get_get_setting('fb_buttonWidth') ?>"/></th>
                        <td><label class="description"
                                   for="fb_buttonWidth"><?php _e('The width of the button in pixels', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <input type="text" class="small-text" name="wplike2get_settings[fb_lang]" id="fb_lang" value="<?php echo wplike2get_get_setting( 'fb_lang' ) ?>">
                        </th>
                        <td>
                            <label class="description" for="fb_lang"><?php _e( 'Set language (en_US, en_GB, de_DE, es_ES, fr_FR ...)', 'wplike2get' ) ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><input type="checkbox" name="wplike2get_settings[fb_send]" id="fb_send"
                                   value="1"<?php checked(wplike2get_get_setting('fb_send'), 1) ?>></th>
                        <td><label class="description"
                                   for="fb_send"><?php _e('Include a send button', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><input type="checkbox" name="wplike2get_settings[fb_showfaces]" id="fb_showfaces"
                                   value="1"<?php checked(wplike2get_get_setting('fb_showfaces'), 1) ?>>
                        </th>
                        <td><label class="description"
                                   for="fb_showfaces"><?php _e('Show profile pictures below the button', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><input type="checkbox" name="wplike2get_settings[fb_comments]" id="fb_comments"
                                   value="1"<?php checked(wplike2get_get_setting('fb_comments'), 1) ?>>
                        </th>
                        <td><label class="description"
                                   for="fb_comments"><?php _e('Include comments below the button', 'wplike2get') ?></label>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
        <tr>
            <th><?php _e('Open Graph Settings', 'wplike2get') ?></th>
            <td class="child-table">
                <table class="form-child-table">
                    <tr>
                        <th><input type="text" class="normal-text" name="wplike2get_settings[fb_appID]" id="fb_appID"
                                   value="<?php echo wplike2get_get_setting('fb_appID') ?>"/></th>
                        <td><label class="description"
                                   for="fb_appID"><?php _e('Facebook App-ID', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><input type="text" class="normal-text" name="wplike2get_settings[fb_userID]" id="fb_userID"
                                   value="<?php echo wplike2get_get_setting('fb_userID') ?>"/></th>
                        <td><label class="description"
                                   for="fb_userID"><?php _e('Facebook User-ID', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><input type="text" class="normal-text" name="wplike2get_settings[fb_siteName]"
                                   id="fb_siteName"
                                   value="<?php echo wplike2get_get_setting('fb_siteName') ?>"/></th>
                        <td><label class="description"
                                   for="fb_siteName"><?php _e('The name of your site/your company or brand', 'wplike2get') ?></label>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th>
                <label for="fb_ga"><?php _e('Google Analytics Tracking', 'wplike2get') ?></label>
            </th>
            <td class="child-table">
                <table class="form-child-table">
                    <tr>
                        <th>
	                        <input type="checkbox" name="wplike2get_settings[fb_ga]" id="fb_ga"
                                   value="1"<?php checked(wplike2get_get_setting('fb_ga'), 1) ?>></th>
                        <td><label class="description"
                                   for="fb_ga"><?php _e('Track likes with Google Analytics', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <select name="wplike2get_settings[fb_ga_version]" id="fb_ga_version">
                                <option value="_gaq" <?php selected(wplike2get_get_setting('fb_ga_version'), '_gaq') ?>><?php _e('new (_gaq)', 'wplike2get') ?></option>
                                <option value="pageTracker" <?php selected(wplike2get_get_setting('fb_ga_version'), 'pageTracker') ?>><?php _e('old (pageTracker)', 'wplike2get') ?></option>
                            </select>
                        </th>
                        <td><label class="description"
                                   for="fb_ga_version"><?php _e('Decide between old and new Tracking-Version', 'wplike2get') ?></label>
                        </td>
                    </tr>
	                <tr>
		                <td colspan="2"><p class="description"><?php _e('Be sure to have a working Google Analytics-Code on your page', 'wplike2get') ?></p></td>
	                </tr>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
    <?php
    }


    /**
     * Display the twitter meta box
     *
     * @since 1.1.0
     */
    function wplike2get_meta_box_display_twitter()
    {
        ?>

    <table class="form-table">
        <tbody>
        <tr>
            <th>
                <label for="tw_hide"><?php _e('Hide after tweet/follow', 'wplike2get') ?></label>
            </th>
            <td>
                <input type="checkbox" name="wplike2get_settings[tw_hide]" id="tw_hide"
                       value="1"<?php checked(wplike2get_get_setting('tw_hide'), 1) ?>>
                <label class="description"
                       for="tw_hide"><?php _e('Hide the button after user sent tweet/followed', 'wplike2get') ?></label>
            </td>
        </tr>
        <tr>
            <th>
                <label for="tw_user"><?php _e('User', 'wplike2get') ?></label>
            </th>
            <td>
                <input type="text" class="normal-text" name="wplike2get_settings[tw_user]" id="tw_user"
                       value="<?php echo wplike2get_get_setting('tw_user') ?>"/>
                <label class="description"
                       for="tw_user"><?php _e('Twitter username who is followed/mentioned', 'wplike2get') ?></label>
            </td>
        </tr>
        <tr>
            <th>
                <label for="tw_user_description"><?php _e('User Description', 'wplike2get') ?></label>
            </th>
            <td>
                <input type="text" class="normal-text" name="wplike2get_settings[tw_user_description]"
                       id="tw_user_description"
                       value="<?php echo wplike2get_get_setting('tw_user_description') ?>"/>
                <label class="description"
                       for="tw_user_description"><?php _e('Is shown after the user sent the tweet', 'wplike2get'); ?></label>
            </td>
        </tr>
        <tr>
            <th>
                <label for="tw_url"><?php _e('URL', 'wplike2get') ?></label>
            </th>
            <td>
                <input type="text" class="normal-text" name="wplike2get_settings[tw_url]" id="tw_url"
                       value="<?php echo wplike2get_get_setting('tw_url') ?>"/>
                <label class="description"
                       for="tw_url"><?php _e('The URL that is sent in tweet. Leave blank to get dynamic URL.', 'wplike2get') ?></label>
            </td>
        </tr>
        <tr>
            <th>
                <label for="tw_title"><?php _e('Title', 'wplike2get') ?></label>
            </th>
            <td>
                <input type="text" class="normal-text" name="wplike2get_settings[tw_title]" id="tw_title"
                       value="<?php echo wplike2get_get_setting('tw_title') ?>"/>
                <label class="description"
                       for="tw_title"><?php _e('Set the title for the tweet. Leave blank to get dynamic title.', 'wplike2get') ?></label>
            </td>
        </tr>
        <tr>
            <th>
                <?php _e('Twitter Button', 'wplike2get') ?>
            </th>
            <td class="child-table">
                <table class="form-child-table">
                    <tr>
                        <th>
                            <select name="wplike2get_settings[tw_layout]" id="tw_layout">
                                <option value="vertical" <?php selected(wplike2get_get_setting('tw_layout'), 'vertical') ?>><?php _e('vertical', 'wplike2get') ?></option>
                                <option value="horizontal" <?php selected(wplike2get_get_setting('tw_layout'), 'horizontal') ?>><?php _e('horizontal', 'wplike2get') ?></option>
                                <option value="none" <?php selected(wplike2get_get_setting('tw_layout'), 'none') ?>><?php _e('none', 'wplike2get') ?></option>
                            </select>
                        </th>
                        <td><label class="description"
                                   for="tw_layout"><?php _e('Layout is affecting only on Tweet button.', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><input type="text" class="normal-text" name="wplike2get_settings[tw_count_url]"
                                   id="tw_count_url"
                                   value="<?php echo wplike2get_get_setting('tw_count_url') ?>"/></th>
                        <td><label class="description"
                                   for="tw_count_url"><?php _e('The displayed counter is based on this URL. Leave blank to get dynamic URL.', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <select name="wplike2get_settings[tw_action]" id="tw_action">
                                <option value="tweet" <?php selected(wplike2get_get_setting('tw_action'), 'tweet') ?>><?php _e('tweet', 'wplike2get') ?></option>
                                <option value="follow" <?php selected(wplike2get_get_setting('tw_action'), 'follow') ?>><?php _e('follow', 'wplike2get') ?></option>
                            </select>
                        </th>
                        <td><label class="description"
                                   for="tw_action"><?php _e('Decide to use tweet or follow button', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <select name="wplike2get_settings[tw_lang]" id="tw_lang">
                                <option value="en" <?php selected(wplike2get_get_setting('tw_lang'), 'en') ?>>
                                    en
                                </option>
                                <option value="de" <?php selected(wplike2get_get_setting('tw_lang'), 'de') ?>>
                                    de
                                </option>
                                <option value="ja" <?php selected(wplike2get_get_setting('tw_lang'), 'ja') ?>>
                                    ja
                                </option>
                                <option value="fr" <?php selected(wplike2get_get_setting('tw_lang'), 'fr') ?>>
                                    fr
                                </option>
                                <option value="es" <?php selected(wplike2get_get_setting('tw_lang'), 'es') ?>>
                                    es
                                </option>
                            </select>
                        </th>
                        <td><label class="description"
                                   for="tw_lang"><?php _e('Select language for button', 'wplike2get') ?></label>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th>
                <label for="fb_ga"><?php _e('Google Analytics Tracking', 'wplike2get') ?></label>
            </th>
            <td class="child-table">
                <table class="form-child-table">
                    <tr>
                        <th><input type="checkbox" name="wplike2get_settings[tw_ga]" id="tw_ga"
                                   value="1"<?php checked(wplike2get_get_setting('tw_ga'), 1) ?>></th>
                        <td><label class="description"
                                   for="tw_ga"><?php _e('Track likes with Google Analytics', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <select name="wplike2get_settings[tw_ga_version]" id="tw_ga_version">
                                <option value="_gaq" <?php selected(wplike2get_get_setting('tw_ga_version'), '_gaq') ?>><?php _e('new (_gaq)', 'wplike2get') ?></option>
                                <option value="pageTracker" <?php selected(wplike2get_get_setting('tw_ga_version'), 'pageTracker') ?>><?php _e('old (pageTracker)', 'wplike2get') ?></option>
                            </select>
                        </th>
                        <td><label class="description"
                                   for="tw_ga_version"><?php _e('Decide between old and new Tracking-Version', 'wplike2get') ?></label>
                        </td>
                    </tr>
	                <tr>
		                <td colspan="2"><p class="description"><?php _e('Be sure to have a working Google Analytics-Code on your page', 'wplike2get') ?></p></td>
	                </tr>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
    <?php
    }

    /**
     * Display the google meta box
     *
     * @since 1.1.0
     */
    function wplike2get_meta_box_display_google()
    {
        ?>
    <table class="form-table">
        <tbody>
        <tr>
            <th>
                <label for="gp_hide"><?php _e('Hide after gplusedone', 'wplike2get') ?></label>
            </th>
            <td>
                <input type="checkbox" name="wplike2get_settings[gp_hide]" id="gp_hide"
                       value="1"<?php checked(wplike2get_get_setting('gp_hide'), 1) ?>>
                <label class="description"
                       for="gp_hide"><?php _e('Hide the button after user gplusedoned', 'wplike2get') ?></label>
            </td>
        </tr>
        <tr>
            <th><?php _e('Google+ Button', 'wplike2get') ?></th>
            <td class="child-table">
                <table class="form-child-table">
                    <tr>
                        <th>
                            <select name="wplike2get_settings[gp_size]" id="gp_size">
                                <option value="small" <?php selected(wplike2get_get_setting('gp_size'), 'small') ?>><?php _e('small', 'wplike2get') ?></option>
                                <option value="medium" <?php selected(wplike2get_get_setting('gp_size'), 'medium') ?>><?php _e('medium', 'wplike2get') ?></option>
                                <option value="standard" <?php selected(wplike2get_get_setting('gp_size'), 'standard') ?>><?php _e('standard', 'wplike2get') ?></option>
                                <option value="tall" <?php selected(wplike2get_get_setting('gp_size'), 'tall') ?>><?php _e('tall', 'wplike2get') ?></option>
                            </select>
                        </th>
                        <td><label class="description"
                                   for="gp_size"><?php _e('Decide for a button size', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><input type="checkbox" name="wplike2get_settings[gp_count]" id="gp_count"
                                   value="1"<?php checked(wplike2get_get_setting('gp_count'), 1) ?>></th>
                        <td><label class="description"
                                   for="gp_count"><?php _e('Show counter (not working for tall button)', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><input type="text" class="large-text" name="wplike2get_settings[gp_url]" id="gp_url"
                                   value="<?php echo wplike2get_get_setting('gp_url') ?>"></th>
                        <td><label class="description"
                                   for="gp_url"><?php _e('The URL that is sent. Leave blank to get dynamic URL.', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><input type="text" class="small-text" name="wplike2get_settings[gp_lang]" id="gp_lang"
                                   value="<?php echo wplike2get_get_setting('gp_lang') ?>"></th>
                        <td><label class="description"
                                   for="gp_lang"><?php _e('Set language (en-US, en-GB, de-DE, es, fr ...)', 'wplike2get') ?></label>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th>
                <label for="fb_ga"><?php _e('Google Analytics Tracking', 'wplike2get') ?></label>
            </th>
            <td class="child-table">
                <table class="form-child-table">
                    <tr>
                        <th><input type="checkbox" name="wplike2get_settings[gp_ga]" id="gp_ga"
                                   value="1"<?php checked(wplike2get_get_setting('gp_ga'), 1) ?>></th>
                        <td><label class="description"
                                   for="gp_ga"><?php _e('Track likes with Google Analytics', 'wplike2get') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <select name="wplike2get_settings[gp_ga_version]" id="gp_ga_version">
                                <option value="_gaq" <?php selected(wplike2get_get_setting('gp_ga_version'), '_gaq') ?>><?php _e('new (_gaq)', 'wplike2get') ?></option>
                                <option value="pageTracker" <?php selected(wplike2get_get_setting('gp_ga_version'), 'pageTracker') ?>><?php _e('old (pageTracker)', 'wplike2get') ?></option>
                            </select>
                        </th>
                        <td><label class="description"
                                   for="gp_ga_version"><?php _e('Decide between old and new Tracking-Version', 'wplike2get') ?></label>
                        </td>
                    </tr>
	                <tr>
		                <td colspan="2"><p class="description"><?php _e('Be sure to have a working Google Analytics-Code on your page', 'wplike2get') ?></p></td>
	                </tr>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
    <?php
    }